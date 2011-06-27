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


#if !defined(ETS_LAYOUTMGR_INCLUDED_)
#define ETS_LAYOUTMGR_INCLUDED_

#if _MSC_VER >= 1000
#pragma once
#endif // _MSC_VER >= 1000
// DialogMgr.h : header file
//

namespace ETSLayout
{

#ifdef CS_HELP
	typedef ETSCSHelpDialog		CBaseDialog;
	typedef ETSCSHelpFormView	CBaseFormView;
	typedef ETSCSHelpDlgBar		CBaseDialogBar;
	typedef ETSCSHelpPropPage	CBasePropertyPage;
#else
	typedef CDialog				CBaseDialog;
	typedef CFormView			CBaseFormView;
	typedef CDialogBar			CBaseDialogBar;
	typedef CPropertyPage		CBasePropertyPage;
#endif
}

// Support for CBCGDialogBar instead of CDialogBar available:
// you just have to change the typedef to CBaseDialogBar

#ifndef ETSGUI_EXT_CLASS
#define ETSGUI_EXT_CLASS
#endif

#include <afxtempl.h>

// Support for CBCGDialogBar instead of CDialogBar

/**
 * Controls whether the Icon is automatically set to IDR_MAINFRAME
 */
#define _AUTO_SET_ICON

/**
 * Forward class declarations
 */
class ETSLayoutDialog;
class ETSLayoutDialogBar;
class ETSLayoutFormView;
class ETSLayoutMgr;
class ETSLayoutPropertyPage;
class ETSLayoutPropertySheet;


/**
 * These are NOOPs now
 */
#define DECLARE_LAYOUT()
#define IMPLEMENT_LAYOUT()

/**
 * This is the default border size between the panes. You
 * may override it in Pane constructor, but it is the
 * fixed border around the root pane
 */
const int nDefaultBorder	= 5;

/**
 * The minimum size for not ABSOLUTE_XXX items
 */
const int nMinConstrain = 5;

class ETSGUI_EXT_CLASS ETSLayoutMgr
{
public:
	
	enum layResizeMode {
		GREEDY				= 0,		// Will eat up as much as it can
		ABSOLUTE_HORZ		= 1 << 0,	// Horizontal size is absolute
		RELATIVE_HORZ		= 1 << 1,	// Horizontal size in percent
		ABSOLUTE_VERT		= 1 << 2,	// Vertical size is absolute
		RELATIVE_VERT		= 1 << 3,	// Vertical size in percent

		NORESIZE			= ABSOLUTE_HORZ | ABSOLUTE_VERT,

		SIZE_MASK			= NORESIZE,

		ALIGN_LEFT			= 1 << 4,   // following only for NORESIZE
		ALIGN_RIGHT			= 1 << 5,
		ALIGN_TOP			= 1 << 6,
		ALIGN_BOTTOM		= 1 << 7,

		ALIGN_HCENTER		= ALIGN_LEFT    | ALIGN_RIGHT,	
		ALIGN_VCENTER		= ALIGN_TOP     | ALIGN_BOTTOM,

		ALIGN_CENTER		= ALIGN_HCENTER | ALIGN_VCENTER,

		ALIGN_FILL_HORZ		= 1 << 8,
		ALIGN_FILL_VERT		= 1 << 9,
		ALIGN_FILL			= ALIGN_FILL_HORZ | ALIGN_FILL_VERT,
	
/*		TRACKER_LEFT		= 1 << 10,	// not yet. May allow tracking of borders
		TRACKER_RIGHT		= 1 << 11,  // between items in the future
		TRACKER_TOP			= 1 << 12,
		TRACKER_BOTTOM		= 1 << 13,
*/
	};

	enum layOrientation {
		HORIZONTAL,
		VERTICAL
	};

	/**
	 * This is the base class for all kind of panes. 
	 */
	class ETSGUI_EXT_CLASS PaneBase {
		friend class ETSLayoutMgr;
		friend class CPaneBase;
		friend class CPane;

	public:

