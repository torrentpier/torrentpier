#pragma once

#include <boost/make_shared.hpp>
#include <boost/shared_ptr.hpp>
#include <boost/utility.hpp>
#include <cassert>
#include <const_memory_range.h>
#include <string>

class Cvirtual_binary_source: boost::noncopyable
{
public:
	Cvirtual_binary_source(const_memory_range);

	~Cvirtual_binary_source()
	{
		delete[] m_range.begin;
	}

	memory_range range()
	{
		return m_range;
	}

	void resize(size_t v)
	{
		assert(v <= m_range.size());
		m_range.end = m_range.begin + v;
	}
private:
	memory_range m_range;
};

class Cvirtual_binary
{
public:
	int save(const std::string&) const;
	int load(const std::string&);
	Cvirtual_binary& load1(const std::string&);
	void clear();
	size_t read(void* d) const;
	unsigned char* write_start(size_t cb_d);
	void write(const_memory_range);
	Cvirtual_binary(size_t);
	Cvirtual_binary(const_memory_range);

	Cvirtual_binary()
	{
	}

	const unsigned char* begin() const
	{
		return range().begin;
	}

	unsigned char* mutable_begin()
	{
		return mutable_range().begin;
	}

	const unsigned char* data() const
	{
		return range().begin;
	}

	unsigned char* data_edit()
	{
		return mutable_range().begin;
	}

	const unsigned char* end() const
	{
		return range().end;
	}

	unsigned char* mutable_end()
	{
		return mutable_range().end;
	}

	const_memory_range range() const
	{
		return m_source ? m_source->range() : memory_range();
	}

	memory_range mutable_range()
	{
		if (!m_source)
			return memory_range();
		if (!m_source.unique())
			m_source = boost::make_shared<Cvirtual_binary_source>(range());
		return m_source->range();
	}

	bool empty() const
	{
		return range().empty();
	}

	size_t size() const
	{
		return range().size();
	}

	void resize(size_t v)
	{
		if (!m_source)
			write_start(v);
		mutable_range();
		m_source->resize(v);
	}

	operator const unsigned char*() const
	{
		return data();
	}

	operator const_memory_range() const
	{
		return range();
	}

	operator memory_range()
	{
		return mutable_range();
	}
private:
	boost::shared_ptr<Cvirtual_binary_source> m_source;
};
