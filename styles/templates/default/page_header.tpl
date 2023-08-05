<!DOCTYPE html>
<html lang="{$bb_cfg['default_lang']}">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<title><!-- IF HAVE_NEW_PM -->({HAVE_NEW_PM}) <!-- ENDIF --><!-- IF PAGE_TITLE -->{PAGE_TITLE} :: {SITENAME}<!-- ELSE -->{SITENAME}<!-- ENDIF --></title>
<meta name="application-name" content="{SITENAME}"/>
<meta property="og:site_name" content="{SITENAME}">
<meta property="og:image" content="{SITE_URL}styles/images/logo/logo.png" />
<meta property="twitter:image" content="{SITE_URL}styles/images/logo/logo.png">
<meta property="og:title" content="<!-- IF PAGE_TITLE -->{PAGE_TITLE} :: {SITENAME}<!-- ELSE -->{SITENAME}<!-- ENDIF -->">
<meta property="twitter:title" content="<!-- IF PAGE_TITLE -->{PAGE_TITLE} :: {SITENAME}<!-- ELSE -->{SITENAME}<!-- ENDIF -->">
{META}
<link rel="stylesheet" href="{STYLESHEET}?v={$bb_cfg['css_ver']}" type="text/css">
<link rel="shortcut icon" href="{SITE_URL}favicon.png" type="image/x-icon">
<link rel="search" type="application/opensearchdescription+xml" href="{SITE_URL}opensearch_desc.xml" title="{SITENAME} (Forum)" />
<link rel="search" type="application/opensearchdescription+xml" href="{SITE_URL}opensearch_desc_bt.xml" title="{SITENAME} (Tracker)" />

<meta name="generator" content="TorrentPier">
<meta name="version" content="{$bb_cfg['tp_version']}">

<script type="text/javascript" src="{SITE_URL}styles/js/libs/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/jquery-migrate.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/oldbrowserdetector.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/clipboard.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/printThis.min.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/libs/legacy.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/main.js?v={$bb_cfg['js_ver']}"></script>

<!-- IF INCLUDE_BBCODE_JS -->
<script type="text/javascript" src="{SITE_URL}styles/js/bbcode.js?v={$bb_cfg['js_ver']}"></script>
<script type="text/javascript">
	window.BB = {};
	window.encURL = encodeURIComponent;
</script>
<script type="text/javascript">
var bb_url = '{SITE_URL}';
var bbl    = { "code": "{L_CODE}", "wrote": "{L_WROTE}", "quote": "{L_QUOTE}", "quoted_post": "{L_GOTO_QUOTED_POST}", "loading": "{L_LOADING}", "spoiler_head": "{L_SPOILER_HEAD}", "spoiler_close": "{L_SPOILER_CLOSE}", "links_are": "{L_LINKS_ARE_FORBIDDEN}", "scr_rules": "{L_SCREENSHOTS_RULES}", "play_on": "{L_PLAY_ON_CURPAGE}" };

var postImg_MaxWidth = screen.width - {POST_IMG_WIDTH_DECR_JS};
var postImgAligned_MaxWidth = Math.round(screen.width/3);
var attachImg_MaxWidth = screen.width - {ATTACH_IMG_WIDTH_DECR_JS};
var ExternalLinks_InNewWindow = '{EXT_LINK_NEW_WIN}';
var hidePostImg = false;
</script>
<!-- ENDIF / INCLUDE_BBCODE_JS -->

<script type="text/javascript">
var BB_ROOT      = "{#BB_ROOT#}";
var cookieDomain = "{$bb_cfg['cookie_domain']}";
var cookiePath   = "{$bb_cfg['script_path']}";
var cookiePrefix = "{$bb_cfg['cookie_prefix']}";
var cookieSecure = "{$bb_cfg['cookie_secure']}";
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

<!-- IF $bb_cfg['show_jumpbox'] -->
$(document).ready(function(){
	$("div.jumpbox").html('\
		<span id="jumpbox-container"> \
		<select id="jumpbox"> \
			<option id="jumpbox-title" value="-1">&nbsp;&raquo;&raquo; {L_JUMPBOX_TITLE} &nbsp;</option> \
		</select> \
		</span> \
		<input id="jumpbox-submit" type="button" class="lite" value="{L_GO}" /> \
	');
	$('#jumpbox-container').one('click', function(){
		$('#jumpbox-title').html('&nbsp;&nbsp; {L_LOADING} ... &nbsp;');
		var jumpbox_src = '/internal_data/ajax_html' + ({LOGGED_IN} ? '/jumpbox_user.html' : '/jumpbox_guest.html');
		$(this).load(jumpbox_src);
		$('#jumpbox-submit').click(function(){ window.location.href='{FORUM_URL}'+$('#jumpbox').val(); });
	});
});
<!-- ENDIF -->

var ajax = new Ajax('{SITE_URL}{$bb_cfg['ajax_url']}', 'POST', 'json');

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
<!-- IF EDITABLE_TPLS -->
<div id="editable-tpl-input" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="text" class="editable-value" />
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;" />
		<input type="button" class="editable-cancel" value="x" style="width: 30px;" />
	</span>
