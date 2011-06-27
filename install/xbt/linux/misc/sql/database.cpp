#include "stdafx.h"
#include "database.h"

#include <fstream>
#include <iostream>
#include <stdexcept>

#ifdef WIN32
#pragma comment(lib, "libmysql")
#else
#include <syslog.h>
#endif

Cdatabase::Cdatabase()
{
	mysql_init(&m_handle);
}

Cdatabase::~Cdatabase()
{
	close();
}

void Cdatabase::open(const std::string& host, const std::string& user, const std::string& password, const std::string& database, bool echo_errors)
{
	m_echo_errors = echo_errors;
	if (!mysql_init(&m_handle) || mysql_options(&m_handle, MYSQL_READ_DEFAULT_GROUP, "") || !mysql_real_connect(&m_handle, host.c_str(), user.c_str(), password.empty() ? NULL : password.c_str(), database.c_str(), 0, NULL, 0))
		throw exception(mysql_error(&m_handle));
	char a0 = true;
	mysql_options(&m_handle, MYSQL_OPT_RECONNECT, &a0);
}

Csql_result Cdatabase::query(const std::string& q)
{
	if (!m_query_log.empty())
	{
		static std::ofstream f(m_query_log.c_str());
		f << q.substr(0, 239) << std::endl;
	}
	if (mysql_real_query(&m_handle, q.data(), q.size()))
	{
		if (m_echo_errors)
		{
			std::cerr << mysql_error(&m_handle) << std::endl
				<< q.substr(0, 239) << std::endl;
		}
#ifndef WIN32
		syslog(LOG_ERR, "%s", mysql_error(&m_handle));
#endif
		throw exception(mysql_error(&m_handle));
	}
	MYSQL_RES* result = mysql_store_result(&m_handle);
	if (!result && mysql_errno(&m_handle))
		throw exception(mysql_error(&m_handle));
	return Csql_result(result);
}

void Cdatabase::close()
{
	mysql_close(&m_handle);
}

int Cdatabase::insert_id()
{
	return mysql_insert_id(&m_handle);
}

void Cdatabase::set_query_log(const std::string& v)
{
	m_query_log = v;
}
