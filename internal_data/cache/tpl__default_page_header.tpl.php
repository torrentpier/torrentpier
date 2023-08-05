<!DOCTYPE html>
<html lang="<?php echo isset($bb_cfg['default_lang']) ? $bb_cfg['default_lang'] : ''; ?>">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<title><?php if (!empty($V['HAVE_NEW_PM'])) { ?>(<?php echo isset($V['HAVE_NEW_PM']) ? $V['HAVE_NEW_PM'] : ''; ?>) <?php } ?><?php if (!empty($V['PAGE_TITLE'])) { ?><?php echo isset($V['PAGE_TITLE']) ? $V['PAGE_TITLE'] : ''; ?> :: <?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?><?php } else { ?><?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?><?php } ?></title>
<meta name="application-name" content="<?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?>"/>
<meta property="og:site_name" content="<?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?>">
<meta property="og:image" content="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/images/logo/logo.png" />
<meta property="twitter:image" content="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/images/logo/logo.png">
<meta property="og:title" content="<?php if (!empty($V['PAGE_TITLE'])) { ?><?php echo isset($V['PAGE_TITLE']) ? $V['PAGE_TITLE'] : ''; ?> :: <?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?><?php } else { ?><?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?><?php } ?>">
<meta property="twitter:title" content="<?php if (!empty($V['PAGE_TITLE'])) { ?><?php echo isset($V['PAGE_TITLE']) ? $V['PAGE_TITLE'] : ''; ?> :: <?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?><?php } else { ?><?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?><?php } ?>">
<?php echo isset($V['META']) ? $V['META'] : ''; ?>
<link rel="stylesheet" href="<?php echo isset($V['STYLESHEET']) ? $V['STYLESHEET'] : ''; ?>?v=<?php echo isset($bb_cfg['css_ver']) ? $bb_cfg['css_ver'] : ''; ?>" type="text/css">
<link rel="shortcut icon" href="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>favicon.png" type="image/x-icon">
<link rel="search" type="application/opensearchdescription+xml" href="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>opensearch_desc.xml" title="<?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?> (Forum)" />
<link rel="search" type="application/opensearchdescription+xml" href="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>opensearch_desc_bt.xml" title="<?php echo isset($V['SITENAME']) ? $V['SITENAME'] : ''; ?> (Tracker)" />

<meta name="generator" content="TorrentPier">
<meta name="version" content="<?php echo isset($bb_cfg['tp_version']) ? $bb_cfg['tp_version'] : ''; ?>">

<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/libs/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/libs/jquery-migrate.min.js"></script>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/libs/oldbrowserdetector.min.js"></script>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/libs/clipboard.min.js"></script>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/libs/printThis.min.js"></script>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/libs/legacy.js"></script>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/main.js?v=<?php echo isset($bb_cfg['js_ver']) ? $bb_cfg['js_ver'] : ''; ?>"></script>

<?php if (!empty($V['INCLUDE_BBCODE_JS'])) { ?>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/bbcode.js?v=<?php echo isset($bb_cfg['js_ver']) ? $bb_cfg['js_ver'] : ''; ?>"></script>
<script type="text/javascript">
	window.BB = {};
	window.encURL = encodeURIComponent;
</script>
<script type="text/javascript">
var bb_url = '<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>';
var bbl    = { "code": "<?php echo isset($L['CODE']) ? $L['CODE'] : (isset($SL['CODE']) ? $SL['CODE'] : $V['L_CODE']); ?>", "wrote": "<?php echo isset($L['WROTE']) ? $L['WROTE'] : (isset($SL['WROTE']) ? $SL['WROTE'] : $V['L_WROTE']); ?>", "quote": "<?php echo isset($L['QUOTE']) ? $L['QUOTE'] : (isset($SL['QUOTE']) ? $SL['QUOTE'] : $V['L_QUOTE']); ?>", "quoted_post": "<?php echo isset($L['GOTO_QUOTED_POST']) ? $L['GOTO_QUOTED_POST'] : (isset($SL['GOTO_QUOTED_POST']) ? $SL['GOTO_QUOTED_POST'] : $V['L_GOTO_QUOTED_POST']); ?>", "loading": "<?php echo isset($L['LOADING']) ? $L['LOADING'] : (isset($SL['LOADING']) ? $SL['LOADING'] : $V['L_LOADING']); ?>", "spoiler_head": "<?php echo isset($L['SPOILER_HEAD']) ? $L['SPOILER_HEAD'] : (isset($SL['SPOILER_HEAD']) ? $SL['SPOILER_HEAD'] : $V['L_SPOILER_HEAD']); ?>", "spoiler_close": "<?php echo isset($L['SPOILER_CLOSE']) ? $L['SPOILER_CLOSE'] : (isset($SL['SPOILER_CLOSE']) ? $SL['SPOILER_CLOSE'] : $V['L_SPOILER_CLOSE']); ?>", "links_are": "<?php echo isset($L['LINKS_ARE_FORBIDDEN']) ? $L['LINKS_ARE_FORBIDDEN'] : (isset($SL['LINKS_ARE_FORBIDDEN']) ? $SL['LINKS_ARE_FORBIDDEN'] : $V['L_LINKS_ARE_FORBIDDEN']); ?>", "scr_rules": "<?php echo isset($L['SCREENSHOTS_RULES']) ? $L['SCREENSHOTS_RULES'] : (isset($SL['SCREENSHOTS_RULES']) ? $SL['SCREENSHOTS_RULES'] : $V['L_SCREENSHOTS_RULES']); ?>", "play_on": "<?php echo isset($L['PLAY_ON_CURPAGE']) ? $L['PLAY_ON_CURPAGE'] : (isset($SL['PLAY_ON_CURPAGE']) ? $SL['PLAY_ON_CURPAGE'] : $V['L_PLAY_ON_CURPAGE']); ?>" };

var postImg_MaxWidth = screen.width - <?php echo isset($V['POST_IMG_WIDTH_DECR_JS']) ? $V['POST_IMG_WIDTH_DECR_JS'] : ''; ?>;
var postImgAligned_MaxWidth = Math.round(screen.width/3);
var attachImg_MaxWidth = screen.width - <?php echo isset($V['ATTACH_IMG_WIDTH_DECR_JS']) ? $V['ATTACH_IMG_WIDTH_DECR_JS'] : ''; ?>;
var ExternalLinks_InNewWindow = '<?php echo isset($V['EXT_LINK_NEW_WIN']) ? $V['EXT_LINK_NEW_WIN'] : ''; ?>';
var hidePostImg = false;
</script>
<?php } ?>

