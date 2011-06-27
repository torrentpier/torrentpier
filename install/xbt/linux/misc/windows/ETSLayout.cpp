////////////////////////////////////////////
//         ___ ____ _________________     //
//        / _/_  _// _______________/     //
//       / _/ / / / /  ___ ___ ____       //
//      /__/ /_/ / / /   // _/_  _/       //
//     _________/ / / / // _/ / /         //
// (c) 1998-2000_/ /___//_/  /_/          //
//                                        //
////////////////////////////////////////////
//          all rights reserved           //
////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
// ETSLayoutDialog
//
// A class for smart layouting of Dialogs and such
//
// USAGE: See LayoutMgr.html
//
// AUTHOR: Erwin Tratar <tr@et-soft.de>
//
// DISCLAIMER:
//
// This Sourcecode and all accompaning material is ©1998-1999 Erwin Tratar. 
// All rights reserved.
//
// The source code may be used in compiled form in any way you desire 
// (including usage in commercial applications), providing that your 
// application adds essential code (i.e. it is not only a wrapper) to the 
// functionality found here
//
// Redistribution of the sourcecode itself, publication in any media or 
// inclusion in a library requires the authors expressed written consent.
// You may not sale this code for profit.
//
// THIS SOFTWARE IS PROVIDED "AS IS" WITHOUT EXPRESS OR IMPLIED WARRANTY. USE IT 
// AT YOUR OWN RISK! THE AUTHOR ACCEPTS NO LIABILITY FOR ANY DAMAGE/LOSS OF 
// BUSINESS THAT THIS PRODUCT MAY CAUSE.
//
//
// HISTORY: 
// 1998/05/1	Initial Release
// 1998/05/13	Added ability to have a Pane with a control
// 1998/05/13	Added better support for TabControls
// 1998/05/14	automatically set Icon to IDR_MAINFRAME
// 1998/05/19	no flicker on restoring position in OnInitialUpdate
//				Changed procedure for load/save, see constructor
// 1998/10/02	Added support for Maximum (tracking) size
// 1998/10/02	Much improved handling regarding RELATIVE/GREEDY
//              /w critical minimum size
// 1998/10/02	turn on/off gripper at lower right corner
// 1998/10/05   Support for user defined minimum size for items
//              (was hardcoded 5 before)
// 1998/10/07   Fix for FormViews
// 1998/10/31	Support for SECDialogBar/CDialogBar
// 1998/10/31	simplified interface
// 1998/10/31	Advanced positioning options
// 1998/10/31	Added paneNull for empty Pane (former: NULL)
// 1998/11/20	Swapped ETSLayoutDialog constructor parameters
// 1998/11/20	Added Pane::addItemSpaceBetween 
//				[Leo Zelevinsky]
// 1998/11/24	Added fixup for greedy panes
// 1998/11/24	addItemSpaceBetween now subtracts 2*nDefaultBorder
// 1998/11/24	addGrowing() added as a shortcut for a paneNull
// 1998/11/24	simplified interface: no more PaneBase:: / Pane:: 
//				needed
// 1998/11/24	added FILL_* Modes
// 1998/11/24	improved maximum size handling for greedy panes
// 1998/11/25	Fixup of greedy panes caused infinite loop in some 
//				cases
// 1999/01/07	addItemSpaceLike() added
// 1999/04/03   Fixed ETSLayoutFormView memory leak
// 1999/04/07   Fixed ALIGN_xCENTER
// 1999/04/08   New simple stream-interface added
// 1999/04/09   Added support for an empty Status-Bar for resizing 
//              instead of a gripper in the lower right corner
//              [Andreas Kapust]
// 1999/04/11   New code for much less flickering, OnEraseBkgnd()
//              overidden for this task
// 1999/05/12   Split Layout code into understandable pieces and adding
//              a lot of comments
// 1999/06/20   ABSOLUTE_X + ALIGN_FILL_X expands item if there is any
//              left space (after all Abs/Rel/Greedy processing is done)
// 1999/10/06   Changed Load() and Save() to use WINDOWPLACEMENT
//              [Keith Bussell]
// 1999/11/18   Added possibility to add panes of the same orientation
//              to another pane. This merges both panes in one big
//              pane with the same orientation
// 1999/11/18   Added support for BCGDialogBar (only with BCG > 4.52!)
// 1999/11/25   Addes support for PropertyPages/Sheets. Uses some code
//              of a code submission from Anreas Kapust
// 1999/11/25   Renamed classes to ETSLayoutXXX
// 1999/11/25   Use CreateRoot() and Root() instead of m_pRootPane in
//              derived class.
// 1999/11/26   Added autopointer support. No need to use normal pointers
//              when defining layout anymore. Changed m_pRootPane to 
//              m_RootPane
// 1999/11/26   Bug in Fixup Greedy II with multiple GREEDY panes and one
//              of them min/max limited
// 1999/11/28   Fixed PaneTab::getConstrainVert() for ABSOLUTE_VERT
// 1999/11/28   Fixed itemFixed()
// 1999/11/28   Changed DWORD modeResize Arguments to layModeResize for 
//              better type safety. Added typesafe operator|
// 1999/12/04   Don't reposition window in UpdateLayout if it's a child
//              (as a child Dialog or PropertyPage)
// 1999/12/04   Erase Backgroung with GCL_HBRBACKGROUND (if available) 
// 1999/12/04   itemSpaceXXX() adds a NORESIZE item instead of ABSOLUTE_XXX
//              this will fix unwanted growing in secondary direction
//
// Version: 1.0 [1999/12/04] Initial Article on CodeProject
//
// 1999/12/10   Erase Backgroung within TabCtrl was 'fixed' badly. Reverted to
//              old working code
// 2000/02/02   When the Dialog is child of a View the class works correctly
//              now [Didier BULTIAUW]
// 2000/02/15   Combo-Boxes were not working correctly (in all modes!)
// 2000/02/17   aligned SpinButton Controls (with buddy) now handled 
//              automatically
//              !! do not add such a control to the layout !! it is always
//              reattached to its buddy.
// 2000/02/17   changed some cotrol class names to the defined constants
//
// Version: 1.1 [2000/02/17]
//
// 2000/02/25   Fixed auto alignment of SpinButton Controls to only affect 
//              visible ones
// 2000/02/27   Put all the classes into the namespace 'ETSLayout'
// 2000/03/07   Fixed growing Dialog after minimizing and restoring
// 2000/05/22   Whole Statusbar (Gripper) is not excluded anymore in EraseBkgnd()
//              instead only the triangular Gripper is excluded
// 2000/05/31   Fix for PropertySheets with PSH_WIZARDHASFINISH [Thömmi]
// 2000/05/31   Fix for UpDown-Controls with EditCtrl Buddy in PropertyPages.
//              These were not repositioned every time the page is being show
//              until the first resize
// 2000/07/28   Problems with resizing ActiveX Controls fixed [Micheal Chapman]
// 2000/07/28   Some strings were not properly wrapped with _T()
// 2000/08/03   Check for BS_GROUPBOX was not correct as BS_GROUPBOX is more than one Bit
// 2000/08/03   New override AddMainArea added to ETSLayoutPropertySheet in order to 
//              have a hook for additional controls in a PropertySheet (besides the Tab)
// 2000/08/03   New override AddButtons added to ETSLayoutPropertySheet in order to 
//              have a hook for additional controls in the bottem pane of a PropertySheet
// 2000/08/03   Removed the need for DECLARE_LAYOUT
//
// Version: 1.2 [2000/08/05]

#define OEMRESOURCE
#include	<windows.h>

#include "stdafx.h"
#include "ETSLayout.h"

using namespace ETSLayout;
#pragma warning(disable: 4097 4610 4510 4100)


#ifndef OBM_SIZE
#define	OBM_SIZE		32766
// taken from WinresRc.h
// if not used for any reason
#endif

#ifdef _DEBUG
#define new DEBUG_NEW
#undef THIS_FILE
static char THIS_FILE[] = __FILE__;
#endif

static UINT auIDStatusBar[] = 
{ 
  ID_SEPARATOR
};

const int ERASE_GROUP_BORDER	= 10;
const int FIXUP_CUTOFF	= 5;
const int TAB_SPACE = 5;

// the _NULL-Pane
CWnd* ETSLayoutMgr::paneNull = 0;

void ETSLayoutMgr::Layout(CRect& rcClient)
{
	if(rcClient.Height() && rcClient.Width()  && m_RootPane.IsValid())	\
		m_RootPane->resizeTo(rcClient);									\
}


ETSLayoutMgr::CPane ETSLayoutMgr::pane( layOrientation orientation, ETSLayoutMgr::layResizeMode modeResize /*=GREEDY*/, 
									   int sizeBorder /*=nDefaultBorder*/, int sizeExtraBorder /*=0*/, 
									   int sizeSecondary /*=0*/)
{
	Pane* pPane = new Pane ( this, orientation, sizeBorder, sizeExtraBorder );
	pPane->m_sizeSecondary = sizeSecondary;
	pPane->m_modeResize    = modeResize;

	return CPane(pPane);
}

ETSLayoutMgr::CPane ETSLayoutMgr::paneTab( CTabCtrl* pTab, layOrientation orientation, 
										  ETSLayoutMgr::layResizeMode modeResize /*=GREEDY*/, int sizeBorder /*=nDefaultBorder*/, 
										  int sizeExtraBorder /*=0*/, int sizeSecondary /*=0*/)
{
	Pane* pPane = new PaneTab ( pTab, this, orientation, sizeBorder, sizeExtraBorder );
	pPane->m_sizeSecondary = sizeSecondary;
	pPane->m_modeResize    = modeResize;

	return CPane(pPane);
}


ETSLayoutMgr::CPane ETSLayoutMgr::paneCtrl( CWnd* pCtrl, layOrientation orientation, 
										   ETSLayoutMgr::layResizeMode modeResize /*=GREEDY*/, int sizeBorder /*=nDefaultBorder*/, 
										   int sizeExtraBorder /*=0*/, int sizeTopExtra /*=0*/, 
										   int sizeSecondary /*=0*/)
{
	Pane* pPane = new PaneCtrl ( pCtrl, this, orientation, sizeBorder, sizeExtraBorder, sizeTopExtra );
	pPane->m_sizeSecondary = sizeSecondary;
	pPane->m_modeResize    = modeResize;

	return CPane(pPane);
}

ETSLayoutMgr::CPane ETSLayoutMgr::paneCtrl( UINT nID, layOrientation orientation, ETSLayoutMgr::layResizeMode modeResize /*=GREEDY*/, 
										   int sizeBorder /*=nDefaultBorder*/, int sizeExtraBorder /*=0*/,
										   int sizeTopExtra /*=0*/, int sizeSecondary /*=0*/)
{
	Pane* pPane = new PaneCtrl ( nID, this, orientation, sizeBorder, sizeExtraBorder, sizeTopExtra );
	pPane->m_sizeSecondary = sizeSecondary;
	pPane->m_modeResize    = modeResize;

	return CPane(pPane);
}


ETSLayoutMgr::CPaneBase ETSLayoutMgr::item(UINT nID, ETSLayoutMgr::layResizeMode modeResize /*=GREEDY*/, int sizeX /*=0*/, int sizeY /*=0*/, 
										   int sizeXMin /*=-1*/, int sizeYMin /*=-1*/)
{
	return new PaneItem( nID, this, modeResize, sizeX, sizeY, sizeXMin, sizeYMin);
}

ETSLayoutMgr::CPaneBase ETSLayoutMgr::item(CWnd* pWnd, ETSLayoutMgr::layResizeMode modeResize /*=GREEDY*/,
										   int sizeX /*=0*/, int sizeY /*=0*/, int sizeXMin /*=-1*/, 
										   int sizeYMin /*=-1*/)
{
	return new PaneItem( pWnd, this, modeResize, sizeX, sizeY, sizeXMin, sizeYMin);
}

ETSLayoutMgr::CPaneBase ETSLayoutMgr::itemFixed(layOrientation orientation, int sizePrimary)
{
	CPaneBase p = new PaneItem(paneNull, this, NORESIZE, (orientation==HORIZONTAL)?sizePrimary:0, (orientation==VERTICAL)?sizePrimary:0);
	return p;
}

ETSLayoutMgr::CPaneBase ETSLayoutMgr::itemGrowing(layOrientation orientation)
{
	return new PaneItem(paneNull, this, (orientation==HORIZONTAL)?ABSOLUTE_VERT:ABSOLUTE_HORZ, 0, 0, -nDefaultBorder, -nDefaultBorder);
}

ETSLayoutMgr::CPaneBase ETSLayoutMgr::itemSpaceBetween( layOrientation orientation, CWnd* pWndFirst, CWnd* pWndSecond )
{
	if( orientation == HORIZONTAL ) {
		// I'm interested in horizontal spacing

		CRect rLeft, rRight;
		pWndFirst->GetWindowRect(&rLeft);
		pWndSecond->GetWindowRect(&rRight);

		int sizeX = rRight.left - rLeft.right;
	
		if( sizeX < 0 ) {
			// compare top to top
			sizeX = rRight.left - rLeft.left;
		}
		else {
			sizeX -= 2*nDefaultBorder;
		}

		return new PaneItem(paneNull, this, NORESIZE, sizeX, 0);
	}
	else {
		// I'm interested in vertical spacing
		CRect rTop, rBot;
		pWndFirst->GetWindowRect(&rTop);
		pWndSecond->GetWindowRect(&rBot);

		int sizeY = rBot.top - rTop.bottom;

		if( sizeY < 0 ) {
			// compare top to top
			sizeY = sizeY = rBot.top - rTop.top;
		}
		else {
			sizeY -= 2*nDefaultBorder;
		}

		return new PaneItem(paneNull, this, NORESIZE, 0, sizeY);
	}
}


