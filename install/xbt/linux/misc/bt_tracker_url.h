#pragma once

#include <string>

class Cbt_tracker_url
{
public:
	enum
	{
		tp_http,
		tp_udp,
		tp_unknown
	};
	
	void clear();
	bool valid() const;
	void write(const std::string&);
	Cbt_tracker_url(const std::string&);
	Cbt_tracker_url();

	int m_protocol;
	std::string m_host;
	int m_port;
	std::string m_path;
};
