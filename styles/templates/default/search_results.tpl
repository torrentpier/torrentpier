<!-- IF SEARCH_MATCHES -->
<h1 class="pagetitle">{SEARCH_MATCHES}</h1>
<!-- ENDIF -->

<div class="nav">
	<a href="{U_INDEX}">{T_INDEX}</a>
	<!-- IF MY_POSTS -->
	&nbsp;&middot;&nbsp;
	<span id="show-edit-btn"><a href="#">{L_EDIT_MY_MESSAGE_LIST}</a></span>
	<span id="edit-sel-topics" style="display: none;"><a href="#" class="bold adm" onclick="$('input.topic-chbox').trigger('click'); return false;">{L_SELECT_INVERT}</a></span>
	<!-- ENDIF -->
	&nbsp;&middot;&nbsp;
	<!-- IF LOGGED_IN --><a href="#" class="med normal" onclick="setCookie('{COOKIE_MARK}', 'all_forums'); post2url ('{SITE_URL}'); return false;">{L_MARK_ALL_FORUMS_READ}</a><!-- ENDIF -->
</div>

<!-- IF DISPLAY_AS_POSTS -->
<table class="topic" cellpadding="0" cellspacing="0">
<tr>
	<th class="thHead td1">{L_AUTHOR}</th>
	<th class="thHead td2">{L_MESSAGE}</th>
</tr>
<!-- BEGIN t -->
<tr>
	<td colspan="2" class="td2	cat nav pad_4">
		&nbsp;<img class="topic_icon" src="{t.TOPIC_ICON}" align="absmiddle" />&nbsp;
		<a href="{FORUM_URL}{t.FORUM_ID}" class="med normal"><i>{t.FORUM_NAME}</i></a>
		<em>&raquo;</em>
		<a href="{TOPIC_URL}{t.TOPIC_ID}" class="med">{t.TOPIC_TITLE}</a>
	</td>
