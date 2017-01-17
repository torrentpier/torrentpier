<!-- IF AUTH_MOD -->
<!-- IF SESSION_ADMIN -->

<script type="text/javascript">
ajax.in_moderation    = false;
ajax.in_title_edit    = false;
ajax.tte_cur_topic_id = 0;
ajax.tte_orig_html    = '';

<!-- IF MODERATION_ON -->
$(function(){
	show_forum_mod_options();
});
<!-- ELSE -->
$(function(){
	$('#show_mod_options a').click( function(){ show_forum_mod_options(); return false; } );

	$('td.topic_id').click(function(){
		if (!ajax.in_moderation) {
			show_forum_mod_options();
		}
	});
	$('td.tt').dblclick(function(){
		if (!ajax.in_moderation) {
			show_forum_mod_options();
			$(this).dblclick();
		}
	});
});
<!-- ENDIF -->

function show_forum_mod_options ()
{
	$('td.topic_id').each(function(){
		var topic_id = $(this).attr('id');
		var input = '<input id="sel-'+ topic_id +'" type="checkbox" value="'+ topic_id +'" class="topic-chbox" />';

		$(this).before('<td>'+input+'</td>').attr('colSpan', 1);
		$(this).click(function(){ edit_topic_title(topic_id); });
		$(this).siblings('td.tt').dblclick(function(){ edit_topic_title(topic_id); });
	});
	$('.tt-text').addClass('folded2 tLink')
		.click(function(){ ajax.view_post($(this).attr('id').substr(3), this); return false; });
	$('input.topic-chbox').click(function(){
		$('#tt-'+this.value).toggleClass('hl-tt');
	});
	$('#mod-action-cell').append( $('#mod-action-content')[0] );
	$('#show_mod_options').html($('#show_mod_options').text());
	$('#mod-action-row, #mod-action-content, #mod-sel-topics').show();

	$('#mod-action').submit(function(){
		var $form = $(this);
		var in_new_window = $('#in-new-window').attr('checked');
		if(!mod_action('go')) return false;
		$('input[name~=topic_id_list]', $form).remove();
		$('input.topic-chbox:checked').each(function(){
			$form.append('<input type="hidden" name="topic_id_list[]" value="'+ this.value +'" />');
			if (in_new_window) {
				$('#tr-'+this.value).remove();
			}
		});
		if (in_new_window) {
			$form.attr('target', '_blank');
		}
	});
	ajax.in_moderation = true;
}

function edit_topic_title (topic_id)
{
	if (ajax.in_title_edit) return false;

	var $tt_td = $('td#'+topic_id).siblings('td.tt');
	var tt_text = $tt_td.find('.tt-text').text();

	$tt_td.attr({id: 'tte-'+topic_id});
	ajax.tte_cur_topic_id = topic_id;
	ajax.tte_orig_html = $tt_td.html();

	$tt_td.html( $('#tt-edit-tpl').html() );
	$('.tt-edit-input', $tt_td).val(tt_text).focus();

	ajax.in_title_edit = true;
}

function tte_submit (mode)
{
	var topic_id = ajax.tte_cur_topic_id;
	var $tt_td = $('#tte-'+topic_id);
	var topic_title = $('.tt-edit-input', $tt_td).val();

	if (mode === 'save') {
		ajax.edit_topic_title(topic_id, topic_title);
	}
	else {
		$tt_td.html(ajax.tte_orig_html);
		$('.tt-text').addClass('folded2 tLink')
			.click(function(){ ajax.view_post(topic_id, this); return false; });
	}
	ajax.in_title_edit = false;
}

ajax.edit_topic_title = function(topic_id, topic_title) {
	ajax.exec({
	    action      : 'mod_action',
		mode        : 'edit_topic_title',
		topic_id    : topic_id,
		topic_title : topic_title
	});
};

function mod_action (mode)
{
	var topics = 0;
	$('input.topic-chbox:checked').each(function(){
		topics += ','+ this.value;
	});
	if(!topics){
		alert('{L_NONE_SELECTED}');
		return false;
	}
	if(mode === 'tor_status'){
		status = $('#st option:selected').val();
		if(status === '-1'){
			alert('{L_TOR_STATUS_NOT_SELECT}');
			return false;
		}
		ajax.mod_action(topics, mode, status);
	}
	return true;
}

