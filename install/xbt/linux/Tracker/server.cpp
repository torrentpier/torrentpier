#include "stdafx.h"
#include "server.h"

#include <boost/format.hpp>
#include <sql/sql_query.h>
#include <iostream>
#include <sstream>
#include <signal.h>
#include <bt_misc.h>
#include <bt_strings.h>
#include <find_ptr.h>
#include <stream_int.h>
#include "transaction.h"

// TorrentPier begin
#include "md5.cpp"
#ifdef WIN32
#include <Ws2tcpip.h>
#endif

long long gcd(long long a, long long b) {
  long long c = 0;
  while (b) {
     c = a % b;
     a = b;
     b = c;        
  }
  return a;
}
// TorrentPier end

static volatile bool g_sig_term = false;

Cserver::Cserver(Cdatabase& database, const std::string& table_prefix, bool use_sql, const std::string& conf_file):
	m_database(database)
{
	m_fid_end = 0;

	for (int i = 0; i < 8; i++)
		m_secret = m_secret << 8 ^ rand();
	m_conf_file = conf_file;
	m_table_prefix = table_prefix;
	m_time = ::time(NULL);
	m_use_sql = use_sql;
}

int Cserver::run()
{
	read_config();
	if (test_sql())
		return 1;
	if (m_epoll.create(1 << 10) == -1)
	{
		std::cerr << "epoll_create failed" << std::endl;
		return 1;
	}
	t_tcp_sockets lt;
	t_udp_sockets lu;

	// TorrentPier begin
	struct addrinfo hints, *res, *res0;
	char hbuf[NI_MAXHOST], sbuf[NI_MAXSERV];
	memset(&hints, 0, sizeof(hints));
	hints.ai_socktype = SOCK_STREAM;
	hints.ai_flags = AI_PASSIVE;
	Csocket::start_up();

	BOOST_FOREACH(Cconfig::t_listen_ipas::const_reference j, m_config.m_listen_ipas)
	{
		BOOST_FOREACH(Cconfig::t_listen_ports::const_reference i, m_config.m_listen_ports)
		{
			if (getaddrinfo(j == "*" ? NULL : j.c_str(), i.c_str(), &hints, &res0)) {
				std::cerr << "getaddrinfo failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
				return 1;
			}
			for (res = res0; res; res = res->ai_next) {
				int s = ::socket(res->ai_family, res->ai_socktype, res->ai_protocol);
				if (s < 0) {
					std::cerr << "socket failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
					return 1;
				}
				Csocket l(s);
#ifdef IPV6_V6ONLY
				if (res->ai_family == AF_INET6 &&
					l.setsockopt(IPPROTO_IPV6, IPV6_V6ONLY, true)) {
					std::cerr << "IPV6_V6ONLY failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
				}
#endif
				if (l.blocking(false))
					std::cerr << "blocking failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
				else if (l.setsockopt(SOL_SOCKET, SO_REUSEADDR, true),
					::bind(s, res->ai_addr, res->ai_addrlen))
					std::cerr << "bind failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
				else if (::listen(s, INT_MAX))
					std::cerr << "listen failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
				else
				{
					if (getnameinfo(res->ai_addr, res->ai_addrlen, hbuf, sizeof(hbuf), sbuf, sizeof(sbuf), NI_NUMERICHOST | NI_NUMERICSERV))
						std::cerr << "getnameinfo failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
					else
						std::cerr << "Listen to " << hbuf << " " << sbuf << std::endl;
#ifdef SO_ACCEPTFILTER
					accept_filter_arg afa;
					bzero(&afa, sizeof(afa));
					strcpy(afa.af_name, "httpready");
					if (l.setsockopt(SOL_SOCKET, SO_ACCEPTFILTER, &afa, sizeof(afa)))
						std::cerr << "setsockopt failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
#elif TCP_DEFER_ACCEPT
					if (l.setsockopt(IPPROTO_TCP, TCP_DEFER_ACCEPT, true))
						std::cerr << "setsockopt failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
#endif
					lt.push_back(Ctcp_listen_socket(this, l));
					if (!m_epoll.ctl(EPOLL_CTL_ADD, l, EPOLLIN | EPOLLOUT | EPOLLPRI | EPOLLERR | EPOLLHUP, &lt.back()))
						continue;
				}
				return 1;
			}
		}

		/*
		BOOST_FOREACH(Cconfig::t_listen_ports::const_reference i, m_config.m_listen_ports)
		{
			Csocket l;
			if (l.open(SOCK_DGRAM) == INVALID_SOCKET)
				std::cerr << "socket failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
			else if (l.setsockopt(SOL_SOCKET, SO_REUSEADDR, true),
				l.bind(j, htons(i)))
				std::cerr << "bind failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
			else
			{
				lu.push_back(Cudp_listen_socket(this, l));
				if (!m_epoll.ctl(EPOLL_CTL_ADD, l, EPOLLIN | EPOLLPRI | EPOLLERR | EPOLLHUP, &lu.back()))
					continue;
			}
			return 1;
		}
		*/
	}
	// TorrentPier end

	clean_up();
	read_db_deny_from_hosts();
	read_db_files();
	read_db_users();
	write_db_files();
	write_db_users();
#ifndef WIN32
	if (m_config.m_daemon)
	{
		if (daemon(true, false))
			std::cerr << "daemon failed" << std::endl;
		std::ofstream(m_config.m_pid_file.c_str()) << getpid() << std::endl;
		struct sigaction act;
		act.sa_handler = sig_handler;
		sigemptyset(&act.sa_mask);
		act.sa_flags = 0;
		if (sigaction(SIGTERM, &act, NULL))
			std::cerr << "sigaction failed" << std::endl;
		act.sa_handler = SIG_IGN;
		if (sigaction(SIGPIPE, &act, NULL))
			std::cerr << "sigaction failed" << std::endl;
	}
#endif
#ifdef EPOLL
	boost::array<epoll_event, 64> events;
#else
	fd_set fd_read_set;
	fd_set fd_write_set;
	fd_set fd_except_set;
#endif
	while (!g_sig_term)
	{
#ifdef EPOLL
		int r = m_epoll.wait(events.data(), events.size(), 5000);
		if (r == -1)
			std::cerr << "epoll_wait failed: " << errno << std::endl;
		else
		{
			int prev_time = m_time;
			m_time = ::time(NULL);
			for (int i = 0; i < r; i++)
				reinterpret_cast<Cclient*>(events[i].data.ptr)->process_events(events[i].events);
			if (m_time == prev_time)
				continue;
			for (t_connections::iterator i = m_connections.begin(); i != m_connections.end(); )
			{
				if (i->run())
					i = m_connections.erase(i);
				else
					i++;
			}
		}
#else
		FD_ZERO(&fd_read_set);
		FD_ZERO(&fd_write_set);
		FD_ZERO(&fd_except_set);
		int n = 0;
		BOOST_FOREACH(t_connections::reference i, m_connections)
		{
			int z = i.pre_select(&fd_read_set, &fd_write_set);
			n = std::max(n, z);
		}
		BOOST_FOREACH(t_tcp_sockets::reference i, lt)
		{
			FD_SET(i.s(), &fd_read_set);
			n = std::max<int>(n, i.s());
		}
		BOOST_FOREACH(t_udp_sockets::reference i, lu)
		{
			FD_SET(i.s(), &fd_read_set);
			n = std::max<int>(n, i.s());
		}
		timeval tv;
		tv.tv_sec = 5;
		tv.tv_usec = 0;
		if (select(n + 1, &fd_read_set, &fd_write_set, &fd_except_set, &tv) == SOCKET_ERROR)
			std::cerr << "select failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
		else
		{
			m_time = ::time(NULL);
			BOOST_FOREACH(t_tcp_sockets::reference i, lt)
			{
				if (FD_ISSET(i.s(), &fd_read_set))
					accept(i.s());
			}
			BOOST_FOREACH(t_udp_sockets::reference i, lu)
			{
				if (FD_ISSET(i.s(), &fd_read_set))
					Ctransaction(*this, i.s()).recv();
			}
			for (t_connections::iterator i = m_connections.begin(); i != m_connections.end(); )
			{
				if (i->post_select(&fd_read_set, &fd_write_set))
					i = m_connections.erase(i);
				else
					i++;
			}
		}
#endif
		if (time() - m_read_config_time > m_config.m_read_config_interval)
                        read_config();
		else if (time() - m_clean_up_time > m_config.m_clean_up_interval)
			clean_up();
		else if (time() - m_read_db_deny_from_hosts_time > m_config.m_read_db_interval)
			read_db_deny_from_hosts();

		// TorrentPier begin
		else if (time() - m_read_db_files_time > m_config.m_read_files_interval)
		// TorrentPier end

			read_db_files();
		else if (time() - m_read_db_users_time > m_config.m_read_db_interval)
			read_db_users();
		else if (m_config.m_write_db_interval && time() - m_write_db_files_time > m_config.m_write_db_interval)
			write_db_files();
		else if (m_config.m_write_db_interval && time() - m_write_db_users_time > m_config.m_write_db_interval)
			write_db_users();
	}
	write_db_files();
	write_db_users();
	unlink(m_config.m_pid_file.c_str());
	return 0;
}

