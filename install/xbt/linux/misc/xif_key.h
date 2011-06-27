#pragma once

#include <map>
#include <string>
#include <xbt/virtual_binary.h>
#include <xif_value.h>

const static int file_id = 0x1a464958; // *reinterpret_cast<const int*>("XIF\x1a");
const static int file_version_old = 0;
const static int file_version_new = 1;
const static int file_version_fast = 2;

struct t_xif_header_old
{
	int id;
	int version;
	int size_uncompressed;
};

struct t_xif_header_fast
{
	int id;
	int version;
	int size_uncompressed;
	int size_compressed;
	int size_external;
};

class Cxif_key;

typedef std::map<int, Cxif_key> t_xif_key_map;
typedef std::map<int, Cxif_value> t_xif_value_map;

class Cxif_key
{
public:
	Cxif_key():
		m_keys(*new t_xif_key_map)
	{
	}

	~Cxif_key()
	{
		delete &m_keys;
	}

	Cxif_key(const Cxif_key& v):
		m_keys(*new t_xif_key_map)
	{
		m_keys = v.m_keys;
		m_values = v.m_values;
	}

	explicit Cxif_key(const Cvirtual_binary& v):
		m_keys(*new t_xif_key_map)
	{
		load_key(v);
	}

	const Cxif_key& operator=(const Cxif_key& v)
	{
		m_keys = v.m_keys;
		m_values = v.m_values;
		return *this;
	}

	Cxif_key& set_key(int id)
	{
		m_keys[id] = Cxif_key();
		return m_keys[id];
	}

	const Cxif_key& open_key_read(int id) const
	{
		return m_keys.find(id)->second;
	}

	Cxif_key& open_key_edit(int id)
	{
		return m_keys[id];
	}

	Cxif_key& open_key_write()
	{
		return open_key_write(m_keys.empty() ? 0 : m_keys.rbegin()->first + 1);
	}

	Cxif_key& open_key_write(int id)
	{
		m_keys[id] = Cxif_key();
		return m_keys[id];
	}

	const Cxif_value& open_value_read(int id) const
	{
		return m_values.find(id)->second;
	}

	Cxif_value& open_value_edit(int id)
	{
		return m_values[id];
	}

	Cxif_value& open_value_write(int id)
	{
		m_values[id] = Cxif_value();
		return m_values[id];
	}

	Cxif_value& set_value(int id)
	{
		m_values[id] = Cxif_value();
		return m_values[id];
	}

	void set_value_bin(int id, int v)
	{
		m_values[id] = Cxif_value(vt_bin32, v);
	}

	void set_value_binary(int id, const Cvirtual_binary v, bool fast = false)
	{
		m_values[id] = Cxif_value(v, fast);
	}

	void set_value_float(int id, float v)
	{
		m_values[id] = Cxif_value(v);
	}

	void set_value_int(int id, int v)
	{
		m_values[id] = Cxif_value(vt_int32, v);
	}

	void set_value_string(int id, const std::string& v)
	{
		m_values[id] = Cxif_value(v);
	}

	void set_value_int64(int id, long long v)
	{
		set_value_binary(id, Cvirtual_binary(const_memory_range(&v, 8)));
	}

	const Cxif_key& get_key(int id) const
	{
		static Cxif_key z;
		t_xif_key_map::iterator i = m_keys.find(id);
		return i == m_keys.end() ? z : i->second;
	}

	const Cxif_value& get_value(int id) const
	{
		static Cxif_value z;
		t_xif_value_map::const_iterator i = m_values.find(id);
		return i == m_values.end() ? z : i->second;
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

	bool exists_key(int id) const
	{
		return m_keys.find(id) != m_keys.end();
	}

	bool exists_value(int id) const
	{
		return m_values.find(id) != m_values.end();
	}

	int c_keys() const
	{
		return m_keys.size();
	}

	int c_values() const
	{
		return m_values.size();
	}

	int load_key(const Cvirtual_binary& data)
	{
		return load_key(data.data(), data.size());
	}

	void delete_key(int id)
	{
		m_keys.erase(id);
	}

	void delete_value(int id)
	{
		m_values.erase(id);
	}

	void clear()
	{
		m_keys.clear();
		m_values.clear();
	}

	void dump(std::ostream& os, bool show_ratio, int depth = 0, Cvirtual_binary* t = NULL) const;
	void dump_ratio(std::ostream& os, Cvirtual_binary* t) const;
	Cvirtual_binary export_bz() const;
	int load_key(const byte* data, size_t size);
	Cvirtual_binary vdata(bool fast = false) const;

	t_xif_key_map& m_keys;
	t_xif_value_map m_values;
private:
	int get_size() const;
	int get_external_size() const;
	void load_old(const byte*& data);
	void load_new(const byte*& data);
	void load_external(const byte*& data);
	void save(byte*& data) const;
	void external_save(byte*& data) const;
};
