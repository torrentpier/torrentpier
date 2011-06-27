#pragma once

#include "client.h"
#include <vector>
#include <xbt/virtual_binary.h>

class Cserver;

class Cconnection: public Cclient, boost::noncopyable
{
public:
	Cclient::s;
	int run();
	void read(const std::string&);
	int recv();
	int send();
	virtual void process_events(int);
	int pre_select(fd_set* fd_read_set, fd_set* fd_write_set);
	int post_select(fd_set* fd_read_set, fd_set* fd_write_set);

	// TorrentPier begin
	Cconnection(Cserver*, const Csocket&, const sockaddr_storage&);
private:
	sockaddr_storage m_a;
	// TorrentPier end

	time_t m_ctime;
	int m_state;
	boost::array<char, 4 << 10> m_read_b;
	Cvirtual_binary m_write_b;
	const_memory_range m_r;
	memory_range m_w;
};
