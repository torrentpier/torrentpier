#pragma once

#include <string>

// TorrentPier begin
#include <socket.h>
// TorrentPier end

class Ctracker_input
{
public:
	void set(const std::string& name, const std::string& value);
	bool valid() const;

	// TorrentPier begin
	bool banned() const;
	Ctracker_input(int family = AF_INET);
	// TorrentPier end

	enum t_event
	{
		e_none,
		e_completed,
		e_started,
		e_stopped,
		e_paused,
	};

	typedef std::vector<std::string> t_info_hashes;

	t_event m_event;
	std::string m_info_hash;
	t_info_hashes m_info_hashes;
	int m_ipa;
	std::string m_peer_id;
	long long m_downloaded;
	long long m_left;
	int m_port;
	long long m_uploaded;
	int m_num_want;
	bool m_compact;

	// TorrentPier begin
	std::string m_passkey;
	bool m_ipv6set;
	char m_ipv6bin[16];
	int m_family;
	int m_protocol; // 4 for IPv4-only, 6 for IPv6-only
	// TorrentPier end
};
