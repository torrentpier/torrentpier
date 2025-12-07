<!DOCTYPE html>
<html dir="{USER_LANG_DIRECTION}" lang="{USER_LANG}">
<head>
<meta charset="{CONTENT_ENCODING}">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="viewport" content="initial-scale=1.0">
<meta name="generator" content="{#APP_NAME#}">
<!-- IF META_DESCRIPTION -->
<meta name="description" content="{META_DESCRIPTION}"/>
<meta property="og:description" content="{META_DESCRIPTION}">
<meta property="twitter:description" content="{META_DESCRIPTION}">
<!-- ENDIF -->
<title><!-- IF HAVE_NEW_PM -->({HAVE_NEW_PM}) <!-- ENDIF --><!-- IF PAGE_TITLE -->{PAGE_TITLE} :: {SITENAME}<!-- ELSE -->{SITENAME}<!-- ENDIF --></title>
<meta name="apple-mobile-web-app-title" content="{SITENAME}">
<meta name="application-name" content="{SITENAME}"/>
<meta property="og:site_name" content="{SITENAME}">
<meta property="og:image" content="{SITE_URL}styles/images/logo/logo.png" />
<meta property="og:type" content="website">
<meta property="twitter:image" content="{SITE_URL}styles/images/logo/logo.png">
<meta property="og:title" content="<!-- IF PAGE_TITLE -->{PAGE_TITLE} :: {SITENAME}<!-- ELSE -->{SITENAME}<!-- ENDIF -->">
<meta property="twitter:title" content="<!-- IF PAGE_TITLE -->{PAGE_TITLE} :: {SITENAME}<!-- ELSE -->{SITENAME}<!-- ENDIF -->">
<meta property="twitter:card" content="summary">
<!-- IF not ALLOW_ROBOTS -->
<meta name="robots" content="noindex">
<!-- ENDIF -->
<!-- IF META_PREV_PAGE -->
<link rel="prev" href="{META_PREV_PAGE}">
<!-- ENDIF / META_PREV_PAGE -->
<!-- IF META_NEXT_PAGE -->
<link rel="next" href="{META_NEXT_PAGE}">
<!-- ENDIF / META_NEXT_PAGE -->
{META}
<link rel="stylesheet" href="{STYLESHEET}?v={{ config('css_ver') }}" type="text/css">
<link rel="stylesheet" href="{SITE_URL}styles/templates/default/css/dark-mode.css?v={{ config('css_ver') }}" type="text/css">
<link rel="shortcut icon" href="{SITE_URL}favicon.png" type="image/x-icon">

<script type="text/javascript" src="{SITE_URL}styles/js/libs/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/jquery-migrate.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/oldbrowserdetector.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/clipboard.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/printThis.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/legacy.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/main.js?v={{ config('js_ver') }}"></script>

<!-- IF INCLUDE_BBCODE_JS -->
<script type="text/javascript" src="{SITE_URL}styles/js/bbcode.js?v={{ config('js_ver') }}"></script>
<script type="text/javascript">
	window.BB = {};
	window.encURL = encodeURIComponent;
</script>
<script type="text/javascript">
var bb_url = '{SITE_URL}';
var bbl    = { "code": "{L_CODE}", "wrote": "{L_WROTE}", "quote": "{L_QUOTE}", "quoted_post": "{L_GOTO_QUOTED_POST}", "loading": "{L_LOADING}", "spoiler_head": "{L_SPOILER_HEAD}", "spoiler_close": "{L_SPOILER_CLOSE}", "links_are": "{L_LINKS_ARE_FORBIDDEN}", "scr_rules": "{L_SCREENSHOTS_RULES}", "play_on": "{L_PLAY_ON_CURPAGE}" };

var postImg_MaxWidth = screen.width - {{ POST_IMG_WIDTH_DECR_JS }};
var postImgAligned_MaxWidth = Math.round(screen.width/3);
var attachImg_MaxWidth = screen.width - {{ ATTACH_IMG_WIDTH_DECR_JS }};
var ExternalLinks_InNewWindow = '{EXT_LINK_NEW_WIN}';
var hidePostImg = false;
</script>
<!-- ENDIF / INCLUDE_BBCODE_JS -->

