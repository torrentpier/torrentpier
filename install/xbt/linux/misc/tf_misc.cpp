#include "stdafx.h"
#include "tf_misc.h"

#include <boost/algorithm/string.hpp>
#include <boost/format.hpp>

static std::string web_encode(const std::string& v)
{
	std::string d;
	d.reserve(v.size() << 1);
	for (int a = 0; a < v.size();)
	{
		int b = v.find_first_of("\"<&", a);
		if (b == std::string::npos)
		{
			d += v.substr(a);
			return d;
		}
		d += v.substr(a, b - a);
		switch (v[b])
		{
		case '"':
			d += "&quot;";
			break;
		case '<':
			d += "&lt;";
			break;
		case '&':
			d += "&amp;";
			break;
		}
		a = b + 1;
	}
	return d;
}

static std::string web_link(const std::string& link_title, const std::string& link, bool encode)
{
	return encode
		? web_link(web_encode(link_title), web_encode(link), false)
		: (boost::format("<a href=\"%s\">%s</a>") % link % (link_title.empty() ? link : link_title)).str();
}

static std::string encode_local_url(const std::string& url, const std::string& local_domain_url)
{
	if (!local_domain_url.empty() && boost::istarts_with(url, local_domain_url))
		return url.substr(local_domain_url.length());
	return url;
}

std::string encode_field(const std::string& v, const std::string& local_domain_url)
{
	std::string r;
	r.reserve(v.length() << 1);
	for (size_t i = 0; i < v.length(); )
	{
		if (boost::istarts_with(v.c_str() + i, "ftp.")
			|| boost::istarts_with(v.c_str() + i, "ftp://")
			|| boost::istarts_with(v.c_str() + i, "http://")
			|| boost::istarts_with(v.c_str() + i, "https://")
			|| boost::istarts_with(v.c_str() + i, "mailto:")
			|| boost::istarts_with(v.c_str() + i, "www."))
		{
			size_t p = i;
			while (p < v.length()
				&& !isspace(v[p] & 0xff)
				&& v[p] != '\"'
				&& v[p] != '<'
				&& v[p] != '>')
			{
				p++;
			}
			if (v[p - 1] == '!' || v[p - 1] == ',' || v[p - 1] == '.' || v[p - 1] == '?')
				p--;
			if (v[p - 1] == ')')
				p--;
			std::string url = web_encode(v.substr(i, p - i));
			if (boost::istarts_with(v.c_str() + i, "ftp."))
				r += web_link(url, "ftp://" + url, false);
			else if (boost::istarts_with(v.c_str() + i, "www."))
				r += web_link(url, "http://" + url, false);
			else
				r += web_link(boost::istarts_with(v.c_str() + i, "mailto:") ? url.substr(7) : encode_local_url(url, local_domain_url), url, false);
			i = p;
		}
		else
		{
			char c = v[i++];
			switch (c)
			{
			case '<':
				r += "&lt;";
				break;
			case '&':
				r += "&amp;";
				break;
			default:
				r += c;
			}
		}
	}
	return r;
}

std::string encode_text(const std::string& v, const std::string& local_domain_url, bool add_span)
{
	std::string r;
	r.reserve(v.length() << 1);
	for (size_t i = 0; i < v.length(); )
	{
		size_t p = v.find('\n', i);
		if (p == std::string::npos)
			p = v.length();
		std::string line = v.substr(i, p - i);
		line = encode_field(line, local_domain_url);
		r += add_span && boost::istarts_with(line, "> ") ? "<span class=quote>" + line + "</span>" : line;
		r += "<br>";
		i = p + 1;
	}
	return r;
}

std::string trim_field(const std::string& v)
{
	std::string r;
	bool copy_white = false;
	for (size_t i = 0; i < v.length(); i++)
	{
		if (isspace(v[i] & 0xff))
			copy_white = true;
		else
		{
			if (copy_white)
			{
				if (!r.empty())
					r += ' ';
				copy_white = false;
			}
			r += v[i];
		}
	}
	return r;
}

std::string trim_text(const std::string& v)
{
	std::string r;
	bool copy_white = false;
	for (size_t i = 0; i < v.length(); )
	{
		size_t p = v.find('\n', i);
		if (p == std::string::npos)
			p = v.length();
		std::string line = trim_field(v.substr(i, p - i));
		if (line.empty())
			copy_white = true;
		else
		{
			if (copy_white)
			{
				if (!r.empty())
					r += '\n';
				copy_white = false;
			}
			r += line + '\n';
		}
		i = p + 1;
	}
	return r;
}
