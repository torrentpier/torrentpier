#pragma once

#include <socket.h>

class Cserver;

class Cclient
{
public:
	virtual void process_events(int) = 0;
	virtual ~Cclient()
	{
	}
protected:
	const Csocket& s() const
	{
		return m_s;
	}

	void s(const Csocket& s)
	{
		m_s = s;
	}

	Csocket m_s;
	Cserver* m_server;
};
