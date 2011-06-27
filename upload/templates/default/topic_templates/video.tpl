<script type="text/javascript">
var video_formats = ['{SEL_VIDEO_FORMATS}'];
var video_codecs = ['{SEL_VIDEO_CODECS}'];
var audio_codecs = ['{SEL_AUDIO_CODECS}'];
var quality = ['{SEL_VIDEO_QUALITY}'];
var translation = ['{SEL_TRANSLATION}'];
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
<?php require($GLOBALS['bb_cfg']['topic_tpl']['rules_video']) ?>

<form action="{S_ACTION}" method="post" name="post" onsubmit="return form_validate(this);" enctype="multipart/form-data">
<input type="hidden" name="preview" value="1">

<table class="forumline">
<col class="row1" width="20%">
<col class="row2" width="80%">
<tr>
	<th colspan="2">{L_RELEASE_WELCOME}</th>
</tr>
<tr>
	<td><b><!-- IF TITLE_HREF --><a href="{TITLE_HREF}" target="_blank">{L_TITLE}</a><!-- ELSE -->{L_TITLE}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[release_name]" maxlength="90" size="80" /> <span class="med nowrap">{L_TITLE_DESC}</span></td>
</tr>
<tr>
	<td><b><!-- IF ORIGINAL_TITLE_HREF --><a href="{ORIGINAL_TITLE_HREF}" target="_blank">{L_ORIGINAL_TITLE}</a><!-- ELSE -->{L_ORIGINAL_TITLE}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[original_name]" maxlength="90" size="80" /> <span class="med nowrap">{L_ORIGINAL_TITLE_DESC}</span></td>
</tr>
<tr>
	<td><b><!-- IF PICTURE_HREF --><a href="{PICTURE_HREF}" target="_blank">{L_PICTURE}</a><!-- ELSE -->{L_PICTURE}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[picture]" size="80" /> <span class="med">URL</span></td>
</tr>
<tr>
	<td><b><!-- IF COUNTRY_HREF --><a href="{COUNTRY_HREF}" target="_blank">{L_COUNTRY}</a><!-- ELSE -->{L_COUNTRY}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[country]" size="50" /></td>
</tr>
<tr>
	<td><b><!-- IF DIRECTOR_HREF --><a href="{DIRECTOR_HREF}" target="_blank">{L_DIRECTOR}</a><!-- ELSE -->{L_DIRECTOR}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[director]" size="50" /></td>
</tr>
<tr>
	<td><b><!-- IF GENRE_HREF --><a href="{GENRE_HREF}" target="_blank">{L_GENRE}</a><!-- ELSE -->{L_GENRE}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[genre]" size="40" /></td>
</tr>
<tr>
	<td><b><!-- IF PLAYTIME_HREF --><a href="{PLAYTIME_HREF}" target="_blank">{L_PLAYTIME}</a><!-- ELSE -->{L_PLAYTIME}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[playtime]" size="30" /></td>
</tr>
<tr>
	<td><b><!-- IF YEAR_HREF --><a href="{YEAR_HREF}" target="_blank">{L_YEAR}</a><!-- ELSE -->{L_YEAR}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[year]" maxlength="4" size="5" /></td>
</tr>
<tr>
	<td><b><!-- IF TRANSLATION_HREF --><a href="{TRANSLATION_HREF}" target="_blank">{L_TRANSLATION}</a><!-- ELSE -->{L_TRANSLATION}<!-- ENDIF --></b>:</td>
	<td><select name="msg[translation]"><option value="">&raquo; {L_SELECT}</option><script type="text/javascript">document.writeln(make_format_list(translation));</script></select></td>
</tr>
<tr>
	<td><b><!-- IF CASTING_HREF --><a href="{CASTING_HREF}" target="_blank">{L_CASTING}</a><!-- ELSE -->{L_CASTING}<!-- ENDIF --></b>:</td>
	<td><textarea name="msg[casting]" rows="3" cols="100" class="editor"></textarea></td>
</tr>
<tr>
	<td><b><!-- IF DESCRIPTION_HREF --><a href="{DESCRIPTION_HREF}" target="_blank">{L_DESCRIPTION}</a><!-- ELSE -->{L_DESCRIPTION}<!-- ENDIF --></b>:</td>
	<td><textarea name="msg[description]" rows="10" cols="100" class="editor"></textarea></td>
</tr>
<tr>
	<td><b><!-- IF MOREINFO_HREF --><a href="{MOREINFO_HREF}" target="_blank">{L_MOREINFO}</a><!-- ELSE -->{L_MOREINFO}<!-- ENDIF --></b>:</td>
	<td><textarea name="msg[moreinfo]" rows="3" cols="100" class="editor"></textarea></td>
</tr>
<tr>
	<td><b><!-- IF FORMAT_HREF --><a href="{FORMAT_HREF}" target="_blank">{L_FORMAT}</a><!-- ELSE -->{L_FORMAT}<!-- ENDIF --></b>:</td>
	<td>
		<select name="msg[quality]"><option value="">&raquo; {L_QUALITY}</option><script type="text/javascript">document.writeln(make_format_list(quality));</script></select>&nbsp;
		<select name="msg[format]"><option value="">&raquo; {L_FORMAT}</option><script type="text/javascript">document.writeln(make_format_list(video_formats));</script></select>&nbsp;
		<select name="msg[video_codec]"><option value="">&raquo; {L_VIDEO_CODEC}</option><script type="text/javascript">document.writeln(make_format_list(video_codecs));</script></select>&nbsp;
		<select name="msg[audio_codec]"><option value="">&raquo; {L_AUDIO_CODEC}</option><script type="text/javascript">document.writeln(make_format_list(audio_codecs));</script></select>&nbsp;
	</td>
</tr>
<tr>
	<td><b><!-- IF VIDEO_HREF --><a href="{VIDEO_HREF}" target="_blank">{L_VIDEO}</a><!-- ELSE -->{L_VIDEO}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[video]" size="80" /></td>
</tr>
<tr>
	<td><b><!-- IF AUDIO_HREF --><a href="{AUDIO_HREF}" target="_blank">{L_AUDIO}</a><!-- ELSE -->{L_AUDIO}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[audio]" size="80" /></td>
</tr>
<tr>
	<td><b>{L_SCREEN_SHOTS}</b>:</td>
	<td><textarea name="msg[screen_shots]" rows="3" cols="100" class="editor"></textarea> <span class="med">URLs</span></td>
</tr>
<tr>
	<td><b><!-- IF TORRENT_HREF --><a href="{TORRENT_HREF}" target="_blank">{L_TORRENT}</a><!-- ELSE -->{L_TORRENT}<!-- ENDIF --></b>:</td>
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
