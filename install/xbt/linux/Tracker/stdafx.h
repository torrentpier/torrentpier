#pragma once

#include <boost/foreach.hpp>
#include <cassert>
#include <ctime>
#include <fstream>
#include <iostream>
#include <list>
#include <map>
#include <set>
#include <string>
#include <vector>

#ifdef WIN32
#define FD_SETSIZE 1024
#define NOMINMAX

#define atoll _atoi64
#else
#include <sys/types.h>
#include <netinet/in.h>
#include <netinet/tcp.h>
#include <sys/ioctl.h>
#include <sys/socket.h>
#include <cstdio>
#include <errno.h>
#include <signal.h>
#include <unistd.h>

// TorrentPier begin
#include <netdb.h>
// TorrentPier end

#endif

typedef unsigned char byte;
