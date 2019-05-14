cmake_minimum_required (VERSION 2.8)
project (dumper)
add_definitions(-DUNICODE -D_UNICODE -DNOMINMAX)

IF(NOT CMAKE_BUILD_TYPE)
  SET(CMAKE_BUILD_TYPE Release CACHE STRING
	  "Choose the type of build, options are: None Debug Release RelWithDebInfo MinSizeRel."
	  FORCE)
ENDIF(NOT CMAKE_BUILD_TYPE)

set(CMAKE_CXX_FLAGS_RELEASE        "-O2 -DNDEBUG")
set(CMAKE_CXX_FLAGS_RELWITHDEBINFO "-O2 -g")

if(UNIX)
	set(CMAKE_CXX_FLAGS "${CMAKE_CXX_FLAGS} -std=c++0x")
endif()

# Set output directory for executable files
set(CMAKE_RUNTIME_OUTPUT_DIRECTORY_DEBUG ${CMAKE_BINARY_DIR}/bin)
set(CMAKE_RUNTIME_OUTPUT_DIRECTORY_RELEASE ${CMAKE_BINARY_DIR}/bin)
set(CMAKE_RUNTIME_OUTPUT_DIRECTORY_RELWITHDEBINFO ${CMAKE_BINARY_DIR}/bin)

# Set output directory for libs
set(CMAKE_LIBRARY_OUTPUT_DIRECTORY_DEBUG ${CMAKE_BINARY_DIR}/bin)
set(CMAKE_LIBRARY_OUTPUT_DIRECTORY_RELEASE ${CMAKE_BINARY_DIR}/bin)
set(CMAKE_LIBRARY_OUTPUT_DIRECTORY_RELWITHDEBINFO ${CMAKE_BINARY_DIR}/bin)

# Set output directory for archives
set(CMAKE_ARCHIVE_OUTPUT_DIRECTORY_DEBUG ${CMAKE_BINARY_DIR}/lib)
set(CMAKE_ARCHIVE_OUTPUT_DIRECTORY_RELEASE ${CMAKE_BINARY_DIR}/lib)
set(CMAKE_ARCHIVE_OUTPUT_DIRECTORY_RELWITHDEBINFO ${CMAKE_BINARY_DIR}/lib)

# add the binary tree to the search path for include files
# so that we will find Config.h
include_directories(${PROJECT_BINARY_DIR}
		${CMAKE_SOURCE_DIR}/thirdparty/tinyxml
		${CMAKE_SOURCE_DIR}/thirdparty/zlib
		${CMAKE_SOURCE_DIR}/thirdparty/minizip
		)

MACRO(ADD_MSVC_PRECOMPILED_HEADER PrecompiledHeader PrecompiledSource SourcesVar)
ENDMACRO(ADD_MSVC_PRECOMPILED_HEADER)

MACRO (ADD_PCH_RULE  _header_filename _src_list)
ENDMACRO(ADD_PCH_RULE _header_filename _src_list)

INCLUDE_DIRECTORIES("${CMAKE_CURRENT_LIST_DIR}/thirdparty/wtl/")
include_directories( ${CMAKE_SOURCE_DIR}/libdumper )

add_subdirectory("libdumper")
add_subdirectory("dumper")
add_subdirectory("daemon")

add_subdirectory("libtest")
#add_subdirectory("libdumper_tests")
#add_subdirectory("daemon_tests")


add_subdirectory("thirdparty/zlib")
add_subdirectory("thirdparty/minizip")
add_subdirectory("thirdparty/tinyxml")

add_executable(ExecuteTest libdumper_tests/ExecuteTest.cpp)
add_executable(ExecuteHang libdumper_tests/ExecuteHang.cpp)
add_dependencies(ExecuteTest ExecuteHang)
target_link_libraries(ExecuteTest PRIVATE libdumper)
if (WIN32)
   target_link_libraries(ExecuteTest PRIVATE psapi.lib)
endif()

# Install
if(UNIX)
SET(CPACK_GENERATOR "DEB;RPM")
SET(CPACK_PACKAGE_NAME "crashfix-service")
SET(CPACK_PACKAGE_VENDOR "IP Oleg Krivtsov")
SET(CPACK_PACKAGE_CONTACT "Oleg Krivtsov <conactus@crashfixsoftware.com>")
SET(CPACK_DEBIAN_PACKAGE_MAINTAINER "Oleg Krivtsov <conactus@crashfixsoftware.com>")
SET(CPACK_PACKAGE_DESCRIPTION_SUMMARY "CrashFix Service")
SET(CPACK_PACKAGE_DESCRIPTION_FILE "${CMAKE_CURRENT_SOURCE_DIR}/README.TXT")
SET(CPACK_RESOURCE_FILE_LICENSE "${CMAKE_CURRENT_SOURCE_DIR}/COPYRIGHT.TXT")
SET(CPACK_PACKAGE_ARCHITECTURE "i386")
SET(CPACK_PACKAGE_VERSION_MAJOR "1")
SET(CPACK_PACKAGE_VERSION_MINOR "0")
SET(CPACK_PACKAGE_VERSION_PATCH "4")
SET(CPACK_PACKAGE_INSTALL_DIRECTORY "CMake ${CMake_VERSION_MAJOR}.${CMake_VERSION_MINOR}")
if(CMAKE_SIZEOF_VOID_P EQUAL 8)
	SET(CPACK_SYSTEM_NAME "Linux-amd64")
else(CMAKE_SIZEOF_VOID_P EQUAL 8)
	SET(CPACK_SYSTEM_NAME "Linux-i386")
endif(CMAKE_SIZEOF_VOID_P EQUAL 8)
SET(CPACK_DEBIAN_PACKAGE_SECTION "utils")
#SET(CPACK_DEBIAN_PACKAGE_DEPENDS "libc6, libssl")
set(CPACK_RPM_PACKAGE_REQUIRES "glibc >= 2.12")
set(CPACK_RPM_SPEC_MORE_DEFINE "%define ignore \#")
set(CPACK_RPM_USER_FILELIST "%ignore /etc" "%ignore /etc/init.d")
#set(CPACK_SET_DESTDIR ON)

include (CPack)

file(WRITE ${CMAKE_BINARY_DIR}/var/log/crashfix/dummy.log)
install(FILES ${CMAKE_BINARY_DIR}/var/log/crashfix/dummy.log
		DESTINATION /var/log/crashfix/ )

file(MAKE_DIRECTORY ${CMAKE_BINARY_DIR}/usr/share/crashfix/licenses)
install(DIRECTORY ${CMAKE_BINARY_DIR}/usr/share/crashfix/licenses
		DESTINATION /usr/share/crashfix )

install(FILES ${CMAKE_SOURCE_DIR}/conf/crashfixd.conf.linux
		DESTINATION /etc/crashfix/
		RENAME crashfixd.conf
		PERMISSIONS OWNER_READ OWNER_WRITE GROUP_READ GROUP_WRITE WORLD_READ)

install(PROGRAMS ${CMAKE_SOURCE_DIR}/daemon/crashfixd.sh
		DESTINATION /etc/init.d/
		RENAME crashfixd)

endif(UNIX)
