#pragma once

#include <ctime>
#include <list>
#include "stream_writer.h"

class Calert
{
public:
	enum t_level
	{
		emerg,
		alert,
		crit,
		error,
		warn,
		notice,
		info,
		debug,
	};

	time_t time() const
	{
		return m_time;
	}

	t_level level() const
	{
		return m_level;
	}

	const std::string& message() const
	{
		return m_message;
	}

	void message(const std::string& v)
	{
		m_message = v;
	}

	Calert(t_level level, const std::string& message)
	{
		m_time = ::time(NULL);
		m_level = level;
		m_message = message;
	}

	Calert(t_level level, const std::string& source, const std::string& message)
	{
		m_time = ::time(NULL);
		m_level = level;
		m_message = message;
		m_source = source;
	}

	int pre_dump() const;
	void dump(Cstream_writer&) const;
private:
	time_t m_time;
	t_level m_level;
	std::string m_message;
	std::string m_source;
};

class Calerts: public std::list<Calert>
{
public:
	void push_back(const value_type& v)
	{
		std::list<value_type>::push_back(v);
		while (size() > 250)
			erase(begin());
	}
};
