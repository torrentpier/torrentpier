#include "stdafx.h"
#include "bt_tracker_url.h"

#include <boost/algorithm/string.hpp>

Cbt_tracker_url::Cbt_tracker_url()
{
}

Cbt_tracker_url::Cbt_tracker_url(const std::string& v)
{
	write(v);
}

void Cbt_tracker_url::clear()
{
	m_protocol = tp_unknown;
	m_host.erase();
	m_port = 0;
	m_path.erase();
}

bool Cbt_tracker_url::valid() const
{
	switch (m_protocol)
	{
	case tp_http:
		if (m_path.empty() || m_path[0] != '/')
			return false;
	case tp_udp:
		return !m_host.empty()
			&& m_port >= 0 && m_port < 0x10000;
	}
	return false;
}

void Cbt_tracker_url::write(const std::string& v)
{
	clear();
	size_t a;
	int protocol;
	int port;
	if (boost::istarts_with(v, "http://"))
	{
		a = 7;
		protocol = tp_http;
		port = 80;
	}
	else if (boost::istarts_with(v, "udp://"))
	{
		a = 6;
		protocol = tp_udp;
		port = 2710;
	}
	else
		return;
	size_t b = v.find_first_of("/:", a);
	std::string host;
	if (b == std::string::npos)
		host = v.substr(a);
	else
	{
		host = v.substr(a, b - a);
		if (v[b] == '/')
			m_path = v.substr(b);
		else
		{
			b++;
			a = v.find('/', b);
			if (a == std::string::npos)
				port = atoi(v.substr(b).c_str());
			else
			{
				port = atoi(v.substr(b, a - b).c_str());
				m_path = v.substr(a);
			}
		}
	}
	m_protocol = protocol;
	m_host = host;
	m_port = port;
}
