#include "stdafx.h"
#include "xbt/virtual_binary.h"
#include "bt_tracker_account.h"

#include "stream_reader.h"

Cbt_tracker_account::Cbt_tracker_account()
{
}

Cbt_tracker_account::Cbt_tracker_account(const std::string& tracker, const std::string& user, const std::string& pass)
{
	m_tracker = tracker;
	m_user = user;
	m_pass = pass;
}

int Cbt_tracker_account::pre_dump() const
{
	return tracker().size() + user().size() + pass().size() + 12;
}

void Cbt_tracker_account::dump(Cstream_writer& w) const
{
	w.write_data(tracker());
	w.write_data(user());
	w.write_data(pass());
}

Cvirtual_binary Cbt_tracker_accounts::dump() const
{
	int cb_d = 4;
	for (const_iterator i = begin(); i != end(); i++)
		cb_d += i->pre_dump();
	Cvirtual_binary d;
	Cstream_writer w(d.write_start(cb_d));
	w.write_int(4, size());
	for (const_iterator i = begin(); i != end(); i++)
		i->dump(w);
	assert(w.w() == d.end());
	return d;

}

const Cbt_tracker_account* Cbt_tracker_accounts::find(const std::string& v) const
{
	for (const_iterator i = begin(); i != end(); i++)
	{
		if (i->tracker() == v)
			return &*i;
	}
	return NULL;
}

void Cbt_tracker_accounts::load(const Cvirtual_binary& s)
{
	clear();
	if (s.size() < 4)
		return;
	Cstream_reader r(s);
	for (int count = r.read_int(4); count--; )
	{
		std::string tracker = r.read_string();
		std::string name = r.read_string();
		std::string pass = r.read_string();
		push_back(Cbt_tracker_account(tracker, name, pass));
	}
}
