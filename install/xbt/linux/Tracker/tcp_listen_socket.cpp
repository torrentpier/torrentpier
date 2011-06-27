#include "stdafx.h"
#include "tcp_listen_socket.h"

#include "server.h"

Ctcp_listen_socket::Ctcp_listen_socket()
{
	m_server = NULL;
}

Ctcp_listen_socket::Ctcp_listen_socket(Cserver* server, const Csocket& s)
{
	m_server = server;
	m_s = s;
}

void Ctcp_listen_socket::process_events(int events)
{
	m_server->accept(m_s);
}