		/**
		 * Informs the caller how much of the given space this pane would
		 * like to receive in horizontal direction
		 */
		virtual int		getConstrainHorz(int sizeParent) = 0;


		/**
		 * Informs the caller how much of the given space this pane would
		 * like to receive in vertical direction
		 */
		virtual int		getConstrainVert(int sizeParent) = 0;

		/**
		 * Informs the caller how much of the given space this pane
		 * minimally need. This would be an absolute Value if 
		 * the mode contains ABSOLUTE_HORZ or an explicit minimum
		 * value, else nMinConstrain
		 */
		virtual int		getMinConstrainHorz() = 0;
		/**
		 * Informs the caller if there is an restriction for maximum
		 * space this pane needs. Return -1 for unrestricted (GREEDY
		 * or RELATIVE)
		 */
		virtual int		getMaxConstrainHorz() = 0;

		/**
		 * Informs the caller how much of the given space this pane
		 * minimally need. This would be an absolute Value if 
		 * the mode contains ABSOLUTE_VERT or an explicit minimum
		 * value, else nMinConstrain
		 */
		virtual int		getMinConstrainVert() = 0;

		/**
		 * Informs the caller if there is an restriction for maximum
		 * space this pane needs. Return -1 for unrestricted (GREEDY
		 * or RELATIVE)
		 */
		virtual int		getMaxConstrainVert() = 0;

		/**
		 * This will do the actual resize operation after the
		 * caller computed a new area for this pane
		 */
		virtual bool	resizeTo(CRect& rcNewArea) = 0;

		/**
		 * Constructor needed pointer to LayoutManager
		 */
		PaneBase( ETSLayoutMgr* pMgr )		{ m_pMgr = pMgr; };

		/**
		 * Virtual destructor needed in Container operations
		 */
		virtual ~PaneBase() {};

		/**
		 * Returs the Resize Mode of this pane
		 */
		DWORD	modeResize() { return m_modeResize; };

	protected:
		/**
		 * How this Item will be resized, a combination of the flags above
		 */
		DWORD	m_modeResize;

		/**
		 * A pointer to the holding LayoutManager derivate
		 */
		ETSLayoutMgr*		m_pMgr;
	};

	/**
	 * CPaneBase represents an autopointer to a PaneBase. Use this and you won't have to worry
	 * about cleaning up any Panes. Also this autopointer lets you return Pane objects
	 * from function without using pointers (at least you won't see them :) )
	 */
	struct ETSGUI_EXT_CLASS PaneHolder
	{
		PaneHolder(PaneBase* pPane );
		~PaneHolder();

		void	AddRef();
		void	Release();

		PaneBase*	m_pPane;
		long		m_nRefCount;
	};

	class ETSGUI_EXT_CLASS CPaneBase
	{
	protected:
		PaneHolder*		m_pPaneHolder;

	public:
		// Standardconstructor
		CPaneBase( );
		CPaneBase( PaneBase* pPane );
		CPaneBase( const CPaneBase& other );

		~CPaneBase();

		void operator=( PaneBase* pPane );
		void operator=( const CPaneBase& other );
		PaneBase* operator->() const;
		PaneBase* GetPaneBase()	{ return operator->(); }

		bool IsValid()			{ return (m_pPaneHolder != 0); }
		bool operator !()		{ return (m_pPaneHolder == 0); }

	};

	class Pane;
	class ETSGUI_EXT_CLASS CPane : public CPaneBase
	{
	public:
		// Standardconstructor
		CPane( );
		CPane( Pane* pPane );
		CPane( const CPane& other );

		~CPane();

		void operator=( Pane* pPane );
		void operator=( const CPane& other );
		Pane* operator->() const;
		Pane* GetPane()			{ return operator->(); }

		CPaneBase ConvertBase() const;

		CPane& operator<< ( const CPane pPane );
		CPane& operator<< ( const CPaneBase pItem );
	};



