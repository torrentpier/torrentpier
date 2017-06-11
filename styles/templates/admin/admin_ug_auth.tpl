<!-- IF TPL_AUTH_UG_MAIN -->
<!--========================================================================-->

<style type="text/css">
#page_content {
	padding: 0;
}
div.tScrollContainer {
	margin-top: -1px;
	overflow: auto;
}
td:last-child {
	padding-right: 20px; /* prevent Mozilla scrollbar from hiding cell content */
}
table>tbody {
	overflow: auto; /* child selector syntax which IE6 and older do not support */
}
thead tr {
	position: relative;
	top: expression($p("tScrollCont").scrollTop); /* IE5+ only */
}
.tPerm {
	background-color: #B4BBC8; margin-top: -2px;
}
.tdHead {
	font-size: 10px;
	padding: 1px 1px 1px 1px;
	color: #000000;
	line-height: 10px;
	letter-spacing: -1px;
	text-align: center;
}
.fName {
	font-size: 11px;
	color: #000000;
	padding: 1px 2px 1px 3px;
}
.yes, .no, .yesDisabled, .noDisabled, .yesMOD, .noMOD {
	text-align: center;
	cursor: pointer;
}
.yes {
	font-size: 11px;
	font-weight: bold;
	color: #FFF5EE;
	background: #006666;
	padding: 1px 2px 0 2px;
}
.yesDisabled {
	font-size: 11px;
	font-weight: normal;
	color: #E4E4E4;
	background: #00888A;
	padding: 1px 2px 0 2px;
	cursor: default;
}
.yesMOD {
	font-size: 10px;
	font-weight: bold;
	color: #F0F0F0;
	background: #006666;
	padding: 1px 2px 1px 2px;
}
.no {
	font-size: 10px;
	font-weight: bold;
	color: #FFECD7;
	background: #9C5928;
	padding: 2px 1px 3px 2px;
}
.noDisabled {
	font-size: 10px;
	font-weight: normal;
	color: #FFECD7;
	background: #BC6D32;
	padding: 2px 1px 3px 2px;
	cursor: default;
}
.noMOD {
	font-size: 10px;
	font-weight: bold;
	color: #FFECD7;
	background: #9C5928;
	padding: 1px 2px 1px 2px;
}
</style>

<form method="post" action="{S_AUTH_ACTION}">
{S_HIDDEN_FIELDS}

<div class="tScrollContainer" id="tScrollCont">

<table class="tPerm w100" id="tPerm" cellspacing="1" cellpadding="2">
<thead>
	<tr>
		<td class="row2 tdHead" valign="bottom" width="100%">{L_FORUM}</td>
		<td
			id="type_{AUTH_MOD_BF}"
			class="row2 tdHead" valign="bottom">{L_MODERATOR_STATUS}</td>
		<!-- BEGIN acltype -->
		<td
			id="type_{acltype.ACL_TYPE_BF}"
			class="row2 tdHead" valign="bottom">{acltype.ACL_TYPE_NAME}</td>
		<!-- END acltype -->
	</tr>
</thead>
<tfoot>
	<tr>
		<td colspan="{S_COLUMN_SPAN}" class="row1" align="center">
			<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />
		</td>
	</tr>
</tfoot>
<tbody id="tScrollBody">

<!-- BEGIN c -->
		<tr>
			<td colspan="{S_COLUMN_SPAN}" class="row5 med">&nbsp;&nbsp;<b><a href="{c.CAT_HREF}">{c.CAT_TITLE}</a></b></td>
		</tr>

	<!-- BEGIN f -->
		<tr>
		<!-- IF c.f.IS_MODERATOR -->
		<td
			id="fname_{c.f.FORUM_ID}"
			class="row2 fName"><div>{c.f.SF_SPACER}<b>{c.f.FORUM_NAME}</b></div></td>
		<!-- ELSE -->
		<td
			id="fname_{c.f.FORUM_ID}"
			class="row1 fName"><div>{c.f.SF_SPACER}{c.f.FORUM_NAME}</div></td>
		<!-- ENDIF -->
			<td
				id="td_{c.f.FORUM_ID}_{AUTH_MOD_BF}"
	<!-- IF not c.f.DISABLED -->
				onclick="flip_mod('{c.f.FORUM_ID}', '{AUTH_MOD_BF}');"
	<!-- ENDIF -->
				onmouseover="hl('{c.f.FORUM_ID}', '{AUTH_MOD_BF}', 1);"
				onmouseout="hl('{c.f.FORUM_ID}', '{AUTH_MOD_BF}', 0);"
				class="{c.f.MOD_CLASS}">{c.f.MOD_STATUS}</td>
	<!-- BEGIN acl -->
			<td
				id="td_{c.f.acl.FORUM_ID}_{c.f.acl.ACL_TYPE_BF}"
	<!-- IF not c.f.acl.DISABLED -->
				onclick="flip_perm('{c.f.acl.FORUM_ID}', '{c.f.acl.ACL_TYPE_BF}');"
	<!-- ENDIF -->
				onmouseover="hl('{c.f.acl.FORUM_ID}', '{c.f.acl.ACL_TYPE_BF}', 1);"
				onmouseout="hl('{c.f.acl.FORUM_ID}', '{c.f.acl.ACL_TYPE_BF}', 0);"
				class="{c.f.acl.ACL_CLASS}<!-- IF c.f.acl.DISABLED -->Disabled<!-- ENDIF -->">{c.f.acl.PERM_SIGN}</td>
	<!-- END acl -->
		</tr>
	<!-- END f -->
<!-- END c -->

</tbody>
</table>

</div><!--/tScrollCont-->

