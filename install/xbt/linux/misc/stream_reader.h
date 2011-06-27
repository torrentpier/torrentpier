#pragma once

#include <stream_int.h>
#include <xbt/virtual_binary.h>

class Cstream_reader
{
public:
	const unsigned char* d() const
	{
		return m_d;
	}

	const unsigned char* d_end() const
	{
		return m_d.end();
	}

	const unsigned char* r() const
	{
		return m_r;
	}

	const unsigned char* read(int size)
	{
		m_r += size;
		return m_r - size;
	}

	long long read_int(int cb)
	{
		m_r += cb;
		return ::read_int(cb, m_r - cb);
	}

	Cvirtual_binary read_data()
	{
		int l = read_int(4);
		return Cvirtual_binary(const_memory_range(read(l), l));
	}

	std::string read_string()
	{
		int l = read_int(4);
		return std::string(reinterpret_cast<const char*>(read(l)), l);
	}

	Cstream_reader()
	{
	}

	Cstream_reader(const Cvirtual_binary& d)
	{
		m_r = m_d = d;
	}
private:
	Cvirtual_binary m_d;
	const unsigned char* m_r;
};
