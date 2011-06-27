#include "stdafx.h"
#include "transaction.h"

#include <bt_misc.h>
#include <bt_strings.h>
#include <iostream>
#include <sha1.h>
#include <stream_int.h>

Ctransaction::Ctransaction(Cserver& server, const Csocket& s):
	m_server(server)
{
	m_s = s;
}

long long Ctransaction::connection_id() const
{
	const int cb_s = 12;
	char s[cb_s];
	write_int(8, s, m_server.secret());
	write_int(4, s + 8, m_a.sin_addr.s_addr);
	char d[20];
	(Csha1(const_memory_range(s, cb_s))).read(d);
	return read_int(8, d);
}

void Ctransaction::recv()
{
	const int cb_b = 2 << 10;
	char b[cb_b];
	while (1)
	{
		socklen_t cb_a = sizeof(sockaddr_in);
		int r = m_s.recvfrom(memory_range(b, cb_b), reinterpret_cast<sockaddr*>(&m_a), &cb_a);
		if (r == SOCKET_ERROR)
		{
			if (WSAGetLastError() != WSAEWOULDBLOCK)
				std::cerr << "recv failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
			return;
		}
		if (r < uti_size)
			return;
		switch (read_int(4, b + uti_action, b + r))
		{
		case uta_connect:
			if (r >= utic_size)
				send_connect(const_memory_range(b, r));
			break;
		case uta_announce:
			if (r >= utia_size)
				send_announce(const_memory_range(b, r));
			break;
		case uta_scrape:
			if (r >= utis_size)
				send_scrape(const_memory_range(b, r));
			break;
		}
	}
}

void Ctransaction::send_connect(const_memory_range r)
{
	if (!m_server.config().m_anonymous_connect)
		return;
	if (read_int(8, r + uti_connection_id, r.end) != 0x41727101980ll)
		return;
	const int cb_d = 2 << 10;
	char d[cb_d];
	write_int(4, d + uto_action, uta_connect);
	write_int(4, d + uto_transaction_id, read_int(4, r + uti_transaction_id, r.end));
	write_int(8, d + utoc_connection_id, connection_id());
	send(const_memory_range(d, utoc_size));
}

void Ctransaction::send_announce(const_memory_range r)
{
	if (read_int(8, r + uti_connection_id, r.end) != connection_id())
		return;
	if (!m_server.config().m_anonymous_announce)
	{
		send_error(r, "access denied");
		return;
	}
	Ctracker_input ti;
	ti.m_downloaded = read_int(8, r + utia_downloaded, r.end);
	ti.m_event = static_cast<Ctracker_input::t_event>(read_int(4, r + utia_event, r.end));
	ti.m_info_hash.assign(reinterpret_cast<const char*>(r + utia_info_hash), 20);
	ti.m_ipa = read_int(4, r + utia_ipa, r.end) && is_private_ipa(m_a.sin_addr.s_addr)
		? htonl(read_int(4, r + utia_ipa, r.end))
		: m_a.sin_addr.s_addr;
	ti.m_left = read_int(8, r + utia_left, r.end);
	ti.m_num_want = read_int(4, r + utia_num_want, r.end);
	ti.m_peer_id.assign(reinterpret_cast<const char*>(r + utia_peer_id), 20);
	ti.m_port = htons(read_int(2, r + utia_port, r.end));
	ti.m_uploaded = read_int(8, r + utia_uploaded, r.end);
	std::string error = m_server.insert_peer(ti, true, NULL);
	if (!error.empty())
	{
		send_error(r, error);
		return;
	}
	const Cserver::t_file* file = m_server.file(ti.m_info_hash);
	if (!file)
		return;
	const int cb_d = 2 << 10;
	char d[cb_d];
	write_int(4, d + uto_action, uta_announce);
	write_int(4, d + uto_transaction_id, read_int(4, r + uti_transaction_id, r.end));
	write_int(4, d + utoa_interval, m_server.config().m_announce_interval);
	write_int(4, d + utoa_leechers, file->leechers);
	write_int(4, d + utoa_seeders, file->seeders);
	std::string peers = file->select_peers(ti);
	memcpy(d + utoa_size, peers.data(), peers.size());
	send(const_memory_range(d, d + utoa_size + peers.size()));
}

void Ctransaction::send_scrape(const_memory_range r)
{
	if (read_int(8, r + uti_connection_id, r.end) != connection_id())
		return;
	if (!m_server.config().m_anonymous_scrape)
	{
		send_error(r, "access denied");
		return;
	}
	const int cb_d = 2 << 10;
	char d[cb_d];
	write_int(4, d + uto_action, uta_scrape);
	write_int(4, d + uto_transaction_id, read_int(4, r + uti_transaction_id, r.end));
	char* w = d + utos_size;
	for (r += utis_size; r + 20 <= r.end && w + 12 <= d + cb_d; r += 20)
	{
		if (const Cserver::t_file* file = m_server.file(r.sub_range(0, 20).string()))
		{
			w = write_int(4, w, file->seeders);
			w = write_int(4, w, file->completed);
			w = write_int(4, w, file->leechers);
		}
		else
		{
			w = write_int(4, w, 0);
			w = write_int(4, w, 0);
			w = write_int(4, w, 0);
		}
	}
	m_server.stats().scraped_udp++;
	send(const_memory_range(d, w));
}

void Ctransaction::send_error(const_memory_range r, const std::string& msg)
{
	const int cb_d = 2 << 10;
	char d[cb_d];
	write_int(4, d + uto_action, uta_error);
	write_int(4, d + uto_transaction_id, read_int(4, r + uti_transaction_id, r.end));
	memcpy(d + utoe_size, msg.data(), msg.size());
	send(const_memory_range(d, utoe_size + msg.size()));
}

void Ctransaction::send(const_memory_range b)
{
	if (m_s.sendto(b, reinterpret_cast<const sockaddr*>(&m_a), sizeof(sockaddr_in)) != b.size())
		std::cerr << "send failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
}
