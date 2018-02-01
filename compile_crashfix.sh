#!/bin/sh
mkdir build 
cd build
cmake -G 'Unix Makefiles' -DCMAKE_BUILD_TYPE=Release ../crashfix_service/ || exit 1
make all


