#include "stdafx.h"
#include "connection.h"

#include <bt_misc.h>
#include <bt_strings.h>
#include <bvalue.h>
#include <iostream>
#include <xcc_z.h>
#include "server.h"

// TorrentPier begin

#ifdef WIN32
#include <Ws2tcpip.h>
#endif

#define PASS_SIZE1 10
#define PASS_SIZE2 32

Cconnection::Cconnection(Cserver* server, const Csocket& s, const sockaddr_storage& a)
// TorrentPier end
{
	m_server = server;
	m_s = s;
	m_a = a;
	m_ctime = server->time();

	m_state = 0;
	m_r.clear();
	m_w = m_read_b;
}

int Cconnection::pre_select(fd_set* fd_read_set, fd_set* fd_write_set)
{
	FD_SET(m_s, fd_read_set);
	if (!m_r.empty())
		FD_SET(m_s, fd_write_set);
	return m_s;
}

int Cconnection::post_select(fd_set* fd_read_set, fd_set* fd_write_set)
{
	return FD_ISSET(m_s, fd_read_set) && recv()
		|| FD_ISSET(m_s, fd_write_set) && send()
		|| m_server->time() - m_ctime > 10
		|| m_state == 5 && m_r.empty();
}

int Cconnection::recv()
{
	int r = m_s.recv(m_w);
	if (!r)
	{
		m_state = 5;
		return 0;
	}
	if (r == SOCKET_ERROR)
	{
		int e = WSAGetLastError();
		switch (e)
		{
		case WSAECONNABORTED:
		case WSAECONNRESET:
			return 1;
		case WSAEWOULDBLOCK:
			return 0;
		}
		std::cerr << "recv failed: " << Csocket::error2a(e) << std::endl;
		return 1;
	}
	if (m_state == 5)
		return 0;
	const unsigned char* a = m_w;
	m_w += r;
	int state;
	do
	{
		state = m_state;
		while (a < m_w && *a != '\n' && *a != '\r')
		{
			a++;
			if (m_state)
				m_state = 1;
		}
		if (a < m_w)
		{
			switch (m_state)
			{
			case 0:
				read(std::string(&m_read_b.front(), reinterpret_cast<const char*>(a) - &m_read_b.front()));
				m_state = 1;
			case 1:
			case 3:
				m_state += *a == '\n' ? 2 : 1;
				break;
			case 2:
			case 4:
				m_state++;
				break;
			}
			a++;
		}
	}
	while (state != m_state);
	return 0;
}

int Cconnection::send()
{
	if (m_r.empty())
		return 0;
	int r = m_s.send(m_r);
	if (r == SOCKET_ERROR)
	{
		int e = WSAGetLastError();
		switch (e)
		{
		case WSAECONNABORTED:
		case WSAECONNRESET:
			return 1;
		case WSAEWOULDBLOCK:
			return 0;
		}
		std::cerr << "send failed: " << Csocket::error2a(e) << std::endl;
		return 1;
	}
	m_r += r;
	if (m_r.empty())
		m_write_b.clear();
	return 0;
}

