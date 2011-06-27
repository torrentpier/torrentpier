#pragma once

#include <boost/intrusive_ptr.hpp>
#include <boost/utility.hpp>
#include <const_memory_range.h>
#include <string>

#ifdef WIN32
#include <winsock2.h>

typedef int socklen_t;
#else
#include <arpa/inet.h>
#include <netinet/in.h>
#include <errno.h>

#define closesocket close
#define ioctlsocket ioctl
#define WSAGetLastError() errno

#define WSAEACCES EACCES
#define WSAEADDRINUSE EADDRINUSE
#define WSAEADDRNOTAVAIL EADDRNOTAVAIL
#define WSAEAFNOSUPPORT EAFNOSUPPORT
#define WSAEALREADY EALREADY
#define WSAEBADF EBADF
#define WSAECONNABORTED ECONNABORTED
#define WSAECONNREFUSED ECONNREFUSED
#define WSAECONNRESET ECONNRESET
#define WSAEDESTADDRREQ EDESTADDRREQ
#define WSAEDQUOT EDQUOT
#define WSAEFAULT EFAULT
#define WSAEHOSTDOWN EHOSTDOWN
#define WSAEHOSTUNREACH EHOSTUNREACH
#define WSAEINPROGRESS EINPROGRESS
#define WSAEINTR EINTR
#define WSAEINVAL EINVAL
#define WSAEISCONN EISCONN
#define WSAELOOP ELOOP
#define WSAEMFILE EMFILE
#define WSAEMSGSIZE EMSGSIZE
#define WSAENAMETOOLONG ENAMETOOLONG
#define WSAENETDOWN ENETDOWN
#define WSAENETRESET ENETRESET
#define WSAENETUNREACH ENETUNREACH
#define WSAENOBUFS ENOBUFS
#define WSAENOPROTOOPT ENOPROTOOPT
#define WSAENOTCONN ENOTCONN
#define WSAENOTEMPTY ENOTEMPTY
#define WSAENOTSOCK ENOTSOCK
#define WSAEOPNOTSUPP EOPNOTSUPP
#define WSAEPFNOSUPPORT EPFNOSUPPORT
#define WSAEPROTONOSUPPORT EPROTONOSUPPORT
#define WSAEPROTOTYPE EPROTOTYPE
#define WSAEREMOTE EREMOTE
#define WSAESHUTDOWN ESHUTDOWN
#define WSAESOCKTNOSUPPORT ESOCKTNOSUPPORT
#define WSAESTALE ESTALE
#define WSAETIMEDOUT ETIMEDOUT
#define WSAETOOMANYREFS ETOOMANYREFS
#define WSAEUSERS EUSERS
#define WSAEWOULDBLOCK EWOULDBLOCK

typedef int SOCKET;

const int INVALID_SOCKET = -1;
const int SOCKET_ERROR = -1;
#endif

class Csocket_source: boost::noncopyable
{
public:
	Csocket_source(SOCKET s)
	{
		m_s = s;
		mc_references = 0;
	}

	~Csocket_source()
	{
		closesocket(m_s);
	}

	operator SOCKET() const
	{
		return m_s;
	}

	friend void intrusive_ptr_add_ref(Csocket_source* v)
	{
		v->mc_references++;
	}

	friend void intrusive_ptr_release(Csocket_source* v)
	{
		v->mc_references--;
		if (!v->mc_references)
			delete v;
	}
private:
	SOCKET m_s;
	int mc_references;
};

class Csocket
{
public:
	static std::string error2a(int v);
	static int get_host(const std::string& name);
	static std::string inet_ntoa(int h);
	static int start_up();
	int accept(int& h, int& p);
	int bind(int h, int p);
	int blocking(bool v);
	void close();
	int connect(int h, int p);
	int getsockopt(int level, int name, void* v, socklen_t& cb_v);
	int getsockopt(int level, int name, int& v);
	int listen();
	const Csocket& open(int t, bool blocking = false);
	int recv(memory_range) const;
	int recvfrom(memory_range, sockaddr* a, socklen_t* cb_a) const;
	int send(const_memory_range) const;
	int sendto(const_memory_range, const sockaddr* a, socklen_t cb_a) const;
	int setsockopt(int level, int name, const void* v, int cb_v);
	int setsockopt(int level, int name, int v);
	Csocket(SOCKET = INVALID_SOCKET);

	operator SOCKET() const
	{
		return m_source ? static_cast<SOCKET>(*m_source) : INVALID_SOCKET;
	}
private:
	boost::intrusive_ptr<Csocket_source> m_source;
};
