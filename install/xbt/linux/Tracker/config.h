#pragma once

#include <config_base.h>

class Cconfig: public Cconfig_base
{
public:
	// TorrentPier begin
	typedef std::set<std::string> t_listen_ipas;
	typedef std::set<std::string> t_listen_ports;
	// TorrentPier end

	int set(const std::string& name, const std::string& value);
	int set(const std::string& name, int value);
	int set(const std::string& name, bool value);
	Cconfig();
	Cconfig(const Cconfig&);
	const Cconfig& operator=(const Cconfig&);

	bool m_anonymous_announce;
	bool m_anonymous_connect;
	bool m_anonymous_scrape;
	bool m_auto_register;
	bool m_daemon;
	bool m_debug;
	bool m_full_scrape;
	bool m_gzip_debug;
	bool m_gzip_scrape;
	bool m_log_access;
	bool m_log_announce;
	bool m_log_scrape;
	int m_announce_interval;
	int m_clean_up_interval;
	int m_read_config_interval;
	int m_read_db_interval;
	int m_scrape_interval;
	int m_write_db_interval;
	std::string m_column_files_completed;
	std::string m_column_files_fid;
	std::string m_column_files_leechers;
	std::string m_column_files_seeders;
	std::string m_column_users_uid;
	std::string m_mysql_database;
	std::string m_mysql_host;
	std::string m_mysql_password;
	std::string m_mysql_table_prefix;
	std::string m_mysql_user;
	std::string m_offline_message;
	std::string m_query_log;
	std::string m_pid_file;
	std::string m_redirect_url;
	std::string m_table_announce_log;
	std::string m_table_deny_from_hosts;
	std::string m_table_files;
	std::string m_table_files_users;
	std::string m_table_scrape_log;
	std::string m_table_users;
	std::string m_torrent_pass_private_key;
	t_listen_ipas m_listen_ipas;
	t_listen_ports m_listen_ports;

	// TorrentPier begin
	bool m_gdc;
	bool m_free_leech;
	std::string m_column_files_dl_percent;
	std::string m_column_users_can_leech;
	std::string m_column_users_torrents_limit;
	int m_read_files_interval;
	bool m_trust_ipv6;
	int m_cheat_upload;
	// TorrentPier end

private:
	void fill_maps(const Cconfig*);
};