void Cserver::accept(const Csocket& l)
{
	// TorrentPier begin
	sockaddr_storage a;
	while (1)
	{
		socklen_t cb_a = sizeof(sockaddr_storage);
		// TorrentPier end

		Csocket s = ::accept(l, reinterpret_cast<sockaddr*>(&a), &cb_a);
		if (s == SOCKET_ERROR)
		{
			if (WSAGetLastError() == WSAECONNABORTED)
				continue;
			if (WSAGetLastError() != WSAEWOULDBLOCK)
				std::cerr << "accept failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
			break;
		}

		// TorrentPier begin
		if (a.ss_family == AF_INET) {
			sockaddr_in *b = reinterpret_cast<sockaddr_in*>(&a);

			t_deny_from_hosts::const_iterator i = m_deny_from_hosts.lower_bound(ntohl(b->sin_addr.s_addr));
			if (i != m_deny_from_hosts.end() && ntohl(b->sin_addr.s_addr) >= i->second.begin)
			{
				m_stats.rejected_tcp++;
				continue;
			}
			m_stats.accepted_tcp4++;
		} else if (a.ss_family == AF_INET6) m_stats.accepted_tcp6++;
		// TorrentPier end

		m_stats.accepted_tcp++;
		if (s.blocking(false))
			std::cerr << "ioctlsocket failed: " << Csocket::error2a(WSAGetLastError()) << std::endl;
		std::auto_ptr<Cconnection> connection(new Cconnection(this, s, a));
		connection->process_events(EPOLLIN);
		if (connection->s() != INVALID_SOCKET)
		{
			m_connections.push_back(connection.release());
			m_epoll.ctl(EPOLL_CTL_ADD, m_connections.back().s(), EPOLLIN | EPOLLOUT | EPOLLPRI | EPOLLERR | EPOLLHUP | EPOLLET, &m_connections.back());
		}
	}
}