</div>
<div id="editable-tpl-yesno-select" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<select class="editable-value">
			<option value="1">&nbsp;{L_YES}&nbsp;</option>
			<option value="0">&nbsp;{L_NO}&nbsp;</option>
		</select>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;" />
		<input type="button" class="editable-cancel" value="x" style="width: 30px;" />
	</span>
</div>
<div id="editable-tpl-yesno-radio" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<label><input class="editable-value" type="radio" name="editable-value" value="1" />{L_YES}</label>
		<label><input class="editable-value" type="radio" name="editable-value" value="0" />{L_NO}</label>&nbsp;
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;" />
		<input type="button" class="editable-cancel" value="x" style="width: 30px;" />
	</span>
</div>
<div id="editable-tpl-yesno-gender" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<select class="editable-value">
			<option value="0">&nbsp;{$lang['GENDER_SELECT'][0]}&nbsp;</option>
			<option value="1">&nbsp;{$lang['GENDER_SELECT'][1]}&nbsp;</option>
			<option value="2">&nbsp;{$lang['GENDER_SELECT'][2]}&nbsp;</option>
		</select>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<div id="editable-tpl-yesno-twitter" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="text" class="editable-value" value="{TWITTER}" />
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<div id="editable-tpl-yesno-birthday" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="date" class="editable-value" value="{BIRTHDAY}" />
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<!-- ENDIF / EDITABLE_TPLS -->

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
	window.location = '{PG_BASE_URL}&start=' + pg_start;
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
<div id="main-nav" <!-- IF HAVE_NEW_PM -->class="new-pm"<!-- ENDIF --> style="height: 17px;">
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
		<td class="nowrap" align="right">
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
<?php
// определяем текущий месяц
$month = date('n');

// путь к папке с картинками
$images_dir = '/styles/images/shapka/';

// формируем имя файла изображения для текущего месяца
$image_filename = $month . '.png';

// полный путь к файлу изображения
$image_path = $_SERVER['DOCUMENT_ROOT'] . $images_dir . $image_filename;

// проверяем, существует ли файл изображения для текущего месяца
if (file_exists($image_path)) {
    // выводим HTML-код с нужной картинкой и JavaScript для обновления
    echo '<div id="logonew" class="header-logo" style="background-image: url(' . $images_dir . $image_filename . '?v=' . mt_rand() . ')">';
    echo '<a id="logoclick" href="https://crackstatus.net/"></a>';
    echo '</div>';
    echo '<script>';
    echo 'setTimeout(function() {';
    echo '  var image = document.getElementById("logonew");';
    echo '  image.style.backgroundImage = "url(' . $images_dir . $image_filename . '?v=' . mt_rand() . ')";';
    echo '}, 3600000);'; // Проверка каждый час (3600000 миллисекунд)
    echo '</script>';
} else {
    // используем последний доступный файл изображения для предыдущего месяца
    $last_month = ($month - 1 <= 0) ? 12 : $month - 1;
    $last_image_filename = $last_month . '.png';
    
    // полный путь к последнему доступному файлу изображения
    $last_image_path = $_SERVER['DOCUMENT_ROOT'] . $images_dir . $last_image_filename;
    
    // проверяем, существует ли последний файл изображения
    if (file_exists($last_image_path)) {
        // выводим HTML-код с последней доступной картинкой
        echo '<div id="logonew" class="header-logo" style="background-image: url(' . $images_dir . $last_image_filename . '?v=' . mt_rand() . ')">';
        echo '<a id="logoclick" href="https://crackstatus.net/"></a>';
        echo '</div>';
    } else {
        // если файл не существует, выводим сообщение об ошибке
        echo 'Ошибка: файл изображения не найден.';
    }
}
?>
<!--/logo-->