<script type="text/javascript">
var BB_ROOT      = "<?php echo defined('BB_ROOT') ? BB_ROOT : ''; ?>";
var cookieDomain = "<?php echo isset($bb_cfg['cookie_domain']) ? $bb_cfg['cookie_domain'] : ''; ?>";
var cookiePath   = "<?php echo isset($bb_cfg['script_path']) ? $bb_cfg['script_path'] : ''; ?>";
var cookiePrefix = "<?php echo isset($bb_cfg['cookie_prefix']) ? $bb_cfg['cookie_prefix'] : ''; ?>";
var cookieSecure = "<?php echo isset($bb_cfg['cookie_secure']) ? $bb_cfg['cookie_secure'] : ''; ?>";
var LOGGED_IN    = <?php echo isset($V['LOGGED_IN']) ? $V['LOGGED_IN'] : ''; ?>;
var IWP          = 'HEIGHT=510,WIDTH=780,resizable=yes';
var IWP_US       = 'HEIGHT=250,WIDTH=400,resizable=yes';
var IWP_SM       = 'HEIGHT=420,WIDTH=470,resizable=yes,scrollbars=yes';

var user = {
	opt_js: <?php echo isset($V['USER_OPTIONS_JS']) ? $V['USER_OPTIONS_JS'] : ''; ?>,

	set: function (opt, val, days, reload) {
		this.opt_js[opt] = val;
		setCookie('opt_js', $.toJSON(this.opt_js), days);
		if (reload) {
			window.location.reload();
		}
	}
};

<?php if (!empty($bb_cfg['show_jumpbox'])) { ?>
$(document).ready(function(){
	$("div.jumpbox").html('\
		<span id="jumpbox-container"> \
		<select id="jumpbox"> \
			<option id="jumpbox-title" value="-1">&nbsp;&raquo;&raquo; <?php echo isset($L['JUMPBOX_TITLE']) ? $L['JUMPBOX_TITLE'] : (isset($SL['JUMPBOX_TITLE']) ? $SL['JUMPBOX_TITLE'] : $V['L_JUMPBOX_TITLE']); ?> &nbsp;</option> \
		</select> \
		</span> \
		<input id="jumpbox-submit" type="button" class="lite" value="<?php echo isset($L['GO']) ? $L['GO'] : (isset($SL['GO']) ? $SL['GO'] : $V['L_GO']); ?>" /> \
	');
	$('#jumpbox-container').one('click', function(){
		$('#jumpbox-title').html('&nbsp;&nbsp; <?php echo isset($L['LOADING']) ? $L['LOADING'] : (isset($SL['LOADING']) ? $SL['LOADING'] : $V['L_LOADING']); ?> ... &nbsp;');
		var jumpbox_src = '/internal_data/ajax_html' + (<?php echo isset($V['LOGGED_IN']) ? $V['LOGGED_IN'] : ''; ?> ? '/jumpbox_user.html' : '/jumpbox_guest.html');
		$(this).load(jumpbox_src);
		$('#jumpbox-submit').click(function(){ window.location.href='<?php echo isset($V['FORUM_URL']) ? $V['FORUM_URL'] : ''; ?>'+$('#jumpbox').val(); });
	});
});
<?php } ?>

var ajax = new Ajax('<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?><?php echo isset($bb_cfg['ajax_url']) ? $bb_cfg['ajax_url'] : ''; ?>', 'POST', 'json');

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
<?php if (!empty($V['USE_TABLESORTER'])) { ?>
$(document).ready(function() {
	$('.tablesorter').tablesorter();
});
<?php } ?>

