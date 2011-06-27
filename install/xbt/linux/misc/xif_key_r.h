#pragma once

#include <vector>
#include "xif_value.h"

class Cxif_key_r
{
public:
	typedef std::vector<std::pair<int, Cxif_key_r> > t_key_map;
	typedef std::vector<std::pair<int, Cxif_value> > t_value_map;

	const Cxif_key_r& get_key(int id) const
	{
		return find_key(id)->second;
	}

	const Cxif_value& get_value(int id) const
	{
		static Cxif_value z;
		t_value_map::const_iterator i = find_value(id);
		return i == values().end() ? z : i->second;
	}

	float get_value_float(int id) const
	{
		return get_value(id).get_float();
	}

	float get_value_float(int id, float v) const
	{
		return get_value(id).get_float(v);
	}

	int get_value_int(int id) const
	{
		return get_value(id).get_int();
	}

	int get_value_int(int id, int v) const
	{
		return get_value(id).get_int(v);
	}

	long long get_value_int64(int id) const
	{
		return *reinterpret_cast<const long long*>(get_value(id).get_data());
	}

	std::string get_value_string(int id) const
	{
		return get_value(id).get_string();
	}

	std::string get_value_string(int id, const std::string& v) const
	{
		return get_value(id).get_string(v);
	}

	const t_key_map& keys() const
	{
		return m_keys;
	}

	const t_value_map& values() const
	{
		return m_values;
	}

	int c_keys() const
	{
		return keys().size();
	}

	int c_values() const
	{
		return values().size();
	}

	bool has_key(int id) const
	{
		return find_key(id) != keys().end();
	}

	bool has_value(int id) const
	{
		return find_value(id) != values().end();
	}

	t_key_map::const_iterator find_key(int id) const;
	t_value_map::const_iterator find_value(int id) const;
	int import(const_memory_range);
private:
	int load(const byte* s);

	t_key_map m_keys;
	t_value_map m_values;
};
