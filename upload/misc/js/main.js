/**
 * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
 *
 * SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */
if(typeof deconcept=="undefined"){var deconcept=new Object();}if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}if(typeof deconcept.SWFObjectUtil=="undefined"){deconcept.SWFObjectUtil=new Object();}deconcept.SWFObject=function(_1,id,w,h,_5,c,_7,_8,_9,_a){if(!document.getElementById){return;}this.DETECT_KEY=_a?_a:"detectflash";this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);this.params=new Object();this.variables=new Object();this.attributes=new Array();if(_1){this.setAttribute("swf",_1);}if(id){this.setAttribute("id",id);}if(w){this.setAttribute("width",w);}if(h){this.setAttribute("height",h);}if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}this.installedVer=deconcept.SWFObjectUtil.getPlayerVersion();if(!window.opera&&document.all&&this.installedVer.major>7){deconcept.SWFObject.doPrepUnload=true;}if(c){this.addParam("bgcolor",c);}var q=_7?_7:"high";this.addParam("quality",q);this.setAttribute("useExpressInstall",false);this.setAttribute("doExpressInstall",false);var _c=(_8)?_8:window.location;this.setAttribute("xiRedirectUrl",_c);this.setAttribute("redirectUrl","");if(_9){this.setAttribute("redirectUrl",_9);}};deconcept.SWFObject.prototype={useExpressInstall:function(_d){this.xiSWFPath=!_d?"expressinstall.swf":_d;this.setAttribute("useExpressInstall",true);},setAttribute:function(_e,_f){this.attributes[_e]=_f;},getAttribute:function(_10){return this.attributes[_10];},addParam:function(_11,_12){this.params[_11]=_12;},getParams:function(){return this.params;},addVariable:function(_13,_14){this.variables[_13]=_14;},getVariable:function(_15){return this.variables[_15];},getVariables:function(){return this.variables;},getVariablePairs:function(){var _16=new Array();var key;var _18=this.getVariables();for(key in _18){_16[_16.length]=key+"="+_18[key];}return _16;},getSWFHTML:function(){var _19="";if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","PlugIn");this.setAttribute("swf",this.xiSWFPath);}_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\"";_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";var _1a=this.getParams();for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}var _1c=this.getVariablePairs().join("&");if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");this.setAttribute("swf",this.xiSWFPath);}_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\">";_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";var _1d=this.getParams();for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}var _1f=this.getVariablePairs().join("&");if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}return _19;},write:function(_20){if(this.getAttribute("useExpressInstall")){var _21=new deconcept.PlayerVersion([6,0,65]);if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){this.setAttribute("doExpressInstall",true);this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));document.title=document.title.slice(0,47)+" - Flash Player Installation";this.addVariable("MMdoctitle",document.title);}}if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){var n=(typeof _20=="string")?document.getElementById(_20):_20;n.innerHTML=this.getSWFHTML();return true;}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}return false;}};deconcept.SWFObjectUtil.getPlayerVer
sion=function(){var _23=new deconcept.PlayerVersion([0,0,0]);if(navigator.plugins&&navigator.mimeTypes.length){var x=navigator.plugins["Shockwave Flash"];if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}}else{if(navigator.userAgent&&navigator.userAgent.indexOf("Windows CE")>=0){var axo=1;var _26=3;while(axo){try{_26++;axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+_26);_23=new deconcept.PlayerVersion([_26,0,0]);}catch(e){axo=null;}}}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}}return _23;};deconcept.PlayerVersion=function(_29){this.major=_29[0]!=null?parseInt(_29[0]):0;this.minor=_29[1]!=null?parseInt(_29[1]):0;this.rev=_29[2]!=null?parseInt(_29[2]):0;};deconcept.PlayerVersion.prototype.versionIsValid=function(fv){if(this.major<fv.major){return false;}if(this.major>fv.major){return true;}if(this.minor<fv.minor){return false;}if(this.minor>fv.minor){return true;}if(this.rev<fv.rev){return false;}return true;};deconcept.util={getRequestParameter:function(_2b){var q=document.location.search||document.location.hash;if(_2b==null){return q;}if(q){var _2d=q.substring(1).split("&");for(var i=0;i<_2d.length;i++){if(_2d[i].substring(0,_2d[i].indexOf("="))==_2b){return _2d[i].substring((_2d[i].indexOf("=")+1));}}}return "";}};deconcept.SWFObjectUtil.cleanupSWFs=function(){var _2f=document.getElementsByTagName("OBJECT");for(var i=_2f.length-1;i>=0;i--){_2f[i].style.display="none";for(var x in _2f[i]){if(typeof _2f[i][x]=="function"){_2f[i][x]=function(){};}}}};if(deconcept.SWFObject.doPrepUnload){if(!deconcept.unloadSet){deconcept.SWFObjectUtil.prepUnload=function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};window.attachEvent("onunload",deconcept.SWFObjectUtil.cleanupSWFs);};window.attachEvent("onbeforeunload",deconcept.SWFObjectUtil.prepUnload);deconcept.unloadSet=true;}}if(!document.getElementById&&document.all){document.getElementById=function(id){return document.all[id];};}var getQueryParamValue=deconcept.util.getRequestParameter;var FlashObject=deconcept.SWFObject;var SWFObject=deconcept.SWFObject;

