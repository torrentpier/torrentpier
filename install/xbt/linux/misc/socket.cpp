#include "stdafx.h"
#include "socket.h"

#ifdef WIN32
#pragma comment(lib, "ws2_32.lib")
#else
#include <sys/ioctl.h>
#include <sys/socket.h>
#include <fcntl.h>
#include <netdb.h>
#include <unistd.h>
#endif

#ifndef INADDR_NONE
const int INADDR_NONE = -1;
#endif

#ifndef MSG_NOSIGNAL
const int MSG_NOSIGNAL = 0;
#endif

static bool g_start_up_done = false;

Csocket::Csocket(SOCKET s)
{
	m_source = s == INVALID_SOCKET ? NULL : new Csocket_source(s);
}

int Csocket::accept(int& h, int& p)
{
	sockaddr_in a;
	socklen_t cb_a = sizeof(sockaddr_in);
	a.sin_family = AF_INET;
	int r = ::accept(*this, reinterpret_cast<sockaddr*>(&a), &cb_a);
	if (r == INVALID_SOCKET)
		return r;
	h = a.sin_addr.s_addr;
	p = a.sin_port;
	return 0;
}

int Csocket::bind(int h, int p)
{
	sockaddr_in a = {0};
	a.sin_family = AF_INET;
	a.sin_addr.s_addr = h;
	a.sin_port = p;
	return ::bind(*this, reinterpret_cast<sockaddr*>(&a), sizeof(sockaddr_in));
}

int Csocket::blocking(bool v)
{
#ifdef FIONBIO
	unsigned long p = !v;
	return ioctlsocket(*this, FIONBIO, &p);
#else
	return fcntl(*this, F_SETFL, v ? 0 : O_NONBLOCK) == -1;
#endif
}

void Csocket::close()
{
	*this = INVALID_SOCKET;
}

int Csocket::connect(int h, int p)
{
	sockaddr_in a = {0};
	a.sin_family = AF_INET;
	a.sin_addr.s_addr = h;
	a.sin_port = p;
	return ::connect(*this, reinterpret_cast<sockaddr*>(&a), sizeof(sockaddr_in));
}

int Csocket::listen()
{
	return ::listen(*this, SOMAXCONN);
}

const Csocket& Csocket::open(int t, bool _blocking)
{
	start_up();
	*this = socket(AF_INET, t, 0);
	if (*this != INVALID_SOCKET && !_blocking && blocking(false))
		close();
	return *this;
}

int Csocket::recv(memory_range d) const
{
	return ::recv(*this, reinterpret_cast<char*>(d.begin), d.size(), MSG_NOSIGNAL);
}

int Csocket::recvfrom(memory_range d, sockaddr* a, socklen_t* cb_a) const
{
	return ::recvfrom(*this, reinterpret_cast<char*>(d.begin), d.size(), MSG_NOSIGNAL, a, cb_a);
}

int Csocket::send(const_memory_range s) const
{
	return ::send(*this, reinterpret_cast<const char*>(s.begin), s.size(), MSG_NOSIGNAL);
}

int Csocket::sendto(const_memory_range s, const sockaddr* a, socklen_t cb_a) const
{
	return ::sendto(*this, reinterpret_cast<const char*>(s.begin), s.size(), MSG_NOSIGNAL, a, cb_a);
}

int Csocket::getsockopt(int level, int name, void* v, socklen_t& cb_v)
{
	return ::getsockopt(*this, level, name, reinterpret_cast<char*>(v), &cb_v);
}

int Csocket::getsockopt(int level, int name, int& v)
{
	socklen_t cb_v = sizeof(int);
	return getsockopt(level, name, &v, cb_v);
}

int Csocket::setsockopt(int level, int name, const void* v, int cb_v)
{
	return ::setsockopt(*this, level, name, reinterpret_cast<const char*>(v), cb_v);
}

int Csocket::setsockopt(int level, int name, int v)
{
	return setsockopt(level, name, &v, sizeof(int));
}

int Csocket::get_host(const std::string& name)
{
	hostent* e = gethostbyname(name.c_str());
	return e && e->h_addrtype == AF_INET && e->h_length == sizeof(in_addr) && e->h_addr_list ? *reinterpret_cast<int*>(*e->h_addr_list) : INADDR_NONE;
}

