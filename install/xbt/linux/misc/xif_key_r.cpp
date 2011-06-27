#include "stdafx.h"
#include "xif_key_r.h"

#include <stream_int.h>
#include <xbt/virtual_binary.h>
#include <xif_key.h>
#include <zlib.h>

static int read_int(const byte*& r)
{
	r += 4;
	return read_int_le(4, r - 4);
}

int Cxif_key_r::import(const_memory_range s)
{
	Cvirtual_binary d;
	const t_xif_header_fast& h = *reinterpret_cast<const t_xif_header_fast*>(s.begin);
	if (s.size() < sizeof(t_xif_header_fast) + 8
		|| h.id != file_id
		|| h.version != file_version_fast)
		return 1;
	unsigned long cb_d = h.size_uncompressed;
	if (cb_d)
	{
		if (Z_OK != uncompress(d.write_start(cb_d), &cb_d, s + sizeof(t_xif_header_fast), h.size_compressed))
			return 1;
		/*
		if (uncompress(d.write_start(cb_d), &cb_d, s + sizeof(t_xif_header_fast), h.size_compressed) != Z_OK)
			return 1;
		*/
		load(d);
		// m_external_data = d + h.size_compressed;
	}
	else
	{
		load(s + sizeof(t_xif_header_fast));
		// m_external_data = s + sizeof(t_xif_header_fast) + h.size_uncompressed
	}

	return 0;
}

int Cxif_key_r::load(const byte* s)
{
	const byte* r = s;
	{
		int count = read_int(r);
		int id = 0;
		m_keys.reserve(count);
		while (count--)
		{
			id += read_int(r);
			m_keys.push_back(std::make_pair(id, Cxif_key_r()));
			r += m_keys.rbegin()->second.load(r);
		}
	}
	{
		int count = read_int(r);
		int id = 0;
		m_values.reserve(count);
		while (count--)
		{
			id += read_int(r);
			m_values.push_back(std::make_pair(id, Cxif_value()));
			m_values.rbegin()->second.load_new(r);
		}
	}
	return r - s;
}

Cxif_key_r::t_key_map::const_iterator Cxif_key_r::find_key(int id) const
{
	t_key_map::const_iterator i = keys().begin();
	while (i != keys().end() && i->first != id)
		i++;
	return i;
}

Cxif_key_r::t_value_map::const_iterator Cxif_key_r::find_value(int id) const
{
	t_value_map::const_iterator i = values().begin();
	while (i != values().end() && i->first != id)
		i++;
	return i;
}
