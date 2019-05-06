//! \file PdbCache.cpp
//! \brief PDB file cache.
//! \author Oleg Krivtsov
//! \date 2011

#include "stdafx.h"
#include "PdbCache.h"
#include "strconv.h"
#include "Misc.h"
#include "FileFinder.h"

namespace
{

std::wstring GetArchDirectory(bool isAMD64)
{
	// @todo: support variety of arches later.
	return isAMD64 ? L"Win64_PDB" : L"Win32_PDB";
}

}

CPdbCache::CPdbCache()
{
}

CPdbCache::~CPdbCache()
{
	Clear();
}

bool CPdbCache::AddSearchDir(std::wstring searchDir)
{
	// Add a directory to PDB search list.

	// Check if directory really exists.
#ifdef _WIN32
	DWORD dwAttrs = GetFileAttributesW(searchDir.c_str());
	if(dwAttrs==INVALID_FILE_ATTRIBUTES || (dwAttrs&FILE_ATTRIBUTE_DIRECTORY)==0)
		return false; // Directory does not exist.
#else
	struct stat st_buf;
	int status = stat (strconv::w2a(searchDir).c_str(), &st_buf);
	if (status != 0)
		return false;

	if (!S_ISDIR (st_buf.st_mode))
		return false;
#endif

	std::lock_guard<std::mutex> lock(m_mutex);
#ifdef _WIN32
	std::replace(searchDir.begin(), searchDir.end(), '/', '\\');
#else
	std::replace(searchDir.begin(), searchDir.end(), '\\', '/');
#endif
	m_searchDirs.push_back(searchDir);

	return true;
}

std::shared_ptr<CPdbReader> CPdbCache::FindPdb(
	const std::wstring & sPdbFileName,
	bool isAMD64,
	std::string* psErrorMsg
	)
{
	std::shared_ptr<CPdbReader> result;
	if(sPdbFileName.empty())
	{
		if(psErrorMsg)
			*psErrorMsg = "path should be specified";
		return result;
	}
	std::lock_guard<std::mutex> lock(m_mutex);
	auto it = m_pdbCache.find(sPdbFileName);
	if (it != m_pdbCache.cend())
		return it->second;

	// Try to find matching PDB file from search dirs

	const std::wstring arch = GetArchDirectory(isAMD64);
	std::string sErrorMsg;
	for (const auto & searchDir : m_searchDirs)
	{
		auto reader = TryPdbFile(searchDir + L"/" + arch + L"/" + sPdbFileName, sErrorMsg);
		if(reader)
		{
			m_pdbCache[sPdbFileName] = reader;
			return reader;
		}
	}
	if (sErrorMsg.empty())
		sErrorMsg = "Exhausted search";

	if(psErrorMsg)
		*psErrorMsg = sErrorMsg;

	return result;
}

std::shared_ptr<CPeReader> CPdbCache::FindPE(const std::wstring &sImageFileName, bool isAMD64, std::string * psErrorMsg)
{
	std::lock_guard<std::mutex> lock(m_mutex);
	auto it = m_peCache.find(sImageFileName);
	if (it != m_peCache.cend())
		return it->second;

	const std::wstring arch = GetArchDirectory(isAMD64);
	std::string sErrorMsg;
	for (const auto & searchDir : m_searchDirs)
	{
		auto reader = TryPeFile(searchDir + L"/" + arch + L"/" + sImageFileName, sErrorMsg);
		if(reader)
		{
			m_peCache[sImageFileName] = reader;
			return reader;
		}
	}

	return nullptr;
}

std::shared_ptr<CPdbReader> CPdbCache::TryPdbFile(std::wstring sPdbFileName, std::string & sErrorMsg)
{
	std::shared_ptr<CPdbReader> reader;

	FixSlashesInFilePath(sPdbFileName);

	// Check if file exists on disk
	BOOL bExists = FALSE;
#ifdef _WIN32
	DWORD dwAttrs = GetFileAttributesW(sPdbFileName.c_str());
	if(dwAttrs!=INVALID_FILE_ATTRIBUTES && (dwAttrs&FILE_ATTRIBUTE_DIRECTORY)==0)
		bExists = TRUE;
#else
	struct stat st;
	if(0==stat(strconv::w2a(sPdbFileName).c_str(), &st) && S_ISREG(st.st_mode))
		bExists = TRUE;
#endif
	if(!bExists)
	{
		// File does not exist.
		sErrorMsg = "File does not exist on disk";
		return nullptr;
	}

	// Create PDB reader
	reader = std::make_shared<CPdbReader>();
	if(!reader->Init(sPdbFileName))
	{
		//wprintf(L"Error, PDB is invalid %s %s\n", sGUIDnAge.c_str(), sPdbFileName.c_str());
		// Couldn't init PDB reader
		sErrorMsg = "Error reading PDB file";
		return nullptr;
	}

	// Get GUID
	auto pHeaders = reader->GetHeadersStream();
	if(!pHeaders)
	{
		//wprintf(L"Error, PDB GUI get error %s %s\n", sGUIDnAge.c_str(), sPdbFileName.c_str());
		sErrorMsg = "Couldn't get headers stream from PDB reader";
		return nullptr;
	}

	// Success
	sErrorMsg = "Success, matching entry loaded";
	return reader;
}

std::shared_ptr<CPeReader> CPdbCache::TryPeFile(std::wstring sPeFileName, std::string& sErrorMsg)
{
	// Create PE reader
	std::shared_ptr<CPeReader> reader = std::make_shared<CPeReader>();
	FixSlashesInFilePath(sPeFileName);
	if(!reader->Init(sPeFileName))
	{
		sErrorMsg = "Failed to init PE reader";
		return nullptr;
	}

	return reader;
}

void CPdbCache::Clear()
{
	std::lock_guard<std::mutex> lock(m_mutex);

	m_searchDirs.clear();
	m_pdbCache.clear();
	m_peCache.clear();
}