std::string Csocket::error2a(int v)
{
	switch (v)
	{
	case WSAEACCES: return "EACCES";
	case WSAEADDRINUSE: return "EADDRINUSE";
	case WSAEADDRNOTAVAIL: return "EADDRNOTAVAIL";
	case WSAEAFNOSUPPORT: return "EAFNOSUPPORT";
	case WSAEALREADY: return "EALREADY";
	case WSAEBADF: return "EBADF";
	case WSAECONNABORTED: return "ECONNABORTED";
	case WSAECONNREFUSED: return "ECONNREFUSED";
	case WSAECONNRESET: return "ECONNRESET";
	case WSAEDESTADDRREQ: return "EDESTADDRREQ";
	case WSAEDQUOT: return "EDQUOT";
	case WSAEFAULT: return "EFAULT";
	case WSAEHOSTDOWN: return "EHOSTDOWN";
	case WSAEHOSTUNREACH: return "EHOSTUNREACH";
	case WSAEINPROGRESS: return "EINPROGRESS";
	case WSAEINTR: return "EINTR";
	case WSAEINVAL: return "EINVAL";
	case WSAEISCONN: return "EISCONN";
	case WSAELOOP: return "ELOOP";
	case WSAEMFILE: return "EMFILE";
	case WSAEMSGSIZE: return "EMSGSIZE";
	case WSAENAMETOOLONG: return "ENAMETOOLONG";
	case WSAENETDOWN: return "ENETDOWN";
	case WSAENETRESET: return "ENETRESET";
	case WSAENETUNREACH: return "ENETUNREACH";
	case WSAENOBUFS: return "ENOBUFS";
	case WSAENOPROTOOPT: return "ENOPROTOOPT";
	case WSAENOTCONN: return "ENOTCONN";
	case WSAENOTEMPTY: return "ENOTEMPTY";
	case WSAENOTSOCK: return "ENOTSOCK";
	case WSAEOPNOTSUPP: return "EOPNOTSUPP";
	case WSAEPFNOSUPPORT: return "EPFNOSUPPORT";
	case WSAEPROTONOSUPPORT: return "EPROTONOSUPPORT";
	case WSAEPROTOTYPE: return "EPROTOTYPE";
	case WSAEREMOTE: return "EREMOTE";
	case WSAESHUTDOWN: return "ESHUTDOWN";
	case WSAESOCKTNOSUPPORT: return "ESOCKTNOSUPPORT";
	case WSAESTALE: return "ESTALE";
	case WSAETIMEDOUT: return "ETIMEDOUT";
	case WSAETOOMANYREFS: return "ETOOMANYREFS";
	case WSAEUSERS: return "EUSERS";
	case WSAEWOULDBLOCK: return "EWOULDBLOCK";
#ifdef WIN32
	case WSAECANCELLED: return "ECANCELLED";
	case WSAEDISCON: return "EDISCON";
	case WSAEINVALIDPROCTABLE: return "EINVALIDPROCTABLE";
	case WSAEINVALIDPROVIDER: return "EINVALIDPROVIDER";
	case WSAENOMORE: return "ENOMORE";
	case WSAEPROVIDERFAILEDINIT: return "EPROVIDERFAILEDINIT";
	case WSAEREFUSED: return "EREFUSED";
	case WSANOTINITIALISED: return "NOTINITIALISED";
	case WSASERVICE_NOT_FOUND: return "SERVICE_NOT_FOUND";
	case WSASYSCALLFAILURE: return "SYSCALLFAILURE";
	case WSASYSNOTREADY: return "SYSNOTREADY";
	case WSATYPE_NOT_FOUND: return "TYPE_NOT_FOUND";
	case WSAVERNOTSUPPORTED: return "VERNOTSUPPORTED";
	case WSA_E_CANCELLED: return "E_CANCELLED";
	case WSA_E_NO_MORE: return "E_NO_MORE";
#endif
	}
	char b[12];
	sprintf(b, "%d", v);
	return b;
}

std::string Csocket::inet_ntoa(int v)
{
	in_addr a;
	a.s_addr = v;
	return ::inet_ntoa(a);
}

int Csocket::start_up()
{
	if (g_start_up_done)
		return 0;
	g_start_up_done = true;
#ifdef WIN32
	WSADATA wsadata;
	if (WSAStartup(MAKEWORD(2, 0), &wsadata))
		return 1;
#endif
	return 0;
}
