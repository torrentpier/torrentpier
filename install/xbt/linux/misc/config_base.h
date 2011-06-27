#pragma once

#include <boost/algorithm/string.hpp>
#include <fstream>
#include <map>
#include <set>
#include <string>

class Cconfig_base
{
public:
	template <class T>
	struct t_attribute
	{
		const char* key;
		T* value;
		T default_value;
	};

	template <class T>
	class t_attributes: public std::map<std::string, t_attribute<T> >
	{
	};

	virtual int set(const std::string& name, const std::string& value)
	{
		t_attributes<std::string>::iterator i = m_attributes_string.find(name);
		if (i != m_attributes_string.end())
			*i->second.value = value;
		else
			return set(name, atoi(value.c_str()));
		return 0;
	}

	virtual int set(const std::string& name, int value)
	{
		t_attributes<int>::iterator i = m_attributes_int.find(name);
		if (i != m_attributes_int.end())
			*i->second.value = value;
		else
			return set(name, static_cast<bool>(value));
		return 0;
	}

	virtual int set(const std::string& name, bool value)
	{
		t_attributes<bool>::iterator i = m_attributes_bool.find(name);
		if (i != m_attributes_bool.end())
			*i->second.value = value;
		else
			return 1;
		return 0;
	}

	std::istream& load(std::istream& is)
	{
		for (std::string s; getline(is, s); )
		{
			size_t i = s.find('=');
			if (i != std::string::npos)
				set(boost::trim_copy(s.substr(0, i)), boost::trim_copy(s.substr(i + 1)));
		}
		return is;
	}

	int load(const std::string& file)
	{
		std::ifstream is(file.c_str());
		if (!is)
			return 1;
		load(is);
		return !is.eof();
	}

	std::ostream& save(std::ostream& os) const
	{
		save_map(os, m_attributes_bool);
		save_map(os, m_attributes_int);
		save_map(os, m_attributes_string);
		return os;
	}

protected:
	t_attributes<bool> m_attributes_bool;
	t_attributes<int> m_attributes_int;
	t_attributes<std::string> m_attributes_string;

	template <class T>
	void fill_map(t_attribute<T>* attributes, const t_attributes<T>* s, t_attributes<T>& d)
	{
		for (t_attribute<T>* i = attributes; i->key; i++)
		{
			*i->value = s ? *s->find(i->key)->second.value : i->default_value;
			d[i->key] = *i;
		}
	}

	template <class T>
	void save_map(std::ostream& os, const T& v) const
	{
		for (typename T::const_iterator i = v.begin(); i != v.end(); i++)
		{
			if (*i->second.value == i->second.default_value)
				os << "# ";
			os << i->first << " = " << *i->second.value << std::endl;
		}
	}
};
