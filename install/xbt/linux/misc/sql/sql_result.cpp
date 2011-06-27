#include "stdafx.h"
#include "sql_result.h"

Csql_row::Csql_row(MYSQL_ROW data, unsigned long* sizes, const boost::shared_ptr<Csql_result_source>& source)
{
	m_data = data;
	m_sizes = sizes;
	m_source = source;
}

Csql_row Csql_result::fetch_row() const
{
	MYSQL_ROW data = mysql_fetch_row(h());
	return Csql_row(data, mysql_fetch_lengths(h()), m_source);
}