ETSLayoutMgr::CPaneBase ETSLayoutMgr::itemSpaceBetween( layOrientation orientation, UINT nIDFirst, UINT nIDSecond )
{
	CWnd *pFirst	= GetWnd()->GetDlgItem(nIDFirst);
	CWnd *pSecond	= GetWnd()->GetDlgItem(nIDSecond);

	ASSERT( pFirst && pSecond );

	return itemSpaceBetween( orientation, pFirst, pSecond );
}


ETSLayoutMgr::CPaneBase ETSLayoutMgr::itemSpaceLike( layOrientation orientation, CWnd* pWnd )
{
	CRect rRect;
	pWnd->GetWindowRect(&rRect);

	if( orientation == HORIZONTAL ) {
		// I'm interested in horizontal spacing
		return new PaneItem(paneNull, this, NORESIZE, rRect.Width(), 0);
	}
	else {
		// I'm interested in vertical spacing
		return new PaneItem(paneNull, this, NORESIZE, 0, rRect.Height() );
	}

}


ETSLayoutMgr::CPaneBase ETSLayoutMgr::itemSpaceLike( layOrientation orientation, UINT nID )
{
	CWnd *pWnd	= GetWnd()->GetDlgItem(nID);
	ASSERT( pWnd );

	return itemSpaceLike( orientation, pWnd );
}



ETSLayoutMgr::~ETSLayoutMgr()
{
}

void ETSLayoutMgr::UpdateLayout()
{
	if(!m_RootPane)
		return;

	// Check constraints
	CRect rcClient = GetRect();

	if( m_pWnd->IsKindOf( RUNTIME_CLASS( CDialog ) ) && !(m_pWnd->GetStyle()&WS_CHILD) ) {
		CRect rcWindow;
		m_pWnd->GetWindowRect(rcWindow);

		// Added by Didier BULTIAUW
        CWnd* parentWnd = m_pWnd->GetParent();
        if( (parentWnd != 0) && parentWnd->IsKindOf(RUNTIME_CLASS(CView)) )
        {
			CRect rcParent;
            parentWnd->GetWindowRect(rcParent);
            rcWindow.OffsetRect(-rcParent.left,-rcParent.top);
        }
		// end add

		CRect rcBorder = rcWindow;
		rcBorder -= rcClient;

		// Min and Max info
		int minWidth	= m_RootPane->getMinConstrainHorz() + rcBorder.Width()  + 2*m_sizeRootBorders.cx;
		int minHeight	= m_RootPane->getMinConstrainVert() + rcBorder.Height() + 2*m_sizeRootBorders.cy;
		int maxWidth	= m_RootPane->getMaxConstrainHorz();
		if(maxWidth != -1) {
			maxWidth += rcBorder.Width()  + 2*m_sizeRootBorders.cx;
			maxWidth = max(maxWidth, minWidth);
		}
		int maxHeight	= m_RootPane->getMaxConstrainVert();
		if(maxHeight != -1) {
			maxHeight += rcBorder.Height() + 2*m_sizeRootBorders.cy;
			maxHeight = max(maxHeight, minHeight);
		}

		if(rcWindow.Width() < minWidth)
			rcWindow.right = rcWindow.left + minWidth;
		if(rcWindow.Height() < minHeight)
			rcWindow.bottom = rcWindow.top + minHeight;

		if(maxWidth != -1  && rcWindow.Width() > maxWidth)
			rcWindow.right = rcWindow.left + maxWidth;
		if(maxHeight != -1 && rcWindow.Height() > maxHeight)
			rcWindow.bottom = rcWindow.top + maxHeight;

		m_pWnd->MoveWindow(rcWindow);
	}
	// Do the Layout
	rcClient = GetRect();

	// Add a Border around the rootPane
	rcClient.top	+= m_sizeRootBorders.cy;
	rcClient.bottom -= m_sizeRootBorders.cy;
	rcClient.left	+= m_sizeRootBorders.cx;
	rcClient.right	-= m_sizeRootBorders.cx;

	if(GetWnd()->IsWindowVisible()) {
		// Avoid ugly artifacts
		//GetWnd()->SetRedraw(FALSE);
		Layout(rcClient);
		//GetWnd()->SetRedraw(TRUE);
	}
	else
		Layout(rcClient);

	// Take special care of SpinButtons (Up-Down Controls) with Buddy set, enumerate
	// all childs:
	CWnd* pWndChild = GetWnd()->GetWindow(GW_CHILD);
	TCHAR szClassName[ MAX_PATH ];
	while(pWndChild)
	{
		::GetClassName( pWndChild->GetSafeHwnd(), szClassName, MAX_PATH );
		DWORD dwStyle = pWndChild->GetStyle();

		// is it a SpinButton?
		if( _tcscmp(szClassName, UPDOWN_CLASS)==0 && ::IsWindowVisible(pWndChild->GetSafeHwnd()) ) {
			HWND hwndBuddy = (HWND)::SendMessage( pWndChild->GetSafeHwnd(), UDM_GETBUDDY, 0, 0);
			if( hwndBuddy != 0 && (dwStyle&(UDS_ALIGNRIGHT|UDS_ALIGNLEFT)) != 0 )
			{
				// reset Buddy
				::SendMessage( pWndChild->GetSafeHwnd(), UDM_SETBUDDY, (WPARAM)hwndBuddy, 0);
			}
		}
		

		pWndChild = pWndChild->GetWindow(GW_HWNDNEXT);
	}


	GetWnd()->Invalidate();
}


bool ETSLayoutMgr::Save(LPCTSTR lpstrRegKey)
{
    CRect rcWnd;

    if(IsWindow(GetWnd()->m_hWnd))
    {
        WINDOWPLACEMENT wp;
        if(GetWnd()->GetWindowPlacement(&wp))
        {
            // Make sure we don't pop up 
            // minimized the next time
            if(wp.showCmd != SW_SHOWMAXIMIZED)
                wp.showCmd = SW_SHOWNORMAL;

            AfxGetApp()->WriteProfileBinary(lpstrRegKey, 
                _T("WindowPlacement"), 
                reinterpret_cast<LPBYTE>(&wp), sizeof(wp));
        }
    }
    return true;
}

bool ETSLayoutMgr::Load(LPCTSTR lpstrRegKey)
{
    LPBYTE pbtData = 0;
    UINT nSize = 0;
    if(AfxGetApp()->GetProfileBinary(lpstrRegKey,
        _T("WindowPlacement"), &pbtData, &nSize))
    {
        WINDOWPLACEMENT* pwp = 
            reinterpret_cast<WINDOWPLACEMENT*>(pbtData);
		
        ASSERT(nSize == sizeof(WINDOWPLACEMENT));
        if(nSize == sizeof(WINDOWPLACEMENT))
            GetWnd()->SetWindowPlacement(reinterpret_cast<WINDOWPLACEMENT*>(pbtData));

        delete [] pbtData;
    }
    return true;
}


void ETSLayoutMgr::EraseBkgnd(CDC* pDC)
{
	CRect	rcClient;
	GetWnd()->GetClientRect( rcClient );

	CRgn	rgn;
	rgn.CreateRectRgnIndirect(rcClient);
    TRACE("CreateRgn (%d,%d,%d,%d)\n", rcClient.left, rcClient.top, rcClient.right, rcClient.bottom );

	CRgn    rgnRect;
	rgnRect.CreateRectRgn(0,0,0,0);

	CRect	rcChild;
	CWnd* pWndChild = GetWnd()->GetWindow( GW_CHILD );

	TCHAR szClassName[ MAX_PATH ];

    pDC->SelectClipRgn(NULL);
    
	while( pWndChild ) {
		
		pWndChild->GetWindowRect(rcChild);
		GetWnd()->ScreenToClient( rcChild );

		rgnRect.SetRectRgn( rcChild );
	
		::GetClassName( pWndChild->GetSafeHwnd(), szClassName, MAX_PATH );
		DWORD dwStyle = pWndChild->GetStyle();

		// doesn't make sense for hidden children
		if( dwStyle & WS_VISIBLE ) {

            // Fix: BS_GROUPBOX is more than one Bit, extend check to (dwStyle & BS_GROUPBOX)==BS_GROUPBOX [ET]
			if( _tcscmp(szClassName,_T("Button"))==0 && (dwStyle & BS_GROUPBOX)==BS_GROUPBOX ) {
				// it is a group-box, ignore completely
			}
			else if( _tcscmp(szClassName,WC_TABCONTROL )==0 ) {
				// ignore Tab-Control's inside rect
				static_cast<CTabCtrl*>(pWndChild)->AdjustRect(FALSE,rcChild);

				CRgn rgnContent;
				rgnContent.CreateRectRgnIndirect(rcChild);

				rgnRect.CombineRgn( &rgnRect, &rgnContent, RGN_DIFF );
				rgn.CombineRgn( &rgn, &rgnRect, RGN_DIFF );
			}
			else if( _tcscmp(szClassName,STATUSCLASSNAME)==0 ) {

				CPoint ptTriangleGrip[3];
				ptTriangleGrip[0] = CPoint(rcChild.right,rcChild.top);
				ptTriangleGrip[1] = CPoint(rcChild.right,rcChild.bottom);
				ptTriangleGrip[2] = CPoint(rcChild.right-rcChild.Height(),rcChild.bottom);

				CRgn rgnGripper;
				rgnGripper.CreatePolygonRgn(ptTriangleGrip,3, WINDING);

				rgn.CombineRgn( &rgn, &rgnGripper, RGN_DIFF );

			}
			else {
				rgn.CombineRgn( &rgn, &rgnRect, RGN_DIFF );
			}
		}

		pWndChild = pWndChild->GetNextWindow();
	}


	HBRUSH hBrBack = (HBRUSH) ::GetClassLong(GetWnd()->GetSafeHwnd(), GCL_HBRBACKGROUND) ;
	if( hBrBack == 0 )
		hBrBack = ::GetSysColorBrush(COLOR_BTNFACE);

	pDC->FillRgn( &rgn, 
		CBrush::FromHandle( hBrBack )
		);
	
}

/////////////////////////////////////////////////////////////////////////////
// ETSLayoutMgr::PaneItem implementation


ETSLayoutMgr::PaneItem::PaneItem(CWnd* pWnd, ETSLayoutMgr* pMgr, ETSLayoutMgr::layResizeMode modeResize/*=GREEDY*/
								 , int sizeX/*=0*/, int sizeY/*=0*/
								 , int sizeXMin/*=-1*/, int sizeYMin/*=-1*/ ) : PaneBase( pMgr )
{
	m_modeResize	= modeResize;
	m_hwndCtrl		= pWnd->GetSafeHwnd();

	m_sizeX			= 0;
	m_sizeY			= 0;

	m_bComboSpecial = false;

	m_sizeXMin		= sizeXMin;
	m_sizeYMin		= sizeYMin;

	if(!m_hwndCtrl) {			// only Dummy!
		m_sizeX = sizeX;
		m_sizeY = sizeY;
	}
	else {
		CRect rcControl;
		::GetWindowRect(m_hwndCtrl, &rcControl);

		if(sizeX == 0) {
			m_sizeX			= rcControl.Width();
		}
		else {
			m_sizeX = sizeX;
		}
		if( m_sizeXMin == -1 ) {
			// do not make smaller than current size
			m_sizeXMin		= rcControl.Width();
		}

		if(sizeY == 0) {
			m_sizeY			= rcControl.Height();
		}
		else {
			m_sizeY = sizeY;
		}
		if( m_sizeYMin == -1 ) {
			// do not make smaller than current size
			m_sizeYMin		= rcControl.Height();
		}

		TCHAR szClassName[ MAX_PATH ];
		::GetClassName( m_hwndCtrl, szClassName, MAX_PATH );

		// special treatment for combo-boxes
		if( _tcscmp(szClassName,_T("ComboBox"))==0 || _tcscmp(szClassName,WC_COMBOBOXEX)==0) {
			m_bComboSpecial = true;
		}
	}
}

ETSLayoutMgr::PaneItem::PaneItem( UINT nID, ETSLayoutMgr* pMgr, ETSLayoutMgr::layResizeMode modeResize/*=GREEDY*/
								 , int sizeX/*=0*/, int sizeY/*=0*/
								 , int sizeXMin/*=-1*/, int sizeYMin/*=-1*/ ) : PaneBase( pMgr )
{
	CWnd* pWnd		= pMgr->GetWnd()->GetDlgItem(nID);
	m_hwndCtrl		= pWnd->GetSafeHwnd();

	m_sizeX			= 0;
	m_sizeY			= 0;

	m_bComboSpecial = false;

	m_modeResize	= modeResize;

	m_sizeXMin = sizeXMin;
	m_sizeYMin = sizeYMin;

	if(!m_hwndCtrl) {			// only Dummy!
		m_sizeX = sizeX;
		m_sizeY = sizeY;
	}
	else {
		CRect rcControl;
		::GetWindowRect(m_hwndCtrl, &rcControl);

		if(sizeX == 0) {
			m_sizeX			= rcControl.Width();
		}
		else {
			m_sizeX = sizeX;
		}
		if( m_sizeXMin == -1 ) {
			// do not make smaller than current size
			m_sizeXMin		= rcControl.Width();
		}

		if(sizeY == 0) {
			m_sizeY			= rcControl.Height();
		}
		else {
			m_sizeY = sizeY;
		}
		if( m_sizeYMin == -1 ) {
			// do not make smaller than current size
			m_sizeYMin		= rcControl.Height();
		}

		TCHAR szClassName[ MAX_PATH ];
		::GetClassName( m_hwndCtrl, szClassName, MAX_PATH );

		// special treatment for combo-boxes
		if( _tcscmp(szClassName,_T("ComboBox"))==0 || _tcscmp(szClassName,WC_COMBOBOXEX)==0) {
			m_bComboSpecial = true;
		}
	}
}