std::string Cserver::insert_peer(const Ctracker_input& v, bool udp, t_user* user)
{
	if (m_use_sql && m_config.m_log_announce)
	{
		m_announce_log_buffer += Csql_query(m_database, "(?,?,?,?,?,?,?,?,?,?),")
			.p(ntohl(v.m_ipa)).p(ntohs(v.m_port)).p(v.m_event).p(v.m_info_hash).p(v.m_peer_id).p(v.m_downloaded).p(v.m_left).p(v.m_uploaded).p(user ? user->uid : 0).p(time()).read();
	}
	if (!m_config.m_offline_message.empty())
		return m_config.m_offline_message;

	// TorrentPier begin
	if (!m_config.m_auto_register && !file(v.m_info_hash))
		return bts_unregistered_torrent;

	t_file& file = m_files[v.m_info_hash];

	if (!m_config.m_anonymous_announce && !user && file.dl_percent >= 0)
		return bts_unregistered_torrent_pass;

	std::string xbt_error = "";
	if (v.m_left && v.m_event != Ctracker_input::e_paused && user && !user->can_leech && !m_config.m_free_leech)
		/*if (xbt_error.empty())*/ xbt_error = bts_can_not_leech;
	if (user && user->user_active == 0) //unactive
		xbt_error = bts_disabled;
	if (!file.ctime)
		file.ctime = time();
	if (v.m_left && v.m_event != Ctracker_input::e_paused &&
	    user && user->wait_time && file.ctime + user->wait_time > time() && !m_config.m_free_leech)
		/*return*/ if (xbt_error.empty()) xbt_error = bts_wait_time;

	t_peers::key_type peer_key = v.m_peer_id;
	t_peer* i = find_ptr(file.peers, peer_key);
	if (i)
	{
		if (i->xbt_error_empty)
		{
			(i->left != e_seeder ? file.leechers : file.seeders)--;
			if (t_user* old_user = find_user_by_uid(i->uid))
				(i->left == e_downloader ? old_user->incompletes : old_user->completes)--;
		}

		file.speed_ul -= i->speed_ul, file.speed_dl -= i->speed_dl;
	}

	if (i && i->xbt_error_empty) { }
	else if (v.m_left && v.m_event != Ctracker_input::e_paused &&
	         user && user->torrents_limit && user->incompletes >= user->torrents_limit && !m_config.m_free_leech)
	{
		/*return*/ if (xbt_error.empty()) xbt_error = bts_torrents_limit_reached;
	}
	else if (v.m_left && v.m_event != Ctracker_input::e_paused &&
	         user && user->peers_limit && !m_config.m_free_leech)
	{
		int c = 0, a = 0;
		BOOST_FOREACH(t_peers::reference j, file.peers) {
			c += j.second.left == e_downloader && j.second.uid == user->uid && j.second.xbt_error_empty;
			a += j.second.uid == user->uid && j.second.xbt_error_empty;
		}
		if (c >= user->peers_limit || a >= user->peers_limit * 3)
			/*return*/ if (xbt_error.empty()) xbt_error = bts_peers_limit_reached;
	}

	long long downloaded = 0, downloaded_db = 0, downspeed = 0;
	long long uploaded = 0, upspeed = 0;
	long long bonus = 0;
	long long rel = 0;
	long long ul_gdc = 0, ul_gdc_16k = 0;
	int ul_gdc_count = 0, ul_16k_count = 0, ul_eq_dl_count = 0;

	bool ipv6set = v.m_ipv6set && (v.m_family == AF_INET6 || m_config.m_trust_ipv6);

	if (m_use_sql && user && file.fid)
	{

		long long timespent = 0;
		if (i
			// && boost::equals(i->peer_id, v.m_peer_id)
			&& v.m_downloaded >= i->downloaded
			&& v.m_uploaded >= i->uploaded)
		{
			downloaded = v.m_downloaded - i->downloaded;
			uploaded = v.m_uploaded - i->uploaded;

			if( downloaded > 100000000000ll || uploaded > 100000000000ll ) {
				downloaded = uploaded = 0; // anti-hack
				if (xbt_error.empty()) xbt_error = bts_banned_client;
			}
			timespent = time() - i->mtime;
			if ((timespent << 1) > m_config.m_announce_interval)
			{
				upspeed = uploaded / timespent;
				downspeed = downloaded / timespent;
			}
			ul_gdc_count = i->ul_gdc_count;
			ul_16k_count = i->ul_16k_count;
			ul_gdc_16k = i->ul_gdc_16k;
			ul_eq_dl_count = i->ul_eq_dl_count;
			if( uploaded && m_config.m_gdc )
			{
				ul_gdc_count++;
				if (uploaded == downloaded) ul_eq_dl_count++;
				long long block = 16384;
				if( (uploaded % block) == 0ll )
				{
					ul_16k_count++;
					if( ul_16k_count > 1 )
						ul_gdc_16k = gcd(uploaded, ul_gdc_16k);
					else
						ul_gdc_16k = uploaded;
				}
				if( ul_gdc_count == ul_16k_count )
					ul_gdc = ul_gdc_16k;
				else
				{
					if( ul_gdc_count > 1 )
						ul_gdc = gcd(uploaded, i->ul_gdc);
					else
						ul_gdc = uploaded;
				}
			}
			else
			{
				ul_gdc = i->ul_gdc;
			}

			downloaded_db = (m_config.m_free_leech || file.dl_percent<0) ? 0 : (downloaded * file.dl_percent /100);

			if (user->uid == file.tor_poster_id)
				rel = uploaded;
			else if (!v.m_left && file.seeders == 1)
				bonus = 1;

			// TorrentPier: bb_bt_users_dl_status
			int new_status = v.m_left ? 0 : 1;
			if (user->uid == file.tor_poster_id) new_status = -1;
			if (new_status != i->dl_status && file.tor_topic_id) {
				Csql_query q(m_database, "(?,?,?),"); // topic_id,user_id,user_status,update_time
				q.p(file.tor_topic_id);               // topic_id
				q.p(user->uid);                       // user_id
				q.p(new_status);
				m_users_dl_status_buffer += q.read();
				i->dl_status = new_status;
			}

			// TorrentPier: bb_bt_tor_dl_stat
			if (uploaded || downloaded) {
				Csql_query q(m_database, "(?,?,?,?,?,?),"); // torrent_id, user_id, attach_id, t_up_total, t_down_total
				q.p(file.fid);                              // torrent_id
				q.p(user->uid);                             // user_id
				q.p(file.tor_attach_id);                    // attach_id
				q.p(uploaded);
				q.p(downloaded_db);
				q.p(bonus ? uploaded * bonus / 100 : 0);
				m_tor_dl_stat_buffer += q.read();
			}

			// TorrentPier: bb_cheat_log
			long long cheat = (long long) m_config.m_cheat_upload * 1024 * 1024 * 1024;
			if (uploaded > cheat) {
				Csql_query q(m_database, "(?,?,?,?),"); // torrent_id, user_id, attach_id, t_up_total, t_down_total
				q.p(user->uid);                         // user_id
				q.p(uploaded);
				q.p(hex_encode(8, ntohl(v.m_ipa)));
				q.p(time());
				m_cheat_buffer += q.read();
			}
		}
		Csql_query q(m_database, "(?,?,?,?,?,?,?,?,?,?, ?,?,?,?, ?,?,?,?,?,?),");

		int cleanup_interval = static_cast<int>(2.5 * m_config.m_announce_interval);
		int min_cleanup = 3900; // 65 min
		if( cleanup_interval < min_cleanup ) cleanup_interval = min_cleanup;

		// TorrentPier
		std::string port_st, ip_st, peer_hash;
		port_st = ntohs(v.m_port);
		ip_st = hex_encode(8, ntohl(v.m_ipa));
		peer_hash = md5(v.m_info_hash+v.m_passkey+port_st+ip_st);

		q.p(file.fid);                                                  // torrent_id mediumint(8) unsigned NOT NULL default '0'
		q.p(v.m_peer_id);
		q.p(peer_hash);
		q.p(user->uid);                                                 // user_id mediumint(9) NOT NULL default '0'
		q.p(hex_encode(8, ntohl(v.m_ipa)));                             // ip char(8) binary NOT NULL default '0'
		q.p(const_memory_range(v.m_ipv6bin, ipv6set ? 16 : 0));         // ipv6 varchar(32)
		q.p(ntohs(v.m_port));                                           // port smallint(5) unsigned NOT NULL default '0'
		q.p(uploaded);                                                  // uploaded bigint(20) unsigned NOT NULL default '0'
		q.p(downloaded_db);                                             // downloaded bigint(20) unsigned NOT NULL default '0'
		q.p(v.m_left && v.m_event != Ctracker_input::e_paused ? 0 : 1); // seeder tinyint(1) NOT NULL default '0'
		q.p(user->uid==file.tor_poster_id),                             // releaser
		q.p(v.m_left ? (v.m_left>=file.tor_size ? 0 : ((file.tor_size-v.m_left)*100/file.tor_size)) : v.m_uploaded); // complete_percent bigint(20) unsigned NOT NULL default '0'
		q.p(upspeed);                                                   // speed_up mediumint(8) unsigned NOT NULL default '0'
		q.p(downspeed);                                                 // speed_down mediumint(8) unsigned NOT NULL default '0'
		q.p(time());                                                    // update_time int(11) NOT NULL default '0'
		q.p(xbt_error);
		q.p( ul_16k_count*3 > ul_gdc_count*2 ? ul_gdc_16k : ul_gdc );
		q.p(ul_gdc_count);
		q.p(ul_16k_count);
		q.p(ul_eq_dl_count);

		m_files_users_updates_buffer += q.read();

		if (downloaded || uploaded)
		{
			Csql_query q(m_database, "(?,?,?,?,?,?,?),");
			q.p(downloaded_db);
			q.p(uploaded);
			q.p(user->uid);
			q.p(rel);
			q.p(bonus ? uploaded / bonus : 0);
			q.p(upspeed);
			q.p(downspeed);
			m_users_updates_buffer += q.read();
		}

		// Double tracker fix
		if (v.m_event == Ctracker_input::e_completed && downloaded)
			file.completed++, file.completed_inc++;
	}
	else
	{
		if (v.m_event == Ctracker_input::e_completed)
			file.completed++, file.completed_inc++;
	}

	if (v.m_event == Ctracker_input::e_stopped)
		file.peers.erase(peer_key);
	else
	{
		t_peer& peer = file.peers[peer_key];
		peer.downloaded = i && v.m_downloaded < i->downloaded ? i->downloaded : v.m_downloaded;
		peer.left = v.m_left == 0 ? e_seeder : v.m_event == Ctracker_input::e_paused ? e_incomplete : e_downloader;
		// std::copy(v.m_peer_id.begin(), v.m_peer_id.end(), peer.peer_id.begin());
		peer.port = v.m_port;
		peer.uid = user ? user->uid : 0;
		peer.uploaded = i && v.m_uploaded < i->uploaded ? i->uploaded : v.m_uploaded;

		file.speed_ul += ( peer.speed_ul = upspeed );
		file.speed_dl += ( peer.speed_dl = downspeed );

		if (xbt_error.empty())
		{
			(peer.left != e_seeder ? file.leechers : file.seeders)++;
			if (user)
				(peer.left == e_downloader ? user->incompletes : user->completes)++;
		}

		peer.xbt_error_empty = xbt_error.empty();
		peer.ul_gdc = ul_gdc;
		peer.ul_gdc_16k = ul_gdc_16k;
		peer.ul_gdc_count = ul_gdc_count;
		peer.ul_16k_count = ul_16k_count;
		peer.ul_eq_dl_count = ul_eq_dl_count;

		if (ipv6set && v.m_protocol != 4) {
			peer.ipv6set = true;
			memcpy(peer.ipv6, v.m_ipv6bin, 16);
			m_stats.announced_with_ipv6++;
		}

		if ((v.m_family == AF_INET || (m_config.m_trust_ipv6 && v.m_ipa != 0)) && v.m_protocol != 6) peer.host_ = v.m_ipa;

		peer.mtime = time();
	}

	// TorrentPier: Fill seeder_last_seen & last_seeder_uid fields
	if (user && !v.m_left)
	{
		file.tor_last_seeder_uid = user->uid;
		file.tor_seeder_last_seen = time();
	}

	(udp ? m_stats.announced_udp : m_stats.announced_http)++;
	file.dirty = true;
	return xbt_error;
	// TorrentPier end
}

