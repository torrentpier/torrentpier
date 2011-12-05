<!-- IF LOGGED_IN -->
<style type="text/css">
<!-- IF HIDE_AVATAR -->.avatar { display: none; }<!-- ENDIF -->
<!-- IF HIDE_RANK_IMG -->.rank_img { display: none; }<!-- ENDIF -->
<!-- IF HIDE_POST_IMG -->img.postImg, div.postImg-wrap { display: none; }<!-- ENDIF -->
<!-- IF HIDE_SMILE -->.smile { display: none; }<!-- ENDIF -->
<!-- IF HIDE_SIGNATURE -->.signature { display: none; }<!-- ENDIF -->

</style>
<!-- IF SPOILER_OPENED -->
<script type="text/javascript">
	$(document).ready(function(){ $('div.sp-head').click(); });
</script>
<!-- ENDIF -->
<!-- ENDIF / LOGGED_IN -->

<!-- IF $bb_cfg['use_ajax_posts'] && (AUTH_DELETE || AUTH_REPLY || AUTH_EDIT) -->
<script type="text/javascript">
ajax.open_edit = false;
function edit_post(post_id, type, text) {
	if(ajax.open_edit && ajax.open_edit != post_id) {
	    alert('{L_AJAX_EDIT_OPEN}');
	} else{
		if(ajax.open_edit && !text){
			$('#pp_'+ post_id).show();
			$('#pe_'+ post_id).hide();
		} else{
			$('#pp_'+ post_id).hide();
			$('#pe_'+ post_id).show();

			ajax.exec({
				action  : 'posts',
				post_id : post_id,
				text    : text,
				type    : type
			});
		}
		ajax.open_edit = false;
	}
}
ajax.callback.posts = function(data) {
	if(data.html){
		$('#pp_'+ data.post_id).show().html(data.html);
		initPostBBCode('#pp_'+ data.post_id);
	    $('#pe_'+ data.post_id).hide();
	    ajax.open_edit = false;
	} else if(data.text){
		ajax.open_edit = data.post_id;
		$('#pe_'+ data.post_id).html(data.text);
	}
	if(data.redirect) document.location.href = data.redirect;
	if(data.hide) {
		if(ajax.open_edit == data.post_id) ajax.open_edit = false;
		$('tbody#post_'+ data.post_id).hide();
	}
	if(data.quote) $('textarea#message').attr('value', $('textarea#message').val() + data.message +' ').focus();
    if(data.message_html){
	    $('#view_message').show();
	    $('.view-message').html(data.message_html);
	    initPostBBCode('.view-message');
			var maxH   = screen.height - 490;
		$('.view-message').css({ maxHeight: maxH });
	}
};
</script>
<!-- ENDIF -->

<!-- IF SPLIT_FORM -->
<script type="text/javascript">
function set_hid_chbox (id)
{
	$('#del_split_row').show();

	// set checkbox value
	$('#cb_'+id).val( $('#cb_'+id).val() == id ? 0 : id );
	// highlight selected post
	$('#post_'+id+' td').toggleClass('hl-selected-post');

	return false;
}
</script>
<!-- ENDIF / SPLIT_FORM -->

<div class="spacer_6"></div>

<h1 class="maintitle"><a href="{U_VIEW_TOPIC}">{TOPIC_TITLE}</a></h1>
<!-- IF PAGINATION -->
<p class="small" style="padding: 1px 6px 5px;"><b>{PAGINATION}</b></p>
<!-- ENDIF -->

<table cellpadding="0" class="w100">
<tr>
	<td valign="bottom">
		<a href="{U_POST_REPLY_TOPIC}"><img src="{REPLY_IMG}" alt="{T_POST_REPLY}" /></a>
	</td>
	<td class="nav w100" style="padding-left: 8px;">
		<a href="{U_INDEX}">{T_INDEX}</a>
		<!-- IF HAS_PARENT_FORUM --> <em>&raquo;</em>&nbsp;<a href="{PARENT_FORUM_HREF}">{PARENT_FORUM_NAME}</a><!-- ENDIF -->
		<em>&raquo;</em>&nbsp;<a href="{U_VIEW_FORUM}">{FORUM_NAME}</a>
	</td>