	/**
	 * PaneItem represents a single control
	 */
	class ETSGUI_EXT_CLASS PaneItem : public PaneBase {
		friend class ETSLayoutMgr;
		friend class Pane;
	protected:
		/**
		 * Creates a new PaneItem from an Control. If sizeX or sizeY are 0
		 * and modeResize is ABSOLUTE will copy the current dimensions of
		 * the control to m_sizeX/Y. So the appearance does not change
		 * from the Dialog Editor
		 */
		PaneItem( CWnd* pWnd, ETSLayoutMgr* pMgr, layResizeMode modeResize = GREEDY, int sizeX=0, int sizeY=0, int sizeXMin=0, int sizeYMin=0);

		/**
		 * If your control is not mapped you can name it by its ChildID. Pass
		 * the pMgr to receive the CWnd* of nID. 
		 * The rest as stated above
		 */
		PaneItem( UINT nID, ETSLayoutMgr* pMgr, layResizeMode modeResize = GREEDY, int sizeX=0, int sizeY=0, int sizeXMin=0, int sizeYMin=0);


	public:
		/**
		 * see PaneBase
		 */
		virtual int getConstrainHorz(int sizeParent);
		virtual int getConstrainVert(int sizeParent);
		virtual int getMinConstrainHorz();
		virtual int getMinConstrainVert();
		virtual int	getMaxConstrainHorz();
		virtual int	getMaxConstrainVert();
		virtual bool resizeTo(CRect& rcNewArea);

		bool	isDummy()				{ return (m_hwndCtrl == 0);	}

	protected:
		friend class ETSLayoutPropertySheet;

		/**
		 * The horizontal size of the control (see m_modeResize)
		 */
		int				m_sizeX;
		int				m_sizeXMin;

		/**
		 * The vertical size of the control (see m_modeResize)
		 */
		int				m_sizeY;
		int				m_sizeYMin;

		/**
		 * Child Control pointer
		 */
		HWND			m_hwndCtrl;

		/**
		 * Combo box needs special treatment
		 */
		bool			m_bComboSpecial;
	};


	/**
	 * This class encapsulates a Subpane (and indeed the root Pane too)
	 * it is a container of PaneBase* which it will recursivly resize
	 */
	class ETSGUI_EXT_CLASS Pane : public PaneBase {
		friend class ETSLayoutMgr;
		friend class CPaneBase;
		friend class CPane;
        friend class ETSLayoutPropertySheet;
        
	protected:
		/**
		 * Tell the pane in which direction it is positioned. A HORIZONTAL pane
		 * arranges it's subpanes from left to right, a VERTICAL from top to bottom
		 */
		Pane( ETSLayoutMgr* pMgr, layOrientation orientation, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0 );

	public:
		/**
		 * If your control is not mapped you can name it by its ChildID. Pass
		 * the pMgr to receive the CWnd* of nID. 
		 * The rest as stated above
		 */
		bool addItem( UINT nID, layResizeMode modeResize = GREEDY, int sizeX=0, int sizeY=0, int sizeXMin=-1, int sizeYMin=-1);

		/**
		 * Creates a new PaneItem from an Control. If sizeX or sizeY are 0
		 * and modeResize is ABSOLUTE will copy the current dimensions of
		 * the control to m_sizeX/Y. So the appearance does not change
		 * from the Dialog Editor
		 */
		bool addItem( CWnd* pWnd, layResizeMode modeResize = GREEDY, int sizeX=0, int sizeY=0, int sizeXMin=-1, int sizeYMin=-1);


		/**
		 * Add a whitespace Item (paneNull) of variable size with
		 * a minimum size of 0
		 */
		bool addItemGrowing();

		/**
		 * Add a whitespace Item (paneNull) with fixed size
		 */
		bool addItemFixed(int size);

		/**
		 * Add a whitespace Item (paneNull) of fixed size based on the
		 * current layout (as in the dialog template). Based on the layout
		 * of the pane vertical or horizontal spacing is considered
		 *
		 * First argument is the left (top) item for a HORIZONTAL (VERTICAL) pane
		 */
		bool addItemSpaceBetween( CWnd* pWndFirst, CWnd* pWndSecond );
		bool addItemSpaceBetween( UINT nIDFirst, UINT nIDSecond );


