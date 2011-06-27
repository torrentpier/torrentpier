#pragma once

#include <const_memory_range.h>
#include <stream_int.h>

class Cstream_writer
{
public:
	unsigned char* w() const
	{
		return m_w;
	}

	unsigned char* write(int size)
	{
		m_w += size;
		return m_w - size;
	}

	void write_int(int cb, long long v)
	{
		m_w = ::write_int(cb, m_w, v);
	}

	void write_data(const_memory_range v)
	{
		write_int(4, v.size());
		memcpy(write(v.size()), v, v.size());
	}

	Cstream_writer()
	{
	}

	Cstream_writer(unsigned char* w)
	{
		m_w = w;
	}
private:
	unsigned char* m_w;
};
