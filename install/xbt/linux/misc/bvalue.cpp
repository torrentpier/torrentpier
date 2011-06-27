#include "stdafx.h"
#include "bvalue.h"

#include <boost/foreach.hpp>
#include <string.h>
#include "bt_misc.h"

Cbvalue::Cbvalue(long long v)
{
	m_value_type = vt_int;
	m_int = v;
}

Cbvalue::Cbvalue(t_value_type t)
{
	switch (m_value_type = t)
	{
	case vt_int:
		break;
	case vt_string:
		m_string = new std::string;
		break;
	case vt_list:
		m_list = new t_list;
		break;
	case vt_dictionary:
		m_map = new t_map;
		break;
	default:
		assert(false);
	}
}

Cbvalue::Cbvalue(const std::string& v)
{
	m_value_type = vt_string;
	m_string = new std::string(v);
}

Cbvalue::Cbvalue(const Cbvalue& v)
{
	switch (m_value_type = v.m_value_type)
	{
	case vt_int:
		m_int = v.m_int;
		break;
	case vt_string:
		m_string = new std::string(*v.m_string);
		break;
	case vt_list:
		m_list = new t_list(*v.m_list);
		break;
	case vt_dictionary:
		m_map = new t_map(*v.m_map);
		break;
	default:
		assert(false);
	}
}

Cbvalue::Cbvalue(const_memory_range s)
{
	m_value_type = vt_int;
	if (write(s))
		clear();
}

Cbvalue::~Cbvalue()
{
	clear();
}

const Cbvalue& Cbvalue::operator=(const Cbvalue& v)
{
	clear();
	m_value_type = v.m_value_type;
	switch (v.m_value_type)
	{
	case vt_int:
		m_int = v.m_int;
		break;
	case vt_string:
		m_string = new std::string(*v.m_string);
		break;
	case vt_list:
		m_list = new t_list(*v.m_list);
		break;
	case vt_dictionary:
		m_map = new t_map(*v.m_map);
		break;
	default:
		assert(false);
	}
	return *this;
}

int Cbvalue::write(const_memory_range s)
{
	return write(reinterpret_cast<const char*>(s.begin), s.size());
}

int Cbvalue::write(const char* s, int cb_s)
{
	return write(s, s + cb_s);
}

int Cbvalue::write(const char*& s, const char* s_end)
{
	clear();
	if (s >= s_end)
		return 1;
	switch (*s++)
	{
	case '0':
	case '1':
	case '2':
	case '3':
	case '4':
	case '5':
	case '6':
	case '7':
	case '8':
	case '9':
		{
			const char* a = s - 1;
			while (s < s_end && *s != ':')
				s++;
			if (s++ >= s_end)
				return 1;
			int l = atoi(a);
			if (s + l > s_end)
				return 1;
			m_value_type = vt_string;
			m_string = new std::string(s, l);
			s += l;
			return 0;
		}
	case 'd':
		{
			m_value_type = vt_dictionary;
			m_map = new t_map;
			while (s < s_end && *s != 'e')
			{
				Cbvalue v;
				Cbvalue w;
				if (v.write(s, s_end) || v.m_value_type != vt_string)
					return 1;
				if (w.write(s, s_end))
					return 1;
				(*m_map)[*v.m_string] = w;
			}
			if (s++ >= s_end)
				return 1;
			return 0;
		}
		break;
	case 'i':
		{
			const char* a = s;
			while (s < s_end && *s != 'e')
				s++;
			if (s++ >= s_end)
				return 1;
			m_value_type = vt_int;
			m_int = atoll(a);
			return 0;
		}
	case 'l':
		{
			m_value_type = vt_list;
			m_list = new t_list;
			while (s < s_end && *s != 'e')
			{
				Cbvalue v;
				if (v.write(s, s_end))
					return 1;
				m_list->push_back(v);
			}
			if (s++ >= s_end)
				return 1;
			return 0;
		}
	}
	return 1;
}

void Cbvalue::clear()
{
	switch (m_value_type)
	{
	case vt_int:
		break;
	case vt_string:
		delete m_string;
		break;
	case vt_list:
		delete m_list;
		break;
	case vt_dictionary:
		delete m_map;
		break;
	default:
		assert(false);
	}
	m_value_type = vt_int;
}

