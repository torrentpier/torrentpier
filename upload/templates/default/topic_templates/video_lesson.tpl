<script type="text/javascript">
var lang = ['{SEL_LANG}'];
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
	<td><input type="text" name="msg[release_name]" maxlength="90" size="80" /></td>
</tr>
<tr>
	<td><b><!-- IF YEAR_HREF --><a href="{YEAR_HREF}" target="_blank">{L_YEAR}</a><!-- ELSE -->{L_YEAR}<!-- ENDIF --></b>:</td>
	<td><input type="text" name="msg[year]" maxlength="4" size="5" /></td>
</tr>
<tr>
	<td><b>{L_MANUFACTURER}</b>:</td>
	<td><input type="text" name="msg[manufacturer][name]" size="60" /></td>
</tr>
<tr>
	<td><b>{L_MANUFACTURER_URL}</b>:</td>
	<td><input type="text" name="msg[manufacturer][url]" size="60" /> <span class="med">URL</span></td>
</tr>
<tr>
	<td><b><!-- IF DESCRIPTION_HREF --><a href="{DESCRIPTION_HREF}" target="_blank">{L_DESCRIPTION}</a><!-- ELSE -->{L_DESCRIPTION}<!-- ENDIF --></b>:</td>
	<td><textarea name="msg[description]" rows="10" cols="100" class="editor"></textarea></td>
</tr>
<tr>
	<td><b><!-- IF FORMAT_HREF --><a href="{FORMAT_HREF}" target="_blank">{L_FORMAT}</a><!-- ELSE -->{L_FORMAT}<!-- ENDIF --></b>:</td>
	<td>
		<select name="msg[video_codec]"><option value="">&raquo; {L_VIDEO_CODEC}</option><script type="text/javascript">document.writeln(make_format_list(video_codecs));</script></select>&nbsp;
		<select name="msg[lang]"><option value="">&raquo; {L_LANG}</option><script type="text/javascript">document.writeln(make_format_list(lang));</script></select>
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
