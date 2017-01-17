<h1>{L_AUTH_CONTROL_FORUM}</h1>

<p>{L_FORUM_AUTH_EXPLAIN}</p>
<br />

<!-- IF TPL_AUTH_SELECT_FORUM -->
<!--========================================================================-->

<br />

<form method="post" action="{S_AUTH_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline wAuto">
	<tr>
		<th>{L_SELECT_A_FORUM}</th>
	</tr>
	<tr>
		<td class="row1 tCenter">
			<p class="mrg_12">{S_AUTH_SELECT}</p>
			<p class="mrg_12"><input type="submit" value="{L_LOOK_UP_FORUM}" /><p class="mrg_8">
		</td>
	</tr>
</table>

</form>

<br /><br /><br /><br />
<!--========================================================================-->
<!-- ENDIF / TPL_AUTH_SELECT_FORUM -->

<!-- IF TPL_EDIT_FORUM_AUTH -->
<!--========================================================================-->

<h2>{L_FORUM}: {FORUM_NAME}</h2>

<form method="post" action="{S_FORUMAUTH_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline wAuto">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_AUTH_CONTROL_FORUM}</th>
</tr>
<!-- BEGIN forum_auth -->
<tr class="tCenter">
	<td style="padding: 5px 20px;">{forum_auth.CELL_TITLE}</td>
	<td style="padding: 5px 20px;">{forum_auth.S_AUTH_LEVELS_SELECT}</td>
</tr>
	<!-- END forum_auth -->
<tr>
	<td colspan="2" class="med tCenter">{U_SWITCH_MODE}</td>
</tr>
<tr>
	<td colspan="2" class="catBottom">
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	</td>
</tr>
</table>

</form>
<!--========================================================================-->
<!-- ENDIF / TPL_EDIT_FORUM_AUTH -->
