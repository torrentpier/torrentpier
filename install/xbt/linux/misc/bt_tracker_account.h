#pragma once

#include <stream_writer.h>

class Cbt_tracker_account
{
public:
	int pre_dump() const;
	void dump(Cstream_writer&) const;
	Cbt_tracker_account();
	Cbt_tracker_account(const std::string& tracker, const std::string& user, const std::string& pass);

	const std::string& tracker() const
	{
		return m_tracker;
	}

	const std::string& user() const
	{
		return m_user;
	}

	const std::string& pass() const
	{
		return m_pass;
	}
private:
	std::string m_tracker;
	std::string m_user;
	std::string m_pass;
};

class Cbt_tracker_accounts: public std::list<Cbt_tracker_account>
{
public:
	Cvirtual_binary dump() const;
	const Cbt_tracker_account* find(const std::string&) const;
	void load(const Cvirtual_binary&);
};
