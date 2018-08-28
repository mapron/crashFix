#include "Misc.h"
#include <iostream>

int main()
{
    std::cout << "GetExecutablePath=" << GetExecutablePath() << "\n";
    std::cout << "Hang 5 sec timeout: "  << executeWithTimeout("./ExecuteHang 10", 5) << "\n";
    std::cout << "Hang 10 sec timeout: " << executeWithTimeout("./ExecuteHang 5", 10) << "\n";
}
