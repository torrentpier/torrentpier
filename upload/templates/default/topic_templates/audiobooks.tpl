<script type="text/javascript">
var audio_codecs = ['{SEL_AUDIO_CODECS}'];
var audiobook_type = ['{SEL_AUDIOBOOK_TYPE}'];
var torrent_sign = "{TORRENT_SIGN}";

function make_format_list (what)
{
	var ret='';
	for (i=0; i<what.length; i++)
	{
		ret += '<option value="'+what[i]+'">'+what[i]+'</option>';
	}
	return ret;
}
function form_validate (f)
{
	var error='';
	var msg="\n\n";

	if (f.elements["msg[release_name]"].value=='')
	{
		f.elements["msg[release_name]"].focus();
		error='{L_TITLE}';
		msg +='{L_TITLE_EXP}';
	}
	else if (f.elements["msg[picture]"].value!='' && !f.elements["msg[picture]"].value.match('^(http|https)://[^ \?&=\#\"<>]+?\.(jpg|jpeg|gif|png)$'))
	{
		f.elements["msg[picture]"].focus();
		error='{L_PICTURE}';
		msg +='{L_PICTURE_EXP}';
	}
	else if (f.elements["msg[year]"].value!='' && (isNaN(f.elements["msg[year]"].value) || f.elements["msg[year]"].value.length!=4))
	{
		f.elements["msg[year]"].focus();
		error='{L_YEAR}';
		msg +='{L_YEAR_EXP}';
	}
	else if (f.fileupload.value=='')
	{
		f.fileupload.focus();
		error='{L_TORRENT}';
		msg +='{L_TORRENT_EXP}';
	}
	else if (f.fileupload.value.substr(f.fileupload.value.length-{TORRENT_EXT_LEN})!='.{TORRENT_EXT}')
	{
		f.fileupload.focus();
		error='{L_TORRENT}';
		msg +='{L_TORRENT_EXP}';
	}
	else if (torrent_sign && f.fileupload.value.indexOf(torrent_sign) == -1)
	{
		f.fileupload.focus();
		error='{L_TORRENT}';
		msg +='{L_TORRENT_SIGN_EXP}';
	}
	else if (f.elements["msg[audio_codec]"].value=='')
	{
		f.elements["msg[audio_codec]"].focus();
		error='{L_AUDIO_CODEC}';
		msg +='{L_AUDIO_CODEC}: {L_REQUIRED}';
	}
	else if (f.elements["msg[audio_bitrate]"].value=='')
	{
		f.elements["msg[audio_bitrate]"].focus();
		error='{L_AUDIO_BITRATE}';
		msg +='{L_AUDIO_BITRATE}: {L_REQUIRED}';
	}
	else if (f.elements["msg[description]"].value=='')
	{
		f.elements["msg[description]"].focus();
		error='{L_DESCRIPTION}';
		msg +='{L_DESCRIPTION}: {L_REQUIRED}';
	}

	if (error) {
		alert('{L_ERROR}: '+error+msg);
		return false;
	}
	return true;
}
</script>

<h1 class="maintitle"><a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></h1>

<div class="nav">
	<p class="floatL"><a href="{U_INDEX}">{T_INDEX}</a></p>
	<!-- IF REGULAR_TOPIC_BUTTON --><p class="floatR"><a href="{REGULAR_TOPIC_HREF}">{L_POST_REGULAR_TOPIC}</a></p><!-- ENDIF -->
	<div class="clear"></div>
</div>

<?php require($GLOBALS['bb_cfg']['topic_tpl']['overall_header']) ?>

<form action="{S_ACTION}" method="post" name="post" onsubmit="return form_validate(this);" enctype="multipart/form-data">
<input type="hidden" name="preview" value="1">

<table class="forumline">
<col class="row1" width="20%">
<col class="row2" width="80%">
<tr>
	<th colspan="2" class="thHead">{L_RELEASE_WELCOME}</th>
</tr>
<tr>
	<td><b>{L_TITLE}</b>:</td>
	<td><input type="text" name="msg[release_name]" maxlength="90" size="80" /></td>
</tr>
<tr>
	<td><b>{L_PICTURE}</b>:</td>
	<td><input type="text" name="msg[picture]" size="80" /> <span class="med">URL</span></td>
</tr>
<tr>
	<td><b>{L_YEAR}</b>:</td>
	<td><input type="text" name="msg[year]" maxlength="4" size="5" /></td>
</tr>
<tr>
	<td><b>{L_AUTHOR}</b>:</td>
	<td><input type="text" name="msg[author]" size="50" /></td>
</tr>
<tr>
	<td><b>{L_PERFORMER}</b>:</td>
	<td><input type="text" name="msg[performer]" size="50" /></td>
</tr>
<tr>
	<td><b>{L_GENRE}</b>:</td>
	<td><input type="text" name="msg[genre]" size="50" /></td>
</tr>
<tr>
	<td><b>{L_PUBLISHER}</b>:</td>
	<td><input type="text" name="msg[publisher]" size="50" /></td>
</tr>
<tr>
	<td><b>{L_FORMAT}</b>:</td>
	<td>
		<select name="msg[audiobook_type]"><option value="">&raquo; {L_AUDIOBOOK_TYPE}</option><script type="text/javascript">document.writeln(make_format_list(audiobook_type));</script></select>&nbsp;
		<select name="msg[audio_codec]"><option value="">&raquo; {L_AUDIO_CODEC}</option><script type="text/javascript">document.writeln(make_format_list(audio_codecs));</script></select>&nbsp;
		<span class="nowrap"><b>{L_AUDIO_BITRATE}</b>: <input type="text" name="msg[audio_bitrate]" size="30" /></span>
	</td>
</tr>
<tr>
	<td><b>{L_DESCRIPTION}</b>:</td>
	<td><textarea name="msg[description]" rows="10" cols="100" class="editor"></textarea></td>
</tr>
<tr>
	<td><b>{L_MOREINFO}</b>:</td>
	<td><textarea name="msg[moreinfo]" rows="3" cols="100" class="editor"></textarea></td>
</tr>
<tr>
	<td><b>{L_TORRENT}</b>:</td>
	<td>
		<p><input type="file" name="fileupload" size="65" /></p>
		<p class="med">{L_TORRENT_EXP}</p>
	</td>
</tr>
<tr>
	<td class="catBottom" colspan="2">
		<input type="submit" name="add_attachment" value="{L_NEXT}" class="bold" />
	</td>
</tr>
</table>

</form>