const Cbvalue::t_map& Cbvalue::d() const
{
	static t_map z;
	return m_value_type == vt_dictionary ? *m_map : z;
}

bool Cbvalue::d_has(const std::string& v) const
{
	return m_value_type == vt_dictionary && m_map->find(v) != m_map->end();
}

const Cbvalue& Cbvalue::d(const std::string& v) const
{
	if (m_value_type == vt_dictionary)
	{
		t_map::const_iterator i = m_map->find(v);
		if (i != m_map->end())
			return i->second;
	}
	static Cbvalue z;
	return z;
}

const Cbvalue& Cbvalue::operator[](const std::string& v) const
{
	return d(v);
}

long long Cbvalue::i() const
{
	return m_value_type == vt_int ? m_int : 0;
}

const Cbvalue::t_list& Cbvalue::l() const
{
	static t_list z;
	return m_value_type == vt_list ? *m_list : z;
}

const std::string& Cbvalue::s() const
{
	static std::string z;
	return m_value_type == vt_string ? *m_string : z;
}

Cbvalue& Cbvalue::d(const std::string& v, const Cbvalue& w)
{
	if (m_value_type != vt_dictionary)
	{
		clear();
		m_value_type = vt_dictionary;
		m_map = new t_map;
	}
	(*m_map)[v] = w;
	return *this;
}

Cbvalue& Cbvalue::l(const Cbvalue& v)
{
	if (m_value_type != vt_list)
	{
		clear();
		m_value_type = vt_list;
		m_list = new t_list;
	}
	(*m_list).push_back(v);
	return *this;
}

int Cbvalue::pre_read() const
{
	switch (m_value_type)
	{
	case vt_int:
		return n(m_int).size() + 2;
	case vt_string:
		return n(m_string->size()).size() + m_string->size() + 1;
	case vt_list:
		{
			int v = 2;
			BOOST_FOREACH(t_list::const_reference i, *m_list)
				v += i.pre_read();
			return v;
		}
	case vt_dictionary:
		{
			int v = 2;
			BOOST_FOREACH(t_map::const_reference i, *m_map)
				v += n(i.first.size()).size() + i.first.size() + i.second.pre_read() + 1;
			return v;
		}
	}
	assert(false);
	return 0;
}

Cvirtual_binary Cbvalue::read() const
{
	Cvirtual_binary d;
	int cb_d = read(d.write_start(pre_read()));
	assert(cb_d == d.size());
	return d;
}

int Cbvalue::read(void* d) const
{
	return read(reinterpret_cast<char*>(d));
}

int Cbvalue::read(char* d) const
{
	char* w = d;
	switch (m_value_type)
	{
	case vt_int:
#ifdef WIN32
		sprintf(d, "i%I64d", m_int);
#else
		sprintf(d, "i%lld", m_int);
#endif
		w += strlen(d);
		*w++ = 'e';
		return w - d;
	case vt_string:
#ifdef WIN32
		sprintf(w, "%d:", m_string->size());
#else
		sprintf(w, "%zu:", m_string->size());
#endif
		w += n(m_string->size()).size() + 1;
		memcpy(w, m_string->data(), m_string->size());
		w += m_string->size();
		return w - d;
	case vt_list:
		{
			*w++ = 'l';
			for (t_list::const_iterator i = m_list->begin(); i != m_list->end(); i++)
				w += i->read(w);
			*w++ = 'e';
			return w - d;
		}
	case vt_dictionary:
		{
			*w++ = 'd';
			for (t_map::const_iterator i = m_map->begin(); i != m_map->end(); i++)
			{
#ifdef WIN32
				sprintf(w, "%d:", i->first.size());
#else
				sprintf(w, "%zu:", i->first.size());
#endif
				w += n(i->first.size()).size() + 1;
				memcpy(w, i->first.data(), i->first.size());
				w += i->first.size();
				w += i->second.read(w);
			}
			*w++ = 'e';
			return w - d;
		}
	}
	assert(false);
	return 0;
}
