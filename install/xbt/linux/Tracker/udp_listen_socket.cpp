#include "stdafx.h"
#include "udp_listen_socket.h"

#include "transaction.h"

Cudp_listen_socket::Cudp_listen_socket()
{
	m_server = NULL;
}

Cudp_listen_socket::Cudp_listen_socket(Cserver* server, const Csocket& s)
{
	m_server = server;
	m_s = s;
}

void Cudp_listen_socket::process_events(int events)
{
	if (events & EPOLLIN)
		Ctransaction(*m_server, m_s).recv();
}
