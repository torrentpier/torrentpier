<!-- IF TPL_AUTH_FORUM_LIST -->
<!--========================================================================-->

<h1>{L_AUTH_CONTROL_CATEGORY}</h1>

<p>{L_FORUM_AUTH_LIST_EXPLAIN}</p>
<br />

<table class="forumline med">
	<tr>
		<th>{L_FORUM_NAME}</th>
		<!-- BEGIN forum_auth_titles -->
		<th>{forum_auth_titles.CELL_TITLE}</th>
		<!-- END forum_auth_titles -->
	</tr>
	<!-- BEGIN cat_row -->
	<tr>
		<td class="cat catTitle" colspan="{S_COLUMN_SPAN}"><a href="{cat_row.CAT_URL}">{cat_row.CAT_NAME}</a></td>
	</tr>
	<!-- BEGIN forum_row -->
	<tr>
		<td class="{cat_row.forum_row.ROW_CLASS}<!-- IF cat_row.forum_row.IS_SUBFORUM --> sf<!-- ENDIF -->">{cat_row.forum_row.FORUM_NAME}</td>
		<!-- BEGIN forum_auth_data -->
		<td class="{cat_row.forum_row.ROW_CLASS} med tCenter" title="{cat_row.forum_row.forum_auth_data.AUTH_EXPLAIN}">{cat_row.forum_row.forum_auth_data.CELL_VALUE}</td>
		<!-- END forum_auth_data -->
	</tr>
	<!-- END forum_row -->
	<!-- END cat_row -->
</table>
<br />

<!--========================================================================-->
<!-- ENDIF / TPL_AUTH_FORUM_LIST -->

<!-- IF TPL_AUTH_CAT -->
<!--========================================================================-->

<h1>{L_PERMISSIONS_LIST}</h1>

<p>{L_CAT_AUTH_LIST_EXPLAIN}</p>

<h2>{L_CATEGORY} : {CAT_NAME}</h2>

<table class="forumline med">
	<tr>
		<th>{L_FORUM_NAME}</th>
		<!-- BEGIN forum_auth_titles -->
		<th>{forum_auth_titles.CELL_TITLE}</th>
		<!-- END forum_auth_titles -->
	</tr>
	<!-- BEGIN cat_row -->
	<tr>
		<td class="catTitle" colspan="{S_COLUMN_SPAN}"><a href="{cat_row.CAT_URL}">{cat_row.CAT_NAME}</a></td>
	</tr>
	<!-- BEGIN forum_row -->
	<tr>
		<td class="{cat_row.forum_row.ROW_CLASS}<!-- IF cat_row.forum_row.IS_SUBFORUM --> sf<!-- ENDIF -->">{cat_row.forum_row.FORUM_NAME}</td>
		<!-- BEGIN forum_auth_data -->
		<td class="{cat_row.forum_row.ROW_CLASS}" align="center"><span class="med" title="{cat_row.forum_row.forum_auth_data.AUTH_EXPLAIN}">{cat_row.forum_row.forum_auth_data.CELL_VALUE}</span></td>
		<!-- END forum_auth_data -->
	</tr>
	<!-- END forum_row -->
	<!-- END cat_row -->
</table>
<br />

<form method="post" action="{S_FORUMAUTH_ACTION}">
<table class="forumline med">
	<tr>
		<th>&nbsp;</th>
		<!-- BEGIN forum_auth_titles -->
		<th>{forum_auth_titles.CELL_TITLE}</th>
		<!-- END forum_auth_titles -->
	</tr>
	<tr>
		<td class="row1">{CAT_NAME}</td>
		<!-- BEGIN forum_auth_data -->
		<td class="row1" align="center">{forum_auth_data.S_AUTH_LEVELS_SELECT}</td>
		<!-- END forum_auth_data -->
	</tr>
	<tr>
		<td colspan="{S_COLUMN_SPAN}" class="catBottom">{S_HIDDEN_FIELDS}
			<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />
			&nbsp;&nbsp;
			<input type="reset" value="{L_RESET}" name="reset" class="liteoption" />
		</td>
	</tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_AUTH_CAT -->