int ETSLayoutMgr::PaneItem::getConstrainHorz(int sizeParent) 
{
	if( m_modeResize & ABSOLUTE_HORZ) {
		return m_sizeX;	
	}
	if(m_modeResize & RELATIVE_HORZ) {
		return (sizeParent * m_sizeX) / 100;	
	}
	return -1;
}

int ETSLayoutMgr::PaneItem::getConstrainVert(int sizeParent) 
{
	if(m_modeResize & ABSOLUTE_VERT) {
		return m_sizeY;	
	}
	if(m_modeResize & RELATIVE_VERT) {
		return (sizeParent * m_sizeY) / 100;	
	}
	return -1;
}

int ETSLayoutMgr::PaneItem::getMinConstrainHorz() 
{
	if(m_modeResize & ABSOLUTE_HORZ) {
		return m_sizeX;	
	}
	return max(nMinConstrain,m_sizeXMin);
}

int ETSLayoutMgr::PaneItem::getMinConstrainVert() 
{
	if(m_modeResize & ABSOLUTE_VERT) {
		return m_sizeY;	
	}
	return max(nMinConstrain,m_sizeYMin);
}

int ETSLayoutMgr::PaneItem::getMaxConstrainHorz() 
{
	if(m_modeResize & ABSOLUTE_HORZ) {
		return m_sizeX;	
	}
	return -1;
}

int ETSLayoutMgr::PaneItem::getMaxConstrainVert() 
{
	if(m_modeResize & ABSOLUTE_VERT) {
		return m_sizeY;	
	}
	return -1;	
}

bool ETSLayoutMgr::PaneItem::resizeTo(CRect& rcNewArea) 
{
	if(m_hwndCtrl) {

		CRect rcWnd;
		::GetWindowRect( m_hwndCtrl, rcWnd );

		if( !(m_modeResize & ALIGN_FILL_HORZ) && m_modeResize & ABSOLUTE_HORZ ) {


			if( (m_modeResize & ALIGN_HCENTER) == ALIGN_HCENTER ) {
				rcNewArea.OffsetRect( (rcNewArea.Width() - rcWnd.Width())/2, 0 ); 
			}
			else if( m_modeResize & ALIGN_RIGHT ) {
				rcNewArea.OffsetRect( rcNewArea.Width() - rcWnd.Width(), 0 ); 
			}

			rcNewArea.right = rcNewArea.left + rcWnd.Width();
		}
		if( !(m_modeResize & ALIGN_FILL_VERT) && m_modeResize & ABSOLUTE_VERT ) {


			if( (m_modeResize & ALIGN_VCENTER) == ALIGN_VCENTER ) {
				rcNewArea.OffsetRect( 0, (rcNewArea.Height()-rcWnd.Height())/2 ); 
			}
			else if( m_modeResize & ALIGN_BOTTOM ) {
				rcNewArea.OffsetRect( 0, rcNewArea.Height() - rcWnd.Height()); 
			}

			rcNewArea.bottom = rcNewArea.top + rcWnd.Height();

		}

		DWORD dwStyle = ::GetWindowLong( m_hwndCtrl, GWL_STYLE );

		// special treatment for combo-boxes
		if( m_bComboSpecial && (dwStyle & CBS_DROPDOWN) ) {
			// keep height (though only fully visible when dropped down)
			rcNewArea.bottom = rcNewArea.top + rcWnd.Height();
		}

    // FIX: ::MoveWindow would case problems with some ActiveX Controls [Micheal Chapman]
    CWnd* pTempWnd = CWnd::FromHandle( m_hwndCtrl );
    pTempWnd->MoveWindow( rcNewArea.left, rcNewArea.top, rcNewArea.Width(), rcNewArea.Height() );

		if( m_bComboSpecial && !(dwStyle & CBS_DROPDOWN) && !(dwStyle & CBS_NOINTEGRALHEIGHT) ) {

			// Keep CB Size = Edit + LB ( if not CBS_NOINTEGRALHEIGHT)

			::GetWindowRect( m_hwndCtrl, rcWnd );

			CRect rcListBox;
			HWND hwndListBox = ::GetDlgItem(m_hwndCtrl, 1000); // ListBox of CB
			if( hwndListBox != 0 )
			{
				::GetWindowRect( hwndListBox, rcListBox );
				rcWnd.bottom = rcListBox.bottom;

				rcNewArea.bottom = rcNewArea.top + rcWnd.Height();

        // FIX: ::MoveWindow would case problems with some ActiveX Controls [Micheal Chapman]
        CWnd* pTempWnd = CWnd::FromHandle( m_hwndCtrl );
        pTempWnd->MoveWindow( rcNewArea.left, rcNewArea.top, rcNewArea.Width(), rcNewArea.Height(), true );
			}
		}

		::RedrawWindow(m_hwndCtrl,0,0, RDW_INVALIDATE | RDW_UPDATENOW ); 

	}
	return true;
}


/////////////////////////////////////////////////////////////////////////////
// ETSLayoutMgr::PaneTab implementation


ETSLayoutMgr::PaneTab::PaneTab( CTabCtrl* pTab, ETSLayoutMgr* pMgr, layOrientation orientation, int sizeBorder /*= nDefaultBorder*/, int sizeExtraBorder /*= 0*/ )
: ETSLayoutMgr::Pane(pMgr, orientation, sizeBorder, sizeExtraBorder) 
{
	ASSERT(pTab);
	m_pTab = pTab;
}

int ETSLayoutMgr::PaneTab::getConstrainHorz(int sizeParent)
{
	CRect rcTab;
	m_pTab->AdjustRect(TRUE, &rcTab);

	if(rcTab.Width() > sizeParent)
		return rcTab.Width();

	return Pane::getConstrainHorz(sizeParent /*- rcTab.Width()*/);
}

int ETSLayoutMgr::PaneTab::getConstrainVert(int sizeParent)
{
	CRect rcTab;
	m_pTab->AdjustRect(TRUE, &rcTab);

	if( m_modeResize & ABSOLUTE_VERT ) {
		return m_sizeSecondary + rcTab.Height();
	}

	if(rcTab.Height() > sizeParent)
		return rcTab.Height();

	return Pane::getConstrainVert(sizeParent /*- rcTab.Height()*/);
}

int ETSLayoutMgr::PaneTab::getMinConstrainHorz()
{
	CRect rcTab(0,0,0,0);
	m_pTab->AdjustRect(TRUE, &rcTab);

	return Pane::getMinConstrainHorz() + rcTab.Width() ;
}

int ETSLayoutMgr::PaneTab::getMinConstrainVert()
{
	CRect rcTab(0,0,0,0);
	m_pTab->AdjustRect(TRUE, &rcTab);

	return Pane::getMinConstrainVert() + rcTab.Height();
}

int ETSLayoutMgr::PaneTab::getMaxConstrainHorz()
{
	CRect rcTab(0,0,0,0);
	m_pTab->AdjustRect(TRUE, &rcTab);

	int paneMax = Pane::getMaxConstrainHorz();
	return (paneMax != -1) ? paneMax + rcTab.Width() : -1;
}

int ETSLayoutMgr::PaneTab::getMaxConstrainVert()
{
	CRect rcTab(0,0,0,0);
	m_pTab->AdjustRect(TRUE, &rcTab);

	int paneMax = Pane::getMaxConstrainVert();
	return (paneMax != -1) ? paneMax + rcTab.Height() : -1;
}

bool ETSLayoutMgr::PaneTab::resizeTo(CRect& rcNewArea)
{
	m_pTab->MoveWindow(rcNewArea);
	m_pTab->AdjustRect(FALSE,rcNewArea);

	return Pane::resizeTo(rcNewArea);
}

/////////////////////////////////////////////////////////////////////////////
// ETSLayoutMgr::PaneCtrl implementation


ETSLayoutMgr::PaneCtrl::PaneCtrl( CWnd* pCtrl, ETSLayoutMgr* pMgr, layOrientation orientation, int sizeBorder /*= nDefaultBorder*/, int sizeExtraBorder /*= 0*/, int sizeTopExtra /*= 0*/ )
: ETSLayoutMgr::Pane(pMgr, orientation, sizeBorder, sizeExtraBorder)
{
	m_sizeTopExtra = sizeTopExtra;

	ASSERT(pCtrl);
	m_hwndCtrl = pCtrl->GetSafeHwnd();
}

ETSLayoutMgr::PaneCtrl::PaneCtrl( UINT nID, ETSLayoutMgr* pMgr, layOrientation orientation, int sizeBorder /*= nDefaultBorder*/, int sizeExtraBorder /*= 0*/, int sizeTopExtra /*= 0*/ )
: ETSLayoutMgr::Pane(pMgr, orientation, sizeBorder, sizeExtraBorder)
{
	m_sizeTopExtra = sizeTopExtra;

	m_hwndCtrl = ::GetDlgItem(pMgr->GetWnd()->GetSafeHwnd(), nID);
	ASSERT(m_hwndCtrl);
}

int ETSLayoutMgr::PaneCtrl::getConstrainHorz(int sizeParent)
{
	return Pane::getConstrainHorz(sizeParent) ;
}

int ETSLayoutMgr::PaneCtrl::getConstrainVert(int sizeParent)
{
	return Pane::getConstrainVert(sizeParent);
}

int ETSLayoutMgr::PaneCtrl::getMinConstrainHorz()
{
	return Pane::getMinConstrainHorz();
}

int ETSLayoutMgr::PaneCtrl::getMinConstrainVert()
{
	return Pane::getMinConstrainVert() + m_sizeTopExtra;
}

int ETSLayoutMgr::PaneCtrl::getMaxConstrainHorz()
{
	int paneMax = Pane::getMaxConstrainHorz();
	return ( paneMax == -1) ? -1 : paneMax ;
}

int ETSLayoutMgr::PaneCtrl::getMaxConstrainVert()
{
	int paneMax = Pane::getMaxConstrainVert();
	return ( paneMax == -1) ? -1 : paneMax + m_sizeTopExtra;
}

bool ETSLayoutMgr::PaneCtrl::resizeTo(CRect& rcNewArea)
{
  // FIX: ::MoveWindow would case problems with some ActiveX Controls [Micheal Chapman]
  CWnd* pTempWnd = CWnd::FromHandle( m_hwndCtrl );
  pTempWnd->MoveWindow( rcNewArea.left, rcNewArea.top, rcNewArea.Width(), rcNewArea.Height(), true );

  ::RedrawWindow(m_hwndCtrl,0,0, RDW_INVALIDATE | RDW_UPDATENOW |RDW_ERASE); 
	rcNewArea.top	+= m_sizeTopExtra;
	return Pane::resizeTo(rcNewArea);
}

/////////////////////////////////////////////////////////////////////////////
// ETSLayoutMgr::Pane implementation

ETSLayoutMgr::Pane::Pane( ETSLayoutMgr* pMgr, layOrientation orientation, int sizeBorder /* = nDefaultBorder */, int sizeExtraBorder /*= 0*/) 
: PaneBase(pMgr)
{
	m_Orientation	= orientation;
	m_sizeBorder	= sizeBorder;
	m_sizeSecondary	= 0;
	m_modeResize	= 0;
	m_sizeExtraBorder= sizeExtraBorder;
}


ETSLayoutMgr::Pane::~Pane() 
{
}


bool ETSLayoutMgr::Pane::addItem( CWnd* pWnd, ETSLayoutMgr::layResizeMode modeResize /*=GREEDY*/, int sizeX /*=0*/, int sizeY /*=0*/, int sizeXMin /*=0*/, int sizeYMin /*=0*/)
{
	CPaneBase pItem = new PaneItem( pWnd, m_pMgr, modeResize, sizeX, sizeY, sizeXMin, sizeYMin);
	return addPane( pItem );
}

bool ETSLayoutMgr::Pane::addItem( UINT nID, ETSLayoutMgr::layResizeMode modeResize /*=GREEDY*/, int sizeX /*=0*/, int sizeY /*=0*/, int sizeXMin /*=0*/, int sizeYMin /*=0*/)
{
	CPaneBase pItem = new PaneItem( nID, m_pMgr, modeResize, sizeX, sizeY, sizeXMin, sizeYMin);
	return addPane( pItem );
}

bool ETSLayoutMgr::Pane::addItemFixed(int size)
{
	CPaneBase pNewItem = m_pMgr->itemFixed(m_Orientation, size);
	return addPane( pNewItem );
}

bool ETSLayoutMgr::Pane::addItemGrowing()
{
	CPaneBase pNewItem = m_pMgr->itemGrowing(m_Orientation);
	return addPane( pNewItem );
}

bool ETSLayoutMgr::Pane::addItemSpaceBetween( CWnd* pWndFirst, CWnd* pWndSecond )
{
	CPaneBase pNewItem = m_pMgr->itemSpaceBetween(m_Orientation, pWndFirst, pWndSecond);
	return addPane( pNewItem );
}

bool ETSLayoutMgr::Pane::addItemSpaceBetween( UINT nIDFirst, UINT nIDSecond )
{
	CPaneBase pNewItem = m_pMgr->itemSpaceBetween(m_Orientation, nIDFirst, nIDSecond);
	return addPane( pNewItem );
}

bool ETSLayoutMgr::Pane::addItemSpaceLike( CWnd* pWnd )
{
	CPaneBase pNewItem = m_pMgr->itemSpaceLike(m_Orientation, pWnd);
	return addPane( pNewItem );
}

