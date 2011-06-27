<script type="text/javascript">
var audio_codecs = ['{SEL_AUDIO_CODECS}'];
var audio_bitrate = ['{SEL_BITRATE}'];
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
	else if (f.elements["msg[genre]"].value=='')
	{
		f.elements["msg[genre]"].focus();
		error='{L_GENRE}';
		msg +='{L_GENRE}: {L_REQUIRED}';
	}
	else if (f.elements["msg[tracklist]"].value=='')
	{
		f.elements["msg[tracklist]"].focus();
		error='{L_TRACKLIST}';
		msg +='{L_TRACKLIST}: {L_REQUIRED}';
	}
	else if (f.elements["msg[format]"].value=='')
	{
		f.elements["msg[format]"].focus();
		error='{L_FORMAT}';
		msg +='{L_FORMAT}: {L_REQUIRED}';
	}
	else if (f.elements["msg[cover]"].value!='' && !f.elements["msg[cover]"].value.match('^(http|https)://[^ \?&=\#\"<>]+?\.(jpg|jpeg|gif|png)$'))
	{
		f.elements["msg[cover]"].focus();
		error='{L_COVER}';
	}
	else if (f.elements["msg[year]"].value=='' || (isNaN(f.elements["msg[year]"].value) || f.elements["msg[year]"].value.length!=4))
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
	<th colspan="2">{L_RELEASE_WELCOME}</th>
</tr>
<tr>
	<td><b>{L_TITLE}</b>:</td>
	<td><input type="text" name="msg[release_name]" maxlength="90" size="80" /></td>
</tr>
<tr>
	<td><b>{L_COVER}</b>:</td>
	<td><input type="text" name="msg[cover]" size="80" /> <span class="med">URL</span></td>
</tr>
<tr>
	<td><b>{L_YEAR}</b>:</td>
	<td><input type="text" name="msg[year]" maxlength="4" size="5" /></td>
</tr>
<tr>
	<td><b>{L_COUNTRY}</b>:</td>
	<td><input type="text" name="msg[country]" size="50" /></td>
</tr>
<tr>
	<td><b>{L_GENRE}</b>:</td>
	<td><input type="text" name="msg[genre]" size="50" /></td>
</tr>
<tr>
	<td><b>{L_PLAYTIME}</b>:</td>
	<td><input type="text" name="msg[playtime]" size="50" /></td>
</tr>
<tr>
	<td><b>{L_FORMAT}</b>:</td>
	<td>
		<select name="msg[format]"><option value="">&raquo; {L_SELECT}</option><script type="text/javascript">document.writeln(make_format_list(audio_codecs));</script></select>&nbsp;
		<span class="nowrap"><b>{L_AUDIO_BITRATE}</b>: <select name="msg[audio_bitrate]"><option value="">&raquo; {L_SELECT}</option><script type="text/javascript">document.writeln(make_format_list(audio_bitrate));</script></select>&nbsp;</span>
	</td>
</tr>
<tr>
	<td><b>{L_TRACKLIST}</b>:</td>
	<td><textarea name="msg[tracklist]" rows="8" cols="100" class="editor"></textarea></td>
</tr>
<tr>
	<td><b>{L_MOREINFO}</b>:</td>
	<td><textarea name="msg[moreinfo]" rows="6" cols="100" class="editor"></textarea></td>
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
