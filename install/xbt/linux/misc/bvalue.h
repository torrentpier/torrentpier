#pragma once

#include <map>
#include <string>
#include <vector>
#include <xbt/virtual_binary.h>

class Cbvalue
{
public:
	enum t_value_type
	{
		vt_int,
		vt_string,
		vt_list,
		vt_dictionary,
	};

	typedef std::map<std::string, Cbvalue> t_map;
	typedef std::vector<Cbvalue> t_list;

	void clear();
	const t_map& d() const;
	const t_list& l() const;
	long long i() const;
	const std::string& s() const;
	bool d_has(const std::string&) const;
	Cbvalue& d(const std::string& v, const Cbvalue& w);
	Cbvalue& l(const Cbvalue& v);
	int pre_read() const;
	int read(char* d) const;
	int read(void* d) const;
	Cvirtual_binary read() const;
	int write(const char* s, int cb_s);
	int write(const_memory_range);
	Cbvalue(long long v = 0);
	Cbvalue(t_value_type t);
	Cbvalue(const std::string& v);
	Cbvalue(const Cbvalue&);
	Cbvalue(const_memory_range);
	const Cbvalue& operator=(const Cbvalue&);
	const Cbvalue& operator[](const std::string&) const;
	~Cbvalue();
private:
	const Cbvalue& d(const std::string&) const;

	t_value_type m_value_type;

	union
	{
		long long m_int;
		std::string* m_string;
		t_list* m_list;
		t_map* m_map;
	};

	int write(const char*& s, const char* s_end);
};
