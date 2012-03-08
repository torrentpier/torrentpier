<!-- IF AJAX_TOPICS -->
<script type="text/javascript">
ajax.openedPosts = {};

ajax.view_post = function(post_id, src) {
	if (!ajax.openedPosts[post_id]) {
		ajax.exec({
			action  : 'view_post',
			post_id : post_id
		});
	}
	else {
		var $post = $('#post_'+post_id);
		if ($post.is(':visible')) {
			$post.hide();
		}	else {
			$post.css({ display: '' });
		}
	}
	$(src).toggleClass('unfolded2');
};

ajax.callback.view_post = function(data) {
	var post_id = data.post_id;
	var $tor = $('#tor_'+post_id);
	window.location.href='#tor_'+post_id;
	$('#post-row tr')
		.clone()
		.attr({ id: 'post_'+post_id })
		.find('div.post_body').html(data.post_html).end()
		.find('a.tLink').attr({ href: $('a.tLink', $tor).attr('href') }).end()
		.find('a.dLink').attr({ href: $('a.tr-dl', $tor).attr('href') }).end()
		.insertAfter($tor)
	;
	initPostBBCode('#post_'+post_id);
	var maxH   = screen.height - 290;
	var maxW   = screen.width - 60;
	var $post  = $('div.post_wrap', $('#post_'+post_id));
	var $links = $('div.post_links', $('#post_'+post_id));
	$post.css({ maxWidth: maxW, maxHeight: maxH });
	$links.css({ maxWidth: maxW });
	if ($.browser.msie) {
		if ($post.height() > maxH) { $post.height(maxH); }
		if ($post.width() > maxW)  { $post.width(maxW); $links.width(maxW); }
	}
	ajax.openedPosts[post_id] = true;
};
</script>