std::string Cserver::t_file::select_peers(const Ctracker_input& ti) const
{
	if (ti.m_event == Ctracker_input::e_stopped)
		return "";

	typedef std::vector<boost::array<char, 6> > t_candidates;

	t_candidates candidates;
	BOOST_FOREACH(t_peers::const_reference i, peers)
	{
		// TorrentPier begin
		if (((!ti.m_left || ti.m_event == Ctracker_input::e_paused) && i.second.left != e_downloader)
			|| !i.second.xbt_error_empty || !i.second.host_
			|| boost::equals(i.first, ti.m_peer_id))
			continue;
		boost::array<char, 6> v;
		memcpy(&v.front(), &i.second.host_, 4);
		// TorrentPier end

		memcpy(&v.front() + 4, &i.second.port, 2);
		candidates.push_back(v);
	}
	size_t c = ti.m_num_want < 0 ? 50 : std::min(ti.m_num_want, 50);
	std::string d;
	d.reserve(300);
	if (candidates.size() > c)
	{
		while (c--)
		{
			int i = rand() % candidates.size();
			d.append(candidates[i].begin(), candidates[i].end());
			candidates[i] = candidates.back();
			candidates.pop_back();
		}
	}
	else
	{
		BOOST_FOREACH(t_candidates::reference i, candidates)
			d.append(i.begin(), i.end());
	}
	return d;
}

// TorrentPier begin
std::string Cserver::t_file::select_peers6(const Ctracker_input& ti) const
{
	if (ti.m_event == Ctracker_input::e_stopped)
		return "";

	typedef std::vector<boost::array<char, 18> > t_candidates;

	t_candidates candidates;
	BOOST_FOREACH(t_peers::const_reference i, peers)
	{
		if (((!ti.m_left || ti.m_event == Ctracker_input::e_paused) && i.second.left != e_downloader)
			|| !i.second.xbt_error_empty ||!i.second.ipv6set
			|| boost::equals(i.first, ti.m_peer_id))
			continue;

		boost::array<char, 18> v;
		memcpy(&v.front(), i.second.ipv6, 16);
		memcpy(&v.front() + 16, &i.second.port, 2);
		candidates.push_back(v);
	}
	size_t c = ti.m_num_want < 0 ? 50 : std::min(ti.m_num_want, 50);
	std::string d;
	d.reserve(900);
	if (candidates.size() > c)
	{
		while (c--)
		{
			int i = rand() % candidates.size();
			d.append(candidates[i].begin(), candidates[i].end());
			candidates[i] = candidates.back();
			candidates.pop_back();
		}
	}
	else
	{
		BOOST_FOREACH(t_candidates::reference i, candidates)
			d.append(i.begin(), i.end());
	}
	return d;
}
// TorrentPier end

Cvirtual_binary Cserver::select_peers(const Ctracker_input& ti) const
{
	const t_file* f = file(ti.m_info_hash);
	if (!f)
		return Cvirtual_binary();
	// TorrentPier begin
	static int rnd = 0;

	long interval = config().m_announce_interval;
	if (ti.m_left) {
		interval >>= 1;
	} else {
		int peers = f->seeders + f->leechers + 1;
		interval = interval * (peers + f->seeders) / (peers + peers);
	}
	interval += (++rnd & 63) + (f->fid & 63) - 64;

	if (ti.m_protocol == 6) { // ti.m_family == AF_INET6 && !m_config.m_trust_ipv6) {
		std::string peers6 = f->select_peers6(ti);
		return Cvirtual_binary((boost::format("d8:completei%de10:incompletei%de8:intervali%de6:peers6%d:%se")
			% f->seeders % f->leechers % interval % peers6.size() % peers6).str());
	} else if (ti.m_protocol == 4) { // ti.m_family == AF_INET && !m_config.m_trust_ipv6) {
		std::string peers = f->select_peers(ti);
		return Cvirtual_binary((boost::format("d8:completei%de10:incompletei%de8:intervali%de5:peers%d:%se")
			% f->seeders % f->leechers % interval % peers.size() % peers).str());
	} else {
		std::string peers = f->select_peers(ti);
		std::string peers6 = f->select_peers6(ti);
		return Cvirtual_binary((boost::format("d8:completei%de10:incompletei%de8:intervali%de5:peers%d:%s6:peers6%d:%se")
			% f->seeders % f->leechers % interval % peers.size() % peers % peers6.size() % peers6).str());
	}
	// TorrentPier end
}

void Cserver::t_file::clean_up(time_t t, Cserver& server)
{
	for (t_peers::iterator i = peers.begin(); i != peers.end(); )
	{
		if (i->second.mtime < t)
		{
			// TorrentPier begin
			if (i->second.xbt_error_empty)
			{
				(i->second.left != e_seeder ? leechers : seeders)--;
				if (t_user* user = server.find_user_by_uid(i->second.uid))
					(i->second.left == e_downloader ? user->incompletes : user->completes)--;
			}
			/*
			if (i->second.uid)
				server.m_files_users_updates_buffer += Csql_query(server.m_database, "(0,0,0,0,-1,0,-1,?,?),").p(fid).p(i->second.uid).read();
			*/
			speed_ul -= i->second.speed_ul, speed_dl -= i->second.speed_dl;
			// TorrentPier end

			peers.erase(i++);
			dirty = true;
		}
		else
			i++;
	}
}

void Cserver::clean_up()
{
	// TorrentPier begin
	int cleanup_interval = static_cast<int>(2.5 * m_config.m_announce_interval);
	if( cleanup_interval < 1800 ) cleanup_interval = 1800;
	BOOST_FOREACH(t_files::reference i, m_files)
		i.second.clean_up(time() - cleanup_interval, *this);
	// TorrentPier end

	m_clean_up_time = time();
}

static byte* write_compact_int(byte* w, unsigned int v)
{
	if (v >= 0x200000)
	{
		*w++ = 0xe0 | (v >> 24);
		*w++ = v >> 16;
		*w++ = v >> 8;
	}
	else if (v >= 0x4000)
	{
		*w++ = 0xc0 | (v >> 16);
		*w++ = v >> 8;
	}
	else if (v >= 0x80)
		*w++ = 0x80 | (v >> 8);
	*w++ = v;
	return w;
}

