#pragma once

#include <string>

std::string encode_field(const std::string&, const std::string& local_domain_url);
std::string encode_text(const std::string&, const std::string& local_domain_url, bool add_span);
std::string trim_field(const std::string&);
std::string trim_text(const std::string&);
