<div class="spacer_12"></div>

<!-- IF TPL_SHOW_NEW_POSTS -->
<!--========================================================================-->

<table class="topic" cellpadding="0" cellspacing="0">
<tr>
	<td colspan="2" class="catTitle td2">{L_NEW_POSTS_PREVIEW}</td>
</tr>
<tr>
	<th class="thHead td1">{L_AUTHOR}</th>
	<th class="thHead td2">{L_MESSAGE}</th>
</tr>
<!-- BEGIN new_posts -->
<tr class="{new_posts.ROW_CLASS}">
	<td width="120" class="poster_info td1">
		<p class="nick" onclick="bbcode.onclickPoster('{new_posts.POSTER_NAME_JS}');">
			<a href="#" onclick="return false;">{new_posts.POSTER}</a>
		</p>
		<p><img src="{SPACER}" width="120" height="10" alt="" /></p>
	</td>
	<td class="message td2">
		<div class="post_head pad_4">{MINIPOST_IMG_NEW} {new_posts.POST_DATE}</div>
		<div class="post_wrap post_body">{new_posts.MESSAGE}</div>
	</td>
</tr>
<!-- END new_posts -->

</table>

<div class="spacer_12"></div>

<!--========================================================================-->
<!-- ENDIF / TPL_SHOW_NEW_POSTS -->

<!-- IF TPL_PREVIEW_POST -->
<!--========================================================================-->

<table class="forumline">
<tr>
	<th>{L_PREVIEW}</th>
</tr>
<tr>
	<td class="row1"><div class="post_wrap post_body">{PREVIEW_MSG}</div></td>
</tr>
</table>

<div class="spacer_12"></div>

<!--========================================================================-->
<!-- ENDIF / TPL_PREVIEW_POST -->

<p class="nav">
	<a href="{U_INDEX}">{T_INDEX}</a>
	<!-- IF U_VIEW_FORUM --><em>&raquo;</em> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a><!-- ENDIF -->
	<!-- IF POSTING_TOPIC_ID --><em>&raquo;</em> <a class="normal" href="{TOPIC_URL}{POSTING_TOPIC_ID}">{POSTING_TOPIC_TITLE}</a><!-- ENDIF -->
</p>

<form action="{S_POST_ACTION}" method="post" name="post" onsubmit="if(checkForm(this)){ dis_submit_btn(); }else{ return false; }" {S_FORM_ENCTYPE}>
{S_HIDDEN_FORM_FIELDS}
{ADD_ATTACH_HIDDEN_FIELDS}
{POSTED_ATTACHMENTS_HIDDEN_FIELDS}

<table class="bordered">
<col class="row1">
<col class="row2">

<tbody class="pad_4">
<tr>
	<th colspan="2" class="thHead"><b>{POSTING_TYPE_TITLE}</b></th>
</tr>
<tr id="view_message" class="hidden">
	<td colspan="2">
		<div class="view-message"></div>
	</td>
</tr>
<!-- IF POSTING_USERNAME -->
<tr>
	<td><b>{L_USERNAME}</b></td>
	<td>
		<input type="text" name="username" size="25" maxlength="25" tabindex="1" value="{USERNAME}" />&nbsp;
		<input type="submit" name="usersubmit" class="lite" value="{L_FIND_USERNAME}" onclick="window.open('{U_SEARCH_USER}', '_bbsearch', IWP_US);return false;" />
	</td>
</tr>
<!-- ENDIF -->
<!-- IF POSTING_SUBJECT -->
<tr>
	<td><b>{L_SUBJECT}</b></td>
	<td><input type="text" name="subject" size="90" tabindex="2" value="{SUBJECT}" maxlength="250" style="width: 98%;" /></td>
</tr>
<!-- ENDIF -->
</tbody>