// prototype $
function $p() {
  var elements = new Array();

  for (var i = 0; i < arguments.length; i++) {
    var element = arguments[i];
    if (typeof element == 'string')
      element = document.getElementById(element);

    if (arguments.length == 1)
      return element;

    elements.push(element);
  }

  return elements;
}

// from http://www.dustindiaz.com/rock-solid-addevent/
function addEvent( obj, type, fn ) {
	if (obj.addEventListener) {
		obj.addEventListener( type, fn, false );
		EventCache.add(obj, type, fn);
	}
	else if (obj.attachEvent) {
		obj["e"+type+fn] = fn;
		obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
		obj.attachEvent( "on"+type, obj[type+fn] );
		EventCache.add(obj, type, fn);
	}
	else {
		obj["on"+type] = obj["e"+type+fn];
	}
}

var EventCache = function(){
	var listEvents = [];
	return {
		listEvents : listEvents,
		add : function(node, sEventName, fHandler){
			listEvents.push(arguments);
		},
		flush : function(){
			var i, item;
			for(i = listEvents.length - 1; i >= 0; i = i - 1){
				item = listEvents[i];
				if(item[0].removeEventListener){
					item[0].removeEventListener(item[1], item[2], item[3]);
				};
				if(item[1].substring(0, 2) != "on"){
					item[1] = "on" + item[1];
				};
				if(item[0].detachEvent){
					item[0].detachEvent(item[1], item[2]);
				};
				item[0][item[1]] = null;
			};
		}
	};
}();
if (document.all) { addEvent(window,'unload',EventCache.flush); }

function imgFit (img, maxW)
{
	img.title  = 'Размеры изображения: '+img.width+' x '+img.height;
	if (typeof(img.naturalHeight) == 'undefined') {
		img.naturalHeight = img.height;
		img.naturalWidth  = img.width;
	}
	if (img.width > maxW) {
		img.height = Math.round((maxW/img.width)*img.height);
		img.width  = maxW;
		img.title  = 'Нажмите на изображение, чтобы посмотреть его в полный размер';
		img.style.cursor = 'move';
		return false;
	}
	else if (img.width == maxW && img.width < img.naturalWidth) {
		img.height = img.naturalHeight;
		img.width  = img.naturalWidth;
		img.title  = 'Размеры изображения: '+img.naturalWidth+' x '+img.naturalHeight;
		return false;
	}
	else {
		return true;
	}
}

function toggle_block (id)
{
	var el = document.getElementById(id);
	el.style.display = (el.style.display == 'none') ? '' : 'none';
}

function toggle_disabled (id, val)
{
	document.getElementById(id).disabled = (val) ? 0 : 1;
}