<br />
<p><a href="{U_ALL_FORUMS}"><b>{L_SHOW_ALL_FORUMS_ON_ONE_PAGE}</b></a></p>
<br />

<!-- BEGIN c -->
<!-- BEGIN f -->
<input type="hidden" name="auth[{c.f.FORUM_ID}][{AUTH_MOD_BF}]" value="{c.f.AUTH_MOD_VAL}" id="cb_{c.f.FORUM_ID}_{AUTH_MOD_BF}" <!-- IF c.f.DISABLED -->disabled="disabled"<!-- ENDIF --> />
<!-- BEGIN acl -->
<input type="hidden" name="auth[{c.f.acl.FORUM_ID}][{c.f.acl.ACL_TYPE_BF}]" value="{c.f.acl.ACL_VAL}" id="cb_{c.f.acl.FORUM_ID}_{c.f.acl.ACL_TYPE_BF}" <!-- IF c.f.acl.DISABLED -->disabled="disabled"<!-- ENDIF --> />
<!-- END acl -->
<!-- END f -->
<!-- END c -->

<input type="hidden" name="c" value="{SELECTED_CAT}" />
</form>

<form method="post" action="{S_AUTH_ACTION}">
{S_HIDDEN_FIELDS}

<h3>{L_PERMISSIONS} ({T_AUTH_TITLE})</h3>
<p class="gen">{T_USER_OR_GROUPNAME}: <span class="maintitle">{USER_OR_GROUPNAME}</span></p>
<!-- IF USER_LEVEL --><b>{USER_LEVEL}</b> &nbsp; <input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" /><!-- ENDIF -->
<p>{T_AUTH_EXPLAIN}</p>

</form>

<script type="text/javascript">
var tCont = $p('tScrollCont');
var tBody = $p('tScrollBody');

if (tCont.offsetHeight > document.body.clientHeight) {
	tCont.style.height = document.body.clientHeight;
}

if (is_moz) {
	if (tBody.offsetHeight > document.body.clientHeight - 65) {
		tBody.style.height = document.body.clientHeight - 65;
	}
	tCont.style.maxWidth = document.body.clientWidth - 20;
}

function flip_perm (f_id, acl_id)
{
	id = f_id + '_' + acl_id;
	var cb = $p('cb_' + id);
	var td = $p('td_' + id);

	if (cb.value == 1) {
		cb.value = 0;
		td.className = 'no';
		td.innerHTML = '{NO_SIGN}';
	}	else {
		cb.value = 1;
		td.className = 'yes';
		td.innerHTML = '{YES_SIGN}';
	}
	mark_changed(f_id, acl_id);
	return false;
}

function flip_mod (f_id, acl_id)
{
	id = f_id + '_' + acl_id;
	var cb = $p('cb_' + id);
	var td = $p('td_' + id);

	if (cb.value == 1) {
		cb.value = 0;
		td.className = 'noMOD';
		td.innerHTML = '{T_MOD_NO}';
	} else {
		cb.value = 1;
		td.className = 'yesMOD';
		td.innerHTML = '{T_MOD_YES}';
	}
	mark_changed(f_id, acl_id);
	return false;
}

function hl (f_id, acl_id, on)
{
	var ac  = $p('type_' + acl_id);
	var fn = $p('fname_' + f_id);

	if (on == 1) {
		ac.style.color = fn.style.color = '#FF4500';
	} else {
		ac.style.color = fn.style.color = '#000000';
	}
	return false;
}

function mark_changed (f_id, acl_id)
{
	var fn = $p('fname_' + f_id);
	fn.style.backgroundColor = '#FFEFD5';
}
</script>

<!--========================================================================-->
<!-- ENDIF / TPL_AUTH_UG_MAIN -->

<!-- IF TPL_SELECT_USER -->
<!--========================================================================-->

<h1>{L_USER_ADMIN}</h1>

<p>{L_USER_AUTH_EXPLAIN}</p>
<br /><br />

<form method="post" name="post" action="{S_AUTH_ACTION}">
<input type="hidden" name="mode" value="edit" />
{S_HIDDEN_FIELDS}

<table class="forumline wAuto">
<tr>
	<th>{L_USER_SELECT}</th>
</tr>
<tr>
	<td class="row1 tCenter pad_8">
		<p class="mrg_12">
			<input type="text" class="post" name="username" maxlength="50" size="20" />
			<input type="button" name="usersubmit" value="{L_FIND_USERNAME}" onclick="window.open('{U_SEARCH_USER}', '_bbsearch', 'HEIGHT=250,resizable=yes,WIDTH=400');return false;" />
		</p>
		<p class="mrg_12">
			<input type="submit" name="submituser" value="{L_LOOK_UP_USER}" class="bold" />
		</p>
	</td>
</tr>
</table>

</form>

<br /><br /><br /><br />

<!--========================================================================-->
<!-- ENDIF / TPL_SELECT_USER -->

<!-- IF TPL_SELECT_GROUP -->
<!--========================================================================-->

<h1>{L_GROUP_ADMINISTRATION}</h1>

<p>{L_GROUP_AUTH_EXPLAIN}</p>
<br /><br />

<form method="post" action="{S_AUTH_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline wAuto">
	<tr>
		<th>{L_GROUP_SELECT}</th>
	</tr>
	<tr>
		<td class="row1 pad_8">
			&nbsp;{S_GROUP_SELECT}&nbsp;
			<input type="submit" value="{L_LOOK_UP_GROUP}" class="mainoption" />&nbsp;
		</td>
	</tr>
</table>

</form>

<br /><br /><br /><br />

<!--========================================================================-->
<!-- ENDIF / TPL_SELECT_GROUP -->