		/**
		 * Add a whitespace Item (paneNull) of fixed size based on the
		 * size of another item
		 */
		bool addItemSpaceLike( CWnd* pWnd );
		bool addItemSpaceLike( UINT nID );


		/**
		 * Add an item to the pane, appending at the end. This may be either obtained
		 * by a call to ETSLayoutMgr::item() or one of the ETSLayoutMgr::paneXXX() calls
		 */
		bool addPane( CPaneBase pItem );
		bool addPane( CPane pSubpane, layResizeMode modeResize, int sizeSecondary /* = 0 */);

		virtual int		getConstrainHorz(int sizeParent);
		virtual int		getConstrainVert(int sizeParent);
		virtual int		getMinConstrainHorz();
		virtual int		getMinConstrainVert();
		virtual int		getMaxConstrainHorz();
		virtual int		getMaxConstrainVert();
		virtual bool	resizeTo(CRect& rcNewArea);

		/**
		 * The destructor takes care of destroying all Subpanes and items
		 */
		virtual ~Pane();

		/**
		 * Access to the orientation of this pane
		 */
		layOrientation	getOrientation() { return m_Orientation; };


	protected:

		int		resizeToAbsolute(int& availSpace, CArray<int,int>& sizePrimary, 
									CArray<int,int>& sizeMin, CArray<int,int>& sizeMax);
		
		bool	resizeToRelative(int& availSpace, CArray<int,int>& sizePrimary, 
									CArray<int,int>& sizeMin, CArray<int,int>& sizeMax);

		bool	resizeToGreedy(  int& availSpace, int nGreedy, CArray<int,int>& sizePrimary, 
									CArray<int,int>& sizeMin, CArray<int,int>& sizeMax);

		/**
		 * The orientation of the pane. Keep in mind that all subpanes
		 * must have the complementary orientation, i.e. a VERTICAL
		 * pane must have all HORIZONTAL SubPanes (or normal Items
		 * of course)
		 */
		layOrientation					m_Orientation;

		/**
		 * This array holds the pointers to the Items/SubPanes
		 */
		CArray<CPaneBase, CPaneBase>	m_paneItems;

		/**
		 * The secondary constrain
		 */
		int				m_sizeSecondary;

		/** 
		 * Size of gap between childs
		 */
		int				m_sizeBorder;
		int				m_sizeExtraBorder;
	};


	/**
	 * This class encapsulates a Subpane which is a Tab
	 * it will use calls to AdjustRect to position it's
	 * childs
	 */
	class ETSGUI_EXT_CLASS PaneTab : public Pane
	{
		friend class ETSLayoutMgr;

	protected:
		/**
		 * Tell the pane in which direction it is positioned. A HORIZONTAL pane
		 * arranges it's subpanes from left to right, a VERTICAL from top to bottom
		 */
		PaneTab( CTabCtrl* pTab, ETSLayoutMgr* pMgr, layOrientation orientation, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0 );

	public:
		virtual int		getConstrainHorz(int sizeParent);
		virtual int		getConstrainVert(int sizeParent);
		virtual int		getMinConstrainHorz();
		virtual int		getMinConstrainVert();
		virtual int		getMaxConstrainHorz();
		virtual int		getMaxConstrainVert();
		virtual bool	resizeTo(CRect& rcNewArea);

	private:
		CTabCtrl* m_pTab;
	};

	/**
	 * This class encapsulates a Subpane which is a Static
	 * it will use calls to AdjustRect to position it's
	 * childs
	 */
	class ETSGUI_EXT_CLASS PaneCtrl : public Pane
	{
		friend class ETSLayoutMgr;
	protected:
		/**
		 * Tell the pane in which direction it is positioned. A HORIZONTAL pane
		 * arranges it's subpanes from left to right, a VERTICAL from top to bottom
		 */
		PaneCtrl( CWnd* pCtrl, ETSLayoutMgr* pMgr, layOrientation orientation, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0, int sizeTopExtra = 0);
		PaneCtrl( UINT nID, ETSLayoutMgr* pMgr, layOrientation orientation, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0, int sizeTopExtra = 0 );