<!-- IF LOGGED_IN -->
<script type="text/javascript">
ajax.index_data = function(tz) {
	ajax.exec({
		action  : 'index_data',
		mode    : 'change_tz',
		tz      : tz
	});
};
ajax.callback.index_data = function(data) {};
$(document).ready(function() {
	x = new Date();
	tz = -x.getTimezoneOffset()/60;
	if (tz != {BOARD_TIMEZONE})
	{
		ajax.index_data(tz);
	}
});
</script>

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
					<option value="tracker.php#results" selected="selected"> {L_TRACKER_S} </option>
					<option value="search.php"> {L_FORUM_S} </option>
					<option value="tracker.php" class="hash"> {L_HASH_S} </option>
				</select>
				<input type="submit" class="med" value="{L_SEARCH}" style="width: 55px;" />
			</form>
		</div>
	</td>
	<td width="50%" class="tRight">
		<a href="{U_OPTIONS}"><b>{L_OPTIONS}</b></a> &#0183;
		<a href="{U_CUR_DOWNLOADS}">{L_PROFILE}</a> <a href="#dls-menu" class="menu-root menu-alt1">&#9660;</a>
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
						<label title="{L_AUTO_LOGIN}"><input type="checkbox" name="autologin" value="1" tabindex="3" checked="checked" />{L_REMEMBER}</label>&nbsp;
						<input type="submit" name="login" value="{L_LOGIN}" tabindex="4" />
					</form> &#0183;
				<a href="{U_SEND_PASSWORD}">{L_FORGOTTEN_PASSWORD}</a>
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
<!-- IF LAST_ADDED -->
<div class="menu-sub" id="hi-poster">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th>Опции ленты новинок</th>
	</tr>
	<tr>
		<td>
			<fieldset id="ajax-topics">
			<legend>Настройка ленты</legend>
			<div class="pad_4">
				<label>
					<input type="checkbox" <!-- IF POSTER -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('poster', this.checked ? 1 : 0);"
					/>Показывать ленту постеров
				</label>
			</div>
			</fieldset>			
			<!-- IF POSTER -->
			<fieldset id="user_hide_poster">
			<legend>Скрыть постеры из разделов</legend>
			<div id="h-poster-ctl" class="pad_4 nowrap">
				<form autocomplete="off">
					<!-- BEGIN h_p -->
					<label><input class="h-poster-cbx" type="checkbox" value="{h_p.H_C_ID}" {h_p.H_C_CHEKED} />{h_p.H_C_TITLE}</label>
					<!-- END h_p -->
				</form>
				<div class="spacer_6"></div>
				<div class="tCenter">
					<!-- IF H_P_AL_MESS -->
					<input style="width: 100px;" type="button" onclick="$('input.h-poster-cbx').attr('checked',false); $('input#sec_h_poster').click(); return false;" value="Сбросить">
					<!-- ENDIF -->
					<input id="sec_h_poster" type="button" onclick="set_h_poster();" style="width: 100px;" value="Отправить">
				    <script type="text/javascript">
					function set_h_poster ()
					{
						h_posters = [];
						$.each($('input.h-poster-cbx:checked'), function(i,el){
							h_posters.push( $(this).val() );
						});
						user.set('h_poster', h_posters.join('-'), 365, true);
					}
					</script>
				</div>
			</div>
			</fieldset>
			<!-- ENDIF -->		
		</td>
	</tr>
	<!-- IF not POSTER -->
	<tr>
		<td class="cat tCenter pad_4"><input type="button" value="{L_SUBMIT}" onclick="window.location.reload();" /></td>
	</tr>
	<!-- ENDIF -->
	</table>
</div>
<!-- ENDIF -->


<!-- IF LAST_ADDED && POSTER && LOGGED_IN && LENTA -->
<script type="text/javascript" src="{SITE_URL}styles/js/misc/jquery.cluetip.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/misc/jquery.scrollable.js"></script>
<script type="text/javascript" src="{SITE_URL}styles/js/misc/jquery.mousewheel.js"></script>
<!-- IF $bb_cfg['new_poster'] --><script type="text/javascript" src="{SITE_URL}styles/js/misc/cvi_glossy_lib.js"></script><!-- ENDIF -->
<link type="text/css" rel="stylesheet" href="{SITE_URL}styles/templates/default/css/cluetip.css"/>
<script type="text/javascript">
    $(document).ready(function() {
 		$('div.load-local').cluetip({local:true, cursor: 'pointer',showTitle: true,arrows: true});
		$("div.scrollable").scrollable({size: 8, items: "#thumbs", hoverClass: "hover", keyboard: true, loop: false });
	});
</script>

<table cellpadding="0" cellspacing="0" class="poster">
<tr>
    <td>
    <!-- root element for scrollable -->
    <div class="scrollable">
        <div id="thumbs">
            <!-- BEGIN last_added -->
            <div class="load-local" rel="#loadme_{last_added.TOPIC_ID}" title="{last_added.TITLE}" onclick="top.location.href='viewtopic.php?t={last_added.TOPIC_ID}';" onmouseover="initPostImages($('#loadme_{last_added.TOPIC_ID}'));">
				<a href="viewtopic.php?t={last_added.TOPIC_ID}"><img src="thumb.php?t={last_added.TOPIC_ID}" alt="" <!-- IF $bb_cfg['new_poster'] -->onload="cvi_glossy.add(this,{radius:30,nogradient:true,angle:-33,shadow:30});"<!-- ENDIF -->></a>&nbsp;				
				<div style="display:none;" id="loadme_{last_added.TOPIC_ID}">
				    <center><var class="posterImg" title="{last_added.POSTER_FULL}" alt="" border="0">&#10;</var></center>
					<br /> {L_FORUM}: <b>{last_added.FORUM_NAME}</b> 
                    <br /> {L_AUTHOR}: <b>{last_added.USER_NAME}</b>
                    <br /> {L_SIZE}: <b>{last_added.SIZE}</b>
				</div>
			</div>	
            <!-- END last_added -->
        </div>
    </div>
    </td>
</tr>
</table>
<!-- ENDIF -->

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
					<!-- IF $bb_cfg['seed_bonus_enabled'] --><tr><td>{L_SEED_BONUS}</td><td><a href="profile.php?mode=bonus"><span class="points bold">{POINTS}</span></a></td></tr><!-- ENDIF -->
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