ajax.mod_action = function(topic_ids, mode, status) {
	ajax.exec({
		action    : 'mod_action',
		mode      : mode,
		topic_ids : topic_ids,
		status    : status
	});
};

ajax.callback.mod_action = function(data) {
	if(data.topics) {
		for(i=0; i < data.topics.length; i++) {
			$('#status-'+ data.topics[i]).html(data.status);
		}
	}
	if(data.topic_title) {
		var $tt_td = $('#tte-'+data.topic_id);
		$tt_td.html(ajax.tte_orig_html);
		$('.tt-text', $tt_td).html(data.topic_title);
	}
};
</script>

<div id="mod-action-content" style="display: none;">
<form id="mod-action" method="post" action="modcp.php" class="tokenized">
	<input type="hidden" name="f" value="{FORUM_ID}" />
	<div class="floatL">
	<input type="checkbox" onclick="$('.topic-chbox').attr({ checked: this.checked }); if(this.checked){$('.tt-text').addClass('hl-tt');}else{$('.tt-text').removeClass('hl-tt');}" />
	<!-- IF TORRENTS -->
	{SELECT_ST}
	<input type="button" onclick="mod_action('tor_status');" value="{L_EDIT}" />
	<!-- ENDIF -->
	</div>
	<div class="floatR">
	<input type="submit" name="delete" value="{L_DELETE}" />
	<input type="submit" name="move" value="{L_MOVE}" />
	<input type="submit" name="lock" value="{L_LOCK}" />
	<input type="submit" name="unlock" value="{L_UNLOCK}" />
	<input type="submit" name="post_pin" value="{L_POST_PIN}" />
	<input type="submit" name="post_unpin" value="{L_POST_UNPIN}" />
	<label><input id="in-new-window" type="checkbox" />{L_NEW_WINDOW}</label>
	</div>
</form>
</div>

<div id="tt-edit-tpl" style="display: none;">
	<div class="tt-edit" style="padding: 4px;">
		<textarea class="tt-edit-input" rows="2" cols="50" style="width: 98%; height: 35px;"></textarea>
		<input type="button" value="{L_SAVE}" onclick="tte_submit('save'); return false;" />
		<input type="button" value="{L_CANCEL}" onclick="tte_submit('cancel'); return false;" />
	</div>
</div>

<script type="text/javascript">
ajax.openedPosts = {};

ajax.view_post = function(topic_id, src) {
	if (!ajax.openedPosts[topic_id]) {
		ajax.exec({
			action   : 'view_post',
			topic_id : topic_id
		});
	}
	else {
		var $post = $('#post_'+topic_id);
		if ($post.is(':visible')) {
			$post.hide();
		}	else {
			$post.css({ display: '' });
		}
	}
	$(src).toggleClass('unfolded2');
};

ajax.callback.view_post = function(data) {
	var topic_id = data.topic_id;
	var $tor = $('#tr-'+topic_id);
	window.location.href='#tr-'+topic_id;
	$('#post-row tr')
		.clone()
		.attr({ id: 'post_'+topic_id })
		.find('div.post_body').html(data.post_html).end()
		.find('a.tLink').attr({ href: $('a.tLink', $tor).attr('href') }).end()
		.insertAfter($tor)
	;
	initPostBBCode('#post_'+topic_id);
	var maxH   = screen.height - 290;
	var maxW   = screen.width - 60;
	var $post  = $('div.post_wrap', $('#post_'+topic_id));
	var $links = $('div.post_links', $('#post_'+topic_id));
	$post.css({ maxWidth: maxW, maxHeight: maxH });
	$links.css({ maxWidth: maxW });
	if ($.browser.msie) {
		if ($post.height() > maxH) { $post.height(maxH); }
		if ($post.width() > maxW)  { $post.width(maxW); $links.width(maxW); }
	}
	ajax.openedPosts[topic_id] = true;
};
</script>