	public:

		virtual int		getConstrainHorz(int sizeParent);
		virtual int		getConstrainVert(int sizeParent);
		virtual int		getMinConstrainHorz();
		virtual int		getMinConstrainVert();
		virtual int		getMaxConstrainHorz();
		virtual int		getMaxConstrainVert();
		virtual bool	resizeTo(CRect& rcNewArea);

	private:
		HWND			m_hwndCtrl;
		int				m_sizeTopExtra;
	};




	ETSLayoutMgr(CWnd* pWnd)	{ m_pWnd = pWnd; m_sizeRootBorders = CSize(5,5); };
	virtual ~ETSLayoutMgr();

	virtual CRect GetRect() { CRect r; m_pWnd->GetClientRect(r); return r; };
	CWnd*	m_pWnd;
	CWnd*	GetWnd()		{ return m_pWnd; };
	void	setRootBorders(int cx, int cy)	{ m_sizeRootBorders = CSize(cx,cy); };

	/**
	 * Pass this for a pseudo Pane with no content
	 */
	static CWnd*	paneNull;

	/**
	 * Loads the current position and size from the registry using a supplied
	 * key. Will be loaded with AfxGetApp()->WriteProfileXXX(). You may
	 * specify a subfolder (e.g. Load( _T("MyDialog\\Layout") ); ). Will
	 * load the following keys:
	 *
	 * - lpstrRegKey+"SizeX";
	 * - lpstrRegKey+"SizeY";
	 * - lpstrRegKey+"PosX";
	 * - lpstrRegKey+"PosY";
	 *
	 * Is automatically called during OnActivate() if key specified in
	 * constructor.
	 */
	bool Load(LPCTSTR lpstrRegKey);

	/**
	 * Store the current position and size to the registry using a supplied
	 * key. Will be stored with AfxGetApp()->WriteProfileXXX(). You may
	 * specify a subfolder (e.g. Save( _T("MyDialog\\Layout") ); ). Will
	 * create the following keys:
	 *
	 * - lpstrRegKey+"SizeX";
	 * - lpstrRegKey+"SizeY";
	 * - lpstrRegKey+"PosX";
	 * - lpstrRegKey+"PosY";
	 *
	 * Is automatically called during DestroyWindow() if key specified in
	 * constructor.
	 */
	bool Save(LPCTSTR lpstrRegKey);

	/**
	 * Updates the layout after you specify the new
	 * layout
	 */
	virtual void UpdateLayout();
	virtual void UpdateLayout(CPane p) {
		if(m_RootPane.IsValid())
		{
			// free old root
			m_RootPane = 0;
		}
		m_RootPane = p;
		UpdateLayout();
	}

	/**
	 * Does the actual Layout, called from OnSize()
	 * Default implementation does nothing, use
	 * IMPLEMENT_LAYOUT in your derived class (see above)
	 */
	virtual void Layout(CRect& rcClient);


	/**
	 * Erasing only the these parts of the client area where
	 * there is no child window. Extra-code for group-boxes 
	 * included!
	 */
	void EraseBkgnd(CDC* pDC);

	/**
	 * Helperfunctions for the stream-interface. For usage see sample Application
	 * and/or documentation.
 	 */

	/**
	 * Create a new Pane. You may specify the resize
	 * mode for both directions. If you add modes for the secondary direction
	 * (i.e. *_VERT for a HORIZONTAL pane) then sizeSecondary is used as it's
	 * size. If you do not specify sizeSecondary and the mode is ABSOLUTE_VERT
	 * it will be computed as the maximum Height of all SubPanes (the same is
	 * true for VERTICAL panes and subpanes with *_HORZ)
	 */
	CPane pane( layOrientation orientation, layResizeMode modeResize = GREEDY, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0, int sizeSecondary = 0);

