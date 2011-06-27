#include "stdafx.h"
#include "xcc_z.h"

#include <cstdio>
#include <string.h>
#include <zlib.h>
#include "stream_int.h"

Cvirtual_binary xcc_z::gunzip(const_memory_range s)
{
	if (s.size() < 18)
		return Cvirtual_binary();
	Cvirtual_binary d;
	z_stream stream;
	stream.zalloc = NULL;
	stream.zfree = NULL;
	stream.opaque = NULL;
	stream.next_in = const_cast<unsigned char*>(s.begin) + 10;
	stream.avail_in = s.size() - 18;
	stream.next_out = d.write_start(read_int_le(4, s.end - 4));
	stream.avail_out = d.size();
	return stream.next_out
		&& Z_OK == inflateInit2(&stream, -MAX_WBITS)
		&& Z_STREAM_END == inflate(&stream, Z_FINISH)
		&& Z_OK == inflateEnd(&stream)
		? d 
		: Cvirtual_binary();
}

Cvirtual_binary xcc_z::gzip(const_memory_range s)
{
	Cvirtual_binary d;
	unsigned long cb_d = s.size() + (s.size() + 999) / 1000 + 12;
	unsigned char* w = d.write_start(10 + cb_d + 8);
	*w++ = 0x1f;
	*w++ = 0x8b;
	*w++ = Z_DEFLATED;
	*w++ = 0;
	*w++ = 0;
	*w++ = 0;
	*w++ = 0;
	*w++ = 0;
	*w++ = 0;
	*w++ = 3;
	{
		z_stream stream;
		stream.zalloc = NULL;
		stream.zfree = NULL;
		stream.opaque = NULL;
		deflateInit2(&stream, Z_DEFAULT_COMPRESSION, Z_DEFLATED, -MAX_WBITS, MAX_MEM_LEVEL, Z_DEFAULT_STRATEGY);
		stream.next_in = const_cast<unsigned char*>(s.begin);
		stream.avail_in = s.size();
		stream.next_out = w;
		stream.avail_out = cb_d;
		deflate(&stream, Z_FINISH);
		deflateEnd(&stream);
		w = stream.next_out;
	}
	w = write_int_le(4, w, crc32(crc32(0, NULL, 0), s, s.size()));
	w = write_int_le(4, w, s.size());
	d.resize(w - d.data());
	return d;
}

void xcc_z::gzip_out(const_memory_range s)
{
	gzFile f = gzdopen(fileno(stdout), "wb");
	gzwrite(f, const_cast<unsigned char*>(s.begin), s.size());
	gzflush(f, Z_FINISH);
}