bool ETSLayoutMgr::Pane::addItemSpaceLike( UINT nID )
{
	CPaneBase pNewItem = m_pMgr->itemSpaceLike(m_Orientation, nID);
	return addPane( pNewItem );
}

bool ETSLayoutMgr::Pane::addPane( CPane pSubpane, ETSLayoutMgr::layResizeMode modeResize, int sizeSecondary /* = 0 */) 
{
	if( pSubpane->getOrientation() == m_Orientation)
	{
		// wrap in subpane of opposite orientation
		CPane pPaneWrap = new Pane(m_pMgr, m_Orientation==HORIZONTAL?VERTICAL:HORIZONTAL,0,0);
		pPaneWrap->addPane( pSubpane  );

		addPane( pPaneWrap, modeResize, sizeSecondary );
	}
	else
	{
		pSubpane->m_modeResize = modeResize;

		if(m_Orientation==HORIZONTAL && (modeResize & ABSOLUTE_HORZ) ) {
			if(sizeSecondary == 0) {
				pSubpane->m_sizeSecondary = pSubpane->getMinConstrainHorz();
			}
		}
		else if(m_Orientation==HORIZONTAL && (modeResize & RELATIVE_HORZ) ) {
			pSubpane->m_sizeSecondary = sizeSecondary;
		}
		else if(m_Orientation==VERTICAL && (modeResize & ABSOLUTE_VERT) ) {
			if(sizeSecondary == 0) {
				pSubpane->m_sizeSecondary = pSubpane->getMinConstrainVert();
			}
		}
		else if(m_Orientation==VERTICAL && (modeResize & RELATIVE_VERT) ) {
			pSubpane->m_sizeSecondary = sizeSecondary;
		}

		m_paneItems.Add(pSubpane);
	}

	return true;
}

bool ETSLayoutMgr::Pane::addPane( CPaneBase pItem ) 
{
	m_paneItems.Add(pItem);
	return true;
}

int ETSLayoutMgr::Pane::getConstrainHorz(int sizeParent) 
{
	ASSERT( m_Orientation == VERTICAL);

	if( m_modeResize & RELATIVE_HORZ ) {
		return (sizeParent * m_sizeSecondary) / 100;
	}
	else if( m_modeResize & ABSOLUTE_HORZ ){
		return m_sizeSecondary;
	}
	else
		return 0;
}


int ETSLayoutMgr::Pane::getConstrainVert(int sizeParent) 
{
	ASSERT( m_Orientation == HORIZONTAL);

	if( m_modeResize & RELATIVE_VERT ) {
		return (sizeParent * m_sizeSecondary) / 100;
	}
	else if( m_modeResize & ABSOLUTE_VERT ) {
		return m_sizeSecondary;
	}
	else {
		return 0;
	}
}

int ETSLayoutMgr::Pane::getMaxConstrainHorz() 
{
	if(m_Orientation == HORIZONTAL) {
		int nMaxConstr = -1;
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];

			int nConstrain = pItem->getMaxConstrainHorz();
			if(nConstrain == -1)
				return -1;

			nMaxConstr += nConstrain;
		}
		return (nMaxConstr == -1) ? -1 : nMaxConstr + (m_paneItems.GetUpperBound()*m_sizeBorder) + 2*m_sizeExtraBorder;
	}
	else if( m_modeResize & ABSOLUTE_HORZ && m_sizeSecondary!=0) {
		return m_sizeSecondary; // + 2*m_sizeExtraBorder;
	}
	else {
		int nMaxConstr = -1;
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];

			int nConstrain = pItem->getMaxConstrainHorz();

			if( nConstrain == -1)
				return -1;
			else
				nMaxConstr = max(nMaxConstr, nConstrain);

		}
		return (nMaxConstr == -1) ? -1 : nMaxConstr + 2*m_sizeExtraBorder;
	}
}

int ETSLayoutMgr::Pane::getMaxConstrainVert() 
{
	if(m_Orientation == VERTICAL) {
		int nMaxConstr = -1;
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];

			int nConstrain = pItem->getMaxConstrainVert();
			if(nConstrain == -1)
				return -1;

			nMaxConstr += nConstrain;
		}
		return (nMaxConstr == -1) ? -1 : nMaxConstr + (m_paneItems.GetUpperBound()*m_sizeBorder) + 2*m_sizeExtraBorder;
	}
	else if( m_modeResize & ABSOLUTE_VERT && m_sizeSecondary!=0) {
		return m_sizeSecondary; // + 2*m_sizeExtraBorder;
	}
	else {
		int nMaxConstr = -1;
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];

			int nConstrain = pItem->getMaxConstrainVert();

			if( nConstrain == -1)
				return -1;
			else
				nMaxConstr = max(nMaxConstr, nConstrain);

		}
		return (nMaxConstr == -1) ? -1 : nMaxConstr + 2*m_sizeExtraBorder;
	}
}

int ETSLayoutMgr::Pane::getMinConstrainHorz() 
{
	if(m_Orientation == HORIZONTAL) {
		int nMaxConstr = 0;
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];
			nMaxConstr += max(nMinConstrain, pItem->getMinConstrainHorz());
		}
		return nMaxConstr + (m_paneItems.GetUpperBound()*m_sizeBorder) + 2*m_sizeExtraBorder;
	}
	else if( m_modeResize & ABSOLUTE_HORZ && m_sizeSecondary!=0) {
		return m_sizeSecondary; // + 2*m_sizeExtraBorder;
	}
	else {
		int nMaxConstr = 0;
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];
			int nConstrain = pItem->getMinConstrainHorz();
			nMaxConstr = max(nMaxConstr, nConstrain);
		}
		return nMaxConstr + 2*m_sizeExtraBorder;
	}
}

int ETSLayoutMgr::Pane::getMinConstrainVert() 
{
	if(m_Orientation == VERTICAL) {
		int nMaxConstr = 0;
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];
			nMaxConstr += max(nMinConstrain, pItem->getMinConstrainVert());
		}
		return nMaxConstr + (m_paneItems.GetUpperBound()*m_sizeBorder) + 2*m_sizeExtraBorder;
	}
	else if( m_modeResize & ABSOLUTE_VERT && m_sizeSecondary!=0) {
		return m_sizeSecondary; // + 2*m_sizeExtraBorder;
	}
	else {
		int nMaxConstr = 0;
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];
			int nConstrain = pItem->getMinConstrainVert();
			nMaxConstr = max(nMaxConstr, nConstrain);
		}
		return nMaxConstr + 2*m_sizeExtraBorder;
	}
}


int ETSLayoutMgr::Pane::resizeToAbsolute(int& availSpace, CArray<int,int>& sizePrimary, 
										 CArray<int,int>& sizeMin, CArray<int,int>& sizeMax)
{
	// count all greedy items as returnvalue
	int nGreedy = 0;

	// first, subtract all absoulute items from available space
	for(int i=0; i<m_paneItems.GetSize(); ++i) {
		CPaneBase pItem = m_paneItems[i];

		if( m_Orientation == HORIZONTAL ) {

			// for absolute items subtract their size from available space
			if(pItem->modeResize() & ABSOLUTE_HORZ) {
				availSpace -= (sizePrimary[i] = pItem->getConstrainHorz(0));
			}

			// count Greedy items for later
			if(!(pItem->modeResize() & ABSOLUTE_HORZ) && !(pItem->modeResize() & RELATIVE_HORZ)) {
				nGreedy++;
			}

			sizeMin[i] = pItem->getMinConstrainHorz();
			sizeMax[i] = pItem->getMaxConstrainHorz();
		}
		else {

			// for absolute items subtract their size from available space
			if(pItem->modeResize() & ABSOLUTE_VERT) {
				availSpace -= (sizePrimary[i] = pItem->getConstrainVert(0));
			}

			// count Greedy items for later
			if(!(pItem->modeResize() & ABSOLUTE_VERT) && !(pItem->modeResize() & RELATIVE_VERT)) {
				nGreedy++;
			}

			sizeMin[i] = pItem->getMinConstrainVert();
			sizeMax[i] = pItem->getMaxConstrainVert();
		}

	}

	// Must not be negative !!
	availSpace = max(availSpace, 0);

	return nGreedy;
}

bool ETSLayoutMgr::Pane::resizeToRelative(int& availSpace, CArray<int,int>& sizePrimary,
										 CArray<int,int>& sizeMin, CArray<int,int>& sizeMax)
{
	// Then all relative items as percentage of left space (as of now after
	// all absolute items are subtracted

	int availRel = availSpace;	// At the beginning all of remaining space is available. We want all
								// operation to be relative to the left space at this moment, so we
								// save this amount here. Then we safly can lower availSpace

	int relDiff = 0;			// The cumulated difference between first proposed size and
								// eventual maximum/minimum size. This amount has to be
								// saved in some other place (i.e. where relativ items/subpane
								// are not limited by min/max
	
	int relLeft = 0;			// The cumulated amout of space that can be saved by
								// shrinking the items/panes up to the minimum
	
	int relCount = 0;			// Actually allocated item/subpane's cumulated primary sizes 
								// of non-limited items/subpanes (these can be modified in fixup)
								// needed for equally distribution of differences amoung non-limited
								// relative items/subpanes

	for(int i=0; i<m_paneItems.GetSize(); ++i) {
		CPaneBase pItem = m_paneItems[i];

		// For all relative items in primary direction
		if( (m_Orientation==HORIZONTAL && pItem->modeResize() & RELATIVE_HORZ)
			||
			(m_Orientation==VERTICAL   && pItem->modeResize() & RELATIVE_VERT) )
		{
			// minimum item/subpane size in primary direction (pixels)
			int nSizeRelMin = sizeMin[i];

			// maximum item/subpane size in primary direction (pixels)
			int nSizeRelMax = sizeMax[i];

			// Relative size in primary direction (pixels)
			int nSizeRel	= (m_Orientation==HORIZONTAL) 
									? 
									(pItem->getConstrainHorz(availRel)) 
									:
									(pItem->getConstrainVert(availRel));

			if( nSizeRel < nSizeRelMin) {
				// The item/pane is shrinked too small!
				// We will grow it to it's minimum-size. In order not to modify
				// this item later when fixing up set the size to the negative
				// minimum size
				sizePrimary[i]	= -nSizeRelMin;

				// As we grew one item/subpane we have to shrink another one.
				// We keep count on how much space we needed to grow the item
				// to it's minimum size
				relDiff += ( nSizeRelMin - nSizeRel );
			}
			else if(  nSizeRelMax != -1 && nSizeRel > nSizeRelMax) {
				// if there's a maximum size (nSizeRelMax != -1) and our item/subpane
				// is to be resized over that amount correct it.  In order not to modify
				// this item later when fixing up set the size to the negative
				// maximum size
				sizePrimary[i]	= -nSizeRelMax;

				// As we shrinked one item/subpane we have to grow another one.
				// We keep count on how much space we needed to grow the item
				// to it's maximum size.
				relDiff += ( nSizeRelMax - nSizeRel );
			}
			else {
				// this is the normal case: neither are we minimum limited nor maximum
				// limited

				// As this item/subpane is larger that it's minimum we could later (if
				// necessary for fixup) shrink it for the difference amount of pixels
				relLeft	+= ( nSizeRel - nSizeRelMin );

				// Set the primary size of this item/pane. Can later be modified by fixup
				sizePrimary[i]	= nSizeRel;

				// Add this item/subpane's primary size to the count of already allocated
				// cumulated size of non-limited items/subpanes (these can be modified in fixup)
				relCount	+= nSizeRel;
			}

			// decrease available space by used space in this step
			availSpace	-= nSizeRel;
		}
	}

	// We now have the situation that some items/subpanes had to be adjusted for cumulated
	// relDiff pixels (positive value means more space taken than indicated by percentage of
	// left space). On the other hand we have some items/subpanes which were not limited (in 
	// their current dimensions) but could be if necessary up to relLeft pixels. 
	if(relLeft < relDiff && availSpace >= (relDiff-relLeft) ){		

		// If it's not possible to shrink other (relative) panes in order to distribute the
		// difference because the left for shrinking (relLeft) is too small we need to aquire
		// more space from the globally left space (if available at all)
		availSpace -= (relDiff-relLeft);
		relDiff = relLeft;
	}

	// At this point we should have some space left (at least not be negative with the leftover
	// space) and on the other hand there's enough space for the limit-difference to be distributed
//	ASSERT( availSpace >= 0 && relLeft >= relDiff);

	// Fixup Relative:
	// Distribute (if anecessary) relDiff on other (not limited) relative items/subpanes 
	// (if available - if not later just grow the limited panes)
	while( relDiff != 0 && relCount >= 0 ) {

		// in every iteration there must be some space distributed (of the difference) or it could 
		// come to endless looping. Save the amount of space actually distributed in this iteration
		int relDist = 0;

		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			
			CPaneBase pItem = m_paneItems[i];


			// For all relative items in primary direction which were NOT limited
			if( (m_Orientation==HORIZONTAL && (pItem->modeResize() & RELATIVE_HORZ) && sizePrimary[i] > 0)
				||
				(m_Orientation==VERTICAL   && (pItem->modeResize() & RELATIVE_VERT) && sizePrimary[i] > 0) )
			{
				// keep a flag for termination of this iteration
				bool bLast = false;

				// the difference should be distributed amoung all non-limited items/subpanes equally.
				// nDiff is the amount for the current item/subpane
				int nDiff = (relDiff * sizePrimary[i]) / relCount;

				// if it's a too small value just add it to the current pane and break iteration
				if( abs(relDiff) <= FIXUP_CUTOFF ) {
					// take it all in this step
					nDiff = relDiff;

					// set break flag
					bLast = true;
				}

				// calculate the new size for the current item/subpane
				int nNewSize = sizePrimary[i] - nDiff;
			
				if( nNewSize < sizeMin[i] ) {
					// oh, we are limited here. Revise our plan:

					// Not all of the space could be saved, add the actually possible space
					// to the sum
					relDist += ( sizePrimary[i] - sizeMin[i] );

					// set it to the minimum possible size
					sizePrimary[i] = -sizeMin[i];

					// as this item/subpane is now limited it's occupied space doesn't count
					// for relCount anymore
					relCount-= ( sizePrimary[i] );
				}
				else {
					// account the difference of the sizes in relDist and set new size
					relDist += ( sizePrimary[i] - nNewSize );
					sizePrimary[i] = nNewSize;

					// if it's the last one break now
					if(bLast)
						break;
				}
			}
		}
		// Distributed some relDiff-space in every iteration
//		ASSERT(relDist != 0);	
		relDiff -= relDist;

		if( relDist == 0 )
			break;
	}

	{
		// Fixup Relative: invert all negative (limited) sized to correct value
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];
			if( (m_Orientation==HORIZONTAL && (pItem->modeResize() & RELATIVE_HORZ) && sizePrimary[i] < 0)
				||
				(m_Orientation==VERTICAL   && (pItem->modeResize() & RELATIVE_VERT) && sizePrimary[i] < 0) )
			{
				sizePrimary[i] *= -1;
			}
		}
	}

	return true;
}

