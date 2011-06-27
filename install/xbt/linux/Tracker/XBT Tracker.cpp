#include "stdafx.h"
#include <windows/nt_service.h>
#include <iostream>
#include "config.h"
#include "server.h"

std::string g_conf_file = "xbt_tracker.conf";
const char* g_service_name = "XBT Tracker";

int main1()
{
	srand(static_cast<int>(time(NULL)));
	Cconfig config;
	if (config.load(g_conf_file))
#ifdef WIN32
	{
		char b[MAX_PATH];
		*b = 0;
		GetModuleFileName(NULL, b, MAX_PATH);
		if (*b)
			strrchr(b, '\\')[1] = 0;
		strcat(b, "xbt_tracker.conf");
		if (config.load(b))
			std::cerr << "Unable to read " << g_conf_file << std::endl;
		else
			g_conf_file = b;
	}
#else
		std::cerr << "Unable to read " << g_conf_file << std::endl;
#endif
	Cdatabase database;
	try
	{
		if (config.m_mysql_host != "-")
			database.open(config.m_mysql_host, config.m_mysql_user, config.m_mysql_password, config.m_mysql_database, true);
	}
	catch (Cdatabase::exception& e)
	{
		std::cerr << e.what() << std::endl;
		return 1;
	}
	database.set_query_log(config.m_query_log);
	return Cserver(database, config.m_mysql_table_prefix, config.m_mysql_host != "-", g_conf_file).run();
}

#ifdef WIN32
static SERVICE_STATUS g_service_status;
static SERVICE_STATUS_HANDLE gh_service_status;

void WINAPI nt_service_handler(DWORD op)
{
	switch (op)
	{
	case SERVICE_CONTROL_STOP:
		g_service_status.dwCurrentState = SERVICE_STOP_PENDING;
		SetServiceStatus(gh_service_status, &g_service_status);
		Cserver::term();
		break;
	}
	SetServiceStatus(gh_service_status, &g_service_status);
}

void WINAPI nt_service_main(DWORD argc, LPTSTR* argv)
{
	g_service_status.dwCheckPoint = 0;
	g_service_status.dwControlsAccepted = SERVICE_ACCEPT_STOP;
	g_service_status.dwCurrentState = SERVICE_START_PENDING;
	g_service_status.dwServiceSpecificExitCode = 0;
	g_service_status.dwServiceType = SERVICE_WIN32_OWN_PROCESS;
	g_service_status.dwWaitHint = 0;
	g_service_status.dwWin32ExitCode = NO_ERROR;
	if (!(gh_service_status = RegisterServiceCtrlHandler(g_service_name, nt_service_handler)))
		return;
	SetServiceStatus(gh_service_status, &g_service_status);
	g_service_status.dwCurrentState = SERVICE_RUNNING;
	SetServiceStatus(gh_service_status, &g_service_status);
	main1();
	g_service_status.dwCurrentState = SERVICE_STOPPED;
	SetServiceStatus(gh_service_status, &g_service_status);
}
#endif

int main(int argc, char* argv[])
{
#ifdef WIN32
	if (argc >= 2)
	{
		if (!strcmp(argv[1], "--install"))
		{
			if (nt_service_install(g_service_name))
			{
				std::cerr << "Failed to install service " << g_service_name << "." << std::endl;
				return 1;
			}
			std::cout << "Service " << g_service_name << " has been installed." << std::endl;
			return 0;
		}
		else if (!strcmp(argv[1], "--uninstall"))
		{
			if (nt_service_uninstall(g_service_name))
			{
				std::cerr << "Failed to uninstall service " << g_service_name << "." << std::endl;
				return 1;
			}
			std::cout << "Service " << g_service_name << " has been uninstalled." << std::endl;
			return 0;
		}
		else if (!strcmp(argv[1], "--conf_file") && argc >= 3)
			g_conf_file = argv[2];
		else
			return 1;
	}
#ifdef NDEBUG
	SERVICE_TABLE_ENTRY st[] =
	{
		{ "", nt_service_main },
		{ NULL, NULL }
	};
	if (StartServiceCtrlDispatcher(st))
		return 0;
	if (GetLastError() != ERROR_CALL_NOT_IMPLEMENTED
		&& GetLastError() != ERROR_FAILED_SERVICE_CONTROLLER_CONNECT)
		return 1;
#endif
#else
	if (argc >= 2)
	{
		if (!strcmp(argv[1], "--conf_file") && argc >= 3)
			g_conf_file = argv[2];
		else
			return 1;
	}
#endif
	return main1();
}