	/**
	 * Create one of the special control panes. Parameter are like pane(). For
	 * additional information see documentation
	 */
	CPane paneTab( CTabCtrl* pTab, layOrientation orientation, layResizeMode modeResize = GREEDY, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0, int sizeSecondary = 0);
	CPane paneCtrl( UINT nID, layOrientation orientation, layResizeMode modeResize = GREEDY, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0, int sizeTopExtra = 0, int sizeSecondary = 0);
	CPane paneCtrl( CWnd* pCtrl, layOrientation orientation, layResizeMode modeResize = GREEDY, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0, int sizeTopExtra = 0, int sizeSecondary = 0);

	/**
	 * Creates a new PaneItem for an Control. If sizeX or sizeY are 0
	 * and modeResize is ABSOLUTE will copy the current dimensions of
	 * the control to m_sizeX/Y. So the appearance does not change
	 * from the Dialog Editor. size*Min = -1 means: do not make smaller
	 * than in Dialog Template.
	 */
	CPaneBase item(UINT nID, layResizeMode modeResize = GREEDY, int sizeX =0, int sizeY =0, int sizeXMin =-1, int sizeYMin =-1);
	CPaneBase item(CWnd* pWnd, layResizeMode modeResize = GREEDY, int sizeX =0, int sizeY =0, int sizeXMin =-1, int sizeYMin =-1);


	/**
	 * Add a whitespace Item (paneNull) of variable size with
	 * a minimum size of 0
	 */
	CPaneBase itemGrowing(layOrientation orientation);

	/**
	 * Add a whitespace Item (paneNull) with fixed size
	 */
	CPaneBase itemFixed(layOrientation orientation, int sizePrimary);

	/**
	 * Add a whitespace Item (paneNull) of fixed size based on the
	 * current layout (as in the dialog template). Based on the layout
	 * of the pane vertical or horizontal spacing is considered
	 *
	 * First argument is the left (top) item for a HORIZONTAL (VERTICAL) pane
	 */
	CPaneBase itemSpaceBetween( layOrientation orientation, CWnd* pWndFirst, CWnd* pWndSecond );
	CPaneBase itemSpaceBetween( layOrientation orientation, UINT nIDFirst, UINT nIDSecond );

	/**
	 * Add a whitespace Item (paneNull) of fixed size based on the
	 * size of another item
	 */
	CPaneBase itemSpaceLike( layOrientation orientation, CWnd* pWnd );
	CPaneBase itemSpaceLike( layOrientation orientation, UINT nID );

protected:
	/**
	 * This holds the root pane. Fill in InitDialog()
	 */
	CPane m_RootPane;

	/**
 	 * Create a root pane
	 */
	CPane CreateRoot(layOrientation orientation, int sizeBorder = nDefaultBorder, int sizeExtraBorder = 0 )
	{
		if(m_RootPane.IsValid())
		{
			// free old root
			m_RootPane = 0;
		}
		m_RootPane = new Pane( this, orientation, sizeBorder, sizeExtraBorder);
		return m_RootPane;
	}

	/**
	 * Key in Registry where to store Size
	 */
	CString m_strRegStore;

	/**
	 * Borders around root
	 */
	CSize	m_sizeRootBorders;
};

inline ETSLayoutMgr::layResizeMode operator|(const ETSLayoutMgr::layResizeMode m1, 
											 const ETSLayoutMgr::layResizeMode m2)
	{ return (ETSLayoutMgr::layResizeMode)( (DWORD)m1|(DWORD)m2); }


/**
 * Base class for the Layout function. Derive your own class
 * from this or derive it from CDialog and modify _all_
 * references to CDialog to ETSLayoutDialog
 */