bool ETSLayoutMgr::Pane::resizeToGreedy(int& availSpace, int nGreedy, CArray<int,int>& sizePrimary, 
									   CArray<int,int>& sizeMin, CArray<int,int>& sizeMax)
{
	// Now resize all Greedy items/subpanes equally among the remaining space
	int greedyDiff = 0;			// The cumulated difference between first proposed size and
								// eventual maximum/minimum size. This amount has to be
								// saved in some other place (i.e. where items/subpane
								// are not limited by min/max
	
	int greedyLeft = 0;			// The cumulated amount of space that can be saved by
								// shrinking the items/panes up to the minimum
	
	int greedyCount = 0;		// Actually allocated item/subpane's cumulated primary sizes 
								// of non-limited items/subpanes (these can be modified in fixup)
								// needed for equally distribution of differences amoung non-limited
								// items/subpanes

	for(int i=0; i<m_paneItems.GetSize(); ++i) {
		CPaneBase pItem = m_paneItems[i];


		if( (m_Orientation==HORIZONTAL 
				&& !(pItem->modeResize()&ABSOLUTE_HORZ) 
				&& !(pItem->modeResize()&RELATIVE_HORZ)
			)
			||
			(m_Orientation==VERTICAL   
				&& !(pItem->modeResize()&ABSOLUTE_VERT) 
				&& !(pItem->modeResize()&RELATIVE_VERT)
			) 
		)
		{

			// All greedy items get an equal portion of the left space
			int nSize		= availSpace / nGreedy;

			// minimum item/subpane size in primary direction (pixels)
			int nSizeMin	= sizeMin[i];

			// maximum item/subpane size in primary direction (pixels)
			int nSizeMax	= sizeMax[i];


			// the last gets the all of the remaining space
			if( nGreedy == 1 )
				nSize = availSpace;						

			if( nSize < nSizeMin) {
				// The item/pane is shrinked too small!
				// We will grow it to it's minimum-size. In order not to modify
				// this item later when fixing up set the size to the negative
				// minimum size
				sizePrimary[i]	= -nSizeMin;

				// As we grew one item/subpane we have to shrink another one.
				// We keep count on how much space we needed to grow the item
				// to it's minimum size
				greedyDiff		+= ( nSizeMin - nSize );
			}
			else if( nSizeMax != -1 && nSize > nSizeMax) {
				// if there's a maximum size (nSizeRelMax != -1) and our item/subpane
				// is to be resized over that amount correct it.  In order not to modify
				// this item later when fixing up set the size to the negative
				// maximum size
				sizePrimary[i]	= -nSizeMax;

				// As we shrinked one item/subpane we have to grow another one.
				// We keep count on how much space we needed to grow the item
				// to it's maximum size.
				greedyDiff		+= ( nSizeMax - nSize );
			}
			else {

				// this is the normal case: neither are we minimum limited nor maximum
				// limited

				// As this item/subpane is larger that it's minimum we could later (if
				// necessary for fixup) shrink it for the difference amount of pixels
				greedyLeft		+= ( nSize - nSizeMin );

				// Set the primary size of this item/pane. Can later be modified by fixup
				sizePrimary[i]	= nSize;

				// Add this item/subpane's primary size to the count of already allocated
				// cumulated size of non-limited items/subpanes (these can be modified in fixup)
				greedyCount		+= nSize;
			}

			// decrease available space by used space in this step
			availSpace	-= nSize;

			// one greedy item/subpane complete
			--nGreedy;
		}
	}


	// Fixup Greedy I
	// Distribute (if anecessary) greedyDiff on other (not limited) greedy items/subpanes 
	// (if available - if not later just grow the limited panes)

	// at least on not limited item present
	bool bAtLeastOne = true;

	while( bAtLeastOne && greedyDiff != 0 && greedyCount > 0) {

		// in every iteration there must be some space distributed (of the difference) or it could 
		// come to endless looping. Save the amount of space actually distributed in this iteration
		int greedyDist = 0;

		// at least on not limited item present
		bAtLeastOne = false;

		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];


			if( (m_Orientation==HORIZONTAL 
					&& !(pItem->modeResize()&ABSOLUTE_HORZ) 
					&& !(pItem->modeResize()&RELATIVE_HORZ)
					&& sizePrimary[i] > 0
				)	
				||
				(m_Orientation==VERTICAL   
					&& !(pItem->modeResize()&ABSOLUTE_VERT) 
					&& !(pItem->modeResize()&RELATIVE_VERT)
					&& sizePrimary[i] > 0 
				)
			)
			{
	 			// keep a flag for termination of this iteration
				bool bLast = false;

				// the difference should be distributed among all non-limited items/subpanes equally.
				// nDiff is the amount for the current item/subpane
				int nDiff = (greedyDiff * sizePrimary[i]) / greedyCount;

				// if it's a too small value just add it to the current pane and break iteration
				if( abs(greedyDiff) <= FIXUP_CUTOFF || nDiff == 0) {
					// take it all in this step
					nDiff = greedyDiff;

					// set break flag
					bLast = true;
				}

				// calculate the new size for the current item/subpane
				int nNewSize = sizePrimary[i] - nDiff;
			
				if( nNewSize < sizeMin[i] ) {
					// oh, we are limited here. Revise our plan:

					if( sizePrimary[i] != sizeMin[i] )
						bAtLeastOne = true;

					// Not all of the space could be saved, add the actually possible space
					// to the sum
					greedyDist += ( sizePrimary[i] - sizeMin[i] );

					// set it to the minimum possible size
					sizePrimary[i] = sizeMin[i];

					// as this item/subpane is now limited its occupied space doesn't count
					// for relCount anymore
					greedyCount -= ( sizePrimary[i] );
				}
				else {
					// yes, there is one
					bAtLeastOne = true;

					// account the difference of the sizes in relDist and set new size
					greedyDist += ( sizePrimary[i] - nNewSize );
					sizePrimary[i] = nNewSize;

					// if it's the last one break now
					if(bLast)
						break;
				}
			}
		}
		// Distributed some greedyDiff-space in every iteration
		ASSERT(!bAtLeastOne || greedyDist != 0 || greedyCount<=0);
		greedyDiff -= greedyDist;
	}


	// Fixup Greedy II
	if( greedyDiff < 0 ) {
		// still difference, some space left

		// are there any items which are minimum-limited where we can give more space?
		for(int i=0; i<m_paneItems.GetSize() && greedyDiff!=0; ++i) {
			CPaneBase pItem = m_paneItems[i];

			if( (m_Orientation==HORIZONTAL 
					&& !(pItem->modeResize()&ABSOLUTE_HORZ) 
					&& !(pItem->modeResize()&RELATIVE_HORZ)
				)	
				||
				(m_Orientation==VERTICAL   
					&& !(pItem->modeResize()&ABSOLUTE_VERT) 
					&& !(pItem->modeResize()&RELATIVE_VERT)
				)
			)
			{
				if( sizePrimary[i] == -sizeMin[i] ) {
					// fill this one up as much as possible
					if( sizeMax[i] == -1) {
						// all fits in
						sizePrimary[i] += greedyDiff;
						greedyDiff = 0;
					}
					else {
						sizePrimary[i] += -min( -greedyDiff, sizeMax[i]-sizeMin[i]);
						greedyDiff     -= -min( -greedyDiff, sizeMax[i]-sizeMin[i]);
					}
				}
			}
		}
	}

	{
		// Fixup Greedy III: invert all negative (limited) sized to correct value
		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];

			if( (m_Orientation==HORIZONTAL 
					&& !(pItem->modeResize() & ABSOLUTE_HORZ) 
					&& !(pItem->modeResize() & RELATIVE_HORZ) 
					&& sizePrimary[i] < 0
					&& sizeMin[i] >= 0
				)
				||
				(m_Orientation==VERTICAL   
					&& !(pItem->modeResize() & ABSOLUTE_VERT) 
					&& !(pItem->modeResize() & RELATIVE_VERT) 
					&& sizePrimary[i] < 0
					&& sizeMin[i] >= 0
				) 
			)
			{
				if(sizePrimary[i] < 0)
					sizePrimary[i] *= -1;
			}
		}
	}

	return true;
}


bool ETSLayoutMgr::Pane::resizeTo(CRect& rcNewArea) 
{
	// There must be some items or subpanes
	ASSERT(m_paneItems.GetSize());

	// This Array holds the size in primary direction for each item/subpane
	CArray<int,int>	sizePrimary;
	sizePrimary.SetSize(m_paneItems.GetSize());

	// This Array holds information about the minimum size in primary direction
	CArray<int,int>	sizeMin;
	sizeMin.SetSize(m_paneItems.GetSize());

	// This Array holds information about the maximum size in primary direction
	CArray<int,int>	sizeMax;
	sizeMax.SetSize(m_paneItems.GetSize());


	// How much space is actually available, subtract all borders between items
	int availSpace = (m_Orientation == HORIZONTAL ? rcNewArea.Width() : rcNewArea.Height() ) - (m_paneItems.GetUpperBound()*m_sizeBorder);
	
	// If there is some Extra border (on top/bottem resp. left/right) subtract it too
	availSpace -= 2*m_sizeExtraBorder;

	// Add the extra Border to top/bottem resp. left/right
	if(m_Orientation == HORIZONTAL) {
		rcNewArea.top		+= m_sizeExtraBorder;
		rcNewArea.bottom	-= m_sizeExtraBorder;
	}
	else {
		rcNewArea.left		+= m_sizeExtraBorder;
		rcNewArea.right		-= m_sizeExtraBorder;
	}

	// Counts the number of greedy items/subpanes
	int nGreedy = resizeToAbsolute(availSpace, sizePrimary, sizeMin, sizeMax );

	if(nGreedy == -1)
		return false;

	if(! resizeToRelative(availSpace, sizePrimary, sizeMin, sizeMax ) )
		return false;

	if(! resizeToGreedy(availSpace, nGreedy, sizePrimary, sizeMin, sizeMax ) )
		return false;


	// If there is any left space and there are ALIGN_FILL_* Items to assign it
	// equally among them
	if( availSpace > 0 ) {
		// Count possible Items
		int nFillItems = 0;

		for(int i=0; i<m_paneItems.GetSize(); ++i) {
			CPaneBase pItem = m_paneItems[i];
			if( m_Orientation == HORIZONTAL 
				&& (pItem->modeResize() & ABSOLUTE_HORZ ) 
				&& (pItem->modeResize() & ALIGN_FILL_HORZ)
			
				||
				
				(pItem->modeResize() & ABSOLUTE_VERT ) 
				&& (pItem->modeResize() & ALIGN_FILL_VERT) 
			)
			{
				++nFillItems;
			}
		}

		if( nFillItems > 0 ) {
			// okay, there are nFillItems, make them all availSpace/nFillItems bigger
			for(int i=0; i<m_paneItems.GetSize(); ++i) {
				CPaneBase pItem = m_paneItems[i];

				if( m_Orientation == HORIZONTAL 
					&& (pItem->modeResize() & ABSOLUTE_HORZ ) 
					&& (pItem->modeResize() & ALIGN_FILL_HORZ)
				
					||
					
					(pItem->modeResize() & ABSOLUTE_VERT ) 
					&& (pItem->modeResize() & ALIGN_FILL_VERT) 
				)
				{

					if( nFillItems == 1 ) {
						// the last one gets all the rest
						sizePrimary[i]	+= availSpace;
						availSpace		= 0;
						--nFillItems;
					}
					else {
						sizePrimary[i]	+= availSpace/nFillItems;
						availSpace		-= availSpace/nFillItems;
						--nFillItems;
					}

				}
			}
		}

	}

	// Now reposition all items:

	// starting offset
	int nOffset = (m_Orientation==HORIZONTAL ? rcNewArea.left : rcNewArea.top ) + m_sizeExtraBorder;
	for(int i=0; i<m_paneItems.GetSize(); ++i) {
		CPaneBase pItem = m_paneItems[i];

		// Calculate rect of item/subpane
		CRect rcPane;
		
		if( m_Orientation==HORIZONTAL ) {
			rcPane.SetRect(nOffset, rcNewArea.top, nOffset+sizePrimary[i], rcNewArea.bottom);
		}
		else {
			rcPane.SetRect(rcNewArea.left, nOffset, rcNewArea.right, nOffset+sizePrimary[i]);
		}

		// do the resizing!
		pItem->resizeTo( rcPane );

		// go to the next position (old pos + size + border)
		ASSERT(sizePrimary[i] >= 0);
		nOffset += m_sizeBorder + sizePrimary[i];
	}				


	return true;			
}