<style type="text/css">
.post_wrap { border: 1px #A5AFB4 solid; margin: 8px 8px 6px; overflow: auto; }
.post_links { margin: 6px; }
</style>

<table id="post-row" style="display: none;">
<tr>
	<td class="row2" colspan="{TOR_COLSPAN}">
		<div class="post_wrap row1">
			<div class="post_body pad_6"></div><!--/post_body-->
			<div class="clear"></div>
		</div><!--/post_wrap-->
		<div class="post_links med bold tCenter"><a class="tLink">{L_OPEN_TOPIC}</a> &nbsp;&#0183;&nbsp; <a class="dLink">{L_DL_TORRENT}</a></div>
	</td>
</tr>
</table>
<!-- ENDIF / AJAX_TOPICS -->

<a name="start"></a>
<h1 class="pagetitle">{PAGE_TITLE}</h1>

<div class="nav">
	<p class="floatL"><a href="{U_INDEX}">{T_INDEX}</a></p>
	<!-- IF MATCHES --><p class="floatR">{MATCHES} {SERACH_MAX}</p><!-- ENDIF -->
	<div class="clear"></div>
</div>

<!-- IF TORHELP_TOPICS -->
	<!-- INCLUDE torhelp.tpl -->
	<div class="spacer_6"></div>
<!-- ENDIF / TORHELP_TOPICS -->

<!-- IF SHOW_SEARCH_OPT -->
<style type="text/css">
#fs-nav .b { font-weight: bold; }
#fs-nav li, #fs-nav-close { cursor: pointer; }
#fs-nav span.f:hover, #fs-nav-close:hover { color: blue; background: #DEE2E4; }
#fs-nav-list { border: 3px double #9AA7AD; background: #EFEFEF; padding: 8px; max-height: 500px; overflow: auto; }
#fs-sel-cat { min-width: 250px; max-width: 300px; }
#fs-sel-cat option.cat-title { font-weight: bold; color: #005A88; background: #F5F5F5; }
</style>

<div class="menu-sub" id="fs-nav-list">
	<div class="tRight"><span id="fs-nav-close" class="med" onclick="$('#fs-nav-list').hide();"> [ {L_HIDE} ] </span></div>
	<ul id="fs-nav" class="tree-root"></ul>
</div>

<form method="POST" name="post" action="{TOR_SEARCH_ACTION}#results">
{S_HIDDEN_FIELDS}

<table class="bordered w100" cellspacing="0">
<col class="row1">
<tr>
	<th class="thHead">{L_TOR_SEARCH_TITLE}</th>
</tr>
<tr>
	<td class="row4" style="padding: 4px";>

		<table class="fieldsets borderless bCenter pad_0" cellspacing="0">
		<tr>
			<td rowspan="2" width="50%">
				<fieldset>
				<legend>
					<select id="fs-sel-cat"><option value="all">&nbsp;{L_SELECT_CAT}&nbsp;</option></select>
					<span id="fs-nav-menu" style="display: none">&middot;&nbsp;<a class="menu-root" href="#fs-nav-list">{L_GO_TO_SECTION}</a></span>
				</legend>
				<div>
					<p class="select">{CAT_FORUM_SELECT}</p>
					<p><img width="300" class="spacer" src="{SPACER}" alt="" /></p>
				</div>
				</fieldset>
			</td>
			<td height="1" width="20%">
				<fieldset>
				<legend>{L_SORT_BY}</legend>
				<div class="med">
					<p class="select">{ORDER_SELECT}</p>
					<p class="radio"><label><input type="radio" name="{SORT_NAME}" value="{SORT_ASC}" {SORT_ASC_CHECKED} /> {L_ASC}</label></p>
					<p class="radio"><label><input type="radio" name="{SORT_NAME}" value="{SORT_DESC}" {SORT_DESC_CHECKED} /> {L_DESC}</label></p>
				</div>
				</fieldset>
				<fieldset>
				<legend>{L_TORRENTS_FROM}</legend>
				<div>
					<p class="select">{TIME_SELECT}</p>
				</div>
				</fieldset>
				<fieldset>
				<legend>{L_SEED_NOT_SEEN}</legend>
				<div>
					<p class="select">{S_NOT_SEEN_SELECT}</p>
				</div>
				</fieldset>
			</td>
			<td width="30%">
				<fieldset>
				<legend>{L_SHOW_ONLY}</legend>
				<div class="gen">
					<p class="chbox">{ONLY_MY_CHBOX}[<b>&reg;</b>]</p>
					<p class="chbox">{ONLY_ACTIVE_CHBOX}</p>
					<p class="chbox">{SEED_EXIST_CHBOX}</p>
					<p class="chbox">{ONLY_NEW_CHBOX}[{MINIPOST_IMG_NEW}]&nbsp;</p>
					<!-- IF $tr_cfg['gold_silver_enabled'] --><p class="chbox">{TOR_TYPE_CHBOX}</p><!-- ENDIF -->
				</div>
				</fieldset>
				<fieldset>
				<legend>{L_MY_DOWNLOADS}</legend>
				<div>
					<table class="borderless my_downloads" cellspacing="0">
					<tr>
						<td>{DL_COMPL_CHBOX}</td>
						<td>{DL_WILL_CHBOX}</td>
					</tr>
					<tr>
						<td>{DL_DOWN_CHBOX}</td>
						<td>{DL_CANCEL_CHBOX}</td>
					</tr>
					</table>
				</div>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td colspan="2" width="50%">
				<fieldset style="margin-top: 0;">
				<legend>{L_SHOW_COLUMN}</legend>
				<div>
					<p class="chbox">{SHOW_CAT_CHBOX}&nbsp; {SHOW_FORUM_CHBOX}&nbsp; {SHOW_AUTHOR_CHBOX}&nbsp; {SHOW_SPEED_CHBOX}&nbsp;</p>
				</div>
				</fieldset>
				<fieldset>
				<legend>{L_AUTHOR}</legend>
				<div>
					<p class="input"><input style="width: 40%" <!-- IF POSTER_ERROR -->style="color: red"<!-- ELSE --> class="post"<!-- ENDIF --> type="text" size="16" maxlength="{POSTER_NAME_MAX}" name="{POSTER_NAME_NAME}" value="{POSTER_NAME_VAL}" /> <input style="width: 40%;" type="button" value="{L_FIND_USERNAME}" onclick="window.open('{U_SEARCH_USER}', '_phpbbsearch', 'HEIGHT=250,resizable=yes,WIDTH=400'); return false;" /></p>
				</div>
				</fieldset>
				<fieldset>
				<legend>{L_TITLE_MATCH}</legend>
				<div>
					<p class="input">
						<input style="width: 95%;" class="post" type="text" size="50" maxlength="{TITLE_MATCH_MAX}" name="{TITLE_MATCH_NAME}" value="{TITLE_MATCH_VAL}" />
					</p>
					<p class="chbox med">
						{ALL_WORDS_CHBOX}
						<!-- IF $bb_cfg['search_help_url'] --> &middot; <a class="med" href="{$bb_cfg['search_help_url']}">{L_SEARCH_HELP_URL}</a><!-- ENDIF -->
					</p>
				</div>
				</fieldset>
			</td>
		</tr>
		</table>

	</td>
</tr>
<tr>
	<td class="row3 pad_4 tCenter">
		<input class="bold long" type="submit" name="submit" value="&nbsp;&nbsp;{L_SEARCH}&nbsp;&nbsp;" />
	</td>
</tr>
</table>

</form>

<div class="spacer_6"></div>

<!-- ENDIF / SHOW_SEARCH_OPT -->

<table class="w100 border bw_TRL" cellpadding="0" cellspacing="0">
<tr>
	<td class="cat pad_2">

	<table cellspacing="0" cellpadding="0" class="borderless w100">
	<tr>

		<td class="small bold nowrap tRight" width="100%" style="padding: 2px 8px 5px 4px;">
			&nbsp;
			<!-- IF LOGGED_IN -->
			<a class="menu-root" href="#tr-options">{L_DISPLAYING_OPTIONS}</a>
			<!-- ENDIF / LOGGED_IN -->
		</td>

	</tr>
	</table>

	</td>
</tr>
</table>

<!-- IF LOGGED_IN -->
<div class="menu-sub" id="tr-options">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th>{L_DISPLAYING_OPTIONS}</th>
	</tr>
	<tr>
		<td>
			<fieldset id="ajax-topics">
			<legend>{L_OPEN_TOPICS}</legend>
			<div class="med pad_4">
				<label>
					<input type="checkbox" <!-- IF AJAX_TOPICS -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('tr_t_ax', this.checked ? 1 : 0);"
					/>{L_OPEN_IN_SAME_WINDOW}
				</label>
				<label>
					<input type="checkbox" <!-- IF SHOW_TIME_TOPICS -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('tr_t_t', this.checked ? 1 : 0);"
					/>{L_SHOW_TIME_TOPICS}
				</label>
				<label>
					<input type="checkbox" <!-- IF SHOW_CURSOR -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('hl_tr', this.checked ? 1 : 0);"
					/>{L_SHOW_CURSOR}
				</label>
			</div>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td class="cat tCenter pad_4"><input type="button" value="{L_DO_SUBMIT}" style="width: 100px;" onclick="window.location.reload();" /></td>
	</tr>
	</table>
</div><!--/tr-options-->
<!-- ENDIF / LOGGED_IN -->

<a name="results"></a>
<table class="forumline tablesorter" id="tor-tbl">
<thead>
<tr>
	<th class="{sorter: false}">&nbsp;</th>
	<th class="{sorter: 'text'}">&nbsp;</th>
	<!-- IF SHOW_CAT -->
	<th class="{sorter: 'text'}" title="{L_CATEGORY}"><b class="tbs-text">{L_CATEGORY}</b></th>
	<!-- ENDIF -->
	<!-- IF SHOW_FORUM -->
	<th class="{sorter: 'text'}" width="25%" title="{L_FORUM}"><b class="tbs-text">{L_FORUM}</b></th>
	<!-- ENDIF -->
	<th class="{sorter: 'text'}" width="75%" title="{L_TOPIC}"><b class="tbs-text">{L_TOPIC}</b></th>
	<!-- IF SHOW_AUTHOR -->
	<th class="{sorter: 'text'}" title="{L_AUTHOR}"><b class="tbs-text">{L_AUTHOR}</b></th>
	<!-- ENDIF -->
	<th class="{sorter: 'digit'}" title="{L_SIZE}"><b class="tbs-text">{L_SIZE}</b></th>
	<th class="{sorter: 'digit'}" title="{L_SEEDERS}"><b class="tbs-text">S</b></th>
	<th class="{sorter: 'digit'}" title="{L_LEECHERS}"><b class="tbs-text">L</b></th>
	<th class="{sorter: 'digit'}" title="{L_COMPLETED} / {L_REPLIES}"><b class="tbs-text">C</b></th>
	<!-- IF SHOW_SPEED -->
	<th class="{sorter: false }" title="{L_DL_SPEED}"><b class="tbs-text">SP</b></th>
	<!-- ENDIF -->
	<th class="{sorter: 'digit'}" title="{L_ADDED}"><b class="tbs-text">{L_ADDED}</b></th>
</tr>
</thead>
<!-- BEGIN tor -->
<tr class="tCenter <!-- IF SHOW_CURSOR -->hl-tr<!-- ENDIF -->" id="tor_{tor.POST_ID}">
	<td class="row1"><!-- IF tor.USER_AUTHOR --><p style="padding-bottom: 3px">&nbsp;<b>&reg;</b>&nbsp;</p><!-- ELSEIF tor.IS_NEW -->{MINIPOST_IMG_NEW}<!-- ELSE -->{MINIPOST_IMG}<!-- ENDIF --></td>
	<td class="row1 tCenter" title="{tor.TOR_STATUS_TEXT}">{tor.TOR_STATUS_ICON}</td>
	<!-- IF SHOW_CAT -->
	<td class="row1"><a class="gen" href="{TR_CAT_URL}{tor.CAT_ID}">{tor.CAT_TITLE}</a></td>
	<!-- ENDIF -->
	<!-- IF SHOW_FORUM -->
	<td class="row1"><a class="gen" href="{TR_FORUM_URL}{tor.FORUM_ID}">{tor.FORUM_NAME}</a></td>
	<!-- ENDIF -->
	<td class="row4 med tLeft">
		<a class="{tor.DL_CLASS}<!-- IF AJAX_TOPICS --> folded2 tLink<!-- ENDIF -->" <!-- IF AJAX_TOPICS -->onclick="ajax.view_post({tor.POST_ID}, this); return false;"<!-- ENDIF --> href="{TOPIC_URL}{tor.TOPIC_ID}"><!-- IF tor.TOR_FROZEN -->{tor.TOPIC_TITLE}<!-- ELSE -->{tor.TOR_TYPE}<b>{tor.TOPIC_TITLE}</b><!-- ENDIF --></a>
	    <!-- IF SHOW_TIME_TOPICS --><div class="tr_tm">{tor.TOPIC_TIME}</div><!-- ENDIF -->
	</td>
	<!-- IF SHOW_AUTHOR -->
	<td class="row1"><a class="med" href="{TR_POSTER_URL}{tor.POSTER_ID}">{tor.USERNAME}</a></td>
	<!-- ENDIF -->
	<td class="row4 small nowrap">
		<u>{tor.TOR_SIZE_RAW}</u>
		<!-- IF not tor.TOR_FROZEN --><a class="small tr-dl" title="{L_DOWNLOAD}" href="{DOWNLOAD_URL}{tor.ATTACH_ID}">{tor.TOR_SIZE}</a> <!-- IF MAGNET_LINKS --><span title="{L_MAGNET}">{tor.MAGNET}</span><!-- ENDIF --><!-- ELSE -->
		{tor.TOR_SIZE}<!-- ENDIF -->
	</td>
	<td class="row4 seedmed" title="{tor.SEEDS_TITLE}"><b>{tor.SEEDS}</b></td>
	<td class="row4 leechmed" title="{L_LEECHERS}"><b>{tor.LEECHS}</b></td>
	<td class="row4 small" title="{L_REPLIES}: {tor.REPLIES}">{tor.COMPLETED}</td>
	<!-- IF SHOW_SPEED -->
	<td class="row4 nowrap">
		<p class="seedmed">{tor.UL_SPEED}</p>
		<p class="leechmed">{tor.DL_SPEED}</p>
	</td>
	<!-- ENDIF -->
	<td class="row4 small nowrap" style="padding: 1px 3px 2px;" title="{L_ADDED}">
		<u>{tor.ADDED_RAW}</u>
		<p>{tor.ADDED_TIME}</p>
		<p>{tor.ADDED_DATE}</p>
	</td>
</tr>
<!-- END tor -->

<!-- IF TOR_NOT_FOUND -->
<tbody>
<tr>
	<td class="row1 tCenter pad_8" colspan="{TOR_COLSPAN}">{NO_MATCH_MSG}</td>
</tr>
</tbody>
<!-- ENDIF / TOR_NOT_FOUND -->
<tfoot>
<tr>
	<td class="catBottom" colspan="{TOR_COLSPAN}">&nbsp;</td>
</tr>
</tfoot>
</table>

<div class="bottom_info">

	<div class="nav">
		<p style="float: left">{PAGE_NUMBER}</p>
		<p style="float: right">{PAGINATION}</p>
		<div class="clear"></div>
	</div>

	<div class="spacer_4"></div>

	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->

<script type="text/javascript">
//$.each( [7], function(i,n){ $('#fs-'+ n).attr('selected', 1) });
$('#search_opt, #search-results').show();

var fs_all = '';
var fs_og = [];
var fs_lb = [];
var show_fs_nav = true;

$(document).ready(function(){
	$('#fs optgroup').each(function(i){
		var $og = $(this);
		$og.attr({ id: 'og-'+i });
		fs_og[i] = $(this).html();
		fs_lb[i] = $(this).attr('label');
		$('#fs-sel-cat').append('<option class="cat-title" value="'+ i +'">&nbsp;&nbsp;&middot;&nbsp;'+ fs_lb[i] +'&nbsp;</option>');
		if (show_fs_nav) {
			$('#fs-nav').append('<li><span class="b" onclick="$(\'#fs\').scrollTo(\'#og-'+i+'\')">'+ fs_lb[i] +'</span><ul id="nav-c-'+ i +'"></ul></li>');
			$('option', $og).each(function(){
				var $op = $(this);
				if ($op[0].className) {
					$('<li><span class="f">'+ $op.html() +'</span></li>').appendTo('#nav-c-'+ i).click(function(){
						$('#fs').scrollTo( '#'+$op.attr('id'), { axis:'y' } ).scrollTo( '-=5px' );
						$('#fs option').attr({ selected: 0 });
						$('#'+$op.attr('id')).attr({ selected: 1 });
					});
				}
			});
		}
	});

	if (show_fs_nav) {
		$('#fs-nav-menu').show();
		$('#fs-nav').treeview({ collapsed: true });
	}
	else {
		$('#fs-nav-menu').remove();
	}

	$('#fs-sel-cat').bind('change', function(){
		var i = $(this).val();
		// еб..я опера не понимает <optgroup> при популяции селекта [http://dev.jquery.com/ticket/3040]
		if ($.browser.opera) {
			if (i == 'all') {
				$('#fs').empty().append('<option id="fs--1" value="-1">&nbsp;{L_ALL_AVAILABLE}</option>');
				$.each(fs_og, function(i, v){
				 $('#fs').append( $(document.createElement('optgroup')).attr('label', fs_lb[i]).append(fs_og[i]) );
				});
				$('#fs-nav-menu').show();
			}
			else {
				$('#fs').empty().append( $(document.createElement('optgroup')).attr('label', fs_lb[i]).append(fs_og[i]) );
				$('#fs-nav-menu').hide();
			}
		}
		else {
			if (i == 'all') {
				var fs_html = fs_all;
				$('#fs-nav-menu').show();
			}
			else {
				var fs_html = '<optgroup label="'+ fs_lb[i] +'">'+ fs_og[i] +'</optgroup>';
				$('#fs-nav-menu').hide();
			}
			$('#fs').html(fs_html).focus();
		}
	});

	fs_all = $('#fs').html();
	$('#fs').css({width: $('#fs').width() +'px'});
});
</script>