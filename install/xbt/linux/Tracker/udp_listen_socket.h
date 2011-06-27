#pragma once

#include "client.h"

class Cserver;

class Cudp_listen_socket: public Cclient
{
public:
	virtual void process_events(int);
	Cclient::s;
	Cudp_listen_socket();
	Cudp_listen_socket(Cserver*, const Csocket&);
};