class ETSGUI_EXT_CLASS ETSLayoutDialog : public ETSLayout::CBaseDialog, protected ETSLayoutMgr
{
// Construction
public:
	ETSLayoutDialog(UINT nID, CWnd* pParent = NULL, LPCTSTR strName = NULL, bool bGripper = true);   // standard constructor

// Dialog Data
	//{{AFX_DATA(ETSLayoutDialog)
	//}}AFX_DATA


// Overrides
	// ClassWizard generated virtual function overrides
	//{{AFX_VIRTUAL(ETSLayoutDialog)
	//}}AFX_VIRTUAL

// Implementation
protected:
	// Generated message map functions
	//{{AFX_MSG(ETSLayoutDialog)
	afx_msg void OnSize(UINT nType, int cx, int cy);
	afx_msg void OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI);
	afx_msg BOOL OnEraseBkgnd(CDC* pDC);
	virtual BOOL OnInitDialog();
	afx_msg void OnDestroy();
	//}}AFX_MSG
	DECLARE_MESSAGE_MAP()

	virtual CRect GetRect();

	bool		m_bGripper;
	CStatusBar	m_StatusBar;
};


/**
 * Base class for the Layout function. Derive your own class
 * from this or derive it from CDialog and modify _all_
 * references to CFormView to ETSLayoutFormView
 */
class ETSGUI_EXT_CLASS ETSLayoutFormView : public ETSLayout::CBaseFormView, public ETSLayoutMgr
{
// Construction
	DECLARE_DYNAMIC(ETSLayoutFormView)
public:
	ETSLayoutFormView(UINT nID, LPCTSTR strName = NULL);   // standard constructor
	virtual ~ETSLayoutFormView();

//	virtual void UpdateLayout();


// Overrides
	// ClassWizard generated virtual function overrides
	//{{AFX_VIRTUAL(ETSLayoutDialog)
	//}}AFX_VIRTUAL

// Implementation
protected:

	// Generated message map functions
	//{{AFX_MSG(ETSLayoutDialog)
	afx_msg void OnSize(UINT nType, int cx, int cy);
	afx_msg BOOL OnEraseBkgnd(CDC* pDC);
	afx_msg void OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI);
	//}}AFX_MSG
	DECLARE_MESSAGE_MAP()
};


/**
 * Base class for the Layout function. Derive your own class
 * from this or derive it from CBCGDialogBar/CDialogBar and 
 * modify _all_  references to CBCGDialogBar/CDialogBar to 
 * ETSLayoutDialogBar
 */
class ETSGUI_EXT_CLASS ETSLayoutDialogBar : public ETSLayout::CBaseDialogBar, protected ETSLayoutMgr
{
// Construction
public:
#ifdef CS_HELP
	ETSLayoutDialogBar(UINT nID);
#else
	ETSLayoutDialogBar();
#endif


// Overrides
	// ClassWizard generated virtual function overrides
	//{{AFX_VIRTUAL(ETSLayoutDialogBar)
	virtual CSize CalcDynamicLayout(int nLength, DWORD dwMode);
	//}}AFX_VIRTUAL

	/**
	 * Override this to define Layout
	 */
	virtual BOOL Initialize() { return false; };
	virtual void UpdateLayout();

// Implementation
protected:
	// Generated message map functions
	//{{AFX_MSG(ETSLayoutDialogBar)
	afx_msg void OnSize(UINT nType, int cx, int cy);
	afx_msg void OnDestroy();
	afx_msg BOOL OnEraseBkgnd(CDC* pDC);
	//}}AFX_MSG
	LRESULT OnInitDialog(WPARAM, LPARAM);
	DECLARE_MESSAGE_MAP()

	virtual CRect GetRect();
	bool	m_bInitialized;
};



/**************************************************
 ** ! the code is only tested for modal sheets ! **
 **************************************************/


/**
 * Resizable PropertySheet. Use this class standalone
 * or as your base class (instead CProptertySheet)
 */