</tr>
</table>

<!-- IF SHOW_DL_LIST || SHOW_TOR_ACT --><!-- INCLUDE viewtopic_torrent.tpl --><!-- ENDIF -->

<!-- IF TOPIC_HAS_POLL --><!-- INCLUDE viewtopic_poll.tpl --><!-- ENDIF -->

<table class="w100 border bw_TRL" cellpadding="0" cellspacing="0">
<tr>
	<td class="cat pad_2">

	<table cellspacing="0" cellpadding="0" class="borderless w100">
	<tr>

		<!-- IF AUTH_MOD -->
		<td class="small bold nowrap" style="padding: 0px 0px 0px 4px;">
			<!-- IF IN_MODERATION -->{L_MODERATE_TOPIC}<!-- ELSE --><a href="{PAGE_URL}&amp;mod=1&amp;start={PAGE_START}" class="small bold">{L_MODERATE_TOPIC}</a><!-- ENDIF -->
			&nbsp;|&nbsp;
			<a class="small bold" href="{PIN_HREF}">{PIN_TITLE}</a>
		</td>
		<!-- IF SELECT_PPP -->
		<td class="med" style="padding: 0px 4px 2px 4px;">|</td>
		<td class="small nowrap" style="padding: 0px 0px 0px 0px;">{L_SELECT_POSTS_PER_PAGE}</td>
		<td class="small nowrap" style="padding: 0px 0px 0px 3px;">
			<form id="ppp" action="{PAGE_URL_PPP}" method="post">{SELECT_PPP}</form>
		</td>
		<!-- ENDIF / SELECT_PPP -->
		<!-- ENDIF / AUTH_MOD -->

		<td class="small bold nowrap tRight" width="100%">
			&nbsp;
			<!-- IF LOGGED_IN -->
			<a class="small" href="{U_SEARCH_SELF}">{L_SEARCH_SELF}</a> &nbsp;|&nbsp;
			<a class="menu-root" href="#topic-options">{L_DISPLAYING_OPTIONS}</a>
			<!-- ENDIF / LOGGED_IN -->
		</td>

		<td class="nowrap" style="padding: 0px 4px 2px 4px;">
			<form action="search.php?t={TOPIC_ID}&amp;dm=1&amp;s=1" method="post" onsubmit="var txt=$('#search-text').val(); return !(txt=='{L_SEARCH_IN_TOPIC}' || !txt);">
				<input id="search-text" type="text" name="nm" value="{L_SEARCH_IN_TOPIC}" class="hint" style="width: 150px;" />
				<input type="submit" class="bold" value="&raquo;" style="width: 30px;" />
			</form>
		</td>
	</tr>
	</table>

	</td>
</tr>
</table>

<!-- IF LOGGED_IN -->
<div class="menu-sub" id="topic-options">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th>{L_DISPLAYING_OPTIONS}</th>
	</tr>
	<tr>
		<td>
			<fieldset id="show-only">
			<legend>{L_HIDE_IN_TOPIC}</legend>
			<div class="med pad_4">
				<label>
					<input type="checkbox" <!-- IF HIDE_AVATAR -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('h_av', this.checked ? 1 : 0);"
					/>{L_AVATARS}
				</label>
				<label>
					<input type="checkbox" <!-- IF HIDE_RANK_IMG -->{CHECKED}<!-- ENDIF --><!-- IF HIDE_RANK_IMG_DIS -->{DISABLED}<!-- ENDIF -->
						onclick="user.set('h_rnk_i', this.checked ? 1 : 0);"
					/>{L_RANK_IMAGES}
				</label>
				<label>
					<input type="checkbox" <!-- IF HIDE_POST_IMG -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('h_post_i', this.checked ? 1 : 0);"
					/>{L_POST_IMAGES}
				</label>
				<label>
					<input type="checkbox" <!-- IF HIDE_SMILE -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('h_smile', this.checked ? 1 : 0);"
					/>{L_SMILIES}
				</label>
				<label>
					<input type="checkbox" <!-- IF HIDE_SIGNATURE -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('h_sig', this.checked ? 1 : 0);"
					/>{L_SIGNATURES}
				</label>
			</div>
			</fieldset>
			<div class="spacer_4"></div>
			<fieldset id="spoiler-opt">
			<legend>{L_SPOILER}</legend>
			<div class="med pad_4">
			<p>
				<label>
					<input type="checkbox" <!-- IF SPOILER_OPENED -->{CHECKED}<!-- ENDIF -->
						onclick="user.set('sp_op', this.checked ? 1 : 0);"
					/>{L_SHOW_OPENED}
				</label>
			</p>
			</div>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td class="cat tCenter pad_4"><input type="button" value="{L_DO_SUBMIT}" style="width: 100px;" onclick="window.location.reload();" /></td>
	</tr>
	</table>