Cvirtual_binary Cserver::scrape(const Ctracker_input& ti)
{
	if (m_use_sql && m_config.m_log_scrape)
	{
		Csql_query q(m_database, "(?,?,?),");
		q.p(ntohl(ti.m_ipa));
		if (ti.m_info_hash.empty())
			q.p_raw(const_memory_range("null"));
		else
			q.p(ti.m_info_hash);
		q.p(time());
		m_scrape_log_buffer += q.read();
	}
	std::string d;
	d += "d5:filesd";
	if (ti.m_info_hashes.empty())
	{
		m_stats.scraped_full++;
		if (ti.m_compact)
		{
			Cvirtual_binary d;
			byte* w = d.write_start(32 * m_files.size() + 1);
			*w++ = 'x';
			BOOST_FOREACH(t_files::reference i, m_files)
			{
				if (!i.second.leechers && !i.second.seeders)
					continue;
				memcpy(w, i.first.data(), i.first.size());
				w += i.first.size();
				w = write_compact_int(w, i.second.seeders);
				w = write_compact_int(w, i.second.leechers);
				w = write_compact_int(w, std::min(i.second.completed, 1));
			}
			d.resize(w - d);
			return d;
		}
		d.reserve(90 * m_files.size());
		BOOST_FOREACH(t_files::reference i, m_files)
		{
			if (i.second.leechers || i.second.seeders)
				d += (boost::format("20:%sd8:completei%de10:incompletei%dee") % i.first % i.second.seeders % i.second.leechers).str();
		}
	}
	else
	{
		m_stats.scraped_http++;
		BOOST_FOREACH(Ctracker_input::t_info_hashes::const_reference j, ti.m_info_hashes)
		{
			if (t_file* i = find_ptr(m_files, j))
				d += (boost::format("20:%sd8:completei%de10:incompletei%dee") % j % i->seeders % i->leechers).str();
		}
	}
	d += "e";
	if (m_config.m_scrape_interval)
		d += (boost::format("5:flagsd20:min_request_intervali%dee") % m_config.m_scrape_interval).str();
	d += "e";
	return Cvirtual_binary(d);
}

void Cserver::read_db_deny_from_hosts()
{
	m_read_db_deny_from_hosts_time = time();
	if (!m_use_sql)
		return;
	try
	{
		Csql_result result = Csql_query(m_database, "SELECT begin, end FROM ?").p_name(table_name(table_deny_from_hosts)).execute();
		BOOST_FOREACH(t_deny_from_hosts::reference i, m_deny_from_hosts)
			i.second.marked = true;
		for (Csql_row row; row = result.fetch_row(); )
		{
			t_deny_from_host& deny_from_host = m_deny_from_hosts[row[1].i()];
			deny_from_host.marked = false;
			deny_from_host.begin = row[0].i();
		}
		for (t_deny_from_hosts::iterator i = m_deny_from_hosts.begin(); i != m_deny_from_hosts.end(); )
		{
			if (i->second.marked)
				m_deny_from_hosts.erase(i++);
			else
				i++;
		}
	}
	catch (Cdatabase::exception&)
	{
	}
}

void Cserver::read_db_files()
{
	m_read_db_files_time = time();
	if (m_use_sql)
		read_db_files_sql();
	else if (!m_config.m_auto_register)
	{
		std::set<std::string> new_files;
		std::ifstream is("xbt_files.txt");
		std::string s;
		while (getline(is, s))
		{
			s = hex_decode(s);
			if (s.size() != 20)
				continue;
			m_files[s];
			new_files.insert(s);
		}
		for (t_files::iterator i = m_files.begin(); i != m_files.end(); )
		{
			if (!new_files.count(i->first))
				m_files.erase(i++);
			else
				i++;
		}
	}
}

// TorrentPier begin
void Cserver::read_db_files_sql()
{
	try
	{
		Csql_query q(m_database);
		if (!m_config.m_auto_register)
		{
			// XBT read only new torrents, so we need to mark deleted in "_del" table
			q = "SELECT rpad(info_hash,20,' '), ?, is_del, dl_percent FROM "+table_name(table_files)+"_del";
			q.p_name(column_name(column_files_fid));
			Csql_result result = q.execute();
			for (Csql_row row; row = result.fetch_row(); )
			{
			//	if (row[0].size() != 20) continue;
				// fix
				t_files::iterator i = m_files.find(row[0].s());
				if (i != m_files.end())
				{
					if (row[2].i())
					{
						for (t_peers::iterator j = i->second.peers.begin(); j != i->second.peers.end(); j++)
						{
							t_user* user = j->second.uid ? find_user_by_uid(j->second.uid) : NULL;
							if (user && j->second.xbt_error_empty)
								(j->second.left == e_downloader ? user->incompletes : user->completes)--;
						}
						m_files.erase(i);
					} else {
						i->second.dl_percent = row[3].i();
					}
				}
				// fix
				q = "DELETE FROM "+table_name(table_files)+"_del WHERE ? = ?";
				q.p_name(column_name(column_files_fid));
				q.p(row[1].i());
				q.execute();
			}
		}
		if (m_files.empty())
			m_database.query("UPDATE bb_bt_tracker_snap SET "
				+ column_name(column_files_leechers) + " = 0, "
				+ column_name(column_files_seeders) + " = 0, speed_up=0, speed_down=0");
		else if (m_config.m_auto_register)
			return;
		q = "SELECT rpad(bt.info_hash,20,' '), bt.?, bt.?, bt.reg_time, bt.size, bt.attach_id, bt.topic_id, bt.poster_id, "
			+ column_name(column_files_dl_percent) + " FROM ? bt WHERE reg_time >= ?";
		q.p_name(column_name(column_files_completed));
		q.p_name(column_name(column_files_fid));
		q.p_name(table_name(table_files));
		q.p(m_fid_end);
		Csql_result result = q.execute();
		for (Csql_row row; row = result.fetch_row(); )
		{
			m_fid_end = std::max(m_fid_end, static_cast<int>(row[3].i()) + 1);
			if (row[0].size() != 20 || m_files.find(row[0].s()) != m_files.end())
				continue;
			t_file& file = m_files[row[0].s()];
			if (file.fid)
				continue;
			file.completed = row[1].i();
			file.dirty = false;
			file.fid = row[2].i();
			file.ctime = row[3].i();
			file.tor_size = row[4].i();
			file.tor_attach_id = row[5].i();
			file.tor_topic_id = row[6].i();
			file.tor_poster_id = row[7].i();
			file.dl_percent = row[8].i();
		}
	}
	catch (Cdatabase::exception&)
	{
	}
}
// TorrentPier end

void Cserver::read_db_users()
{
	m_read_db_users_time = time();
	if (!m_use_sql)
		return;
	try
	{
		// TorrentPier begin
		Csql_query q(m_database, "SELECT bt.?, auth_key, " + column_name(column_users_can_leech) + ", "
			+ column_name(column_users_torrents_limit) + ", u.user_active FROM ? bt LEFT JOIN bb_users u ON (u.user_id = bt.user_id)");
		// TorrentPier end

		q.p_name(column_name(column_users_uid));
		q.p_name(table_name(table_users));
		Csql_result result = q.execute();
		BOOST_FOREACH(t_users::reference i, m_users)
			i.second.marked = true;
		m_users_torrent_passes.clear();
		for (Csql_row row; row = result.fetch_row(); )
		{
			t_user& user = m_users[row[0].i()];
			user.marked = false;

			// TorrentPier begin
			user.uid = row[0].i();
			user.wait_time = 0;
			user.torrents_limit = row[3].i();
			user.peers_limit = 2; // # of IP addresses user can leech from
			user.can_leech = row[2].i();
			user.user_active = row[4].i();
			if (row[1].size()) {
				user.passkey = row[1].s();
				m_users_torrent_passes[user.passkey] = &user;
			}
			// TorrentPier end
		}
		for (t_users::iterator i = m_users.begin(); i != m_users.end(); )
		{
			if (i->second.marked)
				m_users.erase(i++);
			else
				i++;
		}
	}
	catch (Cdatabase::exception&)
	{
	}
}