function rand (min, max)
{
	return min + Math.floor((max - min + 1) * Math.random());
}

//
// Cookie functions [based on ???]
//
/**
 * name       Name of the cookie
 * value      Value of the cookie
 * [days]     Number of days to remain active (default: end of current session)
 * [path]     Path where the cookie is valid (default: path of calling document)
 * [domain]   Domain where the cookie is valid
 *            (default: domain of calling document)
 * [secure]   Boolean value indicating if the cookie transmission requires a
 *            secure transmission
 */
function setCookie (name, value, days, path, domain, secure)
{
	if (days != 'SESSION') {
		var date = new Date();
		days = days || 365;
		date.setTime(date.getTime() + days*24*60*60*1000);
		var expires = date.toGMTString();
	} else {
		var expires = '';
	}

	document.cookie =
		name +'='+ escape(value)
	+	((expires) ? '; expires='+ expires : '')
	+	((path) ? '; path='+ path : ((cookiePath) ? '; path='+ cookiePath : ''))
	+	((domain) ? '; domain='+ domain : ((cookieDomain) ? '; domain='+ cookieDomain : ''))
	+	((secure) ? '; secure' : ((cookieSecure) ? '; secure' : ''));
}

/**
 * Returns a string containing value of specified cookie,
 *   or null if cookie does not exist.
 */
function getCookie (name)
{
	var c, RE = new RegExp('(^|;)\\s*'+ name +'\\s*=\\s*([^\\s;]+)', 'g');
	return (c = RE.exec(document.cookie)) ? c[2] : null;
}

/**
 * name      name of the cookie
 * [path]    path of the cookie (must be same as path used to create cookie)
 * [domain]  domain of the cookie (must be same as domain used to create cookie)
 */
function deleteCookie (name, path, domain)
{
	setCookie(name, '', -1, path, domain);
}

// Simple Javascript Browser/OS detection (based on "Harald Hope, Tapio Markula, http://techpatterns.com ver 2.0.1")
var ua = navigator.userAgent;

var os_win = ( navigator.appVersion.indexOf( 'Win' ) != -1 );
var os_mac = ( navigator.appVersion.indexOf( 'Mac' ) != -1 );
var os_lin = ( ua.indexOf( 'Linux' ) != -1 );

var is_opera = ( ua.indexOf( 'Opera' ) != -1 );
var is_konq  = ( ua.indexOf( 'Konqueror' ) != -1 );
var is_saf   = ( ua.indexOf( 'Safari' ) != -1 );
var is_moz   = ( ua.indexOf( 'Gecko' ) != -1 && !is_saf && !is_konq);
var is_ie    = ( document.all && !is_opera );
var is_ie4   = ( is_ie && !document.getElementById );

// ie5x tests only for functionality
// Opera will register true in this test if set to identify as IE 5
var is_ie5x    = ( document.all && document.getElementById );
var os_ie5mac  = ( os_mac && is_ie5x );
var os_ie5xwin = ( os_win && is_ie5x );

// Copy text to clipboard. Originally got from decompiled `php_manual_en.chm`.
function ie_copyTextToClipboard (fromNode)
{
	var txt = document.body.createTextRange();
	txt.moveToElementText(fromNode);
	return txt.execCommand("Copy");
}

