<script type="text/javascript">
function manage_group(mode, value) {
	ajax.exec({
		action   : 'manage_group',
		mode     : mode,
		group_id : {GROUP_ID},
		value    : value
	});
	ajax.callback.manage_group = function(data) {
		if (data.act === 0) $('div#avatar').hide(100);
		console.log(data);
	}
}
</script>

<h1 class="pagetitle">{PAGE_TITLE}<!-- IF GROUP_NAME --> :: {GROUP_NAME}<!-- ENDIF --></h1>
<p class="nav"><a href="{U_GROUP_URL}">{L_GROUP_RETURN}</a></p>
<table class="forumline pad_4">
	<col class="row1" width="20%">
	<col class="row2" width="100%">
	<tr>
		<th colspan="2">{L_GROUP_INFORMATION}</th>
	</tr>
	<tr>
		<td>{L_GROUP_NAME}:</td>
		<td><input type="text" id="group_name" size="80" value="{GROUP_NAME}" onblur="manage_group(this.id, this.value);" /></td>
	</tr>
	<tr>
		<td>{L_GROUP_DESCRIPTION}:</td>
		<td><div id="preview_description"></div>
			<p>
				<textarea cols="80" id="group_description" rows="6">{GROUP_DESCRIPTION}</textarea>
			</p>
			<p>
				<input type="button" value="{L_AJAX_PREVIEW}" onclick="ajax.exec({ action: 'posts', type: 'view_message', message: $('textarea#group_description').val()});ajax.callback.posts=function(data){$('div#preview_description').html(data.message_html);initPostBBCode('div#preview_description')}">
				<input type="button" value="{L_SAVE}" onclick="manage_group('group_description',$('textarea#group_description').val())">
			</p>
		</td>
	</tr>
	<tr>
		<td>{L_SIGNATURE}:</td>
		<td><div id="preview_signature"></div>
			<p>
				<textarea cols="80" id="group_signature" rows="3">{GROUP_SIGNATURE}</textarea>
			</p>
			<p>
				<input type="button" value="{L_AJAX_PREVIEW}" onclick="ajax.exec({ action: 'posts', type: 'view_message', message: $('textarea#group_signature').val()});ajax.callback.posts=function(data){$('div#preview_signature').html(data.message_html);initPostBBCode('div#preview_signature')}">
				<input type="button" value="{L_SAVE}" onclick="manage_group('group_signature',$('textarea#group_signature').val())">
			</p>
		</td>
	</tr>
	<tr>
		<td>{L_GROUP_TYPE}:</td>
		<td>
			<p>
				<label><input type="radio" name="group_type" onchange="manage_group(this.name,this.value)" value="{S_GROUP_OPEN_TYPE}" {S_GROUP_OPEN_CHECKED} />{L_GROUP_OPEN}</label> &nbsp;&nbsp;
				<label><input type="radio" name="group_type" onchange="manage_group(this.name,this.value)" value="{S_GROUP_CLOSED_TYPE}" {S_GROUP_CLOSED_CHECKED} />{L_GROUP_CLOSED}</label> &nbsp;&nbsp;
				<label><input type="radio" name="group_type" onchange="manage_group(this.name,this.value)" value="{S_GROUP_HIDDEN_TYPE}" {S_GROUP_HIDDEN_CHECKED} />{L_GROUP_HIDDEN}</label>
			</p>
		</td>
	</tr>
	<tr>
		<td>{L_RELEASE_GROUP}:</td>
		<td>
			<label><input type="radio" name="release_group" value="1" onclick="manage_group(this.name,this.value)" <!-- IF RELEASE_GROUP -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
			<label><input type="radio" name="release_group" value="0" onclick="manage_group(this.name,this.value)" <!-- IF not RELEASE_GROUP -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
		</td>
	</tr>
	<tr>
		<td>
			{L_AVATAR}: <br /><br />
			<div id="avatar" align="center">
				<p>{AVATAR_IMG}</p><br />
				<p><input type="button" name="delete_avatar" value="{L_DELETE_IMAGE}" onclick="manage_group(this.name,this.value)" /></p>
			</div>
		</td>
		<td valign="top">
			<div id="avatar_explain" class="med">{AVATAR_EXPLAIN}</div>
			<!-- IF $di->config->get('group_avatars.up_allowed') -->
				<br />
				<form action="{S_GROUP_CONFIG_ACTION}" method="post" enctype="multipart/form-data">
					{S_HIDDEN_FIELDS}
					<input type="hidden" name="MAX_FILE_SIZE" value="{$di->config->get('avatars.max_size')}" />
					<input type="file" name="avatar" />
					<input class="mainoption" type="submit" name="submit" value="{L_UPLOAD_AVATAR_FILE}" />
				</form>
			<!-- ENDIF -->
		</td>
	</tr>
</table>