#include "stdafx.h"
#include "nt_service.h"

#include <windows.h>

int nt_service_install(const char* name)
{
	SC_HANDLE scm = OpenSCManager(NULL, NULL, SC_MANAGER_ALL_ACCESS);
	if (!scm)
		return 1;
	char file_name[MAX_PATH];
	GetModuleFileName(NULL, file_name, MAX_PATH);
	SC_HANDLE service = CreateService(scm, 
		name, 
		name, 
		SERVICE_ALL_ACCESS, 
		SERVICE_WIN32_OWN_PROCESS, 
		SERVICE_AUTO_START, 
		SERVICE_ERROR_NORMAL,
		file_name, 
		NULL, 
		NULL, 
		NULL, 
		"NT AUTHORITY\\LocalService", 
		NULL);
	if (!service)
	{
		service = CreateService(scm, 
			name, 
			name, 
			SERVICE_ALL_ACCESS, 
			SERVICE_WIN32_OWN_PROCESS, 
			SERVICE_AUTO_START, 
			SERVICE_ERROR_NORMAL,
			file_name, 
			NULL, 
			NULL, 
			NULL, 
			NULL, 
			NULL);	
	}
	if (!service)
	{
		CloseServiceHandle(scm);
		return 1;
	}
	CloseServiceHandle(service);
	CloseServiceHandle(scm);
	return 0;
}

int nt_service_uninstall(const char* name)
{
	SC_HANDLE scm = OpenSCManager(NULL, NULL, SC_MANAGER_ALL_ACCESS);
	if (!scm)
		return 1;
	int result = 1;
	SC_HANDLE service = OpenService(scm, name, DELETE);
	if (service)
	{
		if (DeleteService(service))
			result = 0;
		CloseServiceHandle(service);
	}
	CloseServiceHandle(scm);
	return result;
}