/////////////////////////////////////////////////////////////////////////////
// ETSLayoutDialog dialog

#pragma warning(disable: 4355)
ETSLayoutDialog::ETSLayoutDialog(UINT nID, CWnd* pParent /*=NULL*/, LPCTSTR strName /*=NULL*/, bool bGripper /*=true*/)
	: CBaseDialog(nID, pParent), ETSLayoutMgr( this )
{
	//{{AFX_DATA_INIT(ETSLayoutDialog)
		// NOTE: the ClassWizard will add member initialization here
	//}}AFX_DATA_INIT
	m_bGripper	= bGripper;

	if(strName)
		m_strRegStore = strName;
}
#pragma warning(default: 4355)

BEGIN_MESSAGE_MAP(ETSLayoutDialog, CBaseDialog)
	//{{AFX_MSG_MAP(ETSLayoutDialog)
	ON_WM_SIZE()
	ON_WM_GETMINMAXINFO()
	ON_WM_ERASEBKGND()
	ON_WM_DESTROY()
	//}}AFX_MSG_MAP
END_MESSAGE_MAP()


/////////////////////////////////////////////////////////////////////////////
// ETSLayoutDialog message handlers

BOOL ETSLayoutDialog::OnEraseBkgnd(CDC* pDC) 
{
	EraseBkgnd(pDC);
	return true;
}

void ETSLayoutDialog::OnSize(UINT nType, int cx, int cy) 
{
	CBaseDialog::OnSize(nType, cx, cy);

	if( abs(cx) + abs(cy) > 0) 
	{
		// Reposition Size Marker
		// Re-Layout all controls
		UpdateLayout();
		RepositionBars(AFX_IDW_CONTROLBAR_FIRST, AFX_IDW_CONTROLBAR_LAST, 0);
	}

}

void ETSLayoutDialog::OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI) 
{
	if(m_RootPane.IsValid()) {

		CRect rcClient = GetRect();
		if( rcClient.Height() > 0 || rcClient.Width() > 0 )
		{

			CRect rcWnd;
			GetWindowRect(rcWnd);
			
			// How much do Window and Client differ
			int nDiffHorz = rcWnd.Width() - rcClient.Width();
			int nDiffVert = rcWnd.Height() - rcClient.Height();

			// Take into account that there is a border around the rootPane
			lpMMI->ptMinTrackSize = CPoint(m_RootPane->getMinConstrainHorz() + nDiffHorz + 2*m_sizeRootBorders.cx,
				m_RootPane->getMinConstrainVert() + nDiffVert + 2*m_sizeRootBorders.cy);

			int maxWidth = m_RootPane->getMaxConstrainHorz();
			int maxHeight = m_RootPane->getMaxConstrainVert();

			if( maxWidth != -1 ) {
				lpMMI->ptMaxTrackSize.x = maxWidth + nDiffHorz + 2*m_sizeRootBorders.cx;
				lpMMI->ptMaxSize.x = maxWidth + nDiffHorz + 2*m_sizeRootBorders.cx;
			}

			if( maxHeight != -1 ) {
				lpMMI->ptMaxTrackSize.y = maxHeight + nDiffVert + 2*m_sizeRootBorders.cy;
				lpMMI->ptMaxSize.y = maxHeight + nDiffVert + 2*m_sizeRootBorders.cy;
			}
		}
	}
}


CRect ETSLayoutDialog::GetRect() 
{ 
	CRect r; 
	GetClientRect(r);

	if( m_bGripper ) 
	{
		if( ::IsWindow(m_StatusBar.GetSafeHwnd()) ) 
		{
			CRect rcSizeIcon;
			m_StatusBar.GetWindowRect( rcSizeIcon);
			r.bottom -= (rcSizeIcon.Height() - m_sizeRootBorders.cy - 5);
		}
	}

	return r; 
}


BOOL ETSLayoutDialog::OnInitDialog() 
{
	CBaseDialog::OnInitDialog();

    // Ensure that the dialog is resizable
    this->ModifyStyle(0, WS_THICKFRAME);

	if(!m_strRegStore.IsEmpty()) {
		Load(m_strRegStore);
	}	

#ifdef _AUTO_SET_ICON
	POSITION pos = AfxGetApp()->GetFirstDocTemplatePosition();
	if(pos) {

		class ETSPseudoDocTemplate : public CDocTemplate
		{
			friend class ETSLayoutDialog;
		};

		ETSPseudoDocTemplate* pDocT = (ETSPseudoDocTemplate*) AfxGetApp()->GetNextDocTemplate(pos);
		SetIcon( AfxGetApp()->LoadIcon(pDocT->m_nIDResource) ,FALSE);
	}
#endif
	
	// Sizing icon
	if(m_bGripper)
	{
		if(m_StatusBar.Create(m_pWnd))
		{                           
			m_StatusBar.SetIndicators(auIDStatusBar, sizeof(auIDStatusBar) / sizeof(UINT));
			m_StatusBar.SetWindowText(_T(""));		
			m_StatusBar.SetPaneStyle( 0, SBPS_STRETCH | SBPS_NOBORDERS );
			m_pWnd -> RepositionBars(AFX_IDW_CONTROLBAR_FIRST, AFX_IDW_CONTROLBAR_LAST, 0);
		}             
		else
			AfxMessageBox(_T("Error - Statusbar"));

	}
	return TRUE;  // return TRUE unless you set the focus to a control
	              // EXCEPTION: OCX Property Pages should return FALSE
}

void ETSLayoutDialog::OnDestroy() 
{
	// Store size/position
	if(!m_strRegStore.IsEmpty()) {
		Save(m_strRegStore);
	}	

	// manually delete layout definition if object is reused
	m_RootPane = 0;

	CBaseDialog::OnDestroy();
}

/////////////////////////////////////////////////////////////////////////////
// ETSLayoutDialog dialog

#pragma warning(disable: 4355)
#ifdef CS_HELP
ETSLayoutDialogBar::ETSLayoutDialogBar(UINT nID )
	: CBaseDialogBar( nID ), ETSLayoutMgr( this )
#else
ETSLayoutDialogBar::ETSLayoutDialogBar()
	: ETSLayoutMgr( this )
#endif
{
	//{{AFX_DATA_INIT(ETSLayoutDialogBar)
		// NOTE: the ClassWizard will add member initialization here
	//}}AFX_DATA_INIT
	m_bInitialized = false;
	setRootBorders(0,0);
}
#pragma warning(default: 4355)

BEGIN_MESSAGE_MAP(ETSLayoutDialogBar, CBaseDialogBar)
	//{{AFX_MSG_MAP(ETSLayoutDialogBar)
	ON_WM_SIZE()
	ON_WM_GETMINMAXINFO()
	ON_WM_DESTROY()
	ON_WM_ERASEBKGND()
	ON_MESSAGE(WM_INITDIALOG, OnInitDialog)
	//}}AFX_MSG_MAP
END_MESSAGE_MAP()


/////////////////////////////////////////////////////////////////////////////
// ETSLayoutDialogBar message handlers

LRESULT ETSLayoutDialogBar::OnInitDialog(WPARAM, LPARAM)
{
	Default();
	Initialize();
	return TRUE;
}

void ETSLayoutDialogBar::UpdateLayout()
{
	ETSLayoutMgr::UpdateLayout();

	if(m_RootPane.IsValid()) {
		CRect rcClient = GetRect();

		CRect rcWnd;
		GetWindowRect(rcWnd);
			
		// How much do Window and Client differ
		CSize sizeDiff( rcWnd.Width() - rcClient.Width(), rcWnd.Height() - rcClient.Height());

		// Take into account that there is a border around the rootPane
//		m_szMin = CSize(m_RootPane->getMinConstrainHorz() + sizeDiff.cx + 2*m_sizeRootBorders.cx,
//			m_RootPane->getMinConstrainVert() + sizeDiff.cy + 2*m_sizeRootBorders.cy);
	}
}

CSize ETSLayoutDialogBar::CalcDynamicLayout(int nLength, DWORD dwMode)
{
	CSize sizeRet =  CBaseDialogBar::CalcDynamicLayout(nLength, dwMode);

	CSize sizeMin = sizeRet;
	CSize sizeMax = sizeRet;

	if(m_RootPane.IsValid()) {
		CRect rcClient = GetRect();

		CRect rcWnd;
		GetWindowRect(rcWnd);
			
		// How much do Window and Client differ
		CSize sizeDiff( rcWnd.Width() - rcClient.Width(), rcWnd.Height() - rcClient.Height());

		// Take into account that there is a border around the rootPane
//		sizeMin = CSize(m_RootPane->getMinConstrainHorz() + sizeDiff.cx + 2*m_sizeRootBorders.cx,
//			m_RootPane->getMinConstrainVert() + sizeDiff.cy + 2*m_sizeRootBorders.cy);


		int maxWidth = m_RootPane->getMaxConstrainHorz();
		int maxHeight = m_RootPane->getMaxConstrainVert();

		if( maxWidth != -1 ) {
			sizeMax.cx = maxWidth + sizeDiff.cy + 2*m_sizeRootBorders.cx;
		}

		if( maxHeight != -1 ) {
			sizeMax.cy = maxHeight + sizeDiff.cy + 2*m_sizeRootBorders.cy;
		}
	}

	if( IsFloating() || !(dwMode&LM_HORZ))
	{
		sizeRet.cx = min( sizeRet.cx, sizeMax.cx );
	}
	if( IsFloating() || (dwMode&LM_HORZ))
	{
		sizeRet.cy = min( sizeRet.cy, sizeMax.cy );
	}

	sizeRet.cx = max( sizeRet.cx, sizeMin.cx );
	sizeRet.cy = max( sizeRet.cy, sizeMin.cy );

	return sizeRet;
}

BOOL ETSLayoutDialogBar::OnEraseBkgnd(CDC* pDC) 
{
	EraseBkgnd(pDC);
	return true;
}


void ETSLayoutDialogBar::OnSize(UINT nType, int cx, int cy) 
{
	CBaseDialogBar::OnSize(nType, cx, cy);

	if( abs(cx) + abs(cy) > 0)
	{
		// Re-Layout all controls
		UpdateLayout();
	}
	RepositionBars(AFX_IDW_CONTROLBAR_FIRST, AFX_IDW_CONTROLBAR_LAST, 0);

}


CRect ETSLayoutDialogBar::GetRect() 
{ 
	CRect r; 
	GetClientRect(r);

	if( IsFloating() )
		r.DeflateRect(4,4);

	return r; 
}


void ETSLayoutDialogBar::OnDestroy() 
{
	// Store size/position on your own!
	CBaseDialogBar::OnDestroy();
}



/////////////////////////////////////////////////////////////////////////////
// ETSLayoutFormView dialog

IMPLEMENT_DYNAMIC(ETSLayoutFormView, CFormView)

#pragma warning(disable: 4355)
ETSLayoutFormView::ETSLayoutFormView(UINT nID, LPCTSTR strName /*=NULL*/)
	: CBaseFormView(nID), ETSLayoutMgr( this )
{
	if(strName)
		m_strRegStore = strName;
}
#pragma warning(default: 4355)

BEGIN_MESSAGE_MAP(ETSLayoutFormView, CBaseFormView)
	//{{AFX_MSG_MAP(ETSLayoutFormView)
	ON_WM_SIZE()
	ON_WM_GETMINMAXINFO()
	ON_WM_ERASEBKGND()
	//}}AFX_MSG_MAP
END_MESSAGE_MAP()


/////////////////////////////////////////////////////////////////////////////
// ETSLayoutFormView message handlers

BOOL ETSLayoutFormView::OnEraseBkgnd(CDC* pDC) 
{
	EraseBkgnd(pDC);
	return true;
}


void ETSLayoutFormView::OnSize(UINT nType, int cx, int cy) 
{
//	CBaseFormView::OnSize(nType, cx, cy);
	SetScrollSizes(MM_TEXT, CSize(cx,cy));
	if( abs(cx) + abs(cy) > 0) {
		// Re-Layout all controls
		UpdateLayout();
	}
//	MoveWindow(0,0,cx,cy);
}

/*
void ETSLayoutFormView::UpdateLayout()
{
	ETSLayoutMgr::UpdateLayout();

	if(m_RootPane.IsValid()) {
		// Force MainFrame to re-layout
		CFrameWnd* pFrame = static_cast<CFrameWnd*>(GetParent());
		if(pFrame) {

			CRect rcWnd;
			pFrame->GetWindowRect(rcWnd);
			pFrame->MoveWindow(rcWnd);
			pFrame->RecalcLayout();

		}
		return;
	}
}
*/