void Cserver::write_db_files()
{
	m_write_db_files_time = time();
	if (!m_use_sql)
		return;
	try
	{
		std::string buffer;
		BOOST_FOREACH(t_files::reference i, m_files)
		{
			t_file& file = i.second;
			if (!file.dirty)
				continue;
			if (!file.fid)
			{
				// TorrentPier begin
				Csql_query(m_database, "INSERT INTO ? (info_hash, reg_time) VALUES (?, unix_timestamp())").p_name(table_name(table_files)).p(i.first).execute();
				// TorrentPier end

				file.fid = m_database.insert_id();
			}

			// TorrentPier begin
			Csql_query q(m_database, "(?,?,?,?,?),");
			q.p(file.completed_inc); file.completed_inc = 0;
			q.p(file.fid);
			q.p(file.tor_seeder_last_seen); file.tor_seeder_last_seen = 0;
			q.p(file.speed_ul);
			q.p(file.speed_dl);
			buffer += q.read();
			// TorrentPier end

			file.dirty = false;
		}
		if (!buffer.empty())
		{
			buffer.erase(buffer.size() - 1);

			// TorrentPier begin
			m_database.query("INSERT INTO " + table_name(table_files) + " ("
				+ column_name(column_files_completed) + ", "
				+ column_name(column_files_fid)
				+ ", seeder_last_seen, speed_up, speed_down) VALUES "
				+ buffer
				+ " ON DUPLICATE KEY UPDATE speed_up = values(speed_up), speed_down = values(speed_down),"
				+ "  " + column_name(column_files_completed) + " = " + column_name(column_files_completed) + " + values(" + column_name(column_files_completed) + "),"
				+ "  seeder_last_seen = values(seeder_last_seen)"
			);
			// TorrentPier end
		}
	}
	catch (Cdatabase::exception&)
	{
	}
	if (!m_announce_log_buffer.empty())
	{
		try
		{
			m_announce_log_buffer.erase(m_announce_log_buffer.size() - 1);
			m_database.query("INSERT DELAYED INTO " + table_name(table_announce_log) + " (ipa, port, event, info_hash, peer_id, downloaded, left0, uploaded, uid, mtime) VALUES " + m_announce_log_buffer);
		}
		catch (Cdatabase::exception&)
		{
		}
		m_announce_log_buffer.erase();
	}
	if (!m_scrape_log_buffer.empty())
	{
		try
		{
			m_scrape_log_buffer.erase(m_scrape_log_buffer.size() - 1);
			m_database.query("INSERT DELAYED INTO " + table_name(table_scrape_log) + " (ipa, info_hash, mtime) VALUES " + m_scrape_log_buffer);
		}
		catch (Cdatabase::exception&)
		{
		}
		m_scrape_log_buffer.erase();
	}
}

// TorrentPier begin
void Cserver::write_db_users()
{
	m_write_db_users_time = time();

	if (!m_use_sql)
		return;

	if (!m_files_users_updates_buffer.empty())
	{
		m_files_users_updates_buffer.erase(m_files_users_updates_buffer.size() - 1);
		try
		{
			m_database.query("INSERT INTO " + table_name(table_files_users)
				+ " (topic_id, peer_id, peer_hash, user_id, ip, ipv6, port, uploaded, downloaded,  seeder, releaser, complete_percent, speed_up, speed_down, update_time, xbt_error, ul_gdc, ul_gdc_c, ul_16k_c, ul_eq_dl) VALUES "
				+ m_files_users_updates_buffer
				+ " on duplicate key update"
				+ "  topic_id = values(topic_id),"
				+ "  peer_id = values(peer_id),"
				+ "  peer_hash = values(peer_hash),"
				+ "  user_id = values(user_id),"
				+ "  ip = values(ip), ipv6 = values(ipv6),"
				+ "  port = values(port),"
				+ "  uploaded = uploaded + values(uploaded),"
				+ "  downloaded = downloaded + values(downloaded),"
				+ "  complete_percent = values(complete_percent),"
				+ "  seeder = values(seeder),"
				+ "  releaser = values(releaser),"
				+ "  speed_up = values(speed_up),"
				+ "  speed_down = values(speed_down),"
				+ "  up_add = up_add + values(uploaded),"
				+ "  down_add = down_add + values(downloaded),"
				+ "  update_time = values(update_time),"
				+ "  xbt_error = values(xbt_error), ul_gdc = values(ul_gdc), ul_gdc_c = values(ul_gdc_c), ul_16k_c = values(ul_16k_c), ul_eq_dl = values(ul_eq_dl)");
		}
		catch (Cdatabase::exception&)
		{
		}
		m_files_users_updates_buffer.erase();
	}

	if (!m_users_updates_buffer.empty())
	{
		m_users_updates_buffer.erase(m_users_updates_buffer.size() - 1);
		try
		{
			m_database.query("INSERT INTO " + table_name(table_users) + " (u_down_total, u_up_total, " + column_name(column_users_uid) + ", u_up_release, u_up_bonus, max_up_speed, max_down_speed) VALUES "
				+ m_users_updates_buffer
				+ " on duplicate key update"
				+ "  u_down_total = u_down_total + values(u_down_total),"
				+ "  u_up_total = u_up_total + values(u_up_total),"
				+ "  u_up_release = u_up_release + values(u_up_release),"
				+ "  u_up_bonus = u_up_bonus + values(u_up_bonus),"
				+ "  max_up_speed = GREATEST(max_up_speed, values(max_up_speed)),"
				+ "  max_down_speed = GREATEST(max_down_speed, values(max_down_speed)),"
				+ "  u_down_today = u_down_today + values(u_down_total),"
				+ "  u_up_today = u_up_today + values(u_up_total),"
				+ "  u_release_today = u_release_today + values(u_up_release),"
				+ "  u_bonus_today = u_bonus_today + values(u_up_bonus),"
				+ "  u_up_speed_today = GREATEST(u_up_speed_today, values(max_up_speed)),"
				+ "  u_down_speed_today = GREATEST(u_down_speed_today, values(max_down_speed))");
		}
		catch (Cdatabase::exception&)
		{
		}
		m_users_updates_buffer.erase();
	}

	if (!m_users_dl_status_buffer.empty())
	{
		m_users_dl_status_buffer.erase(m_users_dl_status_buffer.size() - 1);
		try
		{
			m_database.query("INSERT INTO bb_bt_dlstatus_main (topic_id,user_id,user_status) VALUES"
				+ m_users_dl_status_buffer
				+ " on duplicate key update"
				+ "  user_status = values(user_status)");
		}
		catch (Cdatabase::exception&)
		{
		}
		m_users_dl_status_buffer.erase();
	}

	if (!m_tor_dl_stat_buffer.empty())
	{
		m_tor_dl_stat_buffer.erase(m_tor_dl_stat_buffer.size() - 1);
		try
		{
			m_database.query("INSERT INTO bb_bt_torrent_activity(user_id, topic_id, torrent_upload, torrent_download, torrent_speed_up, torrent_speed_down, torrent_dl_status, torrent_bonus, torrent_status, torrent_all_download, torrent_time_st, torrent_time) VALUES"
				+ m_tor_dl_stat_buffer
				+ " on duplicate key update"
				+ "  torrent_upload = torrent_upload + values(torrent_upload),"
				+ "  torrent_speed_up = GREATEST(torrent_speed_up, values(torrent_speed_up)),"
				+ "  torrent_speed_down = GREATEST(torrent_speed_down, values(torrent_speed_down)),"
				+ "  torrent_download = torrent_download + values(torrent_download),"
				+ "  torrent_all_download = torrent_all_download + values(torrent_all_download),"
				+ "  torrent_time = torrent_time + values(torrent_time),"
			 	+ "  torrent_status = GREATEST(torrent_status, values(torrent_status)),"
				+ "  torrent_bonus = torrent_bonus + values(torrent_bonus)");
		}
		catch (Cdatabase::exception&)
		{
		}
		m_tor_dl_stat_buffer.erase();
	}

	// Cheat
	if (!m_cheat_buffer.empty())
	{
		m_cheat_buffer.erase(m_cheat_buffer.size() - 1);
		try
		{
			m_database.query("INSERT INTO bb_bt_cheat_log (cheat_user_id, cheat_uploaded, cheat_ip, cheat_log_time) VALUES"
				+ m_cheat_buffer);
		}
		catch (Cdatabase::exception&)
		{
		}
		m_cheat_buffer.erase();
	}
}
// TorrentPier end