//
// Menus
//
var Menu = {
	hideSpeed          : 'fast',
	offsetCorrection_X : -4,
	offsetCorrection_Y : 2,
	iframeFix          : false,

	activeMenuId       : null,  //  currently opened menu (from previous click)
	clickedMenuId      : null,  //  menu to show up
	$root              : null,  //  root element for menu with "href = '#clickedMenuId'"
	$menu              : null,  //  clicked menu
	positioningType    : null,  //  reserved
	outsideClickWatch  : false, //  prevent multiple $(document).click binding

	clicked: function($root) {
		$root.blur();
		this.clickedMenuId = this.getMenuId($root);
		this.$menu = $(this.clickedMenuId);
		this.$root = $root;
		this.toggle();
	},

	hovered: function($root) {
		if (this.activeMenuId && this.activeMenuId !== this.getMenuId($root)) {
			this.clicked($root);
		}
	},

	unhovered: function($root) {
	},

	getMenuId: function($el) {
		var href = $el.attr('href');
		return href.substr(href.indexOf('#'));
	},

	setLocation: function() {
		var CSS = this.$root.offset();
		CSS.top  += this.$root.height() + this.offsetCorrection_Y;
		var curTop = parseInt(CSS.top);
		var tCorner = $(document).scrollTop() + $(window).height() - 20;
		var maxVisibleTop = Math.min(curTop, Math.max(0, tCorner - this.$menu.height()));
		if (curTop != maxVisibleTop) {
			CSS.top = maxVisibleTop;
		}
		CSS.left += this.offsetCorrection_X;
		var curLeft = parseInt(CSS.left);
		var rCorner = $(document).scrollLeft() + $(window).width() - 6;
		var maxVisibleLeft = Math.min(curLeft, Math.max(0, rCorner - this.$menu.width()));
		if (curLeft != maxVisibleLeft) {
			CSS.left = maxVisibleLeft;
		}
		this.$menu.css(CSS);
		if (this.iframeFix) {
			$('iframe.ie-fix-select-overlap', $menu).css({ width: $menu.width(), height: $menu.height() });
		}
	},

	fixLocation: function() {
		var $menu = this.$menu;
		var curLeft = parseInt($menu.css('left'));
		var rCorner = $(document).scrollLeft() + $(window).width() - 6;
		var maxVisibleLeft = Math.min(curLeft, Math.max(0, rCorner - $menu.width()));
		if (curLeft != maxVisibleLeft) {
			$menu.css('left', maxVisibleLeft);
		}
		var curTop = parseInt($menu.css('top'));
		var tCorner = $(document).scrollTop() + $(window).height() - 20;
		var maxVisibleTop = Math.min(curTop, Math.max(0, tCorner - $menu.height()));
		if (curTop != maxVisibleTop) {
			$menu.css('top', maxVisibleTop);
		}
		if (this.iframeFix) {
			$('iframe.ie-fix-select-overlap', $menu).css({ width: $menu.width(), height: $menu.height() });
		}
	},

	toggle: function() {
		if (this.activeMenuId && this.activeMenuId !== this.clickedMenuId) {
			$(this.activeMenuId).hide(this.hideSpeed);
		}
		// toggle clicked menu
		if (this.$menu.is(':visible')) {
			this.$menu.hide(this.hideSpeed);
			this.activeMenuId = null;
		}	else {
			this.showClickedMenu();
			if (!this.outsideClickWatch) {
				$(document).one('mousedown', function(e){ Menu.hideClickWatcher(e); });
				this.outsideClickWatch = true;
			}
		}
	},

	showClickedMenu: function() {
		this.setLocation();
		this.$menu.css({display: 'block'});
		// this.fixLocation();
		this.activeMenuId = this.clickedMenuId;
	},

	// hide if clicked outside of menu
	hideClickWatcher: function(e) {
		this.outsideClickWatch = false;
		this.hide(e);
	},

	hide: function(e) {
		if (this.$menu) {
			this.$menu.hide(this.hideSpeed);
		}
		this.activeMenuId = this.clickedMenuId = this.$menu = null;
	}
};

$(document).ready(function(){
	// Menus
	$('body').append($('div.menu-sub'));
	$('a.menu-root')
		.click(
			function(e){ e.preventDefault(); Menu.clicked($(this)); return false; })
		.hover(
			function(){ Menu.hovered($(this)); return false; },
			function(){ Menu.unhovered($(this)); return false; }
		)
	;
	$('div.menu-sub')
		.mousedown(function(e){ e.stopPropagation(); })
		.find('a')
			.click(function(e){ Menu.hide(e); })
	;
	// Input hints
	$('input')
		.filter('.hint').one('focus', function(){
			$(this).val('').removeClass('hint');
		})
		.end()
		.filter('.error').one('focus', function(){
			$(this).removeClass('error');
		})
	;
});

