#pragma once

#include "server.h"

class Ctransaction
{
public:
	long long connection_id() const;
	void recv();
	void send(const_memory_range);
	void send_announce(const_memory_range);
	void send_connect(const_memory_range);
	void send_scrape(const_memory_range);
	void send_error(const_memory_range, const std::string& msg);
	Ctransaction(Cserver&, const Csocket&);
private:
	Cserver& m_server;
	Csocket m_s;
	sockaddr_in m_a;
};