void ETSLayoutFormView::OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI) 
{
	// To use this you'll have to modify your CMainFrame:
	//
	// 1) Add a handler for WM_GETMINMAXINFO()
	// 2) Let this handler be:
	// void CMainFrame::OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI) 
	// {
	// 	CFrameWnd::OnGetMinMaxInfo(lpMMI);
	// 
	// 	if( GetActiveView() && GetActiveView()->IsKindOf( RUNTIME_CLASS(ETSLayoutFormView) ) ) {
	// 		GetActiveView()->SendMessage( WM_GETMINMAXINFO, 0, (LPARAM) lpMMI );
	// 	}
	// }
	// 3) Add "#include "dialogmgr.h" to MainFrm.cpp

	if(m_RootPane.IsValid()) {
		CRect rcClient = GetRect();

		CRect rcWnd;
		GetParent()->GetWindowRect(rcWnd);
	
		// How much do Window and Client differ
		rcWnd-=rcClient;

		// Take into account that there is a border around the rootPane
		lpMMI->ptMinTrackSize = CPoint(m_RootPane->getMinConstrainHorz() + rcWnd.Width() + 2*m_sizeRootBorders.cx,
			m_RootPane->getMinConstrainVert() + rcWnd.Height() + 2*m_sizeRootBorders.cy);

		int maxWidth = m_RootPane->getMaxConstrainHorz();
		int maxHeight = m_RootPane->getMaxConstrainVert();

		if( maxWidth != -1 ) {
			lpMMI->ptMaxTrackSize.x = maxWidth + rcWnd.Width()+ 2*m_sizeRootBorders.cx;
			lpMMI->ptMaxSize.x = maxWidth + rcWnd.Width()+ 2*m_sizeRootBorders.cx;
		}

		if( maxHeight != -1 ) {
			lpMMI->ptMaxTrackSize.y = maxHeight + rcWnd.Height() + 2*m_sizeRootBorders.cy;
			lpMMI->ptMaxSize.y = maxHeight + rcWnd.Height() + 2*m_sizeRootBorders.cy;
		}
	}
}

ETSLayoutFormView::~ETSLayoutFormView() 
{
	// Cleanup
}


/////////////////////////////////////////////////////////////////////////////
// ETSLayoutPropertyPage

#ifdef CS_HELP
	IMPLEMENT_DYNCREATE(ETSLayoutPropertyPage, ETSCSHelpPropPage)
#else
	IMPLEMENT_DYNCREATE(ETSLayoutPropertyPage, CPropertyPage)
#endif

#pragma warning(disable: 4355)
ETSLayoutPropertyPage::ETSLayoutPropertyPage( ) : ETSLayoutMgr( this )
{
	m_bLockMove = false;
	m_bResetBuddyOnNextTimeVisible = true;
}

ETSLayoutPropertyPage::ETSLayoutPropertyPage( UINT nIDTemplate, UINT nIDCaption /*= 0*/ )
	: CBasePropertyPage(nIDTemplate, nIDCaption), ETSLayoutMgr( this )
{
	m_bLockMove = false;
	m_bResetBuddyOnNextTimeVisible = true;
}

ETSLayoutPropertyPage::ETSLayoutPropertyPage( LPCTSTR lpszTemplateName, UINT nIDCaption /*= 0*/ )
	: CBasePropertyPage(lpszTemplateName, nIDCaption), ETSLayoutMgr( this )
{
	m_bLockMove = false;
	m_bResetBuddyOnNextTimeVisible = true;
}
#pragma warning(default: 4355)

ETSLayoutPropertyPage::~ETSLayoutPropertyPage()
{
}


BEGIN_MESSAGE_MAP(ETSLayoutPropertyPage, CBasePropertyPage)
	//{{AFX_MSG_MAP(ETSLayoutPropertyPage)
	ON_WM_SIZE()
	ON_WM_GETMINMAXINFO()
	ON_WM_ERASEBKGND()
	ON_WM_WINDOWPOSCHANGING()
	ON_WM_DESTROY()
	ON_WM_WINDOWPOSCHANGED()
	//}}AFX_MSG_MAP
END_MESSAGE_MAP()


/////////////////////////////////////////////////////////////////////////////
// Behandlungsroutinen für Nachrichten ETSLayoutPropertyPage 



void ETSLayoutPropertyPage::OnWindowPosChanged(WINDOWPOS FAR* lpwndpos) 
{
	CBasePropertyPage::OnWindowPosChanged(lpwndpos);
	
	// This code is needed in order to reset the buddy after this page has
	// been activated. At least on Win2k this is not done thru normal resizing,
	// as the page is not visible when first layouted. And without the page
	// being visible it's not possible to tell if the attached buddy is visible
	// or not (at least I don't know any way to do so)

	if( ::IsWindowVisible( GetWnd()->GetSafeHwnd() ) )
	{
		if( m_bResetBuddyOnNextTimeVisible ) 
		{
			// Take special care of SpinButtons (Up-Down Controls) with Buddy set, enumerate
			// all childs:
			CWnd* pWndChild = GetWnd()->GetWindow(GW_CHILD);
			TCHAR szClassName[ MAX_PATH ];
			while(pWndChild)
			{
				::GetClassName( pWndChild->GetSafeHwnd(), szClassName, MAX_PATH );
				DWORD dwStyle = pWndChild->GetStyle();

				// is it a SpinButton?
				if( _tcscmp(szClassName, UPDOWN_CLASS)==0 && ::IsWindowVisible(pWndChild->GetSafeHwnd()) ) {
					HWND hwndBuddy = (HWND)::SendMessage( pWndChild->GetSafeHwnd(), UDM_GETBUDDY, 0, 0);
					if( hwndBuddy != 0 && (dwStyle&(UDS_ALIGNRIGHT|UDS_ALIGNLEFT)) != 0 )
					{
						// reset Buddy
						::SendMessage( pWndChild->GetSafeHwnd(), UDM_SETBUDDY, (WPARAM)hwndBuddy, 0);
					}
				}
				

				pWndChild = pWndChild->GetWindow(GW_HWNDNEXT);
			}

			m_bResetBuddyOnNextTimeVisible = false;
		}
	}	
	else
	{
		// has been hidden again
		m_bResetBuddyOnNextTimeVisible = true;
	}
}

void ETSLayoutPropertyPage::OnWindowPosChanging( WINDOWPOS* lpwndpos )
{
	// In WizardMode the System calls SetWindowPos with the 
	// original size at every activation. This could cause
	// some flicker in certain circumstances. Therefore we lock
	// moving the page and unlock it only if _we_ move the page
	if( m_bLockMove)
	{
		lpwndpos->flags |= SWP_NOMOVE | SWP_NOSIZE;
	}
	CBasePropertyPage::OnWindowPosChanging( lpwndpos );
}

BOOL ETSLayoutPropertyPage::OnEraseBkgnd(CDC* pDC) 
{
	EraseBkgnd(pDC);
	return true;
}

void ETSLayoutPropertyPage::OnDestroy() 
{
	// manually delete layout definition if object is reused
	m_RootPane = 0;

	CBasePropertyPage::OnDestroy();
}

void ETSLayoutPropertyPage::OnSize(UINT nType, int cx, int cy) 
{
	CBasePropertyPage::OnSize(nType, cx, cy);
	
	if( abs(cx) + abs(cy) > 0) 
	{
		// Re-Layout all controls
		UpdateLayout();
	}	
}

void ETSLayoutPropertyPage::OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI) 
{
	if(m_RootPane.IsValid()) {
		CRect rcClient = GetRect();

		CRect rcWnd;
		GetWindowRect(rcWnd);
		
		// How much do Window and Client differ
		int nDiffHorz = rcWnd.Width() - rcClient.Width();
		int nDiffVert = rcWnd.Height() - rcClient.Height();

		// Take into account that there is a border around the rootPane
		lpMMI->ptMinTrackSize = CPoint(m_RootPane->getMinConstrainHorz() + nDiffHorz + 2*m_sizeRootBorders.cx,
			m_RootPane->getMinConstrainVert() + nDiffVert + 2*m_sizeRootBorders.cy);

		int maxWidth = m_RootPane->getMaxConstrainHorz();
		int maxHeight = m_RootPane->getMaxConstrainVert();

		if( maxWidth != -1 ) {
			lpMMI->ptMaxTrackSize.x = maxWidth + nDiffHorz + 2*m_sizeRootBorders.cx;
			lpMMI->ptMaxSize.x = maxWidth + nDiffHorz + 2*m_sizeRootBorders.cx;
		}

		if( maxHeight != -1 ) {
			lpMMI->ptMaxTrackSize.y = maxHeight + nDiffVert + 2*m_sizeRootBorders.cy;
			lpMMI->ptMaxSize.y = maxHeight + nDiffVert + 2*m_sizeRootBorders.cy;
		}
	}
}


CRect ETSLayoutPropertyPage::GetRect() 
{ 
	CRect r; 
	GetClientRect(r);
	return r; 
}


BOOL ETSLayoutPropertyPage::OnInitDialog() 
{
	CBasePropertyPage::OnInitDialog();
	UpdateLayout();

	ETSLayoutPropertySheet* pSheet = (ETSLayoutPropertySheet*) GetParent();

	ASSERT_KINDOF( ETSLayoutPropertySheet, pSheet);
	if(pSheet)
	{
		if(pSheet->IsWizard())
		{
			m_bLockMove = true;
		}
	}

	return TRUE;
}

BOOL ETSLayoutPropertyPage::OnSetActive() 
{
	ETSLayoutPropertySheet* pSheet = (ETSLayoutPropertySheet*) GetParent();

	ASSERT_KINDOF( ETSLayoutPropertySheet, pSheet);
	if(pSheet)
	{
		if(pSheet->IsWizard())
		{
			// In WizardMode the System calls SetWindowPos with the 
			// original size on Page Activation. This will position the
			// page at the correct position
			m_bLockMove = false;
			MoveWindow(pSheet->m_rcPage);
			m_bLockMove = true;
		}
	}

	UpdateLayout();	

	return CBasePropertyPage::OnSetActive();
}

/////////////////////////////////////////////////////////////////////////////
// ETSLayoutPropertySheet

IMPLEMENT_DYNAMIC(ETSLayoutPropertySheet, CPropertySheet)

#pragma warning(disable: 4355)
ETSLayoutPropertySheet::ETSLayoutPropertySheet(UINT nIDCaption, CWnd* pParentWnd, UINT iSelectPage, 
											   LPCTSTR strName /*=NULL*/, bool bGripper/*=true*/)
	: CPropertySheet(nIDCaption, pParentWnd, iSelectPage), ETSLayoutMgr( this )
{
	Init(strName, bGripper);
}

ETSLayoutPropertySheet::ETSLayoutPropertySheet(LPCTSTR pszCaption, CWnd* pParentWnd, UINT iSelectPage, 
											   LPCTSTR strName /*=NULL*/, bool bGripper/*=true*/)
	: CPropertySheet(pszCaption, pParentWnd, iSelectPage), ETSLayoutMgr( this )
{
	Init(strName, bGripper);
}
#pragma warning(default: 4355)

void ETSLayoutPropertySheet::Init(LPCTSTR strName, bool bGripper)
{
	m_bGripper	= bGripper;
	if(strName)
		m_strRegStore = strName;

	m_bAutoDestroy	= false;
	m_bAutoDestroyPages	= false;
	m_bModelessButtons = false;
}

ETSLayoutPropertySheet::~ETSLayoutPropertySheet()
{
}


BEGIN_MESSAGE_MAP(ETSLayoutPropertySheet, CPropertySheet)
	//{{AFX_MSG_MAP(ETSLayoutPropertySheet)
	ON_WM_CREATE()
	ON_WM_SIZE()
	ON_WM_GETMINMAXINFO()
	ON_WM_DESTROY()
	ON_WM_ERASEBKGND()
	//}}AFX_MSG_MAP
END_MESSAGE_MAP()

/////////////////////////////////////////////////////////////////////////////
// Behandlungsroutinen für Nachrichten ETSLayoutPropertySheet 

BOOL ETSLayoutPropertySheet::OnEraseBkgnd(CDC* pDC) 
{
	EraseBkgnd(pDC);
	return true;
}


int ETSLayoutPropertySheet::OnCreate(LPCREATESTRUCT lpCreateStruct) 
{
	if (CPropertySheet::OnCreate(lpCreateStruct) == -1)
		return -1;

	ModifyStyle(0,WS_THICKFRAME| WS_SYSMENU);
	return 0;
}


void ETSLayoutPropertySheet::Resize(int cx, int cy)
{
	if( abs(cx) + abs(cy) > 0 && m_RootPane.IsValid() ) 
	{
		UpdateLayout();

		// Fix for PSH_WIZARDHASFINISH [Thömmi]
		if (IsWizard() && !(m_psh.dwFlags & PSH_WIZARDHASFINISH) )
		{
			// manual reposition of the FINISH button
			// can not be done with normaly layouting because it
			// shares position with the NEXT button
			CWnd *pWndFinish;
			pWndFinish=GetDlgItem(ID_WIZFINISH);

			if(pWndFinish)
			{
				CRect rcWnd;
				GetDlgItem(ID_WIZNEXT)->GetWindowRect(&rcWnd);
				ScreenToClient(&rcWnd);
				pWndFinish->MoveWindow(rcWnd);
				pWndFinish->RedrawWindow(0,0, RDW_INVALIDATE | RDW_UPDATENOW );
			}
		}

		// reposition Gripper
		if(m_bGripper)
			RepositionBars(AFX_IDW_CONTROLBAR_FIRST, AFX_IDW_CONTROLBAR_LAST, 0);

		CPropertyPage* pPage = (CPropertyPage*)GetActivePage();

		if(pPage)
		{
			CRect rcWnd;
			GetTabControl()->GetWindowRect(&rcWnd);
			ScreenToClient(&rcWnd);

			if(!IsWizard()) {
				// get inside of tab
				GetTabControl()->AdjustRect(FALSE, &rcWnd);
			}
			else
			{
				rcWnd.bottom += 5;
			}

			// we need this size in WizardMode in order to 
			// reposition newly activated page correctly
			m_rcPage = rcWnd;
			
			if( IsWizard() && pPage->IsKindOf(RUNTIME_CLASS(ETSLayoutPropertyPage)) )
			{
				ETSLayoutPropertyPage* pEtsPage = reinterpret_cast<ETSLayoutPropertyPage*>(pPage);

				pEtsPage->m_bLockMove = false;
				pEtsPage->MoveWindow(m_rcPage);
				pEtsPage->m_bLockMove = true;
			}
			else 
			{
				pPage->MoveWindow(m_rcPage);
			}
			
		}

		if(IsWindowVisible())
		{
			RedrawWindow(0,0, RDW_INVALIDATE|RDW_UPDATENOW );

			if(!IsWizard())
				GetTabControl()->RedrawWindow(0,0, RDW_INVALIDATE|RDW_UPDATENOW );
		}
	}
}

