//! \file PdbReader.cpp
//! \brief PDB file reading functionality.
//! \author Oleg Krivtsov
//! \date 2011

#include "stdafx.h"
#include "PdbReader.h"
#include "Misc.h"
#include "strconv.h"

//-----------------------------------------------
// CPdbReader impl
//-----------------------------------------------

CPdbReader::CPdbReader()
{
	m_bInitialized = FALSE;
	m_bAMD64 = FALSE;
	m_pHeadersStream = NULL;
	m_pDebugInfoStream = NULL;
	m_pSymbolStream = NULL;
	m_pSectionMapStream = NULL;
}

CPdbReader::~CPdbReader()
{
	Destroy();
}


BOOL CPdbReader::Init(std::wstring sFileName)
{
	CAutoLock lock(&m_AccessLock);

	// If already initialized, destroy
	if(m_bInitialized)
		Destroy();

	// Parse multi-stream file (MSF) structure
	if(!m_MsfFile.Init(sFileName))
		return FALSE; // Error reading MSF file

	CPdbDebugInfoStream* pDBI = GetDebugInfoStream();
	if(pDBI==NULL)
		return FALSE; // Error reading DBI stream

	m_bAMD64 = pDBI->GetMachineType()==34404;

	// Save file name
	m_sFileName = sFileName;

	m_bInitialized = TRUE;

	// Done
	return TRUE;
}

BOOL CPdbReader::IsInitialized()
{
	CAutoLock lock(&m_AccessLock);
	return m_bInitialized;
}

void CPdbReader::Destroy()
{
	CAutoLock lock(&m_AccessLock);

	if(!m_bInitialized)
		return; // Not initialized yet

	// Clear file name
	m_sFileName.clear();

	// Clear PDB headers stream
	if(m_pHeadersStream)
	{
		delete m_pHeadersStream;
		m_pHeadersStream = NULL;
	}

	// Clear debug info stream
	if(m_pDebugInfoStream)
	{
		delete m_pDebugInfoStream;
		m_pDebugInfoStream = NULL;
	}

	// Clear symbol stream
	if(m_pSymbolStream)
	{
		delete m_pSymbolStream;
		m_pSymbolStream = NULL;
	}

	// Clear section map stream
	if(m_pSectionMapStream)
	{
		delete m_pSectionMapStream;
		m_pSectionMapStream = NULL;
	}

	// Clear compiland streams
	std::map<int, CPdbCompilandStream*>::iterator it;
	for(it=m_apCompilandStreams.begin(); it!=m_apCompilandStreams.end(); it++)
	{
		delete it->second;
	}

	m_apCompilandStreams.clear();

	// Destroy raw MSF file reader
	m_MsfFile.Destroy();

	m_bInitialized = FALSE;
}

std::wstring CPdbReader::GetFileName()
{
	CAutoLock lock(&m_AccessLock);
	return m_sFileName;
}

CPdbHeadersStream* CPdbReader::GetHeadersStream()
{
	CAutoLock lock(&m_AccessLock);
	if(NULL==m_pHeadersStream)
	{
		// Init headers stream (#1)
		BOOL bResult = FALSE;
		m_pHeadersStream = new CPdbHeadersStream(this, m_MsfFile.GetStream(PDB_STREAM_PDB), &bResult);
		if(!bResult || !m_pHeadersStream)
			return NULL;
	}

	// Return pointer to PDB headers stream.
	return m_pHeadersStream;
}

CPdbDebugInfoStream* CPdbReader::GetDebugInfoStream()
{
	CAutoLock lock(&m_AccessLock);
	if(NULL==m_pDebugInfoStream)
	{
		// Init debug info stream (#3)
		BOOL bResult = FALSE;
		m_pDebugInfoStream = new CPdbDebugInfoStream(this, m_MsfFile.GetStream(PDB_STREAM_DBI), &bResult);
		if(!bResult || !m_pDebugInfoStream)
			return NULL;
	}

	// Return pointer to debug info stream.
	return m_pDebugInfoStream;
}

