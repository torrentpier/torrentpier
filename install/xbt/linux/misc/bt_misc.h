#pragma once

#include <sha1.h>
#include <string>

std::string b2a(long long v, const char* postfix = NULL);
std::string backward_slashes(std::string);
std::string duration2a(float);
std::string escape_string(const std::string&);
std::string forward_slashes(std::string);
std::string generate_random_string(int);
std::string get_env(const std::string&);
int hms2i(int h, int m, int s);
bool is_private_ipa(int a);
int merkle_tree_size(int v);
std::string n(long long);
std::string native_slashes(const std::string&);
std::string hex_decode(const std::string&);
std::string hex_encode(int l, int v);
std::string hex_encode(const_memory_range);
std::string js_encode(const std::string&);
std::string peer_id2a(const std::string&);
std::string time2a(time_t);
std::string uri_decode(const std::string&);
std::string uri_encode(const std::string&);
int xbt_atoi(const std::string&);
std::string xbt_version2a(int);

inline long long htonll(long long v)
{
	const unsigned char* a = reinterpret_cast<const unsigned char*>(&v);
	long long b = a[0] << 24 | a[1] << 16 | a[2] << 8 | a[3];
	return b << 32 | static_cast<long long>(a[4]) << 24 | a[5] << 16 | a[6] << 8 | a[7];
}

inline long long ntohll(long long v)
{
	return htonll(v);
}

enum
{
	hs_name_size = 0,
	hs_name = 1,
	hs_reserved = 20,
	hs_info_hash = 28,
	hs_size = 48,
};

enum
{
	uta_connect,
	uta_announce,
	uta_scrape,
	uta_error,
};

enum
{
	uti_connection_id = 0,
	uti_action = 8,
	uti_transaction_id = 12,
	uti_size = 16,

	utic_size = 16,

	utia_info_hash = 16,
	utia_peer_id = 36,
	utia_downloaded = 56,
	utia_left = 64,
	utia_uploaded = 72,
	utia_event = 80,
	utia_ipa = 84,
	utia_key = 88,
	utia_num_want = 92,
	utia_port = 96,
	utia_size = 98,

	utis_size = 16,

	uto_action = 0,
	uto_transaction_id = 4,
	uto_size = 8,

	utoc_connection_id = 8,
	utoc_size = 16,

	utoa_interval = 8,
	utoa_leechers = 12,
	utoa_seeders = 16,
	utoa_size = 20,

	utos_size = 8,

	utoe_size = 8,
};
