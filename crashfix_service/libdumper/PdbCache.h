//! \file PdbCache.h
//! \brief PDB file cache.
//! \author Oleg Krivtsov
//! \date 2011

#pragma once
#include "stdafx.h"
#include "PdbReader.h"
#include "PeReader.h"
#include "CritSec.h"

#include <memory>
#include <list>
#include <mutex>

//! Modes for PDB search
enum ePdbDirSearchMode
{
	PDB_USUAL_DIR    = 0x1, //!< Search by trying PDB files in turn.
	PDB_SYMBOL_STORE = 0x2  //!< Assume the search dir is organized as symbol store.
};

//! PDB cache statistics.
struct PdbCacheStat
{
	int m_nEntryCount;    //!< Current total number of entries.
	int m_nUnrefCount;    //!< Count of unreferenced entries.
};

//! \class CPdbCache
//! \brief Shared storage for PDB files.
class CPdbCache
{
public:

	//! Constructor.
	CPdbCache();

	//! Destructor.
	virtual ~CPdbCache();

	//! Adds a search directory for PDB and PE files.
	//! @return true on success; otherwise false.
	bool AddSearchDir(std::wstring searchDir);

	//! Searches for matching PDB file.
	//! @param[in] sPdbFileName PDB file name hint.
	//! @param[out] psErrorMsg On output, receives error message. Optional.
	std::shared_ptr<CPdbReader> FindPdb(const std::wstring & sPdbFileName, bool isAMD64, std::string* psErrorMsg = nullptr);

	std::shared_ptr<CPeReader> FindPE(const std::wstring &sImageFileName, bool isAMD64, std::string* psErrorMsg = nullptr);


	//! Removes all cache entries (clears the cache).
	//! All handles become invalid after this operation.
	void Clear();
private:

	//! Loads PDB file, checks its GUID and if matches, adds to cache.
	//! @param[in] sPdbFileName PDB file name.
	std::shared_ptr<CPdbReader> TryPdbFile(std::wstring sPdbFileName, std::string& sErrorMsg);

	std::shared_ptr<CPeReader> TryPeFile(std::wstring sPdbFileName, std::string& sErrorMsg);


	// Variables used internally.
	std::mutex m_mutex;
	std::list<std::wstring> m_searchDirs;      //!< The list of search directories
	std::map<std::wstring, std::shared_ptr<CPdbReader>> m_pdbCache;
	std::map<std::wstring, std::shared_ptr<CPeReader>> m_peCache;
};
