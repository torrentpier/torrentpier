<div class="spacer_6"></div>

<table class="pm_nav bCenter">
<tr>
	<td>{INBOX_IMG}</td><td>{INBOX}</td>
	<td>{SENTBOX_IMG}</td><td>{SENTBOX}</td>
</tr>
<tr>
	<td>{OUTBOX_IMG}</td><td>{OUTBOX}</td>
	<td>{SAVEBOX_IMG}</td><td>{SAVEBOX}</td>
</tr>
</table>

<div class="spacer_6"></div>

<form action="{S_PRIVMSGS_ACTION}" method="post" name="post" onsubmit="if(checkForm(this)){ dis_submit_btn(); }else{ return false; }">
{S_HIDDEN_FIELDS}

<table class="borderless">
<tr>
	<!-- IF REPLY_PM_IMG --><td class="nowrap">{REPLY_PM_IMG}&nbsp;</td><!-- ENDIF -->
	<td width="100%" class="nav"><a href="{U_INDEX}">{T_INDEX}</a></td>
</tr>
</table>

<table class="forumline">
<thead class="row2 med">
<tr>
	<th colspan="2">{BOX_NAME} :: {L_MESSAGE}</th>
</tr>
<tr>
	<td>{L_FROM}:&nbsp;</td>
	<td width="100%"><b>{FROM_USER}</b></td>
</tr>
<tr>
	<td>{L_TO}:&nbsp;</td>
	<td>{TO_USER}</td>
</tr>
<tr>
	<td>{L_POSTED}:&nbsp;</td>
	<td>{POST_DATE}</td>
</tr>
<tr>
	<td>{L_SUBJECT}:&nbsp;</td>
	<td><b>{POST_SUBJECT}</b></td>
</tr>
</thead>
<tbody>
<tr>
	<td colspan="2" class="row1 gen pad_4">
		<div class="post_wrap"><div class="post_body">{PM_MESSAGE}</div></div>
		<div class="clearB tRight">{QUOTE_PM_IMG} {EDIT_PM_IMG}</div>
 </td>
</tr>
<tr>
 <td colspan="2" class="catBottom pad_4">
		<input type="submit" name="save" value="{L_SAVE_MESSAGE}" class="liteoption" />&nbsp;
		<input type="submit" name="delete" value="{L_DELETE_MESSAGE}" class="liteoption" />&nbsp;
 </td>
</tr>
</tbody>
</table>

</form>

<!-- IF QUICK_REPLY -->
<div class="spacer_6"></div>
<script type="text/javascript">
ajax.callback.posts = function(data){
    $('#view_message').show();
	    $('.view-message').html(data.message_html);
	    initPostBBCode('.view-message');
			var maxH   = screen.height - 490;
		$('.view-message').css({ maxHeight: maxH });
};
</script>
<form action="{S_PRIVMSGS_ACTION}" method="post" name="post" onsubmit="if(checkForm(this)){ dis_submit_btn(); }else{ return false; }">
{S_HIDDEN_FIELDS}

<table class="topic" cellpadding="0" cellspacing="0">
<tr>
	<th class="td2 thHead"><b>{L_QUICK_REPLY}</b></th>
</tr>
<tr>
	<td class="td2 row2 tCenter pad_6">
		<b>{L_TO}:</b>
		<input type="text" name="username" size="18" maxlength="25" style="width:110px" class="post" value="{MESSAGE_FROM}" />&nbsp;
		<b>{L_SUBJECT}:</b>
		<input type="text" name="subject" size="50" maxlength="60" style="width:300px" class="post" value="{QR_SUBJECT}" />
	</td>
</tr>
<tr>
	<td class="td2 row2 tCenter pad_4">
		<div id="view_message" class="hidden">
			<div class="tLeft view-message"></div>
		</div>
		<div class="quick_reply_box bCenter">
			<!-- INCLUDE posting_editor.tpl -->
		</div>
	</td>
</tr>
</table>

</form>
<!-- ENDIF / QUICK_REPLY -->