//
// Ajax
//
function Ajax(handlerURL, requestType, dataType) {
	this.url      = handlerURL;
	this.type     = requestType;
	this.dataType = dataType;
	this.errors   = { };
}

Ajax.prototype = {
	init       : {},  // init functions (run before submit, after triggering ajax event)
	callback   : {},  // callback functions (response handlers)
	state      : {},  // current action state
	request    : {},  // request data
	params     : {},  // action params, format: ajax.params[ElementID] = { param: "val" ... }
	form_token : '',

	exec: function(request) {
		this.request[request.action] = request;
		request['form_token'] = this.form_token;
		$.ajax({
			url      : this.url,
			type     : this.type,
			dataType : this.dataType,
			data     : request,
			success  : ajax.success,
			error    : ajax.error
		});
	},

	success: function(response) {
		var action = response.action;
		// raw_output normally might contain only error messages (if php.ini.display_errors == 1)
		if (response.raw_output) {
			$('body').prepend(response.raw_output);
		}
		if (response.sql_log) {
			$('#sqlLog').prepend(response.sql_log +'<hr />');
			fixSqlLog();
		}
		if (response.update_ids) {
			for (id in response.update_ids) {
				$('#'+id).html( response.update_ids[id] );
			}
		}
		if (response.prompt_password) {
			var user_password = prompt('Для доступа к данной функции, пожалуйста, введите свой пароль', '');
			if (user_password) {
				var req = ajax.request[action];
				req.user_password = user_password;
				ajax.exec(req);
			}
			else {
				ajax.clearActionState(action);
				ajax.showErrorMsg('Введен неверный пароль');
			}
		}
		else if (response.prompt_confirm) {
			if (window.confirm(response.confirm_msg)) {
				var req = ajax.request[action];
				req.confirmed = 1;
				ajax.exec(req);
			}
			else {
				ajax.clearActionState(action);
			}
		}
		else if (response.error_code) {
			ajax.showErrorMsg(response.error_msg);
			$('.loading-1').removeClass('loading-1').html('error');
		}
		else {
			ajax.callback[action](response);
			ajax.clearActionState(action);
		}
	},

	error: function(xml, desc) {
	},

	clearActionState: function(action){
		ajax.state[action] = ajax.request[action] = '';
	},

	showErrorMsg: function(msg){
		alert(msg);
	},

	callInitFn: function(event) {
		event.stopPropagation();
		var params = ajax.params[$(this).attr('id')];
		var action = params.action;
		if (ajax.state[action] == 'readyToSubmit' || ajax.state[action] == 'error') {
			return false;
		} else {
			ajax.state[action] = 'readyToSubmit';
		}
		ajax.init[action](params);
	},

	setStatusBoxPosition: function($el) {
		var newTop = $(document).scrollTop();
		var rCorner = $(document).scrollLeft() + $(window).width() - ($.browser.opera ? 14 : 8);
		var newLeft = Math.max(0, rCorner - $el.width());
		$el.css({ top: newTop, left: newLeft });
	},

	makeEditable: function(rootElementId, editableType) {
		var $root = $('#'+rootElementId);
		var $editable = $('.editable', $root);
		var inputsHtml = $('#editable-tpl-'+editableType).html();
		$editable.hide().after(inputsHtml);
		var $inputs = $('.editable-inputs', $root);
		if (editableType == 'input' || editableType == 'textarea') {
			$('.editable-value', $inputs).val( $.trim($editable.text()) );
		}
		$('input.editable-submit', $inputs).click(function(){
			var params = ajax.params[rootElementId];
			var $val = $('.editable-value', '#'+rootElementId);
			params.value = ($val.size() == 1) ? $val.val() : $val.filter(':checked').val();
			params.submit = true;
			ajax.init[params.action](params);
		});
		$('input.editable-cancel', $inputs).click(function(){
			ajax.restoreEditable(rootElementId);
		});
		$inputs.show().find('.editable-value').focus();
		$root.removeClass('editable-container');
	},

	restoreEditable: function(rootElementId, newValue) {
		var $root = $('#'+rootElementId);
		var $editable = $('.editable', $root);
		$('.editable-inputs', $root).remove();
		if (newValue) {
			$editable.text(newValue);
		}
		$editable.show();
		ajax.clearActionState( ajax.params[rootElementId].action );
		ajax.params[rootElementId].submit = false;
		$root.addClass('editable-container');
	}
};