CPdbSymbolStream* CPdbReader::GetSymbolStream()
{
	CAutoLock lock(&m_AccessLock);

	if(!m_pSymbolStream)
	{
		// Init symbol stream (typically, #8)
		BOOL bResult = FALSE;
		CMsfStream* pStream = m_MsfFile.GetStream(GetDebugInfoStream()->GetSymbolStreamIndex());
		m_pSymbolStream = new CPdbSymbolStream(this, pStream, 0, pStream->GetStreamLen(), &bResult);
		if(!bResult || !m_pSymbolStream)
			return FALSE;
	}

	// Return pointer to symbol stream.
	return m_pSymbolStream;
}

CPdbSectionMapStream* CPdbReader::GetSectionMapStream()
{
	CAutoLock lock(&m_AccessLock);
	if(!m_pSectionMapStream)
	{
		// Init section map stream (typically, #9 or #8)
		// This stream is typically #9 for x86, but can be sometimes #8 for x64 when FPO streams missing

		BOOL bResult = FALSE;
		int try_streams[] = { 9, 8 , 10, 11, 12 };
		int i;
		for (i = 0; i < sizeof(try_streams)/sizeof(try_streams[0]); ++i) {
			int stream = try_streams[i];
			CMsfStream* pStream = m_MsfFile.GetStream(stream);
			m_pSectionMapStream = new CPdbSectionMapStream(this, pStream, &bResult);
			if (bResult && m_pSectionMapStream)
				return m_pSectionMapStream;

			delete m_pSectionMapStream;
			m_pSectionMapStream = NULL;
		}
	}

	return m_pSectionMapStream;
}

CPdbCompilandStream* CPdbReader::GetCompilandStream(DBI_ModuleInfo* pModuleInfo)
{
	CAutoLock lock(&m_AccessLock);

	// Determine stream number.
	int nStreamNumber = (int)pModuleInfo->m_Info.wStreamNumber;

	if(m_bAMD64)
	{
		if(nStreamNumber==10)
			nStreamNumber=11;
		else
			nStreamNumber--;
	}

	// Check if such stream already referenced
	std::map<int, CPdbCompilandStream*>::iterator it = m_apCompilandStreams.find(nStreamNumber);
	if(it!=m_apCompilandStreams.end())
		return it->second; // Found

	// Get raw MSF stream
	CMsfStream* pMsfStream = m_MsfFile.GetStream(nStreamNumber);
	if(pMsfStream==NULL)
		return NULL; // Couldn't get MSF stream.

	// Create new compiland stream
	CPdbCompilandStream* pCompiland = new CPdbCompilandStream();

	// Init compiland stream
	BOOL bInit = pCompiland->Init(this, pMsfStream, pModuleInfo);
	if(!bInit)
	{
		delete pCompiland;
		return NULL;
	}

	// Add stream to list
	m_apCompilandStreams[nStreamNumber] = pCompiland;

	return pCompiland;
}

BOOL CPdbReader::ReduceMemoryConsumption()
{
	CAutoLock lock(&m_AccessLock);

	// Release all compiland streams
	std::map<int, CPdbCompilandStream*>::iterator it;
	for(it=m_apCompilandStreams.begin(); it!=m_apCompilandStreams.end(); it++)
	{
		// Delete stream
		delete it->second;
	}

	m_apCompilandStreams.clear();

	return TRUE;
}

BOOL CPdbReader::IsAMD64()
{
	CAutoLock lock(&m_AccessLock);
	return m_bAMD64;
}

DWORD CPdbReader::GetFileSize()
{
#ifdef _WIN32
	struct _stat filestatus;
	if(0!=_wstat(m_sFileName.c_str() , &filestatus ))
		return 0;
#else
	struct stat filestatus;
	if(0!=stat(strconv::w2utf8(m_sFileName).c_str() , &filestatus ))
		return 0;
#endif
	return filestatus.st_size;
}

std::wstring CPdbReader::GetMD5Hash()
{
	std::wstring sMD5;
	CalcFileMD5Hash(m_sFileName, sMD5);
	return sMD5;
}