function cfm (txt)
{
	return window.confirm(txt);
}
function post2url (url, params) {
	params = params || {};
	var f = document.createElement('form');
	f.setAttribute('method', 'post');
	f.setAttribute('action', url);
	params['form_token'] = '<?php echo isset($V['FORM_TOKEN']) ? $V['FORM_TOKEN'] : ''; ?>';
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
<?php if (!empty($V['EDITABLE_TPLS'])) { ?>
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
			<option value="1">&nbsp;<?php echo isset($L['YES']) ? $L['YES'] : (isset($SL['YES']) ? $SL['YES'] : $V['L_YES']); ?>&nbsp;</option>
			<option value="0">&nbsp;<?php echo isset($L['NO']) ? $L['NO'] : (isset($SL['NO']) ? $SL['NO'] : $V['L_NO']); ?>&nbsp;</option>
		</select>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;" />
		<input type="button" class="editable-cancel" value="x" style="width: 30px;" />
	</span>
</div>
<div id="editable-tpl-yesno-radio" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<label><input class="editable-value" type="radio" name="editable-value" value="1" /><?php echo isset($L['YES']) ? $L['YES'] : (isset($SL['YES']) ? $SL['YES'] : $V['L_YES']); ?></label>
		<label><input class="editable-value" type="radio" name="editable-value" value="0" /><?php echo isset($L['NO']) ? $L['NO'] : (isset($SL['NO']) ? $SL['NO'] : $V['L_NO']); ?></label>&nbsp;
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;" />
		<input type="button" class="editable-cancel" value="x" style="width: 30px;" />
	</span>
</div>
<div id="editable-tpl-yesno-gender" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<select class="editable-value">
			<option value="0">&nbsp;<?php echo isset($lang['GENDER_SELECT'][0]) ? $lang['GENDER_SELECT'][0] : ''; ?>&nbsp;</option>
			<option value="1">&nbsp;<?php echo isset($lang['GENDER_SELECT'][1]) ? $lang['GENDER_SELECT'][1] : ''; ?>&nbsp;</option>
			<option value="2">&nbsp;<?php echo isset($lang['GENDER_SELECT'][2]) ? $lang['GENDER_SELECT'][2] : ''; ?>&nbsp;</option>
		</select>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<div id="editable-tpl-yesno-twitter" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="text" class="editable-value" value="<?php echo isset($V['TWITTER']) ? $V['TWITTER'] : ''; ?>" />
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<div id="editable-tpl-yesno-birthday" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="date" class="editable-value" value="<?php echo isset($V['BIRTHDAY']) ? $V['BIRTHDAY'] : ''; ?>" />
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;">
		<input type="button" class="editable-cancel" value="x" style="width: 30px;">
	</span>
</div>
<?php } ?>

<?php if (!empty($V['PAGINATION'])) { ?>
<div class="menu-sub" id="pg-jump">
	<table cellspacing="1" cellpadding="4">
	<tr><th><?php echo isset($L['GO_TO_PAGE']) ? $L['GO_TO_PAGE'] : (isset($SL['GO_TO_PAGE']) ? $SL['GO_TO_PAGE'] : $V['L_GO_TO_PAGE']); ?></th></tr>
	<tr><td>
		<form method="get" onsubmit="return go_to_page();">
			<input id="pg-page" type="text" size="5" maxlength="4" />
			<input type="submit" value="<?php echo isset($L['GO']) ? $L['GO'] : (isset($SL['GO']) ? $SL['GO'] : $V['L_GO']); ?>"/>
		</form>
	</td></tr>
	</table>
</div>
<script type="text/javascript">
function go_to_page ()
{
	var page_num = (parseInt( $('#pg-page').val() ) > 1) ? $('#pg-page').val() : 1;
	var pg_start = (page_num - 1) * <?php echo isset($V['PG_PER_PAGE']) ? $V['PG_PER_PAGE'] : ''; ?>;
	window.location = '<?php echo isset($V['PG_BASE_URL']) ? $V['PG_BASE_URL'] : ''; ?>&start=' + pg_start;
	return false;
}
</script>
<?php } ?>

<div id="ajax-loading"></div><div id="ajax-error"></div>
<div id="preload" style="position: absolute; overflow: hidden; top: 0; left: 0; height: 1px; width: 1px;"></div>

<div id="body_container">

<!--******************-->
<?php if (!empty($V['SIMPLE_HEADER'])) { ?>
<!--==================-->

<style>body { background: #E3E3E3; min-width: 10px; }</style>

<!--=================-->
<?php } elseif (!empty($V['IN_ADMIN'])) { ?>
<!--=================-->

<!--======-->
<?php } else { ?>
<!--======-->

<!--page_container-->
<div id="page_container">
<a name="top"></a>

<!--page_header-->
<div id="page_header">

<div id="old-browser-warn" style="background: #FFF227; padding: 8px 0 10px; text-align: center; font-size: 14px; display: none; ">
	<b><?php echo isset($L['OLD_BROWSER']) ? $L['OLD_BROWSER'] : (isset($SL['OLD_BROWSER']) ? $SL['OLD_BROWSER'] : $V['L_OLD_BROWSER']); ?></b>
</div>
<script>
  var Detector = new oldBrowserDetector(null, function () {
    $('#old-browser-warn').show();
  });

  Detector.detect();
</script>

<!--main_nav-->
<div id="main-nav" <?php if (!empty($V['HAVE_NEW_PM'])) { ?>class="new-pm"<?php } ?> style="height: 17px;">
	<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td class="nowrap">
			<a href="<?php echo isset($V['U_INDEX']) ? $V['U_INDEX'] : ''; ?>"><b><?php echo isset($L['HOME']) ? $L['HOME'] : (isset($SL['HOME']) ? $SL['HOME'] : $V['L_HOME']); ?></b></a><span style="color:#CDCDCD;">|</span>
			<a href="<?php echo isset($V['U_TRACKER']) ? $V['U_TRACKER'] : ''; ?>"><b><?php echo isset($L['TRACKER']) ? $L['TRACKER'] : (isset($SL['TRACKER']) ? $SL['TRACKER'] : $V['L_TRACKER']); ?></b></a><span style="color:#CDCDCD;">|</span>
			<a href="<?php echo isset($V['U_SEARCH']) ? $V['U_SEARCH'] : ''; ?>"><b><?php echo isset($L['SEARCH']) ? $L['SEARCH'] : (isset($SL['SEARCH']) ? $SL['SEARCH'] : $V['L_SEARCH']); ?></b></a><span style="color:#CDCDCD;">|</span>
			<a href="<?php echo isset($V['U_TERMS']) ? $V['U_TERMS'] : ''; ?>"><b style="color: #993300;"><?php echo isset($L['TERMS']) ? $L['TERMS'] : (isset($SL['TERMS']) ? $SL['TERMS'] : $V['L_TERMS']); ?></b></a><span style="color:#CDCDCD;">|</span>
			<a href="<?php echo isset($V['U_GROUPS']) ? $V['U_GROUPS'] : ''; ?>"><b><?php echo isset($L['USERGROUPS']) ? $L['USERGROUPS'] : (isset($SL['USERGROUPS']) ? $SL['USERGROUPS'] : $V['L_USERGROUPS']); ?></b></a><span style="color:#CDCDCD;">|</span>
			<a href="<?php echo isset($V['U_MEMBERLIST']) ? $V['U_MEMBERLIST'] : ''; ?>"><b><?php echo isset($L['MEMBERLIST']) ? $L['MEMBERLIST'] : (isset($SL['MEMBERLIST']) ? $SL['MEMBERLIST'] : $V['L_MEMBERLIST']); ?></b></a>
		</td>
		<td class="nowrap" align="right">
			<?php if (!empty($V['LOGGED_IN'])) { ?>
				<?php if ($V['HAVE_NEW_PM'] || $V['HAVE_UNREAD_PM']) { ?>
					<a href="<?php echo isset($V['U_READ_PM']) ? $V['U_READ_PM'] : ''; ?>" class="new-pm-link"><b><?php echo isset($L['PRIVATE_MESSAGES']) ? $L['PRIVATE_MESSAGES'] : (isset($SL['PRIVATE_MESSAGES']) ? $SL['PRIVATE_MESSAGES'] : $V['L_PRIVATE_MESSAGES']); ?>: <?php echo isset($V['PM_INFO']) ? $V['PM_INFO'] : ''; ?></b></a>
				<?php } else { ?>
					<a href="<?php echo isset($V['U_PRIVATEMSGS']) ? $V['U_PRIVATEMSGS'] : ''; ?>"><b><?php echo isset($L['PRIVATE_MESSAGES']) ? $L['PRIVATE_MESSAGES'] : (isset($SL['PRIVATE_MESSAGES']) ? $SL['PRIVATE_MESSAGES'] : $V['L_PRIVATE_MESSAGES']); ?>: <?php echo isset($V['PM_INFO']) ? $V['PM_INFO'] : ''; ?></b></a>
				<?php } ?>
			<?php } ?>
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

<?php if (!empty($V['LOGGED_IN'])) { ?>
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
	if (tz != <?php echo isset($V['BOARD_TIMEZONE']) ? $V['BOARD_TIMEZONE'] : ''; ?>)
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
		<?php echo isset($L['USER_WELCOME']) ? $L['USER_WELCOME'] : (isset($SL['USER_WELCOME']) ? $SL['USER_WELCOME'] : $V['L_USER_WELCOME']); ?>&nbsp;<b class="med"><?php echo isset($V['THIS_USER']) ? $V['THIS_USER'] : ''; ?></b>&nbsp;[ <a href="<?php echo isset($V['U_LOGIN_LOGOUT']) ? $V['U_LOGIN_LOGOUT'] : ''; ?>" onclick="return confirm('<?php echo isset($L['CONFIRM_LOGOUT']) ? $L['CONFIRM_LOGOUT'] : (isset($SL['CONFIRM_LOGOUT']) ? $SL['CONFIRM_LOGOUT'] : $V['L_CONFIRM_LOGOUT']); ?>');"><?php echo isset($L['LOGOUT']) ? $L['LOGOUT'] : (isset($SL['LOGOUT']) ? $SL['LOGOUT'] : $V['L_LOGOUT']); ?></a> ]
	</td>
	<td style="padding: 3px;">
		<div>
			<form id="quick-search" action="" method="get" onsubmit="$(this).attr('action', $('#search-action').val()); if($('#search-action option:selected').attr('class') == 'hash') $('#search-text').attr('name', 'hash');">
				<input type="hidden" name="max" value="1" />
				<input type="hidden" name="to" value="1" />
				<input id="search-text" type="text" name="nm" placeholder="<?php echo isset($L['SEARCH_S']) ? $L['SEARCH_S'] : (isset($SL['SEARCH_S']) ? $SL['SEARCH_S'] : $V['L_SEARCH_S']); ?>" required />
				<select id="search-action">
					<option value="tracker.php#results" selected="selected"> <?php echo isset($L['TRACKER_S']) ? $L['TRACKER_S'] : (isset($SL['TRACKER_S']) ? $SL['TRACKER_S'] : $V['L_TRACKER_S']); ?> </option>
					<option value="search.php"> <?php echo isset($L['FORUM_S']) ? $L['FORUM_S'] : (isset($SL['FORUM_S']) ? $SL['FORUM_S'] : $V['L_FORUM_S']); ?> </option>
					<option value="tracker.php" class="hash"> <?php echo isset($L['HASH_S']) ? $L['HASH_S'] : (isset($SL['HASH_S']) ? $SL['HASH_S'] : $V['L_HASH_S']); ?> </option>
				</select>
				<input type="submit" class="med" value="<?php echo isset($L['SEARCH']) ? $L['SEARCH'] : (isset($SL['SEARCH']) ? $SL['SEARCH'] : $V['L_SEARCH']); ?>" style="width: 55px;" />
			</form>
		</div>
	</td>
	<td width="50%" class="tRight">
		<a href="<?php echo isset($V['U_OPTIONS']) ? $V['U_OPTIONS'] : ''; ?>"><b><?php echo isset($L['OPTIONS']) ? $L['OPTIONS'] : (isset($SL['OPTIONS']) ? $SL['OPTIONS'] : $V['L_OPTIONS']); ?></b></a> &#0183;
		<a href="<?php echo isset($V['U_CUR_DOWNLOADS']) ? $V['U_CUR_DOWNLOADS'] : ''; ?>"><?php echo isset($L['PROFILE']) ? $L['PROFILE'] : (isset($SL['PROFILE']) ? $SL['PROFILE'] : $V['L_PROFILE']); ?></a> <a href="#dls-menu" class="menu-root menu-alt1">&#9660;</a>
	</td>
</tr>
</table>
</div>
<!--/logout-->

<div class="menu-sub" id="dls-menu">
	<div class="menu-a bold nowrap">
		<a class="med" href="<?php echo isset($V['U_TRACKER']) ? $V['U_TRACKER'] : ''; ?>?rid=<?php echo isset($V['SESSION_USER_ID']) ? $V['SESSION_USER_ID'] : ''; ?>#results"><?php echo isset($L['CUR_UPLOADS']) ? $L['CUR_UPLOADS'] : (isset($SL['CUR_UPLOADS']) ? $SL['CUR_UPLOADS'] : $V['L_CUR_UPLOADS']); ?></a>
		<a class="med" href="<?php echo isset($V['U_SEARCH']) ? $V['U_SEARCH'] : ''; ?>?dlu=<?php echo isset($V['SESSION_USER_ID']) ? $V['SESSION_USER_ID'] : ''; ?>&dlc=1"><?php echo isset($L['SEARCH_DL_COMPLETE_DOWNLOADS']) ? $L['SEARCH_DL_COMPLETE_DOWNLOADS'] : (isset($SL['SEARCH_DL_COMPLETE_DOWNLOADS']) ? $SL['SEARCH_DL_COMPLETE_DOWNLOADS'] : $V['L_SEARCH_DL_COMPLETE_DOWNLOADS']); ?></a>
		<a class="med" href="<?php echo isset($V['U_SEARCH']) ? $V['U_SEARCH'] : ''; ?>?dlu=<?php echo isset($V['SESSION_USER_ID']) ? $V['SESSION_USER_ID'] : ''; ?>&dlw=1"><?php echo isset($L['SEARCH_DL_WILL_DOWNLOADS']) ? $L['SEARCH_DL_WILL_DOWNLOADS'] : (isset($SL['SEARCH_DL_WILL_DOWNLOADS']) ? $SL['SEARCH_DL_WILL_DOWNLOADS'] : $V['L_SEARCH_DL_WILL_DOWNLOADS']); ?></a>
		<a class="med" href="<?php echo isset($V['U_WATCHED_TOPICS']) ? $V['U_WATCHED_TOPICS'] : ''; ?>"><?php echo isset($L['WATCHED_TOPICS']) ? $L['WATCHED_TOPICS'] : (isset($SL['WATCHED_TOPICS']) ? $SL['WATCHED_TOPICS'] : $V['L_WATCHED_TOPICS']); ?></a>
	</div>
</div>
<?php } else { ?>

<!--login form-->
<div class="topmenu">
	<table width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tCenter pad_2">
				<a href="<?php echo isset($V['U_REGISTER']) ? $V['U_REGISTER'] : ''; ?>" id="register_link"><b><?php echo isset($L['REGISTER']) ? $L['REGISTER'] : (isset($SL['REGISTER']) ? $SL['REGISTER'] : $V['L_REGISTER']); ?></b></a> &#0183;
					<form action="<?php echo isset($V['S_LOGIN_ACTION']) ? $V['S_LOGIN_ACTION'] : ''; ?>" method="post">
						<?php echo isset($L['USERNAME']) ? $L['USERNAME'] : (isset($SL['USERNAME']) ? $SL['USERNAME'] : $V['L_USERNAME']); ?>: <input type="text" name="login_username" size="12" tabindex="1" accesskey="l" />
						<?php echo isset($L['PASSWORD']) ? $L['PASSWORD'] : (isset($SL['PASSWORD']) ? $SL['PASSWORD'] : $V['L_PASSWORD']); ?>: <input type="password" name="login_password" size="12" tabindex="2" />
						<label title="<?php echo isset($L['AUTO_LOGIN']) ? $L['AUTO_LOGIN'] : (isset($SL['AUTO_LOGIN']) ? $SL['AUTO_LOGIN'] : $V['L_AUTO_LOGIN']); ?>"><input type="checkbox" name="autologin" value="1" tabindex="3" checked="checked" /><?php echo isset($L['REMEMBER']) ? $L['REMEMBER'] : (isset($SL['REMEMBER']) ? $SL['REMEMBER'] : $V['L_REMEMBER']); ?></label>&nbsp;
						<input type="submit" name="login" value="<?php echo isset($L['LOGIN']) ? $L['LOGIN'] : (isset($SL['LOGIN']) ? $SL['LOGIN'] : $V['L_LOGIN']); ?>" tabindex="4" />
					</form> &#0183;
				<a href="<?php echo isset($V['U_SEND_PASSWORD']) ? $V['U_SEND_PASSWORD'] : ''; ?>"><?php echo isset($L['FORGOTTEN_PASSWORD']) ? $L['FORGOTTEN_PASSWORD'] : (isset($SL['FORGOTTEN_PASSWORD']) ? $SL['FORGOTTEN_PASSWORD'] : $V['L_FORGOTTEN_PASSWORD']); ?></a>
			</td>
		</tr>
	</table>
</div>
<!--/login form-->
<?php } ?>

</div>
<!--/page_header-->

<!--menus-->

<?php if (!empty($V['SHOW_ONLY_NEW_MENU'])) { ?>
<div class="menu-sub" id="only-new-options">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th><?php echo isset($L['DISPLAYING_OPTIONS']) ? $L['DISPLAYING_OPTIONS'] : (isset($SL['DISPLAYING_OPTIONS']) ? $SL['DISPLAYING_OPTIONS'] : $V['L_DISPLAYING_OPTIONS']); ?></th>
	</tr>
	<tr>
		<td>
			<fieldset id="show-only">
			<legend><?php echo isset($L['SHOW_ONLY']) ? $L['SHOW_ONLY'] : (isset($SL['SHOW_ONLY']) ? $SL['SHOW_ONLY'] : $V['L_SHOW_ONLY']); ?></legend>
			<div class="pad_4">
				<label>
					<input id="only_new_posts" type="checkbox" <?php if (!empty($V['ONLY_NEW_POSTS_ON'])) { ?><?php echo isset($V['CHECKED']) ? $V['CHECKED'] : ''; ?><?php } ?>
						onclick="
							user.set('only_new', ( this.checked ? <?php echo defined('ONLY_NEW_POSTS') ? ONLY_NEW_POSTS : ''; ?> : 0 ), 365, true);
							$('#only_new_topics').attr('checked', false);
						" /><?php echo isset($L['ONLY_NEW_POSTS']) ? $L['ONLY_NEW_POSTS'] : (isset($SL['ONLY_NEW_POSTS']) ? $SL['ONLY_NEW_POSTS'] : $V['L_ONLY_NEW_POSTS']); ?>
				</label>
				<label>
					<input id="only_new_topics" type="checkbox" <?php if (!empty($V['ONLY_NEW_TOPICS_ON'])) { ?><?php echo isset($V['CHECKED']) ? $V['CHECKED'] : ''; ?><?php } ?>
						onclick="
							user.set('only_new', ( this.checked ? <?php echo defined('ONLY_NEW_TOPICS') ? ONLY_NEW_TOPICS : ''; ?> : 0 ), 365, true);
							$('#only_new_posts').attr('checked', false);
						" /><?php echo isset($L['ONLY_NEW_TOPICS']) ? $L['ONLY_NEW_TOPICS'] : (isset($SL['ONLY_NEW_TOPICS']) ? $SL['ONLY_NEW_TOPICS'] : $V['L_ONLY_NEW_TOPICS']); ?>
				</label>
			</div>
			</fieldset>
			<?php if (!empty($V['USER_HIDE_CAT'])) { ?>
			<fieldset id="user_hide_cat">
			<legend><?php echo isset($L['HIDE_CAT']) ? $L['HIDE_CAT'] : (isset($SL['HIDE_CAT']) ? $SL['HIDE_CAT'] : $V['L_HIDE_CAT']); ?></legend>
			<div id="h-cat-ctl" class="pad_4 nowrap">
				<form autocomplete="off">
					<?php

$h_c_count = ( isset($this->_tpldata['h_c.']) ) ?  sizeof($this->_tpldata['h_c.']) : 0;
for ($h_c_i = 0; $h_c_i < $h_c_count; $h_c_i++)
{
 $h_c_item = &$this->_tpldata['h_c.'][$h_c_i];
 $h_c_item['S_ROW_COUNT'] = $h_c_i;
 $h_c_item['S_NUM_ROWS'] = $h_c_count;

?>
					<label><input class="h-cat-cbx" type="checkbox" value="<?php echo isset($h_c_item['H_C_ID']) ? $h_c_item['H_C_ID'] : ''; ?>" <?php echo isset($h_c_item['H_C_CHEKED']) ? $h_c_item['H_C_CHEKED'] : ''; ?> /><?php echo isset($h_c_item['H_C_TITLE']) ? $h_c_item['H_C_TITLE'] : ''; ?></label>
					<?php

} // END h_c

if(isset($h_c_item)) { unset($h_c_item); } 

?>
				</form>
				<div class="spacer_6"></div>
				<div class="tCenter">
					<?php if (!empty($V['H_C_AL_MESS'])) { ?>
					<input style="width: 100px;" type="button" onclick="$('input.h-cat-cbx').attr('checked', false); $('input#sec_h_cat').click(); return false;" value="<?php echo isset($L['RESET']) ? $L['RESET'] : (isset($SL['RESET']) ? $SL['RESET'] : $V['L_RESET']); ?>">
					<?php } ?>
					<input id="sec_h_cat" type="button" onclick="set_h_cat();" style="width: 100px;" value="<?php echo isset($L['SUBMIT']) ? $L['SUBMIT'] : (isset($SL['SUBMIT']) ? $SL['SUBMIT'] : $V['L_SUBMIT']); ?>">
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
			<?php } ?>
		</td>
	</tr>
	</table>
</div><!--/only-new-options-->
<?php } ?>
<?php if (!empty($V['LAST_ADDED'])) { ?>
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
					<input type="checkbox" <?php if (!empty($V['POSTER'])) { ?><?php echo isset($V['CHECKED']) ? $V['CHECKED'] : ''; ?><?php } ?>
						onclick="user.set('poster', this.checked ? 1 : 0);"
					/>Показывать ленту постеров
				</label>
			</div>
			</fieldset>			
			<?php if (!empty($V['POSTER'])) { ?>
			<fieldset id="user_hide_poster">
			<legend>Скрыть постеры из разделов</legend>
			<div id="h-poster-ctl" class="pad_4 nowrap">
				<form autocomplete="off">
					<?php

$h_p_count = ( isset($this->_tpldata['h_p.']) ) ?  sizeof($this->_tpldata['h_p.']) : 0;
for ($h_p_i = 0; $h_p_i < $h_p_count; $h_p_i++)
{
 $h_p_item = &$this->_tpldata['h_p.'][$h_p_i];
 $h_p_item['S_ROW_COUNT'] = $h_p_i;
 $h_p_item['S_NUM_ROWS'] = $h_p_count;

?>
					<label><input class="h-poster-cbx" type="checkbox" value="<?php echo isset($h_p_item['H_C_ID']) ? $h_p_item['H_C_ID'] : ''; ?>" <?php echo isset($h_p_item['H_C_CHEKED']) ? $h_p_item['H_C_CHEKED'] : ''; ?> /><?php echo isset($h_p_item['H_C_TITLE']) ? $h_p_item['H_C_TITLE'] : ''; ?></label>
					<?php

} // END h_p

if(isset($h_p_item)) { unset($h_p_item); } 

?>
				</form>
				<div class="spacer_6"></div>
				<div class="tCenter">
					<?php if (!empty($V['H_P_AL_MESS'])) { ?>
					<input style="width: 100px;" type="button" onclick="$('input.h-poster-cbx').attr('checked',false); $('input#sec_h_poster').click(); return false;" value="Сбросить">
					<?php } ?>
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
			<?php } ?>		
		</td>
	</tr>
	<?php if (! $V['POSTER']) { ?>
	<tr>
		<td class="cat tCenter pad_4"><input type="button" value="<?php echo isset($L['SUBMIT']) ? $L['SUBMIT'] : (isset($SL['SUBMIT']) ? $SL['SUBMIT'] : $V['L_SUBMIT']); ?>" onclick="window.location.reload();" /></td>
	</tr>
	<?php } ?>
	</table>
</div>
<?php } ?>


<?php if ($V['LAST_ADDED'] && $V['POSTER'] && $V['LOGGED_IN'] && $V['LENTA']) { ?>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/misc/jquery.cluetip.js"></script>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/misc/jquery.scrollable.js"></script>
<script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/misc/jquery.mousewheel.js"></script>
<?php if (!empty($bb_cfg['new_poster'])) { ?><script type="text/javascript" src="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/js/misc/cvi_glossy_lib.js"></script><?php } ?>
<link type="text/css" rel="stylesheet" href="<?php echo isset($V['SITE_URL']) ? $V['SITE_URL'] : ''; ?>styles/templates/default/css/cluetip.css"/>
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
            <?php

$last_added_count = ( isset($this->_tpldata['last_added.']) ) ?  sizeof($this->_tpldata['last_added.']) : 0;
for ($last_added_i = 0; $last_added_i < $last_added_count; $last_added_i++)
{
 $last_added_item = &$this->_tpldata['last_added.'][$last_added_i];
 $last_added_item['S_ROW_COUNT'] = $last_added_i;
 $last_added_item['S_NUM_ROWS'] = $last_added_count;

?>
            <div class="load-local" rel="#loadme_<?php echo isset($last_added_item['TOPIC_ID']) ? $last_added_item['TOPIC_ID'] : ''; ?>" title="<?php echo isset($last_added_item['TITLE']) ? $last_added_item['TITLE'] : ''; ?>" onclick="top.location.href='viewtopic.php?t=<?php echo isset($last_added_item['TOPIC_ID']) ? $last_added_item['TOPIC_ID'] : ''; ?>';" onmouseover="initPostImages($('#loadme_<?php echo isset($last_added_item['TOPIC_ID']) ? $last_added_item['TOPIC_ID'] : ''; ?>'));">
				<a href="viewtopic.php?t=<?php echo isset($last_added_item['TOPIC_ID']) ? $last_added_item['TOPIC_ID'] : ''; ?>"><img src="thumb.php?t=<?php echo isset($last_added_item['TOPIC_ID']) ? $last_added_item['TOPIC_ID'] : ''; ?>" alt="" <?php if (!empty($bb_cfg['new_poster'])) { ?>onload="cvi_glossy.add(this,{radius:30,nogradient:true,angle:-33,shadow:30});"<?php } ?>></a>&nbsp;				
				<div style="display:none;" id="loadme_<?php echo isset($last_added_item['TOPIC_ID']) ? $last_added_item['TOPIC_ID'] : ''; ?>">
				    <center><var class="posterImg" title="<?php echo isset($last_added_item['POSTER_FULL']) ? $last_added_item['POSTER_FULL'] : ''; ?>" alt="" border="0">&#10;</var></center>
					<br /> <?php echo isset($L['FORUM']) ? $L['FORUM'] : (isset($SL['FORUM']) ? $SL['FORUM'] : $V['L_FORUM']); ?>: <b><?php echo isset($last_added_item['FORUM_NAME']) ? $last_added_item['FORUM_NAME'] : ''; ?></b> 
                    <br /> <?php echo isset($L['AUTHOR']) ? $L['AUTHOR'] : (isset($SL['AUTHOR']) ? $SL['AUTHOR'] : $V['L_AUTHOR']); ?>: <b><?php echo isset($last_added_item['USER_NAME']) ? $last_added_item['USER_NAME'] : ''; ?></b>
                    <br /> <?php echo isset($L['SIZE']) ? $L['SIZE'] : (isset($SL['SIZE']) ? $SL['SIZE'] : $V['L_SIZE']); ?>: <b><?php echo isset($last_added_item['SIZE']) ? $last_added_item['SIZE'] : ''; ?></b>
				</div>
			</div>	
            <?php

} // END last_added

if(isset($last_added_item)) { unset($last_added_item); } 

?>
        </div>
    </div>
    </td>
</tr>
</table>
<?php } ?>

<!--/menus-->

<!--page_content-->
<div id="page_content">
<table cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
<tr><?php if (!empty($V['SHOW_SIDEBAR1'])) { ?>
	<!--sidebar1-->
	<td id="sidebar1">
		<div id="sidebar1-wrap">
			<?php if (!empty($V['SHOW_BT_USERDATA'])) { ?><div id="user_ratio">
				<h3><?php echo isset($L['USER_RATIO']) ? $L['USER_RATIO'] : (isset($SL['USER_RATIO']) ? $SL['USER_RATIO'] : $V['L_USER_RATIO']); ?></h3>
				<table cellpadding="0">
					<div align="center"><?php echo isset($V['THIS_AVATAR']) ? $V['THIS_AVATAR'] : ''; ?></div>
					<tr><td><?php echo isset($L['USER_RATIO']) ? $L['USER_RATIO'] : (isset($SL['USER_RATIO']) ? $SL['USER_RATIO'] : $V['L_USER_RATIO']); ?></td><td><?php if ($V['DOWN_TOTAL_BYTES'] > $V['MIN_DL_BYTES']) { ?><b><?php echo isset($V['USER_RATIO']) ? $V['USER_RATIO'] : ''; ?></b><?php } else { ?><b><?php echo isset($L['NONE']) ? $L['NONE'] : (isset($SL['NONE']) ? $SL['NONE'] : $V['L_NONE']); ?></b> (DL < <?php echo isset($V['MIN_DL_FOR_RATIO']) ? $V['MIN_DL_FOR_RATIO'] : ''; ?>)<?php } ?></td></tr>
					<tr><td><?php echo isset($L['DOWNLOADED']) ? $L['DOWNLOADED'] : (isset($SL['DOWNLOADED']) ? $SL['DOWNLOADED'] : $V['L_DOWNLOADED']); ?></td><td class="leechmed"><b><?php echo isset($V['DOWN_TOTAL']) ? $V['DOWN_TOTAL'] : ''; ?></b></td></tr>
					<tr><td><?php echo isset($L['UPLOADED']) ? $L['UPLOADED'] : (isset($SL['UPLOADED']) ? $SL['UPLOADED'] : $V['L_UPLOADED']); ?></td><td class="seedmed"><b><?php echo isset($V['UP_TOTAL']) ? $V['UP_TOTAL'] : ''; ?></b></td></tr>
					<tr><td><?php echo isset($L['RELEASED']) ? $L['RELEASED'] : (isset($SL['RELEASED']) ? $SL['RELEASED'] : $V['L_RELEASED']); ?></td><td class="seedmed"><?php echo isset($V['RELEASED']) ? $V['RELEASED'] : ''; ?></td></tr>
					<tr><td><?php echo isset($L['BONUS']) ? $L['BONUS'] : (isset($SL['BONUS']) ? $SL['BONUS'] : $V['L_BONUS']); ?></td><td class="seedmed"><?php echo isset($V['UP_BONUS']) ? $V['UP_BONUS'] : ''; ?></td></tr>
					<?php if (!empty($bb_cfg['seed_bonus_enabled'])) { ?><tr><td><?php echo isset($L['SEED_BONUS']) ? $L['SEED_BONUS'] : (isset($SL['SEED_BONUS']) ? $SL['SEED_BONUS'] : $V['L_SEED_BONUS']); ?></td><td><a href="profile.php?mode=bonus"><span class="points bold"><?php echo isset($V['POINTS']) ? $V['POINTS'] : ''; ?></span></a></td></tr><?php } ?>
				</table>
			</div><?php } ?>
			<?php if (!empty($V['HTML_SIDEBAR_1'])) { ?>
				<?php include($V['HTML_SIDEBAR_1']); ?>
			<?php } ?>
			<img width="210" class="spacer" src="<?php echo isset($V['SPACER']) ? $V['SPACER'] : ''; ?>" alt="" />
		</div><!--/sidebar1-wrap-->
	</td><!--/sidebar1-->
<?php } ?>

<!--main_content-->
<td id="main_content">
	<div id="main_content_wrap">
		<div id="latest_news">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<?php if (!empty($V['SHOW_LATEST_NEWS'])) { ?>
					<td width="50%">
						<h3><?php echo isset($L['LATEST_NEWS']) ? $L['LATEST_NEWS'] : (isset($SL['LATEST_NEWS']) ? $SL['LATEST_NEWS'] : $V['L_LATEST_NEWS']); ?></h3>
						<table cellpadding="0">
							<?php

$news_count = ( isset($this->_tpldata['news.']) ) ?  sizeof($this->_tpldata['news.']) : 0;
for ($news_i = 0; $news_i < $news_count; $news_i++)
{
 $news_item = &$this->_tpldata['news.'][$news_i];
 $news_item['S_ROW_COUNT'] = $news_i;
 $news_item['S_NUM_ROWS'] = $news_count;

?>
							<tr>
								<td><div class="news_date"><?php echo isset($news_item['NEWS_TIME']) ? $news_item['NEWS_TIME'] : ''; ?></div></td>
								<td width="100%">
									<div class="news_title<?php if ($news_item['NEWS_IS_NEW']) { ?> new<?php } ?>"><a href="<?php echo isset($V['TOPIC_URL']) ? $V['TOPIC_URL'] : ''; ?><?php echo isset($news_item['NEWS_TOPIC_ID']) ? $news_item['NEWS_TOPIC_ID'] : ''; ?>"><?php echo isset($news_item['NEWS_TITLE']) ? $news_item['NEWS_TITLE'] : ''; ?></a></div>
								</td>
							</tr>
							<?php

} // END news

if(isset($news_item)) { unset($news_item); } 

?>
						</table>
					</td>
					<?php } ?>

					<?php if (!empty($V['SHOW_NETWORK_NEWS'])) { ?>
						<td width="50%">
						<h3><?php echo isset($L['NETWORK_NEWS']) ? $L['NETWORK_NEWS'] : (isset($SL['NETWORK_NEWS']) ? $SL['NETWORK_NEWS'] : $V['L_NETWORK_NEWS']); ?></h3>
						<table cellpadding="0">
							<?php

$net_count = ( isset($this->_tpldata['net.']) ) ?  sizeof($this->_tpldata['net.']) : 0;
for ($net_i = 0; $net_i < $net_count; $net_i++)
{
 $net_item = &$this->_tpldata['net.'][$net_i];
 $net_item['S_ROW_COUNT'] = $net_i;
 $net_item['S_NUM_ROWS'] = $net_count;

?>
							<tr>
								<td><div class="news_date"><?php echo isset($net_item['NEWS_TIME']) ? $net_item['NEWS_TIME'] : ''; ?></div></td>
								<td width="100%">
									<div class="news_title<?php if ($net_item['NEWS_IS_NEW']) { ?> new<?php } ?>"><a href="<?php echo isset($V['TOPIC_URL']) ? $V['TOPIC_URL'] : ''; ?><?php echo isset($net_item['NEWS_TOPIC_ID']) ? $net_item['NEWS_TOPIC_ID'] : ''; ?>"><?php echo isset($net_item['NEWS_TITLE']) ? $net_item['NEWS_TITLE'] : ''; ?></a></div>
								</td>
							</tr>
							<?php

} // END net

if(isset($net_item)) { unset($net_item); } 

?>
						</table>
					</td>
					<?php } ?>
				</tr>
			</table>
		</div>

<!--=======================-->
<?php } ?>
<!--***********************-->

<?php if (!empty($V['ERROR_MESSAGE'])) { ?>
<div class="info_msg_wrap">
<table class="error">
	<tr><td><div class="msg"><?php echo isset($V['ERROR_MESSAGE']) ? $V['ERROR_MESSAGE'] : ''; ?></div></td></tr>
</table>
</div>
<?php } ?>

<!-- page_header.tpl END -->
<!-- module_xx.tpl START -->