<tr>
	<td class="vTop pad_4">
		<p><b>{L_MESSAGE}</b></p>
		<table id="smilies" class="smilies borderless mrg_16">
		<!-- BEGIN smilies_row -->
		<tr>
			<!-- BEGIN smilies_col -->
			<td><a href="#" onclick="bbcode && bbcode.emoticon('{smilies_row.smilies_col.SMILEY_CODE}'); return false;"><img src="{smilies_row.smilies_col.SMILEY_IMG}" alt="" title="{smilies_row.smilies_col.SMILEY_DESC}" /></a></td>
			<!-- END smilies_col -->
		</tr>
		<!-- END smilies_row -->
		<!-- BEGIN switch_smilies_extra -->
		<tr>
			<td colspan="{S_SMILIES_COLSPAN}"><a href="{U_MORE_SMILIES}" onclick="window.open('{U_MORE_SMILIES}', '_bbsmilies', IWP_SM); return false;" target="_bbsmilies" class="med">{L_MORE_EMOTICONS}</a></td>
		</tr>
		<!-- END switch_smilies_extra -->
		</table><!--/smilies-->
	</td>
	<td class="vTop pad_0 w100"><!-- INCLUDE posting_editor.tpl --></td>
</tr>
<!-- IF IN_PM -->
<!-- ELSEIF LOGGED_IN -->
<tr>
	<td class="vTop pad_4" valign="top">
		<p><b>{L_OPTIONS}</b></p>
	</td>
	<td>
	<div class="floatL">
		<table class="borderless inline">
		<!-- IF SHOW_UPDATE_POST_TIME -->
		<tr>
			<td><input type="checkbox" id="update_post_time" name="update_post_time" <!-- IF UPDATE_POST_TIME_CHECKED -->checked="checked"<!-- ENDIF --> /></td>
			<td><label for="update_post_time">{L_UPDATE_POST_TIME}</label></td>
		</tr>
		<!-- ENDIF -->
		<!-- IF SHOW_NOTIFY_CHECKBOX -->
		<tr>
			<td><input type="checkbox" id="notify" name="notify" {S_NOTIFY_CHECKED} /></td>
			<td><label for="notify">{L_NOTIFY}</label></td>
		</tr>
		<!-- ENDIF -->
		</table>
	</div>
	</td>
</tr>
<!-- IF ATTACHBOX -->
<!-- IF POSTER_RGROUPS -->
<tr>
	<td class="vTop pad_4" valign="top"><b>{L_POST_RELEASE_FROM_GROUP}</b></td>
	<td>
		<select name="poster_rg">
			<option value="-1">{L_CHOOSE_RELEASE_GROUP}</option>
			{POSTER_RGROUPS}
		</select>
		<label><input type="checkbox" name="attach_rg_sig" <!-- IF ATTACH_RG_SIG -->checked<!-- ENDIF -->/> {L_ATTACH_RG_SIG}</label>
	</td>
</tr>
<!-- ENDIF / POSTER_RGROUPS -->
<!-- ENDIF / ATTACHBOX -->
<!-- ENDIF / LOGGED_IN -->
<!-- BEGIN switch_type_toggle -->
<tr>
	<td colspan="2" class="row2 tCenter pad_6">{S_TYPE_TOGGLE}</td>
</tr>
<!-- END switch_type_toggle -->
<!-- IF ATTACHBOX --><!-- INCLUDE posting_attach.tpl --><!-- ENDIF -->
</table>

</form>

<!-- IF TPL_TOPIC_REVIEW -->
<!--========================================================================-->

<div class="spacer_12"></div>

<table class="topic" cellpadding="0" cellspacing="0">
<tr>
	<td colspan="2" class="catTitle td2">{L_TOPIC_REVIEW}</td>
</tr>
<tr>
	<th class="thHead td1">{L_AUTHOR}</th>
	<th class="thHead td2">{L_MESSAGE}</th>
</tr>
<!-- BEGIN review -->
<tr class="{review.ROW_CLASS}">
	<td width="120" class="poster_info td1">
		<p class="nick" onclick="bbcode.onclickPoster('{review.POSTER_NAME_JS}');">
			<a href="#" onclick="return false;">{review.POSTER}</a>
		</p>
		<p><img src="{SPACER}" width="120" height="10" alt="" /></p>
	</td>
	<td class="message td2">
		<div class="post_head pad_4">{MINIPOST_IMG} {review.POST_DATE}</div>
		<div class="post_wrap post_body">{review.MESSAGE}</div>
	</td>
</tr>
<!-- END review -->

</table>

<div class="spacer_12"></div>

<!--========================================================================-->
<!-- ENDIF / TPL_TOPIC_REVIEW -->
