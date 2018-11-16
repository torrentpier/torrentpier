<!-- IF TPL_EDIT_GROUP -->
<!--========================================================================-->

<h1>{L_GROUP_ADMINISTRATION}</h1>

<form action="{S_GROUP_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline wAuto">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{T_GROUP_EDIT_DELETE}</th>
</tr>
<tr>
	<td width="30%">{L_GROUP_NAME}:</td>
	<td width="70%"><input class="post" type="text" name="group_name" size="35" maxlength="40" value="{GROUP_NAME}" /></td>
</tr>
<tr>
	<td>{L_GROUP_DESCRIPTION}:</td>
	<td><textarea class="post" name="group_description" rows="5" cols="51">{GROUP_DESCRIPTION}</textarea></td>
</tr>
<tr>
	<td>{L_GROUP_MODERATOR}:</td>
	<td><input class="post" type="text" class="post" name="username" maxlength="50" size="20" value="{GROUP_MODERATOR}" /> &nbsp; <input type="submit" name="usersubmit" value="{L_FIND_USERNAME}" class="liteoption" onClick="window.open('{U_SEARCH_USER}', '_bbsearch', 'HEIGHT=250,resizable=yes,WIDTH=400');return false;" /></td>
</tr>
<tr>
	<td>{L_GROUP_STATUS}:</td>
	<td class="row2 med">
		<div><input type="radio" name="group_type" value="{S_GROUP_OPEN_TYPE}" {S_GROUP_OPEN_CHECKED} /> {L_GROUP_OPEN}</div>
		<div><input type="radio" name="group_type" value="{S_GROUP_CLOSED_TYPE}" {S_GROUP_CLOSED_CHECKED} /> {L_GROUP_CLOSED}</div>
		<div><input type="radio" name="group_type" value="{S_GROUP_HIDDEN_TYPE}" {S_GROUP_HIDDEN_CHECKED} /> {L_GROUP_HIDDEN}</div>
	</td>
</tr>
<tr>
	<td>{L_RELEASE_GROUP}</td>
	<td>
		<label><input type="radio" name="release_group" value="1" <!-- IF RELEASE_GROUP -->checked="checked"<!-- ENDIF --> />{L_YES}</label>
		<label><input type="radio" name="release_group" value="0" <!-- IF not RELEASE_GROUP -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<!-- BEGIN group_edit -->
<tr>
	<td>{L_DELETE_OLD_GROUP_MOD}</td>
	<td>
		<input type="checkbox" name="delete_old_moderator" value="1"> {L_YES}
		<h6>{L_DELETE_OLD_GROUP_MOD_EXPL}</h6>
	</td>
</tr>
<tr>
	<td>{L_GROUP_DELETE}:</td>
	<td><input type="checkbox" name="group_delete" value="1"> {L_GROUP_DELETE_CHECK}</td>
</tr>
<!-- END group_edit -->
<tr>
	<td class="catBottom" colspan="2">
		<input type="submit" name="group_update" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" name="reset" class="liteoption" />
	</td>
</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_EDIT_GROUP -->

<!-- IF TPL_GROUP_SELECT -->
<!--========================================================================-->

<h1>{L_GROUP_ADMINISTRATION}</h1>

<p>{L_GROUP_ADMIN_EXPLAIN}</p>

<br /><br />

<form method="post" action="{S_GROUP_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline wAuto">
	<tr>
		<th>{L_SELECT_GROUP}</th>
	</tr>
	<!-- IF S_GROUP_SELECT -->
	<tr>
		<td class="row1" style="padding: 10px 30px;">
			{S_GROUP_SELECT}&nbsp;&nbsp;<input type="submit" name="edit" value="{L_LOOK_UP_GROUP}" class="mainoption" />
		</td>
	</tr>
	<!-- ENDIF -->
	<tr>
		<td class="catBottom"><input type="submit" class="liteoption" name="new" value="{L_CREATE_NEW_GROUP}" /></td>
	</tr>
</table>
</form>

<br /><br /><br /><br />

<!--========================================================================-->
<!-- ENDIF / TPL_GROUP_SELECT -->