</div><!--/topic-options-->
<!-- ENDIF / LOGGED_IN -->

<table class="topic" id="topic_main" cellpadding="0" cellspacing="0">
<tr>
	<th class="thHead td1">{L_AUTHOR}</th>
	<th class="thHead td2">{L_MESSAGE}</th>
</tr>

<!-- BEGIN postrow -->
<tbody id="post_{postrow.POST_ID}" class="{postrow.ROW_CLASS}">
<tr>
	<td class="poster_info td1"><a name="{postrow.POST_ID}"></a><!-- IF postrow.IS_NEWEST --><a name="newest"></a><!-- ENDIF -->

	<!-- IF postrow.POSTER_BOT -->
		<!-- IF SHOW_BOT_NICK --><p class="nick">{postrow.POSTER_NAME}</p><!-- ENDIF -->
		<p class="avatar">{postrow.POSTER_AVATAR}</p>
	<!-- ELSE -->
		<!-- IF QUICK_REPLY -->
		<p class="nick" title="{L_INS_NAME_TIP}" onmouseout="bbcode.refreshSelection(false);" onmouseover="bbcode.refreshSelection(true);" onclick="bbcode.onclickPoster('{postrow.POSTER_NAME_JS}');">
			<a href="#" onclick="return false;">{postrow.POSTER_NAME}</a> <!-- IF postrow.POSTER_AUTHOR --><sup>&reg;</sup><!-- ENDIF -->
		</p>
		<!-- ELSE -->
		<p class="nick">{postrow.POSTER_NAME} <!-- IF postrow.POSTER_AUTHOR --><sup>&reg;</sup><!-- ENDIF --></p>
		<!-- ENDIF -->

		<!-- IF postrow.POSTER_RANK --><p class="rank_txt">{postrow.POSTER_RANK}</p><!-- ENDIF -->
		<!-- IF postrow.RANK_IMAGE --><p class="rank_img">{postrow.RANK_IMAGE}</p><!-- ENDIF -->
		<!-- IF postrow.POSTER_AVATAR --><p class="avatar">{postrow.POSTER_AVATAR}</p><!-- ENDIF -->
		<!-- IF postrow.POSTER_JOINED --><p class="joined" title="{postrow.POSTER_JOINED_DATE}"><em>{L_LONGEVITY}:</em> {postrow.POSTER_JOINED}</p><!-- ENDIF -->
		<!-- IF postrow.POSTER_POSTS --><p class="posts"><em>{L_POSTS}:</em> {postrow.POSTER_POSTS}</p><!-- ENDIF -->
		<!-- IF postrow.POSTER_FROM --><p class="from"><em>{L_LOCATION}:</em> {postrow.POSTER_FROM}</p><!-- ENDIF -->
	<!-- ENDIF -->

	<p><img src="{SPACER}" width="{TOPIC_LEFT_COL_SPACER_WITDH}" height="<!-- IF postrow.POSTER_AVATAR || postrow.RANK_IMAGE -->2<!-- ELSE -->30<!-- ENDIF -->" border="0" alt="" /></p>

	</td>
	<td class="message td2" rowspan="2">

		<div class="post_head">
			<p style="float: left;<!-- IF TEXT_BUTTONS --> padding: 4px 0 3px;<!-- ELSE --> padding-top: 5px;<!-- ENDIF -->">
				<!-- IF postrow.IS_UNREAD -->{MINIPOST_IMG_NEW}<!-- ELSE -->{MINIPOST_IMG}<!-- ENDIF -->
				<a class="small" href="{POST_URL}{postrow.POST_ID}#{postrow.POST_ID}" title="{L_POST_LINK}">{postrow.POST_DATE}</a>
				<!-- IF postrow.POSTED_AFTER -->
					<span class="posted_since">({L_POSTED_AFTER} {postrow.POSTED_AFTER})</span>
				<!-- ENDIF -->
			</p>

			<!-- IF postrow.MOD_CHECKBOX --><input type="checkbox" class="select_post" onclick="set_hid_chbox('{postrow.POST_ID}');"><!-- ENDIF -->

			<p style="float: right;<!-- IF TEXT_BUTTONS --> padding: 3px 2px 4px;<!-- ELSE --> padding: 1px 6px 2px;<!-- ENDIF -->" class="post_btn_1">
				<!-- IF postrow.QUOTE --><a class="txtb" href="<!-- IF $bb_cfg['use_ajax_posts'] -->" onclick="ajax.exec({ action: 'posts', post_id: {postrow.POST_ID}, type: 'reply'}); return false;<!-- ELSE -->{QUOTE_URL}{postrow.POST_ID}<!-- ENDIF -->">{QUOTE_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- IF postrow.EDIT --><a class="txtb" href="<!-- IF $bb_cfg['use_ajax_posts'] -->" onclick="edit_post({postrow.POST_ID}, 'edit'); return false;<!-- ELSE -->{EDIT_POST_URL}{postrow.POST_ID}<!-- ENDIF -->">{EDIT_POST_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- IF postrow.DELETE --><a class="txtb" href="<!-- IF $bb_cfg['use_ajax_posts'] -->" onclick="ajax.exec({ action: 'posts', post_id: {postrow.POST_ID}, type: 'delete'}); return false;<!-- ELSE -->{DELETE_POST_URL}{postrow.POST_ID}<!-- ENDIF -->">{DELETE_POST_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- IF postrow.IP --><a class="txtb" href="{IP_POST_URL}{postrow.POST_ID}&amp;t={TOPIC_ID}">{IP_POST_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- IF postrow.REPORT -->{postrow.REPORT}{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- IF AUTH_MOD -->
					<!-- IF not IN_MODERATION --><a class="txtb" href="{PAGE_URL}&amp;mod=1&amp;start={PAGE_START}#{postrow.POST_ID}">{MOD_POST_IMG}</a>{POST_BTN_SPACER}<!-- ENDIF -->
				<!-- ENDIF -->
			</p>
			<div class="clear"></div>
		</div>

		<div class="post_body">
			<div class="post_wrap">
			    <span id="pp_{postrow.POST_ID}">{postrow.MESSAGE}</span>
				<span id="pe_{postrow.POST_ID}"></span>
				{postrow.ATTACHMENTS}
			</div><!--/post_wrap-->
			<!-- IF postrow.SIGNATURE -->{postrow.SIGNATURE}<!-- ENDIF -->
			<!-- IF postrow.EDITED_MESSAGE --><div class="last_edited">{postrow.EDITED_MESSAGE}</div><!-- ENDIF -->
		</div><!--/post_body-->

	</td>
</tr>
<tr>
	<td class="poster_btn td3">

	<!-- IF postrow.POSTER_BOT || not postrow.PROFILE -->
		&nbsp;
	<!-- ELSE -->
		<div style="<!-- IF TEXT_BUTTONS --> padding: 2px 6px 4px;<!-- ELSE --> padding: 2px 3px;<!-- ENDIF -->" class="post_btn_2">
			<a class="txtb" href="{PROFILE_URL}{postrow.POSTER_ID}">{PROFILE_IMG}</a>{POST_BTN_SPACER}
			<a class="txtb" href="{PM_URL}{postrow.POSTER_ID}">{PM_IMG}</a>{POST_BTN_SPACER}
		</div>
	<!-- ENDIF -->

	</td>
</tr>
</tbody>
<!-- END postrow -->

<!-- IF SPLIT_FORM -->
<tbody>
<tr id="del_split_row" class="row5" style="display: none;">
	<td colspan="2" class="med pad_4 td2">
	<form method="post" action="{S_SPLIT_ACTION}">
	<input type="hidden" name="redirect" value="modcp.php?t={TOPIC_ID}&amp;mode=split" />
	<input type="hidden" name="{POST_FORUM_URL}" value="{FORUM_ID}" />
	<input type="hidden" name="{POST_TOPIC_URL}" value="{TOPIC_ID}" />
	<input type="hidden" name="start" value="{START}" />
	<input type="hidden" name="mode" value="split" />

		<!-- BEGIN postrow -->
		<input type="hidden" name="post_id_list[]" id="cb_{postrow.POST_ID}" />
		<!-- END postrow -->

		<table class="bordered bCenter">
		<tr>
			<td class="row1">{L_NEW_TOPIC_TITLE}</td>
			<td class="row2"><input class="post" type="text" size="35" style="width: 500px" maxlength="120" name="subject" /></td>
		</tr>
		<tr>
			<td class="row1">{L_FORUM_FOR_NEW_TOPIC}</td>
			<td class="row2">{S_FORUM_SELECT}</td>
		</tr>
		<tr>
			<td colspan="2" class="row2 tCenter">
				<label><input type="checkbox" name="after_split_to_old" checked="checked" /> {L_BOT_AFTER_SPLIT_TO_OLD}</label>
				&nbsp;
				<label><input type="checkbox" name="after_split_to_new" checked="checked" /> {L_BOT_AFTER_SPLIT_TO_NEW}</label>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center" class="row3">
				<input type="submit" name="delete_posts" id="del" value="{L_DELETE_POSTS}" disabled="disabled" onclick="return window.confirm('{L_DELETE_POSTS}?');" />
				<input type="submit" name="split_type_all" id="spl_all" value="{L_SPLIT_POSTS}" disabled="disabled" onclick="return window.confirm('{L_SPLIT_POSTS}?');" />
				<input type="submit" name="split_type_beyond" id="spl_b" value="{L_SPLIT_AFTER}" disabled="disabled" onclick="return window.confirm('{L_SPLIT_AFTER}?');" />
				<label for="spl_cnf">
					{L_CONFIRM}
					<input id="spl_cnf" type="checkbox" name="confirm" value="1" onclick="
						toggle_disabled('del', this.checked);
						toggle_disabled('spl_all', this.checked);
						toggle_disabled('spl_b', this.checked);
					" />
				</label>
			</td>
		</tr>
		</table>
	</form>
	</td>
</tr>
</tbody>
<!-- ENDIF / SPLIT_FORM -->

</table><!--/topic_main-->

<!-- IF HIDE_POST_IMG --><script type="text/javascript">$('img.postImg').remove();</script><!-- ENDIF -->
<!-- IF HIDE_SMILE --><script type="text/javascript">$('img.smile').remove();</script><!-- ENDIF -->

<table id="pagination" class="topic" cellpadding="0" cellspacing="0">
<tr>
	<td class="nav pad_6 {PG_ROW_CLASS}">
		<p style="float: left">{PAGE_NUMBER}</p>
		<p style="float: right">{PAGINATION}</p>
	</td>
</tr>
</table><!--/pagination-->

<!-- IF QUICK_REPLY -->
<form action="{QR_POST_ACTION}" method="post" name="post" onsubmit="if(checkForm(this)){ dis_submit_btn(); }else{ return false; }">
<input type="hidden" name="mode" value="reply" />
<input type="hidden" name="t" value="{QR_TOPIC_ID}" />

<table id="topic_quick_reply" class="topic" cellpadding="0" cellspacing="0">
<tr>
	<th class="thHead gen"><b>{L_QUICK_REPLY}</b></th>
</tr>
<tr>
	<td class="td2 row2 tCenter">
		<div id="view_message" class="hidden">
			<div class="tLeft view-message"></div>
		</div>
		<div class="quick_reply_box bCenter">
			<!-- IF not LOGGED_IN -->
			<p class="mrg_6"><b>{L_QR_USERNAME}: </b><input type="text" name="username" size="20" maxlength="25" /></p>
			<!-- ENDIF -->
			<div class="spacer_2"></div>
			<!-- INCLUDE posting_editor.tpl -->
			<div class="spacer_2"></div>
		</div>

	</td>
</tr>
<tr id="post_opt" class="row2">
	<td class="td2 med tCenter pad_4">
		<label><input type="checkbox" name="notify" <!-- IF QR_NOTIFY_CHECKED -->checked="checked"<!-- ENDIF --> <!-- IF not LOGGED_IN -->disabled="disabled"<!-- ENDIF --> />
		{L_QR_NOTIFY}&nbsp;</label>
	</td>
</tr>
</table><!--/topic_quick_reply-->

</form>
<!-- ENDIF / QUICK_REPLY -->

<table class="topic" cellpadding="0" cellspacing="0">
<tr>
	<td class="catBottom med">
	<form method="post" action="{S_POST_DAYS_ACTION}">
		{L_DISPLAY_POSTS}: {S_SELECT_POST_DAYS}&nbsp;
		{S_SELECT_POST_ORDER}&nbsp;
		<input type="submit" value="{L_GO}" class="lite" name="submit" />
	</form>
	</td>
</tr>
</table>

<!-- IF PORNO_FORUM -->
<script type="text/javascript">
// block Imageshack images
$(document).ready(function(){
 $("div.post_body")
   .find("img.postImg[@src*='imageshack']")
     .attr({ src: "images/smiles/tr_oops.gif", alt: "{L_SCREENSHOTS_RULES_TOPIC}" })
     .wrap('<a href="viewtopic.php?t={$bb_cfg['porno_forums_screenshots_topic_id']}"></a>')
   .end()
   .find("a.postLink[@href*='imageshack']")
     .attr({ href: "viewtopic.php?t={$bb_cfg['porno_forums_screenshots_topic_id']}" })
     .text("{L_SCREENSHOTS_RULES_TOPIC}");
});
</script>
<!-- ENDIF -->

<table cellpadding="0" class="w100" style="padding-top: 2px;">
<tr>
	<td valign="top">
		<a href="{U_POST_REPLY_TOPIC}"><img src="{REPLY_IMG}" alt="{T_POST_REPLY}" /></a>
	</td>
	<td class="nav w100" style="padding-left: 8px;">
		<a href="{U_INDEX}">{T_INDEX}</a>
		<!-- IF HAS_PARENT_FORUM --> <em>&raquo;</em>&nbsp;<a href="{PARENT_FORUM_HREF}">{PARENT_FORUM_NAME}</a><!-- ENDIF -->
		<em>&raquo;</em>&nbsp;<a href="{U_VIEW_FORUM}">{FORUM_NAME}</a>
	</td>
</tr>
</table>

<!--bottom_info-->
<div class="bottom_info">

	<div class="jumpbox"></div>

	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->

<table width="100%">
<tr>
	<td width="40%" valign="top" nowrap="nowrap"><span class="small">{S_WATCH_TOPIC}</span><br />
	<div class="small">
<!-- IF S_TOPIC_ADMIN -->
		<br clear="all" />
		<div style="float: left;">{S_TOPIC_ADMIN}</div>
<!-- ENDIF -->
<!-- IF IS_ADMIN -->
		<div class="med" style="float: right;">
		Admin:&nbsp;
		<a href="{U_LOGS}">{L_LOGS}</a>&nbsp;
		</div>
<!-- ENDIF -->
<!-- IF S_DL_DELETE -->
		<br clear="all" />
		<div style="float: left;">{S_DL_DELETE}</div>
<!-- ENDIF -->
		<br clear="all" />
		<div style="float: left;">{S_AUTH_LIST}</div>
	</div>
	</td>
</tr>
</table>