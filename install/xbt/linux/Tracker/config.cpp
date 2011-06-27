#include "stdafx.h"
#include "config.h"

#include <find_ptr.h>
#include <socket.h>

Cconfig::Cconfig()
{
	fill_maps(NULL);
}

Cconfig::Cconfig(const Cconfig& v)
{
	fill_maps(&v);
}

const Cconfig& Cconfig::operator=(const Cconfig& v)
{
	fill_maps(&v);
	return *this;
}

void Cconfig::fill_maps(const Cconfig* v)
{
	{
		t_attribute<bool> attributes[] =
		{
			"auto_register", &m_auto_register, false,
			"anonymous_connect", &m_anonymous_connect, true,
			"anonymous_announce", &m_anonymous_announce, true,
			"anonymous_scrape", &m_anonymous_scrape, true,
			"daemon", &m_daemon, true,
			"debug", &m_debug, false,
			"full_scrape", &m_full_scrape, false,
			"gzip_debug", &m_gzip_debug, true,
			"gzip_scrape", &m_gzip_scrape, true,
			"log_access", &m_log_access, false,
			"log_announce", &m_log_announce, false,
			"log_scrape", &m_log_scrape, false,

			// TorrentPier begin
			"gdc", &m_gdc, true,
			"free_leech", &m_free_leech, false,
			"trust_ipv6", &m_trust_ipv6, false,
			// TorrentPier end

			NULL
		};
		fill_map(attributes, v ? &v->m_attributes_bool : NULL, m_attributes_bool);
	}
	{
		t_attribute<int> attributes[] =
		{
			"announce_interval", &m_announce_interval, 1800,
			"clean_up_interval", &m_clean_up_interval, 60,
			"read_config_interval", &m_read_config_interval, 60,
			"read_db_interval", &m_read_db_interval, 60,
			"scrape_interval", &m_scrape_interval, 0,
			"write_db_interval", &m_write_db_interval, 15,

			// TorrentPier begin
			"cheat_upload", &m_cheat_upload, 18,
			"read_files_interval", &m_read_files_interval, 60,
			// TorrentPier end

			NULL
		};
		fill_map(attributes, v ? &v->m_attributes_int : NULL, m_attributes_int);
	}
	{
		t_attribute<std::string> attributes[] =
		{
			"column_files_completed", &m_column_files_completed, "completed",
			"column_files_fid", &m_column_files_fid, "fid",
			"column_files_leechers", &m_column_files_leechers, "leechers",
			"column_files_seeders", &m_column_files_seeders, "seeders",
			"column_users_uid", &m_column_users_uid, "uid",
			"mysql_database", &m_mysql_database, "xbt",
			"mysql_host", &m_mysql_host, "localhost",
			"mysql_password", &m_mysql_password, "",
			"mysql_table_prefix", &m_mysql_table_prefix, "xbt_",
			"mysql_user", &m_mysql_user, "",
			"offline_message", &m_offline_message, "",
			"pid_file", &m_pid_file, "",
			"query_log", &m_query_log, "",
			"redirect_url", &m_redirect_url, "",
			"table_announce_log", &m_table_announce_log, "",
			"table_deny_from_hosts", &m_table_deny_from_hosts, "",
			"table_files", &m_table_files, "",
			"table_files_users", &m_table_files_users, "",
			"table_scrape_log", &m_table_scrape_log, "",
			"table_users", &m_table_users, "",
			"torrent_pass_private_key", &m_torrent_pass_private_key, "",

			// TorrentPier begin
			"column_files_dl_percent", &m_column_files_dl_percent, "",
			"column_users_can_leech", &m_column_users_can_leech, "",
			"column_users_torrents_limit", &m_column_users_torrents_limit, "",
			// TorrentPier end

			NULL, NULL, ""
		};
		fill_map(attributes, v ? &v->m_attributes_string : NULL, m_attributes_string);
	}
	if (v)
	{
		m_listen_ipas = v->m_listen_ipas;
		m_listen_ports = v->m_listen_ports;
	}
}

int Cconfig::set(const std::string& name, const std::string& value)
{
	if (t_attribute<std::string>* i = find_ptr(m_attributes_string, name))
		*i->value = value;
	else if (name == "listen_ipa")

	// TorrentPier begin
		m_listen_ipas.insert(value);
	else if (name == "listen_port")
		m_listen_ports.insert(value);
	// TorrentPier end

	else
		return set(name, atoi(value.c_str()));
	return 0;
}

int Cconfig::set(const std::string& name, int value)
{
	if (t_attribute<int>* i = find_ptr(m_attributes_int, name))
		*i->value = value;
	// TorrentPier // listen_port
	else
		return set(name, static_cast<bool>(value));
	return 0;
}

int Cconfig::set(const std::string& name, bool value)
{
	if (t_attribute<bool>* i = find_ptr(m_attributes_bool, name))
		*i->value = value;
	else
		return 1;
	return 0;
}
