#include "stdafx.h"
#include "xif_key.h"

#include <zlib.h>
#include "stream_int.h"

static int read_int(const byte*& r)
{
	r += 4;
	return read_int_le(4, r - 4);
}

void Cxif_key::load_old(const byte*& data)
{
	for (int count = read_int(data); count--; )
	{
		Cxif_key& i = set_key(read_int(data));
		i.load_old(data);
	}
	for (int count = read_int(data); count--; )
	{
		Cxif_value& i = set_value(read_int(data));
		i.load_old(data);
	}
}

void Cxif_key::load_new(const byte*& data)
{
	for (int count = read_int(data), id = 0; count--; )
	{
		id += read_int(data);
		open_key_write(id).load_new(data);
	}
	for (int count = read_int(data), id = 0; count--; )
	{
		id += read_int(data);
		open_value_write(id).load_new(data);
	}
}

void Cxif_key::load_external(const byte*& data)
{
	BOOST_FOREACH(t_xif_key_map::reference i, m_keys)
		i.second.load_external(data);
	BOOST_FOREACH(t_xif_value_map::reference i, m_values)
		i.second.load_external(data);
}

int Cxif_key::get_size() const
{
	int size = 8;
	BOOST_FOREACH(t_xif_key_map::const_reference i, m_keys)
		size += 4 + i.second.get_size();
	BOOST_FOREACH(t_xif_value_map::const_reference i, m_values)
	{
		size += 9;
		switch (i.second.get_type())
		{
		case vt_bin32:
		case vt_int32:
			break;
		default:
			if (!i.second.external_data())
				size += i.second.get_size();
		}
	}
	return size;
}

int Cxif_key::get_external_size() const
{
	int size = 0;
	BOOST_FOREACH(t_xif_key_map::const_reference i, m_keys)
		size += i.second.get_external_size();			
	BOOST_FOREACH(t_xif_value_map::const_reference i, m_values)
		if (i.second.external_data())
			size += i.second.get_size();
	return size;
}

void Cxif_key::save(byte*& data) const
{
	{
		data = write_int_le(4, data, m_keys.size());
		int id = 0;
		BOOST_FOREACH(t_xif_key_map::const_reference i, m_keys)
		{
			data = write_int_le(4, data, i.first - id);
			id = i.first;
			i.second.save(data);
		}
	}
	{
		data = write_int_le(4, data, m_values.size());
		int id = 0;
		BOOST_FOREACH(t_xif_value_map::const_reference i, m_values)
		{
			data = write_int_le(4, data, i.first - id);
			id = i.first;
			i.second.save(data);
		}
	}
}

void Cxif_key::external_save(byte*& data) const
{
	BOOST_FOREACH(t_xif_key_map::const_reference i, m_keys)
		i.second.external_save(data);
	BOOST_FOREACH(t_xif_value_map::const_reference i, m_values)
		i.second.external_save(data);
}

int Cxif_key::load_key(const byte* data, size_t size)
{
	const byte* read_p = data;
	const t_xif_header_fast& header = *reinterpret_cast<const t_xif_header_fast*>(read_p);
	if (size < sizeof(t_xif_header_old)
		|| header.id != file_id
		|| header.version != file_version_old && header.version != file_version_new && header.version != file_version_fast)
		return 1;
	int error = 0;
	if (header.version == file_version_old)
	{
		read_p += sizeof(t_xif_header_old) - 4;
		load_old(read_p);
		error = size != read_p - data;
	}
	else
	{
		unsigned long cb_d = header.size_uncompressed;
		if (cb_d)
		{
			Cvirtual_binary d;
			if (header.version == file_version_new)
				error = Z_OK != uncompress(d.write_start(cb_d), &cb_d, data + sizeof(t_xif_header_old), size - sizeof(t_xif_header_old));
			else
				error = Z_OK != uncompress(d.write_start(cb_d), &cb_d, data + sizeof(t_xif_header_fast), header.size_compressed);
			if (!error)
			{
				read_p = d.data();
				load_new(read_p);
				error = read_p != d.end();
				if (header.version == file_version_fast && !error)
				{
					read_p = data + sizeof(t_xif_header_fast) + header.size_compressed;
					load_external(read_p);
					error = size != read_p - data;
				}
			}
		}
		else
		{
			read_p = data + (header.version == file_version_fast ? sizeof(t_xif_header_fast) : sizeof(t_xif_header_old));
			load_new(read_p);
			load_external(read_p);
			error = size != read_p - data;
		}
	}
	return error;
}

Cvirtual_binary Cxif_key::vdata(bool fast) const
{
	Cvirtual_binary d;
	int size = get_size();
	int external_size = get_external_size();
	if (fast)
	{
		t_xif_header_fast& header = *reinterpret_cast<t_xif_header_fast*>(d.write_start(sizeof(t_xif_header_fast) + size + external_size));
		header.id = file_id;
		header.version = file_version_fast;
		header.size_uncompressed = 0;
		header.size_compressed = size;
		header.size_external = external_size;
		byte* w = d.data_edit() + sizeof(t_xif_header_fast);
		save(w);
		external_save(w);
		assert(d.end() == w);
		return d;
	}
	Cvirtual_binary s;
	byte* w = s.write_start(size);
	save(w);
	unsigned long cb_d = s.size() + (s.size() + 999) / 1000 + 12;
	t_xif_header_fast& header = *reinterpret_cast<t_xif_header_fast*>(d.write_start(sizeof(t_xif_header_fast) + cb_d + external_size));
	compress(d.data_edit() + sizeof(t_xif_header_fast), &cb_d, s.data(), s.size());
	w = d.data_edit() + sizeof(t_xif_header_fast) + cb_d;
	external_save(w);
	header.id = file_id;
	header.version = file_version_fast;
	header.size_uncompressed = size;
	header.size_compressed = cb_d;
	header.size_external = external_size;
	d.resize(sizeof(t_xif_header_fast) + cb_d + external_size);
	return d;
}