<style type="text/css">
.post_wrap { border: 1px #A5AFB4 solid; margin: 8px 8px 6px; overflow: auto; }
.post_links { margin: 6px; }
.unfolded2, .folded2 { display: inline !important; }
</style>

<table id="post-row" style="display: none;">
<tr>
	<td class="row2" colspan="7">
		<div class="post_wrap row1">
			<div class="post_body pad_6"></div>
			<div class="clear"></div>
		</div>
		<div class="post_links med bold tCenter"><a class="tLink">{L_OPEN_TOPIC}</a></div>
	</td>
</tr>
</table>

<!-- ELSE -->
<script type="text/javascript">
$(function(){
	$('#show_mod_options a').attr('href', '{MOD_REDIRECT_URL}');
});
</script>
<!-- ENDIF / !SESSION_ADMIN -->

<style type="text/css">
.tor-time { font-size: 10px; padding-left: 2px; }
td.topic_id { cursor: pointer; }
.tt-edit-input { font-size: 11px; }
.hl-tt, a.hl-tt, a.hl-tt:visited { color: #9E0000; }
</style>
<!-- ENDIF / AUTH_MOD -->

<table width="100%">
	<tr>
		<td valign="bottom">
			<h1 class="maintitle"><a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></h1>

			<p class="small" id="moderators"><a style="text-decoration: none;" href="#">{L_MODERATORS}</a></p>
			<script type="text/javascript">
				$(document).ready(function(){
					$("#moderators a").one('click', function(){
						$('#moderators').html($('#moderators').text());
						ajax.index_data();
						return false;
					});
				});
				ajax.index_data = function() {
					ajax.exec({
						action   : 'index_data',
						mode     : 'get_forum_mods',
						forum_id : {FORUM_ID}
					});
				};
				ajax.callback.index_data = function(data) {
					$('#moderators').append(data.html);
				};
			</script>

			<!-- IF SHOW_ONLINE_LIST -->
			<p class="small">{LOGGED_IN_USER_LIST}</p>
			<!-- ENDIF -->
		</td>
		<td class="tRight vBottom nowrap small"><b>{PAGINATION}</b></td>
	</tr>
</table>

<table width="100%">
	<tr>
		<td><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" alt="{T_POST_NEW_TOPIC}" /></a></td>
		<td class="nav" width="100%">
			&nbsp;<a href="{U_INDEX}">{L_HOME}</a>&nbsp;<em>&raquo;</em>
			<a href="{U_VIEWCAT}">{CAT_TITLE}</a>
			<!-- IF PARENT_FORUM_NAME --><em>&raquo;</em>&nbsp;<a href="{PARENT_FORUM_HREF}">{PARENT_FORUM_NAME}</a><!-- ENDIF -->
			<em>&raquo;</em>&nbsp;<a href="{U_VIEW_FORUM}">{FORUM_NAME}</a>
		</td>
	</tr>
</table>

<!-- IF SHOW_SUBFORUMS -->
<table class="forumline forum">
<col class="row1">
<col class="row1" width="65%">
<col class="row2" width="10%">
<col class="row2" width="10%">
<col class="row2" width="15%">
<tr>
	<th colspan="2">{L_FORUM}</th>
	<th>{L_TOPICS}</th>
	<th>{L_POSTS_SHORT}</th>
	<th>{L_LASTPOST}</th>
</tr>
<!-- BEGIN f -->
<tr>
	<td class="f_icon"><img class="forum_icon" src="{f.FORUM_FOLDER_IMG}" /></td>
	<td class="pad_4">{f.TOPIC_TYPE}
		<h4 class="forumlink"><a href="{f.U_VIEWFORUM}">{f.FORUM_NAME}</a></h4>
		<!-- IF f.FORUM_DESC --><p class="forum_desc">{f.FORUM_DESC}</p><!-- ENDIF -->
	</td>
	<td class="med tCenter">{f.TOPICS}</td>
	<td class="med tCenter">{f.POSTS}</td>
	<td class="small tCenter" nowrap="nowrap" style="padding: 4px 8px;">
		<!-- BEGIN last -->
			<!-- IF f.last.FORUM_LAST_POST -->

				<!-- IF f.last.SHOW_LAST_TOPIC -->

				<h6 class="last_topic">
					<a title="{f.last.LAST_TOPIC_TIP}" href="{TOPIC_URL}{f.last.LAST_TOPIC_ID}{NEWEST_URL}">{f.last.LAST_TOPIC_TITLE}</a>
					<a href="{POST_URL}{f.last.LAST_POST_ID}#{f.last.LAST_POST_ID}">{ICON_LATEST_REPLY}</a>
				</h6>
				<p class="small" style="margin-top:4px;">
					{f.last.LAST_POST_TIME}
					&middot;
					{f.last.LAST_POST_USER}
				</p>

				<!-- ELSE / start of !f.last.SHOW_LAST_TOPIC -->

				<p class="small">{f.last.LAST_POST_TIME}</p>
				<p class="small" style="margin-top:3px;">
					{f.last.LAST_POST_USER}
					<a href="{POST_URL}{f.last.LAST_POST_ID}#{f.last.LAST_POST_ID}">{ICON_LATEST_REPLY}</a>
				</p>

				<!-- ENDIF / !f.last.SHOW_LAST_TOPIC -->
			<!-- ELSE -->
			<span class="med">{L_NO_POSTS}</span>
			<!-- ENDIF -->
		<!-- END last -->
	</td>
</tr>
<!-- END f -->
<tr>
	<td colspan="5" class="spaceRow"><div class="spacer_6"></div></td>
</tr>
</table>
<div class="spacer_6"></div>

<!-- ENDIF / SHOW_SUBFORUMS -->

<table class="forumline">
<tr>
	<td class="cat bw_TRL pad_2">

	<table cellspacing="0" cellpadding="0" class="borderless w100">
	<tr>
		<!-- IF AUTH_MOD -->
		<td class="small bold nowrap" style="padding: 0 0 0 4px;">
			<span id="show_mod_options"><a href="#" class="small bold">{L_MODERATE_FORUM}</a></span>
		</td>
		<td class="med" style="padding: 0 4px 2px 4px;color:#CDCDCD">|</td>
		<td class="small nowrap" style="padding: 0;">{L_TOPICS_PER_PAGE}:</td>
		<td class="small nowrap" style="padding: 0 0 0 3px;">
			<form id="tpp" action="{PAGE_URL_TPP}" method="post">{SELECT_TPP}</form>
		</td>
		<!-- IF TORRENTS -->
		<td class="small nowrap" style="padding: 0 0 0 6px;">{L_STATUS}:</td>
		<td class="small nowrap" style="padding: 0 0 0 3px;">{SELECT_TST}</td>
		<!-- ENDIF -->
		<td class="small nowrap" style="padding: 0 0 0 3px;">&nbsp;<input id="tst-submit-btn" type="button" class="bold" value="&raquo;" style="width: 30px;" onclick="mod_goto(); return false;" /></td>
		<script type="text/javascript">
		function mod_goto(){
			window.location = '{MOD_URL}' +'&tpp='+ $('#tpp').val() <!-- IF TORRENTS -->+'&tst='+ $('#tst').val()<!-- ENDIF --> +'&mod=1';
		}
		$(function(){
			$('#tst').bind('change', function(){ $('#tst-submit-btn').attr({disabled: 1}); mod_goto(); });
		});
		</script>
		<!-- ENDIF / AUTH_MOD -->

		<td class="small bold nowrap tRight w100">
			&nbsp;
			<!-- IF LOGGED_IN -->
			<a class="small" href="#" onclick="return post2url('feed.php', {mode: 'get_feed_url', type: 'f', id: '{FORUM_ID}'})">{FEED_IMG} {L_ATOM_SUBSCRIBE}</a>&nbsp;&#0183;
			<a class="small" href="{U_SEARCH_SELF}">{L_MY_POSTS}</a>&nbsp;&#0183;
			<a class="menu-root" href="#only-new-options">{L_DISPLAYING_OPTIONS}</a>
			<!-- ENDIF / LOGGED_IN -->
		</td>

		<td class="nowrap" style="padding: 0 4px 2px 4px;">
			<form action="{PAGE_URL}" method="post">
				<input id="search-text" type="text" name="nm"
				<!-- IF TITLE_MATCH -->
					value="{TITLE_MATCH}" required <!-- IF FOUND_TOPICS -->class="found"<!-- ELSE -->class="error"<!-- ENDIF -->
				<!-- ELSE -->
					placeholder="{L_TITLE_SEARCH_HINT}" required class="hint"
				<!-- ENDIF -->
				style="width: 150px;" />
				<input type="submit" class="bold" value="&raquo;" style="width: 30px;" />
			</form>
		</td>
	</tr>
	</table>

	</td>
</tr>
</table>

<!-- IF TORRENTS -->
<table class="forumline forum" id="forum-table">
<col class="row1">
<col class="row1">
<col class="row1" width="75%">
<col class="row2" width="5%">
<col class="row2" width="5%">
<col class="row2" width="15%">
<tr>
	<th colspan="3">{L_TOPICS}</th>
	<th>{L_TORRENT}</th>
	<th>{L_REPLIES_SHORT}</th>
	<th>{L_LASTPOST}</th>
</tr>
<!-- BEGIN t -->
<!-- IF t.TOPICS_SEPARATOR -->
<tr>
	<td colspan="6" class="row3 topicSep">{t.TOPICS_SEPARATOR}</td>
</tr>
<!-- ENDIF -->
<tr id="tr-{t.TOPIC_ID}">
	<td colspan="2" id="{t.TOPIC_ID}" class="topic_id"><img class="topic_icon" src="{t.TOPIC_ICON}" /></td>

	<td style="padding: 2px 5px 3px 3px;" class="tt">
	<div class="torTopic">
		<!-- IF t.TOR_STATUS_ICON --><span id="status-{t.TOPIC_ID}" title="{t.TOR_STATUS_TEXT}">{t.TOR_STATUS_ICON}</span>&#0183;<!-- ENDIF -->
		<!-- IF t.IS_UNREAD --><a href="{TOPIC_URL}{t.HREF_TOPIC_ID}{NEWEST_URL}">{ICON_NEWEST_REPLY}</a><!-- ENDIF -->
		<!-- IF t.STATUS == MOVED --><span class="topicMoved">{L_TOPIC_MOVED}</span>
			<!-- ELSEIF t.DL_CLASS --><span class="{t.DL_CLASS} iconDL"><b>{L_TOPIC_DL}</b></span>
		<!-- ENDIF -->
		<!-- IF t.POLL --><span class="topicPoll">{L_TOPIC_POLL}</span><!-- ENDIF -->
		<!-- IF t.TOR_STALED || t.TOR_FROZEN -->
			<!-- IF t.ATTACH --><span>{TOPIC_ATTACH_ICON}</span><!-- ENDIF -->
			<a id="tt-{t.TOPIC_ID}" href="{TOPIC_URL}{t.HREF_TOPIC_ID}" class="gen tt-text">{t.TOPIC_TITLE}</a>
		<!-- ELSE -->
			{t.TOR_TYPE}<a id="tt-{t.TOPIC_ID}" href="{TOPIC_URL}{t.HREF_TOPIC_ID}" class="torTopic tt-text"><b>{t.TOPIC_TITLE}</b></a>
		<!-- ENDIF -->
		<!-- IF t.PAGINATION --><span class="topicPG">[{ICON_GOTOPOST}{L_GOTO_SHORT} {t.PAGINATION} ]</span><!-- ENDIF -->
	</div>
	<div class="topicAuthor nowrap" style="padding-top: 2px;">
		{t.TOPIC_AUTHOR}
	</div>
	</td>

	<td class="tCenter nowrap" style="padding: 2px 4px;">
	<!-- BEGIN tor -->
		<div title="{L_DL_TORRENT}">
			<div><span class="seedmed" title="Seeders"><b>{t.tor.SEEDERS}</b></span><span class="med"> | </span><span class="leechmed" title="Leechers"><b>{t.tor.LEECHERS}</b></span></div>
			<div style="padding-top: 2px" class="small"><!-- IF t.TOR_FROZEN -->{t.tor.TOR_SIZE}<!-- ELSE --><a href="{DOWNLOAD_URL}{t.TOPIC_ID}" class="small" style="text-decoration: none">{t.tor.TOR_SIZE}</a> <!-- IF MAGNET_LINKS -->{t.tor.MAGNET}<!-- ENDIF --><!-- ENDIF --></div>
		</div>
	<!-- END tor -->
	</td>

	<td class="tCenter small nowrap" style="padding: 3px 4px 2px;">
	<p>
		<span title="{L_REPLIES}">{t.REPLIES}</span>
		<span class="small"> | </span>
		<span title="{L_VIEWS}">{t.VIEWS}</span>
	</p>
	<!-- BEGIN tor -->
	<p style="padding-top: 2px" class="med" title="{L_COMPLETED}">
		<b>{t.tor.COMPL_CNT}</b>
	</p>
	<!-- END tor -->
	</td>

	<td class="tCenter small nowrap" style="padding: 3px 6px 2px;">
		<p>{t.LAST_POST_TIME}</p>
		<p style="padding-top: 2px">
			{t.LAST_POSTER}
			<a href="{POST_URL}{t.LAST_POST_ID}#{t.LAST_POST_ID}">{ICON_LATEST_REPLY}</a>
		</p>
	</td>
</tr>
<!-- END t -->
<!-- IF NO_TOPICS -->
<tr>
	<td colspan="6" class="row1 pad_10 tCenter">{NO_TOPICS}</td>
</tr>
<!-- ENDIF / NO_TOPICS -->
<!-- IF SESSION_ADMIN -->
<tr id="mod-action-row" style="display: none;">
	<td colspan="6" id="mod-action-cell" class="row5 med tCenter pad_4"></td>
</tr>
<!-- ENDIF -->
<tr>
	<td colspan="6" class="catBottom med pad_4">
	<!-- IF LOGGED_IN -->
	<form method="post" action="{S_POST_DAYS_ACTION}">
		{L_DISPLAY_TOPICS}: {S_SELECT_TOPIC_DAYS} {S_DISPLAY_ORDER}
		<input type="submit" value="{L_GO}" />
	</form>
	<!-- ELSE -->
	&nbsp;
	<!-- ENDIF -->
	</td>
</tr>
</table>

<!-- ELSE / start of !TORRENTS -->

<table class="forumline forum" id="forum-table">
<col class="row1">
<col class="row1">
<col class="row1" width="60%">
<col class="row2" width="3%">
<col class="row2" width="10%">
<col class="row2" width="7%">
<col class="row2" width="20%">
<tr>
	<th colspan="3">{L_TOPICS}</th>
	<th>{L_REPLIES}</th>
	<th>{L_AUTHOR}</th>
	<th>{L_VIEWS}</th>
	<th>{L_LASTPOST}</th>
</tr>
<!-- BEGIN t -->
<!-- IF t.TOPICS_SEPARATOR -->
<tr>
	<td colspan="7" class="row3 topicSep">{t.TOPICS_SEPARATOR}</td>
</tr>
<!-- ENDIF -->
<tr id="tr-{t.TOPIC_ID}">
	<td colspan="2" id="{t.TOPIC_ID}" class="topic_id"><img class="topic_icon" src="{t.TOPIC_ICON}" /></td>
	<td class="tt">
		<span class="topictitle">
			<!-- IF t.IS_UNREAD --><a href="{TOPIC_URL}{t.HREF_TOPIC_ID}{NEWEST_URL}">{ICON_NEWEST_REPLY}</a><!-- ENDIF -->
			<!-- IF t.STATUS == MOVED --><span class="topicMoved">{L_TOPIC_MOVED}</span>
				<!-- ELSEIF t.DL --><span class="">{L_TOPIC_DL}</span>
				<!-- ELSEIF t.ATTACH -->{TOPIC_ATTACH_ICON}
			<!-- ENDIF -->
			<!-- IF t.POLL --><span class="topicPoll">{L_TOPIC_POLL}</span><!-- ENDIF -->
			<a id="tt-{t.TOPIC_ID}" href="{TOPIC_URL}{t.HREF_TOPIC_ID}" class="topictitle tt-text">{t.TOPIC_TITLE}</a>
		</span>
		<!-- IF t.PAGINATION --><span class="topicPG">[{ICON_GOTOPOST}{L_GOTO_SHORT} {t.PAGINATION} ]</span><!-- ENDIF -->
	</td>
	<td class="tCenter med">{t.REPLIES}</td>
	<td class="tCenter med nowrap">{t.TOPIC_AUTHOR}</td>
	<td class="tCenter med">{t.VIEWS}</td>
	<td class="tCenter nowrap small" style="padding: 1px 6px 2px;">
		<p>{t.LAST_POST_TIME}</p>
		<p>
			{t.LAST_POSTER}
			<a href="{POST_URL}{t.LAST_POST_ID}#{t.LAST_POST_ID}">{ICON_LATEST_REPLY}</a>
		</p>
	</td>
</tr>
<!-- END t -->
<!-- IF NO_TOPICS -->
<tr>
	<td colspan="7" class="row1 pad_10 tCenter">{NO_TOPICS}</td>
</tr>
<!-- ENDIF / NO_TOPICS -->
<!-- IF SESSION_ADMIN -->
<tr id="mod-action-row" style="display: none;">
	<td colspan="7" id="mod-action-cell" class="row5 med tCenter pad_4"></td>
</tr>
<!-- ENDIF -->
<tr>
	<td colspan="7" class="catBottom med pad_4">
	<!-- IF LOGGED_IN -->
	<form method="post" action="{S_POST_DAYS_ACTION}">
		{L_DISPLAY_TOPICS}: {S_SELECT_TOPIC_DAYS} {S_DISPLAY_ORDER}
		<input type="submit" value="{L_GO}" />
	</form>
	<!-- ELSE -->
	&nbsp;
	<!-- ENDIF -->
	</td>
</tr>
</table>

<!-- ENDIF / !TORRENTS -->

<table width="100%">
	<tr>
		<td><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" alt="{T_POST_NEW_TOPIC}" /></a></td>
		<td class="nav" width="100%">
			&nbsp;<a href="{U_INDEX}">{L_HOME}</a>&nbsp;<em>&raquo;</em>
			<a href="{U_VIEWCAT}">{CAT_TITLE}</a>
			<!-- IF PARENT_FORUM_NAME --><em>&raquo;</em>&nbsp;<a href="{PARENT_FORUM_HREF}">{PARENT_FORUM_NAME}</a><!-- ENDIF -->
			<em>&raquo;</em>&nbsp;<a href="{U_VIEW_FORUM}">{FORUM_NAME}</a>
		</td>
	</tr>
</table>

<!--bottom_info-->
<div class="bottom_info">

<!-- IF PAGINATION -->
<div class="nav">
	<p style="float: left">{PAGE_NUMBER}</p>
	<p style="float: right">{PAGINATION}</p>
	<div class="clear"></div>
</div>
<!-- ENDIF -->

<div class="jumpbox"></div>

<div id="timezone">
	<p>{CURRENT_TIME}</p>
	<p>{S_TIMEZONE}</p>
</div>
<div class="clear"></div>

<!-- IF LOGGED_IN -->
<p class="med"><a href="{U_MARK_READ}">{L_MARK_TOPICS_READ}</a></p>
<!-- IF IS_AM -->
<p class="mrg_2 tRight">{L_AUTOCLEAN}: <!-- IF PRUNE_DAYS --><b>{PRUNE_DAYS} {L_DAYS}</b><!-- ELSE -->{L_DISABLED}<!-- ENDIF --></p>
<!-- ENDIF -->

<!-- IF IS_ADMIN -->
<div class="med tCenter">{L_ADMIN}:
<a href="{POSTING_URL}?mode=new_rel&amp;f={FORUM_ID}&amp;edit_tpl=1" class="bold" target="_blank">{L_DESIGNER}</a>&nbsp;&middot;
<a href="admin/admin_log.php?f={FORUM_ID}&amp;db={$di->config->get('log_days_keep')}" target="_blank">{L_FORUM_LOGS}</a>&nbsp;&middot;
<a href="admin/admin_forums.php?mode=editforum&amp;f={FORUM_ID}" target="_blank">{L_EDIT}</a>&nbsp;&middot;
<a href="admin/admin_forumauth.php?f={FORUM_ID}" target="_blank">{L_PERMISSIONS}</a>&nbsp;&middot;
<a href="admin/admin_forums.php?mode=deleteforum&amp;f={FORUM_ID}" target="_blank">{L_DELETE}</a>
</div>
<!-- ENDIF / IS_ADMIN -->

<!-- ENDIF / LOGGED_IN -->

<table width="100%" cellspacing="0">
<tr>
	<td width="60%" valign="top">
		<table class="bRight small">
		<tr>
			<td><img class="topic_icons" src="{FOLDER_NEW_IMG}" /></td>
			<td>{L_NEW_POSTS}</td>
			<td><img class="topic_icons" src="{FOLDER_ANNOUNCE_IMG}" /></td>
			<td>{L_POST_ANNOUNCEMENT}</td>
		</tr>
		<tr>
			<td><img class="topic_icons" src="{FOLDER_IMG}" /></td>
			<td>{L_NO_NEW_POSTS}</td>
			<td><img class="topic_icons" src="{FOLDER_STICKY_IMG}" /></td>
			<td>{L_POST_STICKY}</td>
		</tr>
		<tr>
			<td><img class="topic_icons" src="{FOLDER_LOCKED_IMG}" /></td>
			<td>{L_TOPIC_LOCKED_SHORT}</td>
			<td><img class="topic_icons" src="{FOLDER_DOWNLOAD_IMG}" /></td>
			<td>{L_POST_DOWNLOAD}</td>
		</tr>
		</table>
	</td>
</tr>
</table>

</div><!--/bottom_info-->