void ETSLayoutPropertySheet::OnSize(UINT nType, int cx, int cy) 
{
	CPropertySheet::OnSize(nType, cx, cy);
	Resize(cx,cy);
}

// IDs of all PropertySheet controls
long _PropertySheetIDs[] =
{
	ID_WIZBACK,
	ID_WIZNEXT, 
	ID_WIZFINISH,
	IDOK, 
	IDCANCEL,
	ID_APPLY_NOW, 
	IDHELP
};

void ETSLayoutPropertySheet::AddMainArea(CPane paneRoot, CPaneBase itemTab)
{
    // the default is: Whole main Area is covered by the TabCtrl
    paneRoot << itemTab;
}

void ETSLayoutPropertySheet::AddButtons(CPane paneBottom)
{
	// first item greedy to keep others right
	paneBottom->addItem (paneNull, GREEDY);


	// add all Controls to the layouting
	bool bFirst = true;
	for(int i = 0; i < (sizeof(_PropertySheetIDs) / sizeof(long)) ; i++)
	{
		// Prevent movement of finish button, if it is not shown explicitly [Thömmi]
		if( IsWizard() 
			&& _PropertySheetIDs[i] == ID_WIZFINISH 
			&& !(m_psh.dwFlags & PSH_WIZARDHASFINISH) ) 
		{
			continue;
		}

		CWnd* pWnd = GetDlgItem(_PropertySheetIDs[i]);

		if(pWnd)
		{

			if(!(m_psh.dwFlags & PSH_HASHELP) && _PropertySheetIDs[i] == IDHELP)
			{
				// don't insert
				continue;
			}

			if((m_psh.dwFlags & PSH_NOAPPLYNOW) && _PropertySheetIDs[i] == ID_APPLY_NOW)
			{
				// don't insert
				continue;
			}

			// space before first one and between BACK & NEXT
			if( IsWizard() )
			{
				if( !bFirst && !(_PropertySheetIDs[i]==ID_WIZNEXT) )
				{
					paneBottom->addItem(paneNull, NORESIZE,12,0,0,0);
				}
			}

			pWnd->ShowWindow(true);
			paneBottom->addItem(_PropertySheetIDs[i], NORESIZE);			
			bFirst = false;
		}
	}

}

BOOL ETSLayoutPropertySheet::OnInitDialog() 
{
	BOOL bRet = CPropertySheet::OnInitDialog();

	ASSERT(!m_RootPane);

	// Save initial rect
	GetWindowRect(&m_rcStart);

	CPropertyPage* pPage = CPropertySheet::GetActivePage();
	ASSERT(pPage);

	CRect rcPage;
	pPage->GetClientRect(&rcPage);

	CreateRoot(VERTICAL);
	ASSERT(m_RootPane.IsValid());

	// Add Tabcontrol to root pane
	m_ItemTab = item( GetTabControl(), GREEDY, 0, 0, 0, 0);
    AddMainArea(m_RootPane, m_ItemTab);

	// Tabcontrol is invisible in WizardMode
	if(IsWizard())
	{
		GetTabControl()->ShowWindow(false);
	}

	// add horizontal line in WizardMode
	if(IsWizard() && GetDlgItem(ID_WIZFINISH+1))
	{
		m_RootPane << item(ID_WIZFINISH+1, ABSOLUTE_VERT, 0, 0, 0, 0);
	}

	if( IsWizard() || !m_bModeless || m_bModelessButtons )
	{
		// No spaces in WizardMode in order to keep BACK & NEXT together
		CPane bottomPane = pane(HORIZONTAL, ABSOLUTE_VERT, IsWizard() ? 0 : 5);

        AddButtons(bottomPane);
		// add bottom (button) pane if any controls were added
        if(bottomPane->m_paneItems.GetSize() > 0) {
    		m_RootPane << bottomPane;
        }
	}



	// some Space between Buttons und Gripper
	if(m_bGripper)
	{
		m_RootPane->addItem(paneNull, ABSOLUTE_VERT,0,2);

		if(m_StatusBar.Create(m_pWnd))
		{                           
			m_StatusBar.SetIndicators(auIDStatusBar,
				sizeof(auIDStatusBar) / sizeof(UINT));
			m_StatusBar.SetWindowText(_T(""));		
			RepositionBars(AFX_IDW_CONTROLBAR_FIRST, AFX_IDW_CONTROLBAR_LAST, 0);
		}             
		else
		{
			AfxMessageBox(_T("Error - Statusbar"));
		}
	}

	if(!m_strRegStore.IsEmpty())
	{
		Load(m_strRegStore);
	}	

	Resize(1,1); // Fix. for 95/98/NT difference

	CRect rcWnd;
	GetWindowRect( & rcWnd );
	MoveWindow( rcWnd );

	return bRet;
}


void ETSLayoutPropertySheet::OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI) 
{
	if(m_RootPane.IsValid() && GetTabControl() != 0 ) 
	{
		CRect rcWnd;
		GetWindowRect(rcWnd);		

		CRect rcClient = GetRect();
		rcWnd-=rcClient;

		// ask for MinMax of all pages
		CSize sizePageMax(0,0);
		CSize sizePageMin(0,0);
		for( int nPage=0; nPage<GetPageCount(); ++nPage)
		{
			CPropertyPage* pPage = GetPage(nPage);
			ASSERT(pPage);
			if( pPage )
			{
				MINMAXINFO mmi;
				memset(&mmi, 0, sizeof(mmi));

				if( IsWindow(pPage->GetSafeHwnd()) )
				{
					pPage->SendMessage(WM_GETMINMAXINFO, 0, (LPARAM) &mmi);

					if(mmi.ptMaxTrackSize.x != 0)
					{
						sizePageMax.cx = min(sizePageMax.cx, mmi.ptMaxTrackSize.x);
					}
					if(mmi.ptMaxTrackSize.y != 0)
					{
						sizePageMax.cy = min(sizePageMax.cy, mmi.ptMaxTrackSize.y);
					}
					if(mmi.ptMinTrackSize.x != 0)
					{
						sizePageMin.cx = max(sizePageMin.cx, mmi.ptMinTrackSize.x);
					}
					if(mmi.ptMinTrackSize.y != 0)
					{
						sizePageMin.cy = max(sizePageMin.cy, mmi.ptMinTrackSize.y);
					}
				}
			}
		}
		static_cast<PaneItem*>( m_ItemTab.GetPaneBase() )->m_sizeXMin = sizePageMin.cx;
		static_cast<PaneItem*>( m_ItemTab.GetPaneBase() )->m_sizeYMin = sizePageMin.cy;

		// calculate the needed size of the tabctrl in non-wizard-mode
		CRect rcItem(0,0,0,0);
		if(!IsWizard())
		{
			GetTabControl()->AdjustRect( TRUE, rcItem );
		}

		lpMMI->ptMinTrackSize.x = m_RootPane->getMinConstrainHorz() + rcWnd.Width() + 2*m_sizeRootBorders.cx
					+ rcItem.Width();

		lpMMI->ptMinTrackSize.y = m_RootPane->getMinConstrainVert() + rcWnd.Height() + 2*m_sizeRootBorders.cy 
				+ rcItem.Height();

		// never smaller than inital size!
		lpMMI->ptMinTrackSize.x = max(lpMMI->ptMinTrackSize.x, m_rcStart.Width() );
		lpMMI->ptMinTrackSize.y = max(lpMMI->ptMinTrackSize.y, m_rcStart.Height() );

		// Rest like ETSLayoutMgr

		int maxWidth = m_RootPane->getMaxConstrainHorz();
		int maxHeight = m_RootPane->getMaxConstrainVert();

		if( maxWidth != -1 ) 
		{
			lpMMI->ptMaxSize.x = sizePageMax.cx + rcWnd.Width()+ 2*m_sizeRootBorders.cx + rcItem.Width() ;
		}

		if( maxHeight != -1 ) 
		{
			lpMMI->ptMaxSize.y = sizePageMax.cy + rcWnd.Height() + 2*m_sizeRootBorders.cy + rcItem.Width() ;
		}

		lpMMI->ptMaxTrackSize = lpMMI->ptMaxSize;

	}
}


void ETSLayoutPropertySheet::OnDestroy() 
{
	// Store size/position
	if(!m_strRegStore.IsEmpty()) 
	{
		Save(m_strRegStore);
	}	
	m_RootPane = 0;

	CPropertySheet::OnDestroy();
}

void ETSLayoutPropertySheet::PostNcDestroy()
{
	if(m_bAutoDestroyPages)
	{
		// walk all pages and destry them
		for( int nPage=0; nPage<GetPageCount(); ++nPage)
		{
			CPropertyPage* pPage = GetPage(nPage);
			ASSERT(pPage);
			if( pPage )
			{
				delete pPage;
			}
		}
	}

	if(m_bAutoDestroy)
		delete this;
}



/**
 * CPane represents an autopointer to a PaneBase. Use this and you won't have to worry
 * about cleaning up any Panes. Also this autopointer lets you return Pane objects
 * from function without using pointers (at least you won't see them :) )
 */
ETSLayoutMgr::PaneHolder::PaneHolder(PaneBase* pPane )
{

	ASSERT( pPane );
	m_pPane = pPane;

	// Implicitly AddRef()
	m_nRefCount = 1;
}

ETSLayoutMgr::PaneHolder::~PaneHolder()
{
	ASSERT( m_pPane );
	ASSERT( m_nRefCount == 0 );

	delete m_pPane;
}

void ETSLayoutMgr::PaneHolder::AddRef()
{
	InterlockedIncrement( &m_nRefCount );
}

void ETSLayoutMgr::PaneHolder::Release()
{
	if( InterlockedDecrement( &m_nRefCount ) <= 0 )
	{
		// no more references on me, so destroy myself
		delete this;
	}
}

ETSLayoutMgr::CPaneBase::CPaneBase( )
{
	// MUST be initialized later
	m_pPaneHolder = 0;
}

ETSLayoutMgr::CPaneBase::CPaneBase( PaneBase* pPane )
{
	m_pPaneHolder = 0;
	
	if( pPane != 0)
		operator=( pPane );
}

ETSLayoutMgr::CPaneBase::CPaneBase( const CPaneBase& other )
{
	m_pPaneHolder = 0;
	operator=(other);
}

ETSLayoutMgr::CPaneBase::~CPaneBase()
{
	if(m_pPaneHolder)
		m_pPaneHolder->Release();
}

void ETSLayoutMgr::CPaneBase::operator=( PaneBase* pPane )
{
	if(m_pPaneHolder)
	{
		m_pPaneHolder->Release();
		m_pPaneHolder = 0;
	}

	if( pPane != 0 )
		m_pPaneHolder = new PaneHolder( pPane );
}

void ETSLayoutMgr::CPaneBase::operator=( const CPaneBase& other )
{
	ASSERT( other.m_pPaneHolder );

	if(m_pPaneHolder)
	{
		m_pPaneHolder->Release();
		m_pPaneHolder = 0;
	}

	other.m_pPaneHolder->AddRef();
	m_pPaneHolder = other.m_pPaneHolder;
}

ETSLayoutMgr::PaneBase* ETSLayoutMgr::CPaneBase::operator->() const
{
	ASSERT(m_pPaneHolder);

	if(!m_pPaneHolder)
		return 0;

	return (m_pPaneHolder->m_pPane);
}



ETSLayoutMgr::CPane::CPane( )
{
}

ETSLayoutMgr::CPane::CPane( Pane* pPane ) : ETSLayoutMgr::CPaneBase( static_cast<PaneBase*>(pPane) )
{
}

ETSLayoutMgr::CPane::CPane( const CPane& other )
{
	operator=(other);
}

ETSLayoutMgr::CPane::~CPane()
{
}

void ETSLayoutMgr::CPane::operator=( Pane* pPane )
{
	CPaneBase::operator=(pPane);
}

void ETSLayoutMgr::CPane::operator=( const ETSLayoutMgr::CPane& other )
{
	ASSERT( other.m_pPaneHolder );

	if(m_pPaneHolder)
	{
		m_pPaneHolder->Release();
		m_pPaneHolder = 0;
	}

	other.m_pPaneHolder->AddRef();
	m_pPaneHolder = other.m_pPaneHolder;
}

ETSLayoutMgr::Pane* ETSLayoutMgr::CPane::operator->() const
{
	ASSERT(m_pPaneHolder);

	if(!m_pPaneHolder)
		return 0;

	return reinterpret_cast<Pane*>(m_pPaneHolder->m_pPane);
}

ETSLayoutMgr::CPaneBase ETSLayoutMgr::CPane::ConvertBase() const
{
	ASSERT(m_pPaneHolder);
	return CPaneBase( m_pPaneHolder->m_pPane );
}

ETSLayoutMgr::CPane& ETSLayoutMgr::CPane::operator<< ( const ETSLayoutMgr::CPane pPane )
{
	GetPane()->addPane( pPane, (ETSLayoutMgr::layResizeMode)pPane->m_modeResize, pPane->m_sizeSecondary);
	return (*this);
}

ETSLayoutMgr::CPane& ETSLayoutMgr::CPane::operator<< ( const ETSLayoutMgr::CPaneBase pItem )
{
	GetPane()->addPane( pItem );
	return (*this);
}