void Cserver::read_config()
{
	if (m_use_sql)
	{
		try
		{
			Csql_result result = m_database.query("SELECT name, value FROM " + table_name(table_config) + " WHERE value is not null");
			Cconfig config;
			for (Csql_row row; row = result.fetch_row(); )
			{
				if (config.set(row[0].s(), row[1].s()))
					std::cerr << "unknown config name: " << row[0].s() << std::endl;
			}
			config.load(m_conf_file);
			if (config.m_torrent_pass_private_key.empty())
			{
				config.m_torrent_pass_private_key = generate_random_string(27);
				Csql_query(m_database, "INSERT INTO xbt_config (name, value) VALUES ('torrent_pass_private_key', ?)").p(config.m_torrent_pass_private_key).execute();
			}
			m_config = config;
		}
		catch (Cdatabase::exception&)
		{
		}
	}
	else
	{
		Cconfig config;
		if (!config.load(m_conf_file))
			m_config = config;
	}

	// TorrentPier begin
	if (m_config.m_listen_ipas.empty())
		m_config.m_listen_ipas.insert("*");
	if (m_config.m_listen_ports.empty())
		m_config.m_listen_ports.insert("2710");
	// TorrentPier end

	m_read_config_time = time();
}

void Cserver::t_file::debug(std::ostream& os) const
{
	BOOST_FOREACH(t_peers::const_reference i, peers)
	{
		// TorrentPier begin
		os << "<tr><td>" + Csocket::inet_ntoa(i.second.host_)
			<< "<td align=right>" << (i.second.ipv6set ? hex_encode(const_memory_range(i.second.ipv6,16)) : "")
			// TorrentPier end

			<< "<td align=right>" << ntohs(i.second.port)
			<< "<td align=right>" << i.second.uid
			<< "<td align=right>" << i.second.left
			<< "<td align=right>" << ::time(NULL) - i.second.mtime

			// TorrentPier begin
			<< "<td>" << hex_encode(const_memory_range(i.first.c_str(), 20));
			// TorrentPier end
	}
}

std::string Cserver::debug(const Ctracker_input& ti) const
{
	std::ostringstream os;
	os << "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\"><meta http-equiv=refresh content=60><title>XBT Tracker</title>";
	int leechers = 0;
	int seeders = 0;
	int torrents = 0;
	os << "<table>";
	if (ti.m_info_hash.empty())
	{
		BOOST_FOREACH(t_files::const_reference i, m_files)
		{
			if (!i.second.leechers && !i.second.seeders)
				continue;
			leechers += i.second.leechers;
			seeders += i.second.seeders;
			torrents++;
			os << "<tr><td align=right>" << i.second.fid
				<< "<td><a href=\"?info_hash=" << uri_encode(i.first) << "\">" << hex_encode(i.first) << "</a>"
				<< "<td>" << (i.second.dirty ? '*' : ' ')
				<< "<td align=right>" << i.second.leechers
				<< "<td align=right>" << i.second.seeders;
		}
	}
	else
	{
		if (const t_file* i = find_ptr(m_files, ti.m_info_hash))
			i->debug(os);
	}
	os << "</table>";
	return os.str();
}

std::string Cserver::statistics() const
{
	std::ostringstream os;
	os << "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\"><meta http-equiv=refresh content=60><title>XBT Tracker</title>";
	int leechers = 0;
	int seeders = 0;
	int torrents = 0;
	BOOST_FOREACH(t_files::const_reference i, m_files)
	{
		leechers += i.second.leechers;
		seeders += i.second.seeders;
		torrents += i.second.leechers || i.second.seeders;
	}
	time_t t = time();
	os << "<table><tr><td>leechers<td align=right>" << leechers
		<< "<tr><td>seeders<td align=right>" << seeders
		<< "<tr><td>peers<td align=right>" << leechers + seeders
		<< "<tr><td>torrents<td align=right>" << torrents
		<< "<tr><td>"
		<< "<tr><td>accepted tcp<td align=right>" << m_stats.accepted_tcp

		// TorrentPier begin
		<< "<tr><td>accepted tcp4<td align=right>" << m_stats.accepted_tcp4 << "<td align=right>" << m_stats.accepted_tcp4 * 100 / m_stats.accepted_tcp << " %"
		<< "<tr><td>accepted tcp6<td align=right>" << m_stats.accepted_tcp6 << "<td align=right>" << m_stats.accepted_tcp6 * 100 / m_stats.accepted_tcp << " %"
		// TorrentPier end

		<< "<tr><td>rejected tcp<td align=right>" << m_stats.rejected_tcp
		<< "<tr><td>announced<td align=right>" << m_stats.announced();
	if (m_stats.announced())
	{
		os << "<tr><td>announced http <td align=right>" << m_stats.announced_http << "<td align=right>" << m_stats.announced_http * 100 / m_stats.announced() << " %"
			<< "<tr><td>announced udp<td align=right>" << m_stats.announced_udp << "<td align=right>" << m_stats.announced_udp * 100 / m_stats.announced() << " %"

			// TorrentPier begin
			<< "<tr><td>with &amp;ipv6=<td align=right>" << m_stats.announced_with_ipv6 << "<td align=right>" << m_stats.announced_with_ipv6 * 100 / m_stats.announced() << " %";
			// TorrentPier end
	}
	os << "<tr><td>scraped full<td align=right>" << m_stats.scraped_full
		<< "<tr><td>scraped<td align=right>" << m_stats.scraped();
	if (m_stats.scraped())
	{
		os << "<tr><td>scraped http<td align=right>" << m_stats.scraped_http << "<td align=right>" << m_stats.scraped_http * 100 / m_stats.scraped() << " %"
			<< "<tr><td>scraped udp<td align=right>" << m_stats.scraped_udp << "<td align=right>" << m_stats.scraped_udp * 100 / m_stats.scraped() << " %";
	}
	os << "<tr><td>"
		<< "<tr><td>up time<td align=right>" << duration2a(time() - m_stats.start_time)
		<< "<tr><td>"
		<< "<tr><td>anonymous connect<td align=right>" << m_config.m_anonymous_connect
		<< "<tr><td>anonymous announce<td align=right>" << m_config.m_anonymous_announce
		<< "<tr><td>anonymous scrape<td align=right>" << m_config.m_anonymous_scrape
		<< "<tr><td>auto register<td align=right>" << m_config.m_auto_register
		<< "<tr><td>full scrape<td align=right>" << m_config.m_full_scrape

		// TorrentPier begin
		<< "<tr><td>free leech<td align=right>" << m_config.m_free_leech
		<< "<tr><td>announce interval<td align=right>" << m_config.m_announce_interval
		// TorrentPier end

		<< "<tr><td>read config time<td align=right>" << t - m_read_config_time << " / " << m_config.m_read_config_interval
		<< "<tr><td>clean up time<td align=right>" << t - m_clean_up_time << " / " << m_config.m_clean_up_interval

		// TorrentPier begin
		<< "<tr><td>read db files time<td align=right>" << t - m_read_db_files_time << " / " << m_config.m_read_files_interval;
		// TorrentPier end

	if (m_use_sql)
	{
		os << "<tr><td>read db users time<td align=right>" << t - m_read_db_users_time << " / " << m_config.m_read_db_interval
			<< "<tr><td>write db files time<td align=right>" << t - m_write_db_files_time << " / " << m_config.m_write_db_interval
			<< "<tr><td>write db users time<td align=right>" << t - m_write_db_users_time << " / " << m_config.m_write_db_interval;
	}
	os << "</table><font size=2><div align=\"right\"><a href=\"http://ivbt.ru\">Adapted by GliX</a></div></font>";
	return os.str();
}

