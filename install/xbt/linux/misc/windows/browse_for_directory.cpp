#include "stdafx.h"
#include "browse_for_directory.h"

int CALLBACK BrowseCallbackProc(HWND hwnd, UINT uMsg, LPARAM lParam, LPARAM lpData)
{
	if (uMsg == BFFM_INITIALIZED)
		SendMessage(hwnd, BFFM_SETSELECTION, true, lpData);
	return 0;
}

int browse_for_directory(HWND hWnd, const std::string& title, std::string& directory)
{
	BROWSEINFO bi;
	ZeroMemory(&bi, sizeof(BROWSEINFO));
	bi.hwndOwner = hWnd;
	bi.lpszTitle = title.c_str();
	bi.ulFlags = BIF_NEWDIALOGSTYLE | BIF_RETURNONLYFSDIRS;
	bi.lpfn = BrowseCallbackProc;
	bi.lParam = reinterpret_cast<LPARAM>(directory.c_str());
	ITEMIDLIST* idl = SHBrowseForFolder(&bi);
	if (!idl)
		return 1;
	char path[MAX_PATH];
	if (!SHGetPathFromIDList(idl, path))
		*path = 0;
	LPMALLOC lpm;
	if (SHGetMalloc(&lpm) == NOERROR)
		lpm->Free(idl);
	if (!*path)
		return 1;
	directory = path;
	return 0;
}