<script type="text/javascript">
var BB_ROOT      = "{#BB_ROOT#}";
var cookieDomain = "{{ config('cookie_domain') }}";
var cookiePath   = "{{ config('script_path') }}";
var cookiePrefix = "{{ config('cookie_prefix') }}";
var cookieSecure = "{{ config('cookie_secure') }}";
var cookieSameSite = "{{ config('cookie_same_site') }}";
var LOGGED_IN    = {LOGGED_IN};
var IWP          = 'HEIGHT=510,WIDTH=780,resizable=yes';
var IWP_US       = 'HEIGHT=250,WIDTH=400,resizable=yes';
var IWP_SM       = 'HEIGHT=420,WIDTH=470,resizable=yes,scrollbars=yes';

var user = {
	opt_js: {USER_OPTIONS_JS},

	set: function (opt, val, days, reload) {
		this.opt_js[opt] = val;
		setCookie('opt_js', $.toJSON(this.opt_js), days);
		if (reload) {
			window.location.reload();
		}
	}
};

<!-- IF JUMPBOX -->
$(document).ready(function () {
    $("div.jumpbox").html('\
		<span id="jumpbox-container"> \
		<select id="jumpbox"> \
			<option id="jumpbox-title" value="-1">&nbsp;&raquo;&raquo; {L_JUMPBOX_TITLE} &nbsp;</option> \
		</select> \
		</span> \
		<input id="jumpbox-submit" type="button" class="lite" value="{L_GO}" /> \
	');
    $('#jumpbox-container').one('click', function () {
        $('#jumpbox-title').html('&nbsp;&nbsp; {L_LOADING} ... &nbsp;');
        $(this).html('{JUMPBOX}');
        $('#jumpbox-submit').click(function () {
            window.location.href = '{FORUM_URL}' + $('#jumpbox').val();
        });
    });
});
<!-- ENDIF -->

var ajax = new Ajax('{SITE_URL}{{ config('ajax_url') }}', 'POST', 'json');

function getElText (e)
{
	var t = '';
	if (e.textContent !== undefined) { t = e.textContent; } else if (e.innerText !== undefined) { t = e.innerText; } else { t = jQuery(e).text(); }
	return t;
}
function escHTML (txt)
{
	return txt.replace(/</g, '&lt;');
}
<!-- IF USE_TABLESORTER -->
$(document).ready(function() {
	$('.tablesorter').tablesorter();
});
<!-- ENDIF -->

function cfm (txt)
{
	return window.confirm(txt);
}
function post2url (url, params) {
	params = params || {};
	var f = document.createElement('form');
	f.setAttribute('method', 'post');
	f.setAttribute('action', url);
	params['form_token'] = '{FORM_TOKEN}';
	for (var k in params) {
		var h = document.createElement('input');
		h.setAttribute('type', 'hidden');
		h.setAttribute('name', k);
		h.setAttribute('value', params[k]);
		f.appendChild(h);
	}
	document.body.appendChild(f);
	f.submit();
	return false;
}
</script>

<!--[if gte IE 7]><style>
input[type="checkbox"] { margin-bottom: -1px; }
</style><![endif]-->

<!--[if IE]><style>
.post-hr { margin: 2px auto; }
.fieldsets div > p { margin-bottom: 0; }
</style><![endif]-->

<style>
	.menu-sub, #ajax-loading, #ajax-error, var.ajax-params, .sp-title, .q-post { display: none; }
</style>
</head>

<body>
<!-- IF PAGINATION -->
<div class="menu-sub" id="pg-jump">
	<table cellspacing="1" cellpadding="4">
	<tr><th>{L_GO_TO_PAGE}</th></tr>
	<tr><td>
		<form method="get" onsubmit="return go_to_page();">
			<input id="pg-page" type="text" size="5" maxlength="4" />
			<input type="submit" value="{L_GO}"/>
		</form>
	</td></tr>
	</table>
</div>
<script type="text/javascript">
function go_to_page ()
{
	var page_num = (parseInt( $('#pg-page').val() ) > 1) ? $('#pg-page').val() : 1;
	var pg_start = (page_num - 1) * {PG_PER_PAGE};
	var base_url = '{PG_BASE_URL}';
	var separator = base_url.indexOf('?') === -1 ? '?' : '&';
	window.location = base_url + separator + 'start=' + pg_start;
	return false;
}
</script>
<!-- ENDIF -->

<div id="ajax-loading"></div><div id="ajax-error"></div>
<div id="preload" style="position: absolute; overflow: hidden; top: 0; left: 0; height: 1px; width: 1px;"></div>

