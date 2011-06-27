#include "stdafx.h"
#include "alerts.h"

#include "bt_misc.h"

int Calert::pre_dump() const
{
	return m_message.size() + m_source.size() + 16;
}

void Calert::dump(Cstream_writer& w) const
{
	w.write_int(4, m_time);
	w.write_int(4, m_level);
	w.write_data(m_message);
	w.write_data(m_source);
}