</tr>
<!-- BEGIN p -->
<tr class="<!-- IF t.p.ROW_NUM is even -->row1<!-- ELSE -->row2<!-- ENDIF -->">
	<td class="poster_info td1">

		<p class="nick">{t.p.POSTER}</p>
		<p><img src="{SPACER}" width="{TOPIC_LEFT_COL_SPACER_WITDH}" height="30" alt="" /></p>

	</td>
	<td class="message td2">

		<div class="post_head">
			<p style="float: left;<!-- IF TEXT_BUTTONS --> padding: 4px 0 3px;<!-- ELSE --> padding-top: 5px;<!-- ENDIF -->">
				<!-- IF t.p.IS_UNREAD -->{MINIPOST_IMG_NEW}<!-- ELSE -->{MINIPOST_IMG}<!-- ENDIF -->
				<a class="small" href="{POST_URL}{t.p.POST_ID}#{t.p.POST_ID}" title="{L_POST_LINK}">{t.p.POST_DATE}</a>
				<!-- IF t.p.POSTED_AFTER -->
					<span class="posted_since">({L_POSTED_AFTER} {t.p.POSTED_AFTER})</span>
				<!-- ENDIF -->
			</p>

			<p style="float: right;<!-- IF TEXT_BUTTONS --> padding: 3px 2px 4px;<!-- ELSE --> padding: 1px 6px 2px;<!-- ENDIF -->" class="post_btn_1">
				<!-- IF t.p.QUOTE --><a class="txtb" href="{QUOTE_URL}{t.p.POST_ID}">{QUOTE_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- IF t.p.EDIT --><a class="txtb" href="{EDIT_POST_URL}{t.p.POST_ID}">{EDIT_POST_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- IF t.p.DELETE --><a class="txtb" href="{DELETE_POST_URL}{t.p.POST_ID}">{DELETE_POST_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- IF t.p.IP --><a class="txtb" href="{IP_POST_URL}{t.p.POST_ID}&amp;t={t.TOPIC_ID}">{IP_POST_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
			</p>
			<div class="clear"></div>
		</div>

		<div class="post_wrap">
			<div class="post_body">{t.p.MESSAGE}</div>
		</div>

	</td>
</tr>
<!-- END p -->
<!-- END t -->
<tr>
	<td colspan="2" class="catBottom td2">&nbsp;</td>
</tr>
</table>

<!-- ELSE / start of !DISPLAY_AS_POSTS -->

<!-- IF DL_CONTROLS -->
<form method="POST" action="{DL_ACTION}">
<!-- ELSEIF MY_POSTS -->
<script type="text/javascript">
ajax.in_edit_mode = false;

$(document).ready(function(){
	$('#show-edit-btn a').click(function(){
		show_edit_options();
		$('#show-edit-btn').html( $('#edit-sel-topics').html() );
		return false;
	});

	$('td.topic_id').click(function(){
		if (!ajax.in_edit_mode) {
			$('#show-edit-btn a').click();
			$(this).find('input').click();
		}
	});
});

function show_edit_options ()
{
	$('td.topic_id').each(function(){
		var topic_id = $(this).attr('id');
		var input = '<input id="sel-'+ topic_id +'" type="checkbox" value="'+ topic_id +'" class="topic-chbox" />';
		$(this).html(input);
	});

	$('input.topic-chbox').click(function(){
		if ($.browser.mozilla) {
			$('#tr-'+this.value+' td').toggleClass('hl-selected-row');
		}	else {
			$('#tr-'+this.value).toggleClass('hl-selected-row');
		}
	});
	$('#pagination a.pg').each(function(){ this.href += '&mod=1'; });
	$('#ed-list-desc').hide();
	$('#mod-action-cell').append( $('#mod-action-content')[0] );
	$('#mod-action-row, #mod-action-content').show();

	$('#mod-action').submit(function(){
		var $form = $(this);
		$('input[name~=topic_id_list]', $form).remove();
		$('input.topic-chbox:checked').each(function(){
			$form.append('<input type="hidden" name="topic_id_list[]" value="'+ this.value +'" />');
			$('#tr-'+this.value).remove();
		});
	});
	ajax.in_edit_mode = true;
}
</script>

<div id="mod-action-content" style="display: none;">
<form id="mod-action" method="POST" action="{U_SEARCH}" target="_blank">
	<table class="borderless pad_0" cellpadding="0" cellspacing="0">
	    <tr>
		    <td class="pad_4">
		        <input type="submit" name="del_my_post" value="{L_DEL_LIST_MY_MESSAGE}" class="bold" onclick="if (!window.confirm( this.value +'?' )){ return false };" />
	        </td>
			<td class="med" style="padding: 0 8px;">{L_DEL_LIST_MY_MESSAGE_INFO}</td>
		</tr>
	</table>
</form>
</div>
<!-- ENDIF -->

<table class="forumline forum">
<col class="row1">
<col class="row1" width="25%">
<col class="row4" width="75%">
<col class="row1">
<col class="row4">
<col class="row1">
<tr>
	<th>&nbsp;</th>
	<th>{L_FORUM}</th>
	<th>{L_TOPICS}</th>
	<th>{L_AUTHOR}</th>
	<th>{L_REPLIES_SHORT}</th>
	<th>{L_LASTPOST}</th>
</tr>
<!-- BEGIN t -->
<tr id="tr-{t.TOPIC_ID}" class="tCenter">
	<td id="{t.TOPIC_ID}" class="topic_id">
		<!-- IF DL_CONTROLS -->
			<input type="checkbox" name="dl_topics_id_list[]" value="{t.TOPIC_ID}" />
		<!-- ELSE -->
			<img class="topic_icon" src="{t.TOPIC_ICON}" />
		<!-- ENDIF -->
	</td>
	<td><a href="{FORUM_URL}{t.FORUM_ID}" class="gen">{t.FORUM_NAME}</a></td>
		<td class="tLeft" style="padding: 2px 5px 3px 4px;">
		<div class="topictitle" onmousedown="$p('tid_{t.TOPIC_ID}').className='opened'">
			<!-- IF t.IS_UNREAD --><a href="{TOPIC_URL}{t.HREF_TOPIC_ID}{NEWEST_URL}">{ICON_NEWEST_REPLY}</a><!-- ENDIF -->
			<!-- IF t.STATUS == MOVED --><span class="topicMoved">{L_TOPIC_MOVED}</span>
				<!-- ELSEIF t.TYPE == ANNOUNCE --><span class="topicAnnounce">{L_TOPIC_ANNOUNCEMENT}</span>
				<!-- ELSEIF t.DL_CLASS --><span class="{t.DL_CLASS} iconDL">{L_TOPIC_DL}</span>
				<!-- ELSEIF t.ATTACH and not t.DL -->{TOPIC_ATTACH_ICON}
			<!-- ENDIF -->
			<!-- IF t.POLL --><span class="topicPoll">{L_TOPIC_POLL}</span><!-- ENDIF -->
			<a href="{TOPIC_URL}{t.TOPIC_ID}" class="topictitle"><span id="tid_{t.TOPIC_ID}">{t.TOPIC_TITLE}</span></a>
		<!-- IF t.PAGINATION --><span class="topicPG">[{ICON_GOTOPOST}{L_GOTO_SHORT} {t.PAGINATION} ]</span><!-- ENDIF -->
		</div>
	</td>
	<td class="med nowrap">{t.TOPIC_AUTHOR}</td>
	<td class="small">{t.REPLIES}</td>
	<td class="small nowrap" style="padding: 1px 4px 3px 4px;">
		<p>{t.LAST_POST_TIME}</p>
		<p>
			{t.LAST_POSTER}
			<span onmousedown="$p('tid_{t.TOPIC_ID}').className='opened'"><a href="{POST_URL}{t.LAST_POST_ID}#{t.LAST_POST_ID}">{ICON_LATEST_REPLY}</a></span>
		</p>
	</td>
</tr>
<!-- END t -->
<!-- IF MY_POSTS -->
<tr id="mod-action-row">
	<td colspan="6" id="mod-action-cell" class="row2">
		<span id="ed-list-desc" class="small">{L_DEL_LIST_INFO}</span>
	</td>
</tr>
<!-- ENDIF -->
<tr>
	<td class="row2" colspan="6">
		<!-- IF DL_CONTROLS -->
		<input type="submit" name="dl_set_will" value="{L_DLWILL}" class="liteoption" />
		<input type="submit" name="dl_set_down" value="{L_DLDOWN}" class="liteoption" />
		<input type="submit" name="dl_set_complete" value="{L_DLCOMPLETE}" class="liteoption" />
		<input type="submit" name="dl_set_cancel" value="{L_DLCANCEL}" class="liteoption" />
		<input type="hidden" name="redirect_type" value="search" />
		<input type="hidden" name="mode" value="set_topics_dl_status" />
		<!-- ELSE -->
		&nbsp;
		<!-- ENDIF -->
	</td>
</tr>
</table>

<!-- IF DL_CONTROLS -->
</form>
<!-- ENDIF -->

<!-- ENDIF -->

<div class="bottom_info">

	<div class="nav">
		<p style="float: left">{PAGE_NUMBER}</p>
		<p style="float: right">{PAGINATION}</p>
		<div class="clear"></div>
	</div>

	<div class="spacer_4"></div>

	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->