<div id="body_container">

<!--******************-->
<!-- IF SIMPLE_HEADER -->
<!--==================-->

<style>body { background: #E3E3E3; min-width: 10px; }</style>

<!--=================-->
<!-- ELSEIF IN_ADMIN -->
<!--=================-->

<!--======-->
<!-- ELSE -->
<!--======-->

<!--page_container-->
<div id="page_container">
<a name="top"></a>

<!--page_header-->
<div id="page_header">

<div id="old-browser-warn" style="background: #FFF227; padding: 8px 0 10px; text-align: center; font-size: 14px; display: none; ">
	<b>{L_OLD_BROWSER}</b>
</div>
<script>
  var Detector = new oldBrowserDetector(null, function () {
    $('#old-browser-warn').show();
  });

  Detector.detect();
</script>

<!--main_nav-->
<div id="main-nav"<!-- IF HAVE_NEW_PM --> class="new-pm"<!-- ENDIF -->>
	<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td class="nowrap">
			<a href="{U_INDEX}"><b>{L_HOME}</b></a><span style="color:#CDCDCD;">|</span>
			<a href="{U_TRACKER}"><b>{L_TRACKER}</b></a><span style="color:#CDCDCD;">|</span>
			<a href="{U_SEARCH}"><b>{L_SEARCH}</b></a><span style="color:#CDCDCD;">|</span>
			<a href="{U_TERMS}"><b style="color: #993300;">{L_TERMS}</b></a><span style="color:#CDCDCD;">|</span>
			<a href="{U_GROUPS}"><b>{L_USERGROUPS}</b></a><span style="color:#CDCDCD;">|</span>
			<a href="{U_MEMBERLIST}"><b>{L_MEMBERLIST}</b></a>
		</td>
		<td class="nowrap tRight">
			<!-- IF LOGGED_IN -->
				<!-- IF HAVE_NEW_PM || HAVE_UNREAD_PM -->
					<a href="{U_READ_PM}" class="new-pm-link"><b>{L_PRIVATE_MESSAGES}: {PM_INFO}</b></a>
				<!-- ELSE -->
					<a href="{U_PRIVATEMSGS}"><b>{L_PRIVATE_MESSAGES}: {PM_INFO}</b></a>
				<!-- ENDIF -->
			<!-- ENDIF -->
		</td>
	</tr>
	</table>
</div>
<!--/main_nav-->

<!--logo-->
<div id="logo">
	<!--<h1>{SITENAME}</h1>
	<h6>{SITE_DESCRIPTION}</h6> -->
	<a href="{U_INDEX}"><img src="styles/images/logo/logo.png" title="{SITENAME}" alt="{SITENAME}" /></a>
</div>
<!--/logo-->

<!-- IF LOGGED_IN -->
<!--logout-->
<div class="topmenu">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
	<td width="40%">
		{L_USER_WELCOME}&nbsp;<b class="med">{THIS_USER}</b>&nbsp;[ <a href="{U_LOGIN_LOGOUT}" onclick="return confirm('{L_CONFIRM_LOGOUT}');">{L_LOGOUT}</a> ]
	</td>
	<td style="padding: 3px;">
		<div>
			<form id="quick-search" action="" method="get" onsubmit="$(this).attr('action', $('#search-action').val()); if($('#search-action option:selected').attr('class') == 'hash') $('#search-text').attr('name', 'hash');">
				<input type="hidden" name="max" value="1" />
				<input type="hidden" name="to" value="1" />
				<input id="search-text" type="text" name="nm" placeholder="{L_SEARCH_S}" required />
				<select id="search-action">
					<option value="{U_TRACKER}#results" selected> {L_TRACKER_S} </option>
					<option value="{U_SEARCH}"> {L_FORUM_S} </option>
					<option value="{U_TRACKER}" class="hash"> {L_HASH_S} </option>
				</select>
				<input type="submit" class="med" value="{L_SEARCH}" style="width: 55px;" />
			</form>
		</div>
	</td>
	<td width="50%" class="tRight">
		<a href="{U_OPTIONS}"><b>{L_OPTIONS}</b></a> &#0183;
		<a href="{U_CUR_DOWNLOADS}">{L_PROFILE}</a> <a href="#dls-menu" class="menu-root menu-alt1">&#9660;</a>
		&#0183;
		<a href="#" class="dark-mode-toggle" title="{L_DARK_MODE_TOGGLE}"><span class="theme-icon">☀️</span></a>
	</td>
</tr>
</table>
</div>
<!--/logout-->

<div class="menu-sub" id="dls-menu">
	<div class="menu-a bold nowrap">
		<a class="med" href="{U_TRACKER}?rid={SESSION_USER_ID}#results">{L_CUR_UPLOADS}</a>
		<a class="med" href="{U_SEARCH}?dlu={SESSION_USER_ID}&dlc=1">{L_SEARCH_DL_COMPLETE_DOWNLOADS}</a>
		<a class="med" href="{U_SEARCH}?dlu={SESSION_USER_ID}&dlw=1">{L_SEARCH_DL_WILL_DOWNLOADS}</a>
		<a class="med" href="{U_WATCHED_TOPICS}">{L_WATCHED_TOPICS}</a>
	</div>
</div>
<!-- ELSE -->

<!--login form-->
<div class="topmenu">
	<table width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tCenter pad_2">
				<a href="{U_REGISTER}" id="register_link"><b>{L_REGISTER}</b></a> &#0183;
					<form action="{S_LOGIN_ACTION}" method="post">
						{L_USERNAME}: <input type="text" name="login_username" size="12" tabindex="1" accesskey="l" />
						{L_PASSWORD}: <input type="password" name="login_password" size="12" tabindex="2" />
						<label title="{L_AUTO_LOGIN}"><input type="checkbox" name="autologin" value="1" tabindex="3" checked />{L_REMEMBER}</label>&nbsp;
						<input type="submit" name="login" value="{L_LOGIN}" tabindex="4" />
					</form> &#0183;
				<a href="{U_SEND_PASSWORD}">{L_FORGOTTEN_PASSWORD}</a>
				&#0183;
				<a href="#" class="dark-mode-toggle" title="{L_DARK_MODE_TOGGLE}"><span class="theme-icon">☀️</span></a>
			</td>
		</tr>
	</table>
</div>
<!--/login form-->
<!-- ENDIF -->

</div>
<!--/page_header-->

<!--menus-->

<!-- IF SHOW_ONLY_NEW_MENU -->
<div class="menu-sub" id="only-new-options">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th>{L_DISPLAYING_OPTIONS}</th>
	</tr>
	<tr>
		<td>
			<fieldset id="show-only">
			<legend>{L_SHOW_ONLY}</legend>
			<div class="pad_4">
				<label>
					<input id="only_new_posts" type="checkbox" <!-- IF ONLY_NEW_POSTS_ON -->{CHECKED}<!-- ENDIF -->
						onclick="
							user.set('only_new', ( this.checked ? {#ONLY_NEW_POSTS#} : 0 ), 365, true);
							$('#only_new_topics').attr('checked', false);
						" />{L_ONLY_NEW_POSTS}
				</label>
				<label>
					<input id="only_new_topics" type="checkbox" <!-- IF ONLY_NEW_TOPICS_ON -->{CHECKED}<!-- ENDIF -->
						onclick="
							user.set('only_new', ( this.checked ? {#ONLY_NEW_TOPICS#} : 0 ), 365, true);
							$('#only_new_posts').attr('checked', false);
						" />{L_ONLY_NEW_TOPICS}
				</label>
			</div>
			</fieldset>
			<!-- IF USER_HIDE_CAT -->
			<fieldset id="user_hide_cat">
			<legend>{L_HIDE_CAT}</legend>
			<div id="h-cat-ctl" class="pad_4 nowrap">
				<form autocomplete="off">
					<!-- BEGIN h_c -->
					<label><input class="h-cat-cbx" type="checkbox" value="{h_c.H_C_ID}" {h_c.H_C_CHEKED} />{h_c.H_C_TITLE}</label>
					<!-- END h_c -->
				</form>
				<div class="spacer_6"></div>
				<div class="tCenter">
					<!-- IF H_C_AL_MESS -->
					<input style="width: 100px;" type="button" onclick="$('input.h-cat-cbx').attr('checked', false); $('input#sec_h_cat').click(); return false;" value="{L_RESET}">
					<!-- ENDIF -->
					<input id="sec_h_cat" type="button" onclick="set_h_cat();" style="width: 100px;" value="{L_SUBMIT}">
					<script type="text/javascript">
					function set_h_cat ()
					{
						h_cats = [];
						$.each($('input.h-cat-cbx:checked'), function(i,el){
							h_cats.push( $(this).val() );
						});
						user.set('h_cat', h_cats.join('-'), 365, true);
					}
					</script>
				</div>
			</div>
			</fieldset>
			<!-- ENDIF -->
		</td>
	</tr>
	</table>
</div><!--/only-new-options-->
<!-- ENDIF / SHOW_ONLY_NEW_MENU -->

<!--/menus-->

<!--page_content-->
<div id="page_content">
<table cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
<tr><!-- IF SHOW_SIDEBAR1 -->
	<!--sidebar1-->
	<td id="sidebar1">
		<div id="sidebar1-wrap">
			<!-- IF SHOW_BT_USERDATA --><div id="user_ratio">
				<h3>{L_USER_RATIO}</h3>
				<table cellpadding="0">
					<div align="center">{THIS_AVATAR}</div>
					<tr><td>{L_USER_RATIO}</td><td><!-- IF DOWN_TOTAL_BYTES gt MIN_DL_BYTES --><b>{USER_RATIO}</b><!-- ELSE --><b>{L_NONE}</b> (DL < {MIN_DL_FOR_RATIO})<!-- ENDIF --></td></tr>
					<tr><td>{L_DOWNLOADED}</td><td class="leechmed"><b>{DOWN_TOTAL}</b></td></tr>
					<tr><td>{L_UPLOADED}</td><td class="seedmed"><b>{UP_TOTAL}</b></td></tr>
					<tr><td>{L_RELEASED}</td><td class="seedmed">{RELEASED}</td></tr>
					<tr><td>{L_BONUS}</td><td class="seedmed">{UP_BONUS}</td></tr>
					<!-- IF config('seed_bonus_enabled') --><tr><td>{L_SEED_BONUS}</td><td><a href="{BONUS_URL}"><span class="points bold">{POINTS}</span></a></td></tr><!-- ENDIF -->
				</table>
			</div><!-- ENDIF -->
			<!-- IF HTML_SIDEBAR_1 -->
				<?php include($V['HTML_SIDEBAR_1']); ?>
			<!-- ENDIF -->
			<img width="210" class="spacer" src="{SPACER}" alt="" />
		</div><!--/sidebar1-wrap-->
	</td><!--/sidebar1-->
<!-- ENDIF -->

<!--main_content-->
<td id="main_content">
	<div id="main_content_wrap">
		<div id="latest_news">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<!-- IF SHOW_LATEST_NEWS -->
					<td width="50%">
						<h3>{L_LATEST_NEWS}</h3>
						<table cellpadding="0">
							<!-- BEGIN news -->
							<tr>
								<td><div class="news_date">{news.NEWS_TIME}</div></td>
								<td width="100%">
									<div class="news_title<!-- IF news.NEWS_IS_NEW --> new<!-- ENDIF -->"><a href="{TOPIC_URL}{news.NEWS_TOPIC_ID}">{news.NEWS_TITLE}</a></div>
								</td>
							</tr>
							<!-- END news -->
						</table>
					</td>
					<!-- ENDIF -->

					<!-- IF SHOW_NETWORK_NEWS -->
						<td width="50%">
						<h3>{L_NETWORK_NEWS}</h3>
						<table cellpadding="0">
							<!-- BEGIN net -->
							<tr>
								<td><div class="news_date">{net.NEWS_TIME}</div></td>
								<td width="100%">
									<div class="news_title<!-- IF net.NEWS_IS_NEW --> new<!-- ENDIF -->"><a href="{TOPIC_URL}{net.NEWS_TOPIC_ID}">{net.NEWS_TITLE}</a></div>
								</td>
							</tr>
							<!-- END net -->
						</table>
					</td>
					<!-- ENDIF -->
				</tr>
			</table>
		</div>

<!--=======================-->
<!-- ENDIF / COMMON_HEADER -->
<!--***********************-->

<!-- IF ERROR_MESSAGE -->
<div class="info_msg_wrap">
<table class="error">
	<tr><td><div class="msg">{ERROR_MESSAGE}</div></td></tr>
</table>
</div>
<!-- ENDIF / ERROR_MESSAGE -->

<!-- page_header.tpl END -->
<!-- module_xx.tpl START -->
