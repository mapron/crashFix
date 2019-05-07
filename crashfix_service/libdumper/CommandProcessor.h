#pragma once
#include "stdafx.h"

#include <memory>

// The following macros are used for parsing the command line
#define args_left() (argc-cur_arg)
#define arg_exists() (cur_arg<argc && argv[cur_arg]!=NULL)
#define get_arg() ( arg_exists() ? argv[cur_arg]:NULL )
#define skip_arg() cur_arg++
#define cmp_arg(val) (arg_exists() && (0==strcmp(argv[cur_arg], val)))

class CLog;
class CPdbCache;

//! \class CCommandProcessor
//! \brief Processes console commands.
class CCommandProcessor
{
public:

	//! Constructor.
	CCommandProcessor();

	//! Destructor.
	virtual ~CCommandProcessor();

	//! Prints usage to log
	void PrintUsage();

	//! Runs a command
	int Run(int argc, char* argv[]);

	//! Reads a minidump file and writes output to file
	int ReadDump(LPCSTR szFileName, LPCSTR szOutFile);

	//! Reads a PDB file and writes results to file
	int ReadPdb(LPCSTR szFileName, LPCSTR szOutFile);

	//! Extracts a stream from PDB file
	int ExtractPdbStream(LPCSTR szPdbFileName, int nStream, LPCSTR szOutFile);

	//! Extracts all PDB streams
	int ExtractPdbStreams(LPCSTR szPdbFileName, LPCSTR szOutDir);

	//! Dumps PDB file content
	int Dia2Dump(LPCSTR szPdbFileName, LPCSTR szOutFile);

	//! Dumps crash report contents.
	//! @param[in] szCrashRptFileName Crash report ZIP archive.
	//! @param[in] szOutFile Output XML file that will receive the resulting information.
	//! @param[in] szSymbolSearchDir Directory name where to search symbols. Optional.
	//! @param[in] bExactMatchBuildAge Wether to require exact match of PDB build age or not require.
	int DumpCrashReport(const std::wstring & szCrashRptFileName, const std::wstring & szOutFile, const std::wstring & szSymbolSearchDir, bool bExactMatchBuildAge);

	//! Extracts a file from crash report ZIP archive.
	int ExtractFile(LPCWSTR szCrashRptFileName, LPCWSTR szFileItemName, LPCWSTR szOutFile);

	//! Imports PDB file into symbol store.
	//! @param[in] PDB file name to import.
	//! @param[in] Name of the symbol store directory
	int ImportPdb(LPCWSTR szPdbFileName, LPCWSTR szSymDir, LPCWSTR szOutFile);

	//! Opens log file
	bool InitLog(std::wstring sFileName, int nLoggingLevel);

	//! Replaces our log with another one
	void SetLog(const std::shared_ptr<CLog> & pLog);

	//! Returns last error message
	std::string GetErrorMsg();

	//! Replaces
	void SetPdbCache(const std::shared_ptr<CPdbCache> & pPdbCache);

private:

	std::string m_sErrorMsg; //!< Last error message
	std::shared_ptr<CLog> m_pLog; //!< Pointer to log
	std::shared_ptr<CPdbCache> m_pPdbCache;  //!< PDB cache.
};