void Cconnection::read(const std::string& v)
{
#ifndef NDEBUG
	std::cout << v << std::endl;
#endif
	if (m_server->config().m_log_access)
	{
		// TorrentPier begin
		char hbuf[NI_MAXHOST], sbuf[NI_MAXSERV];
		if (!getnameinfo(reinterpret_cast<sockaddr*>(&m_a), sizeof(m_a), hbuf, sizeof(hbuf), sbuf, sizeof(sbuf), NI_NUMERICHOST | NI_NUMERICSERV)) {
			static std::ofstream f("xbt_tracker_raw.log");
			f << m_server->time() << '\t' << hbuf << '\t' << sbuf << '\t' << v << std::endl;
		}
	}
	Ctracker_input ti(m_a.ss_family);
	// TorrentPier end

	size_t e = v.find('?');
	if (e == std::string::npos)
		e = v.size();
	else
	{
		size_t a = e + 1;
		size_t b = v.find(' ', a);
		if (b == std::string::npos)
			return;
		while (a < b)
		{
			// TorrentPier begin
			if( v[a] == '&' || v[a] == '?' ) { a++; continue; } // "&&?" hack
			// TorrentPier end

			size_t c = v.find('=', a);
			if (c++ == std::string::npos)
				break;
			size_t d = v.find_first_of(" &", c);
			if (d == std::string::npos)
				break;
			ti.set(v.substr(a, c - a - 1), uri_decode(v.substr(c, d - c)));
			a = d + 1;
		}
	}

	// TorrentPier begin
	if (m_a.ss_family == AF_INET) {
		sockaddr_in *b = reinterpret_cast<sockaddr_in*>(&m_a);
		if (!ti.m_ipa || !is_private_ipa(b->sin_addr.s_addr))
			ti.m_ipa = b->sin_addr.s_addr;
	} else if (m_a.ss_family == AF_INET6) {
		sockaddr_in6 *b = reinterpret_cast<sockaddr_in6*>(&m_a);
		ti.m_ipv6set = true;
		memcpy(ti.m_ipv6bin, &(b->sin6_addr), 16);
	}

	std::string torrent_pass0 = ti.m_passkey;
	// TorrentPier end

	size_t a = 4;
	if (a < e && v[a] == '/')
	{
		do { a++;
		} while (a < e && v[a] == '/');
		if (a + 1 < e && v[a + 1] == '/')
			a += 2;

		// TorrentPier begin
		if (a + 2 < e && v[a + 2] == '/') // Skip "/bt/"
			a += 3;

		if (a + PASS_SIZE1 < e && v[a + PASS_SIZE1] == '/')
		{
			torrent_pass0 = v.substr(a, PASS_SIZE1);
			a += PASS_SIZE1+1;
		}

		if (a + PASS_SIZE2 < e && v[a + PASS_SIZE2] == '/')
		{
			torrent_pass0 = v.substr(a, PASS_SIZE2);
			a += PASS_SIZE2+1;
		}
		// TorrentPier end
	}
	std::string h = "HTTP/1.0 200 OK\r\n";
	Cvirtual_binary s;
	bool gzip = true;
	switch (a < v.size() ? v[a] : 0)
	{
	case 'a':
		if (!ti.valid())
			break;
		gzip = false;
			if (ti.banned())
				s = Cbvalue().d(bts_failure_reason, bts_banned_client).read();
		else if (0)
			s = Cbvalue().d(bts_failure_reason, bts_banned_client).read();
		else
		{
			std::string error = m_server->insert_peer(ti, false, m_server->find_user_by_torrent_pass(torrent_pass0, ti.m_info_hash));
			s = error.empty() ? m_server->select_peers(ti) : Cbvalue().d(bts_failure_reason, error).read();
		}
		break;
	case 'd':
		if (m_server->config().m_debug)
		{
			gzip = m_server->config().m_gzip_debug;
			h += "Content-Type: text/html; charset=us-ascii\r\n";
			s = Cvirtual_binary(m_server->debug(ti));
		}
		break;
	case 's':
		if (v.size() >= 7 && v[6] == 't')
		{
			gzip = m_server->config().m_gzip_debug;
			h += "Content-Type: text/html; charset=us-ascii\r\n";
			s = Cvirtual_binary(m_server->statistics());
		}
		else if (m_server->config().m_full_scrape || ti.m_compact || !ti.m_info_hash.empty())
		{
			gzip = m_server->config().m_gzip_scrape && !ti.m_compact && ti.m_info_hash.empty();
			s = m_server->scrape(ti);
		}
		break;
	}
	if (s.empty())
	{
		if (!ti.m_peer_id.empty() || m_server->config().m_redirect_url.empty())
			h = "HTTP/1.0 404 Not Found\r\n";
		else
		{
			h = "HTTP/1.0 302 Found\r\n"
				"Location: " + m_server->config().m_redirect_url + (ti.m_info_hash.empty() ? "" : "?info_hash=" + uri_encode(ti.m_info_hash)) + "\r\n";
		}
	}
	else if (gzip)
	{
		Cvirtual_binary s2 = xcc_z::gzip(s);
#ifndef NDEBUG
		static std::ofstream f("xbt_tracker_gzip.log");
		f << m_server->time() << '\t' << v[5] << '\t' << s.size() << '\t' << s2.size() << std::endl;
#endif
		if (s2.size() + 24 < s.size())
		{
			h += "Content-Encoding: gzip\r\n";
			s = s2;
		}
	}
	h += "\r\n";
#ifdef WIN32
	m_write_b.resize(h.size() + s.size());
	memcpy(m_write_b.data_edit(), h.data(), h.size());
	s.read(m_write_b.data_edit() + h.size());
	int r = m_s.send(m_write_b);
#else
	boost::array<iovec, 2> d;
	d[0].iov_base = const_cast<char*>(h.data());
	d[0].iov_len = h.size();
	d[1].iov_base = const_cast<unsigned char*>(s.data());
	d[1].iov_len = s.size();
	msghdr m;
	m.msg_name = NULL;
	m.msg_namelen = 0;
	m.msg_iov = const_cast<iovec*>(d.data());
	m.msg_iovlen = d.size();
	m.msg_control = NULL;
	m.msg_controllen = 0;
	m.msg_flags = 0;
	int r = sendmsg(m_s, &m, MSG_NOSIGNAL);
#endif
	if (r == SOCKET_ERROR)
	{
		if (WSAGetLastError() != WSAECONNRESET)
			std::cerr << "send failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
	}
	else if (r != h.size() + s.size())
	{
#ifndef WIN32
		if (r < h.size())
		{
			m_write_b.resize(h.size() + s.size());
			memcpy(m_write_b.data_edit(), h.data(), h.size());
			s.read(m_write_b.data_edit() + h.size());
		}
		else
		{
			m_write_b = s;
			r -= h.size();
		}
#endif
		m_r = m_write_b;
		m_r += r;
	}
	if (m_r.empty())
		m_write_b.clear();
}

void Cconnection::process_events(int events)
{
	if (events & (EPOLLIN | EPOLLPRI | EPOLLERR | EPOLLHUP) && recv()
		|| events & EPOLLOUT && send()
		|| m_state == 5 && m_write_b.empty())
		m_s.close();
}

int Cconnection::run()
{
	return s() == INVALID_SOCKET || m_server->time() - m_ctime > 10;
}
