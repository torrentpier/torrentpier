<!-- IF TPL_SMILE_MAIN -->
<!--========================================================================-->

<h1>{L_SMILEY_TITLE}</h1>

<p>{L_SMILE_DESC}</p>
<br />

<form method="post" action="{S_SMILEY_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline">
	<tr>
		<th>{L_CODE}</th>
		<th>{L_SMILE}</th>
		<th>{L_EMOTION}</th>
		<th colspan="2">{L_ACTION}</th>
	</tr>
	<!-- BEGIN smiles -->
	<tr>
		<td class="{smiles.ROW_CLASS}">{smiles.CODE}</td>
		<td class="{smiles.ROW_CLASS}"><img src="{smiles.SMILEY_IMG}" alt="{smiles.CODE}" /></td>
		<td class="{smiles.ROW_CLASS}">{smiles.EMOT}</td>
		<td class="{smiles.ROW_CLASS}"><a href="{smiles.U_SMILEY_EDIT}">{L_EDIT}</a></td>
		<td class="{smiles.ROW_CLASS}"><a href="{smiles.U_SMILEY_DELETE}">{L_DELETE}</a></td>
	</tr>
	<!-- END smiles -->
	<tr>
		<td class="catBottom" colspan="5"><input type="submit" name="add" value="{L_SMILE_ADD}" class="mainoption" />&nbsp;&nbsp;<input class="liteoption" type="submit" name="import_pack" value="{L_IMPORT_SMILE_PACK}">&nbsp;&nbsp;<input class="liteoption" type="submit" name="export_pack" value="{L_EXPORT_SMILE_PACK}"></td>
	</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_SMILE_MAIN -->

<!-- IF TPL_SMILE_EDIT -->
<!--========================================================================-->

<h1>{L_SMILEY_TITLE}</h1>

<p>{L_SMILEY_IMPORT_INST}</p>
<br />

<script type="text/javascript">
function update_smiley(newimage)
{
	document.smiley_image.src = "{S_SMILEY_BASEDIR}/" + newimage;
}
</script>

<form method="post" action="{S_SMILEY_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline">
	<tr>
		<th colspan="2">{L_SMILEY_CONFIG}</th>
	</tr>
	<tr>
		<td class="row2">{L_SMILEY_CODE}</td>
		<td class="row2"><input class="post" type="text" name="smile_code" value="{SMILEY_CODE}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_SMILEY_URL}</td>
		<td class="row1"><select name="smile_url" onchange="update_smiley(this.options[selectedIndex].value);">{S_FILENAME_OPTIONS}</select> &nbsp; <img name="smiley_image" src="{SMILEY_IMG}" border="0" alt="" /> &nbsp;</td>
	</tr>
	<tr>
		<td class="row2">{L_SMILEY_EMOT}</td>
		<td class="row2"><input class="post" type="text" name="smile_emotion" value="{SMILEY_EMOTICON}" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2"><input class="mainoption" type="submit" value="{L_SUBMIT}" /></td>
	</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_SMILE_EDIT -->

<!-- IF TPL_SMILE_IMPORT -->
<!--========================================================================-->

<h1>{L_SMILEY_TITLE}</h1>

<p>{L_SMILEY_IMPORT_INST}</p>
<br />

<form method="post" action="{S_SMILEY_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline">
	<tr>
		<th colspan="2">{L_SMILEY_IMPORT}</th>
	</tr>
	<tr>
		<td class="row2">{L_CHOOSE_SMILE_PAK}</td>
		<td class="row2">{S_SMILE_SELECT}</td>
	</tr>
	<tr>
		<td class="row1">{L_DEL_EXISTING_SMILEYS}</td>
		<td class="row1"><input type="checkbox" name="clear_current" value="1" /></td>
	</tr>
	<tr>
		<td class="row2" colspan="2" align="center">{L_SMILE_CONFLICTS}<br /><input type="radio" name="replace" value="1" checked="checked"/> {L_REPLACE_EXISTING} &nbsp; <input type="radio" name="replace" value="0" /> {L_KEEP_EXISTING}</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2"><input class="mainoption" name="import_pack" type="submit" value="{L_IMPORT}" /></td>
	</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_SMILE_IMPORT -->