Cserver::t_user* Cserver::find_user_by_torrent_pass(const std::string& v, const std::string& info_hash)
{
	// TorrentPier begin
	if (v.size() == 32) if (t_user* user = find_user_by_uid(read_int(4, hex_decode(v.substr(0, 8)))))
	{
		if (Csha1((boost::format("%s %s %d %s") % m_config.m_torrent_pass_private_key % user->passkey % user->uid % info_hash).str()).read().substr(0, 12) == hex_decode(v.substr(8)))
			return user;
	}
	// TorrentPier end

	t_user** i = find_ptr(m_users_torrent_passes, v);
	return i ? *i : NULL;
}

Cserver::t_user* Cserver::find_user_by_uid(int v)
{
	return find_ptr(m_users, v);
}

void Cserver::sig_handler(int v)
{
	switch (v)
	{
	case SIGTERM:
		g_sig_term = true;
		break;
	}
}

void Cserver::term()
{
	g_sig_term = true;
}

std::string Cserver::column_name(int v) const
{
	switch (v)
	{
	case column_files_completed:
		return m_config.m_column_files_completed;
	case column_files_leechers:
		return m_config.m_column_files_leechers;
	case column_files_seeders:
		return m_config.m_column_files_seeders;
	case column_files_fid:
		return m_config.m_column_files_fid;
	case column_users_uid:
		return m_config.m_column_users_uid;

	// TorrentPier begin
	case column_files_dl_percent:
		return m_config.m_column_files_dl_percent;
	case column_users_can_leech:
		return m_config.m_column_users_can_leech;
	case column_users_torrents_limit:
		return m_config.m_column_users_torrents_limit;
	// TorrentPier end

	}
	assert(false);
	return "";
}

std::string Cserver::table_name(int v) const
{
	switch (v)
	{
	case table_announce_log:
		return m_config.m_table_announce_log.empty() ? m_table_prefix + "announce_log" : m_config.m_table_announce_log;
	case table_config:
		return m_table_prefix + "config";
	case table_deny_from_hosts:
		return m_config.m_table_deny_from_hosts.empty() ? m_table_prefix + "deny_from_hosts" : m_config.m_table_deny_from_hosts;
	case table_files:
		return m_config.m_table_files.empty() ? m_table_prefix + "files" : m_config.m_table_files;
	case table_files_users:
		return m_config.m_table_files_users.empty() ? m_table_prefix + "files_users" : m_config.m_table_files_users;
	case table_scrape_log:
		return m_config.m_table_scrape_log.empty() ? m_table_prefix + "scrape_log" : m_config.m_table_scrape_log;
	case table_users:
		return m_config.m_table_users.empty() ? m_table_prefix + "users" : m_config.m_table_users;
	}
	assert(false);
	return "";
}

int Cserver::test_sql()
{
	if (!m_use_sql)
		return 0;
	try
	{
		/*mysql_get_server_version(m_database.handle());
		//xbtt
		m_database.query("select id, ipa, port, event, info_hash, peer_id, downloaded, left0, uploaded, uid, mtime from " + table_name(table_announce_log) + " where 0");
		m_database.query("select name, value from " + table_name(table_config) + " where 0");
		\m_database.query("select begin, end from " + table_name(table_deny_from_hosts) + " where 0");
		m_database.query("select id, ipa, info_hash, uid, mtime from " + table_name(table_scrape_log) + " where 0");

		// TorrentPier begin
		m_database.query("select peer_hash, user_id, ip, ipv6, port, uploaded, downloaded, seeder, speed_up, speed_down, update_time, ul_gdc, ul_gdc_c, ul_16k_c, ul_eq_dl from " + table_name(table_files_users) + " where 0"); // Note: `port_open` is not used any more
		m_database.query("select " + column_name(column_users_uid) + ", auth_key, "
                + column_name(column_users_can_leech) + " as u_cl, " + column_name(column_users_torrents_limit)
                + " as u_tl, u_down_total, u_up_total, u_up_release, u_down_today, u_up_today, u_up_bonus, max_up_speed, max_down_speed from " + table_name(table_users) + " where 0");
	
		m_database.query("select bbt.info_hash, bt.seeder, bbt.reg_time, bbt.size, bbt.attach_id, bbt.topic_id, bbt.seeder_last_seen, bt.speed_up, bt.speed_down, bbt.poster_id, "
                   + column_name(column_files_dl_percent) + " from " + table_name(table_files) + " bbt LEFT JOIN bb_bt_tracker bt ON (bt.topic_id = bbt.topic_id) where 0");
		// TorrentPier: Files deletion table = table_name(table_files) + "_del" suffix
		\m_database.query("select rpad(info_hash,20,' '), " + column_name(column_files_fid)
                \+ ", is_del, dl_percent from " + table_name(table_files) + " where 0");
		m_database.query("select topic_id,user_id,user_status,last_modified_dlstatus from bb_bt_dlstatus_main where 0");
		m_database.query("select torrent_id,user_id,attach_id,t_up_total,t_down_total,t_bonus_total from bb_bt_tor_dl_stat where 0");
		// TorrentPier end

		m_read_users_can_leech = m_database.query("show columns from " + table_name(table_users) + " like 'can_leech'");
		m_read_users_peers_limit = m_database.query("show columns from " + table_name(table_users) + " like 'peers_limit'");
		m_read_users_torrent_pass = m_database.query("show columns from " + table_name(table_users) + " like 'torrent_pass'");
		m_read_users_torrents_limit = m_database.query("show columns from " + table_name(table_users) + " like 'torrents_limit'");
		m_read_users_wait_time = m_database.query("show columns from " + table_name(table_users) + " like 'wait_time'");
		*/
		return 0;
	}
	catch (Cdatabase::exception&)
	{
	}
	return 1;
}