$(document).ready(function(){
	// Setup ajax-loading box
	$("#ajax-loading").ajaxStart(function(){
		$("#ajax-error").hide();
		$(this).show();
		ajax.setStatusBoxPosition($(this));
	});
	$("#ajax-loading").ajaxStop(function(){ $(this).hide(); });

	// Setup ajax-error box
	$("#ajax-error").ajaxError(function(req, xml){
		var status = xml.status;
		var text = xml.statusText;
		if (status == 200) {
			status = '';
			text = 'неверный формат данных';
		}
		$(this).html(
			"Ошибка в: <i>"+ ajax.url +"</i><br /><b>"+ status +" "+ text +"</b>"
		).show();
		ajax.setStatusBoxPosition($(this));
	});

	// Bind ajax events
	$('var.ajax-params').each(function(){
		var params = $.evalJSON( $(this).html() );
		params.event = params.event || 'dblclick';
		ajax.params[params.id] = params;
		$("#"+params.id).bind(params.event, ajax.callInitFn);
		if (params.event == 'click' || params.event == 'dblclick') {
			$("#"+params.id).addClass('editable-container');
		}
	});
});

/**
  * Autocomplete password
  **/
	var array_for_rand_pass = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
	var array_rand = function (array) {
		var array_length = array.length;
		var result = Math.random() * array_length;
		return Math.floor(result);

	};

	var autocomplete = function (noCenter) {
		var string_result = ""; // Empty string
		for (var i = 1; i <= 8; i++) {
			string_result += array_for_rand_pass[array_rand(array_for_rand_pass)];
		}

		var _popup_left = (Math.ceil(window.screen.availWidth / 2) - 150);
		var _popup_top = (Math.ceil(window.screen.availHeight / 2) - 50);

		if (!noCenter) {
			$("div#autocomplete_popup").css({
				left:_popup_left + "px",
				top:_popup_top + "px"
			}).show(1000);
		} else {
			$("div#autocomplete_popup").show(1000);
		}

		$("input#pass, input#pass_confirm, div#autocomplete_popup input").each(function () {
			$(this).val(string_result);
		});
	};
	
$(document).ready(function () {
	$("span#autocomplete").click(function() {
		autocomplete();
	});

	// перемещение окна
	var _X, _Y;
	var _bMoveble = false;

	$("div#autocomplete_popup div.title").mousedown(function (event) {
		_bMoveble = true;
		_X = event.clientX;
		_Y = event.clientY;
	});

	$("div#autocomplete_popup div.title").mousemove(function (event) {
		var jFrame = $("div#autocomplete_popup");
		var jFLeft = parseInt(jFrame.css("left"));
		var jFTop = parseInt(jFrame.css("top"));

		if (_bMoveble) {
			if (event.clientX < _X) {
				jFrame.css("left", jFLeft - (_X - event.clientX) + "px");
			} else {
				jFrame.css("left", (jFLeft + (event.clientX - _X)) + "px");
			}

			if (event.clientY < _Y) {
				jFrame.css("top", jFTop - (_Y - event.clientY) + "px");
			} else {
				jFrame.css("top", (jFTop + (event.clientY - _Y)) + "px");
			}

			_X = event.clientX;
			_Y = event.clientY;
		}
	});

	$("div#autocomplete_popup div.title").mouseup(function () {
		_bMoveble = false;
	}).mouseout(function () {
		_bMoveble = false;
	});
});
