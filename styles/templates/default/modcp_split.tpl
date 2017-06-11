<script type="text/javascript">
function toggle_cbox (cb_id, tr_id)
{
	var cb = document.getElementById(cb_id);
	var tr = document.getElementById(tr_id);
	cb.checked	= (cb.checked) ? 0 : 1;
	if (cb.checked) {
		tr.className = 'sel';
	}
	else {
		tr.className = ( !(tr_id % 2) ) ? 'row1' : 'row2';
	}
	return false;
}
</script>
<style type="text/css"> .sel { background-color:#FFEFD5; } </style>

<form method="post" action="{S_SPLIT_ACTION}">

<table width="100%">
	<tr>
		<td style="padding-left: 0;" class="nav">
			<a href="{U_INDEX}" class="nav">{T_INDEX}</a>
			<span class="nav">
				&raquo;&nbsp;<a href="{U_VIEW_FORUM}" class="nav">{FORUM_NAME}</a>
			</span>
		</td>
	</tr>
</table>

<table class="forumline">
	<tr>
		<th colspan="2">{L_TOPIC_SPLIT}</th>
	</tr>
	<tr>
		<td class="row2" colspan="2" align="center">
			<span class="small">{L_SPLIT_TOPIC_EXPLAIN}</span>
		</td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen">{L_NEW_TOPIC_TITLE}</span></td>
		<td class="row2"><input class="post" type="text" size="35" style="width: 500px" maxlength="120" name="subject" /></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen">{L_FORUM_FOR_NEW_TOPIC}</span></td>
		<td class="row2">{S_FORUM_SELECT}</td>
	</tr>
	<!-- //bot -->
	<tr>
		<td colspan="2" class="row2" align="center" style="padding: 0">
		<table class="borderless">
			<tr>
				 <td class="row2" align="center"><span class="gen"><input type="checkbox" name="after_split_to_old" id="after_split_to_old" checked="checked" /><label for="after_split_to_old"> {L_BOT_AFTER_SPLIT_TO_OLD}</label></span></td>
				 <td>&nbsp;</td>
				 <td class="row2" align="center"><span class="gen"><input type="checkbox" name="after_split_to_new" id="after_split_to_new" checked="checked" /><label for="after_split_to_new"> {L_BOT_AFTER_SPLIT_TO_NEW}</label></span></td>
				</tr>
			</table>
		</td>
	</tr>
	<!-- //bot end -->
	<tr>
		<td colspan="2" class="catBottom">
			<input type="submit" name="delete_posts" value="{L_DELETE_POSTS}" style="width: 140px;" />
			<input type="submit" name="split_type_all" value="{L_SPLIT_POSTS}" style="width: 215px;" />
			<input type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}" style="width: 280px;" />
		</td>
	</tr>
</table>
<div><img src="{SPACER}" alt="" width="1" height="6" /></div>

<table class="topic" cellpadding="0" cellspacing="0">
	<tr>
		<th class="thHead td1">#</th>
		<th class="thHead td1">{L_AUTHOR}</th>
		<th class="thHead td2">{L_MESSAGE}</th>
	</tr>
	<!-- BEGIN postrow -->
	<tr <!-- IF postrow.CHECKBOX -->id="{postrow.ROW_ID}" onclick="toggle_cbox('{postrow.CB_ID}', '{postrow.ROW_ID}');"<!-- ENDIF --> class="{postrow.ROW_CLASS}">
		<td class="td1 tCenter"><!-- IF postrow.CHECKBOX --><input type="checkbox" name="post_id_list[]" value="{postrow.POST_ID}" id="{postrow.CB_ID}" onclick="toggle_cbox('{postrow.CB_ID}', '{postrow.ROW_ID}');" /><!-- ENDIF --></td>
		<td class="td1 vTop pad_2">
			<p><b>{postrow.POSTER_NAME}</b></p>
			<p class="small nowrap">{postrow.POST_DATE}</p>
		</td>
		<td class="message td2" width="100%">
			<div class="post_wrap">
				<div class="post_body">{postrow.MESSAGE}</div>
			</div>
		</td>
	</tr>
	<!-- END postrow -->
	<tr>
		<td class="catBottom" colspan="3">
			<input type="hidden" name="confirm" value="1" />
			<input class="liteoption" type="submit" name="delete_posts" value="{L_DELETE_POSTS}" style="width: 140px;" />
			<input class="liteoption" type="submit" name="split_type_all" value="{L_SPLIT_POSTS}" style="width: 210px;" />
			<input class="liteoption" type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}" style="width: 270px;" />
		{S_HIDDEN_FIELDS}
		</td>
	</tr>
</table>
</form>
