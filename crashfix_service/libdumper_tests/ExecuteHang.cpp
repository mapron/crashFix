#include <thread>
#include <string>

int main(int argc, char *argv[])
{
    if (argc != 2)
        return 1;
    
    int seconds = std::atoi(argv[1]);
    std::this_thread::sleep_for(std::chrono::seconds(seconds));
    return 0;
}