class ETSGUI_EXT_CLASS ETSLayoutPropertySheet : public CPropertySheet, protected ETSLayoutMgr
{
	DECLARE_DYNAMIC(ETSLayoutPropertySheet)

// Construction
public:
	ETSLayoutPropertySheet(UINT nIDCaption, CWnd *pParentWnd = NULL, UINT iSelectPage = 0, LPCTSTR strName=NULL, bool bGripper=true);
	ETSLayoutPropertySheet(LPCTSTR pszCaption, CWnd *pParentWnd = NULL, UINT iSelectPage = 0, LPCTSTR strName=NULL, bool bGripper=true);

// Operationen
public:
	void	SetAutoDestroy()		{ m_bAutoDestroy = true; }
	void	SetAutoDestroyPages()	{ m_bAutoDestroyPages = true; }
	void	ModelessWithButtons()	{ m_bModelessButtons = true; }
// Overrides
    virtual void AddMainArea(CPane paneRoot, CPaneBase itemTab);
    virtual void AddButtons(CPane paneBottom);
    
	// ClassWizard generated virtual function overrides
	//{{AFX_VIRTUAL(ETSLayoutPropertySheet)
	public:
	virtual BOOL OnInitDialog();
	virtual void PostNcDestroy();
	//}}AFX_VIRTUAL

// Implementation
public:
	virtual ~ETSLayoutPropertySheet();

	// Generated message map functions
protected:
	//{{AFX_MSG(ETSLayoutPropertySheet)
	afx_msg int OnCreate(LPCREATESTRUCT lpCreateStruct);
	afx_msg void OnSize(UINT nType, int cx, int cy);
	afx_msg void OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI);
	afx_msg void OnDestroy();
	afx_msg BOOL OnEraseBkgnd(CDC* pDC);
	//}}AFX_MSG
	DECLARE_MESSAGE_MAP()

	void Resize(int cx, int cy);

friend class ETSLayoutPropertyPage;

	void		Init(LPCTSTR strName, bool bGripper);
	CRect		m_rcStart;
	CRect		m_rcPage;
	bool		m_bGripper;
	CStatusBar	m_StatusBar;
	CPaneBase	m_ItemTab;
	bool		m_bAutoDestroy;
	bool		m_bAutoDestroyPages;
	bool		m_bModelessButtons;
};

/**
 * Base class for the Layout function. Derive your own class
 * from this or derive it from CPropertyPage and 
 * modify _all_  references to CPropertyPage to 
 * ETSLayoutPropertyPage
 */
class ETSGUI_EXT_CLASS ETSLayoutPropertyPage : public ETSLayout::CBasePropertyPage, protected ETSLayoutMgr
{
friend class ETSLayoutPropertySheet;

	DECLARE_DYNCREATE(ETSLayoutPropertyPage)

// Konstruktion
public:
	ETSLayoutPropertyPage( );
	ETSLayoutPropertyPage( UINT nIDTemplate, UINT nIDCaption = 0 );
	ETSLayoutPropertyPage( LPCTSTR lpszTemplateName, UINT nIDCaption = 0 );

	~ETSLayoutPropertyPage();


// Overrides
	// ClassWizard generated virtual function overrides
	//{{AFX_VIRTUAL(ETSLayoutPropertyPage)
	public:
	virtual BOOL OnSetActive();
	//}}AFX_VIRTUAL

// Implementation
protected:
	// Generated message map functions
	//{{AFX_MSG(ETSLayoutPropertyPage)
	afx_msg void OnSize(UINT nType, int cx, int cy);
	afx_msg void OnGetMinMaxInfo(MINMAXINFO FAR* lpMMI);
	virtual BOOL OnInitDialog();
	afx_msg BOOL OnEraseBkgnd(CDC* pDC);
	afx_msg void OnWindowPosChanging( WINDOWPOS* lpwndpos );
	afx_msg void OnDestroy();
	afx_msg void OnWindowPosChanged(WINDOWPOS FAR* lpwndpos);
	//}}AFX_MSG
	DECLARE_MESSAGE_MAP()

	virtual CRect GetRect();
	bool m_bLockMove;
	bool m_bResetBuddyOnNextTimeVisible;
};



//{{AFX_INSERT_LOCATION}}
// Microsoft Developer Studio will insert additional declarations immediately before the previous line.

#endif // !defined(ETS_LAYOUTMGR_INCLUDED_)
