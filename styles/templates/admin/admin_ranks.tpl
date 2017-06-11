<!-- IF TPL_RANKS_EDIT -->
<!--========================================================================-->

<h1>{L_RANKS_TITLE}</h1>

<p>{L_RANKS_EXPLAIN}</p>
<br />

<form action="{S_RANK_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline wAuto">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_RANKS_TITLE}</th>
</tr>
<tr>
	<td width="40%"><h4>{L_RANK_TITLE}</h4></td>
	<td>
		<input class="post" type="text" name="title" size="60" maxlength="40" value="{RANK}" />
	</td>
</tr>
<tr>
	<td width="40%"><h4>{L_STYLE_COLOR}</h4><br />
		<h6>{L_STYLE_COLOR_FAQ}</h6>
	</td>
	<td>
		<input class="post" type="text" name="style" size="60" maxlength="40" value="{STYLE}" />
	</td>
</tr>
<tr>
	<td valign="top"><h4>{L_RANK_IMAGE}:</h4><br />
		<h6>{L_RANK_IMAGE_EXPLAIN}</h6></td>
	<td>
		<input class="post" type="text" name="rank_image" size="60" maxlength="255" value="{IMAGE}" />
		<br /><br />
		{IMAGE_DISPLAY}
	</td>
</tr>
<tr>
	<td class="catBottom" colspan="2">
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp; &nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	</td>
</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_RANKS_EDIT -->

<!-- IF TPL_RANKS_LIST -->
<!--========================================================================-->

<h1>{L_RANKS_TITLE}</h1>

<p>{L_RANKS_EXPLAIN}</p>
<br />

<form method="post" action="{S_RANKS_ACTION}">

<table class="forumline w80">
<tr>
	<th>{L_RANK_TITLE}</th>
	<th>{L_RANK_IMAGE}</th>
	<th>{L_EDIT}</th>
	<th>{L_DELETE}</th>
</tr>
<!-- BEGIN ranks -->
<tr class="{ranks.ROW_CLASS} tCenter">
	<td><div class="{ranks.STYLE}">{ranks.RANK}</div></td>
	<td>{ranks.IMAGE_DISPLAY}</td>
	<td><a href="{ranks.U_RANK_EDIT}">{L_EDIT}</a></td>
	<td><a href="{ranks.U_RANK_DELETE}">{L_DELETE}</a></td>
</tr>
<!-- END ranks -->
<tr>
	<td class="catBottom" colspan="4">
		<input type="submit" class="mainoption" name="add" value="{L_ADD_NEW_RANK}" />
	</td>
</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_RANKS_LIST -->
