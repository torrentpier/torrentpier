#pragma once

#include <stdexcept>
#include "sql_result.h"

class Cdatabase: boost::noncopyable
{
public:
	class exception: public std::runtime_error
	{
	public:
		exception(const std::string& s): runtime_error(s)
		{
		}
	};

	void open(const std::string& host, const std::string& user, const std::string& password, const std::string& database, bool echo_errors = false);
	Csql_result query(const std::string&);
	void set_query_log(const std::string&);
	int insert_id();
	void close();
	Cdatabase();
	~Cdatabase();

	MYSQL* handle()
	{
		return &m_handle;
	}
private:
	bool m_echo_errors;
	MYSQL m_handle;
	std::string m_query_log;
};
