#pragma once

#include <const_memory_range.h>

class Cdatabase;
class Csql_result;

class Csql_query
{
public:
	Csql_result execute() const;
	std::string read() const;
	void operator=(const std::string&);
	void operator+=(const std::string&);
	Csql_query& p_name(const std::string&);
	Csql_query& p_raw(const_memory_range);
	Csql_query& p(long long);
	Csql_query& p(const_memory_range);
	Csql_query(Cdatabase&, const std::string& = "");
private:
	std::string m_in;
	std::string m_out;
	Cdatabase& m_database;
};
