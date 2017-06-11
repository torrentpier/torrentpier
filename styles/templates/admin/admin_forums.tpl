<!-- IF TPL_EDIT_FORUM -->
<!--========================================================================-->

<script type="text/javascript">
function toggle_cat_list (val)
{
	if (val == -1) {
		$p('cat_list').className = '';
		$p('show_on_index').className = 'hidden';
	}
	else {
		$p('cat_list').className = 'hidden';
		$p('show_on_index').className = '';
	}
	return false;
}
</script>

<h1>{L_FORUM_TITLE}</h1>

<p>{L_FORUM_EDIT_DELETE_EXPLAIN}</p>
<br />

<form method="post" action="{S_FORUM_ACTION}" name="frm">
{S_HIDDEN_FIELDS}

<table class="forumline pad_4">
<col class="row1" width="20%">
<col class="row2" width="80%">
	<tr>
		<th colspan="2">{L_FORUM_SETTINGS}</th>
	</tr>
	<tr>
		<td>{L_FORUM_NAME}</td>
		<td><input style="width: 96%;" type="text" name="forumname" value="{FORUM_NAME}" class="post" /></td>
	</tr>
	<tr>
		<td>{L_FORUM_DESC}</td>
		<td><textarea style="width: 96%;" rows="2" wrap="virtual" name="forumdesc" class="post">{DESCRIPTION}</textarea></td>
	</tr>
	<tr>
		<td>{L_SF_PARENT_FORUM}</td>
		<td><select onchange="toggle_cat_list(this.value)" name="forum_parent">{S_PARENT_FORUM}</select></td>
	</tr>
	<tr id="cat_list" class="{CAT_LIST_CLASS}">
		<td>{L_CATEGORY}</td>
		<td><select name="c">{S_CAT_LIST}</select></td>
	</tr>
	<tr id="show_on_index" class="{SHOW_ON_INDEX_CLASS}">
		<td>{L_SF_SHOW_ON_INDEX}</td>
		<td>
			<label><input type="radio" name="show_on_index" value="1" <!-- IF SHOW_ON_INDEX -->checked="checked"<!-- ENDIF -->  />{L_YES}</label>&nbsp;&nbsp;
			<label><input type="radio" name="show_on_index" value="0" <!-- IF not SHOW_ON_INDEX -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
		</td>
	</tr>
	<tr>
		<td>{L_FORUM_STATUS}</td>
		<td><select name="forumstatus">{S_STATUS_LIST}</select></td>
	</tr>
	<tr>
		<td>{L_REG_TORRENTS}</td>
		<td>{ALLOW_REG_TRACKER} &nbsp; {L_SELF_MODERATED}: {SELF_MODERATED}  &nbsp; {L_ALLOW_PORNO_TOPIC}: {ALLOW_PORNO_TOPIC}</td>
	</tr>
	<tr>
		<td>{L_DESIGNER}</td>
		<td>{TPL_SELECT} - {L_FOR_NEW_TEMPLATE}</td>
	</tr>
	<tr>
		<td>{L_FORUM_PRUNING}</td>
		<td>{L_PRUNE_DAYS} <input type="text" name="prune_days" value="{PRUNE_DAYS}" size="4" class="post" /> {L_DAYS} &nbsp;<i class="med">(0 = {L_DISABLED})</i></td>
	</tr>
	<tr>
		<td>{L_SORT_BY}</td>
		<td>
			<select name="forum_display_sort">{S_FORUM_DISPLAY_SORT_LIST}</select>&nbsp;
			<select name="forum_display_order">{S_FORUM_DISPLAY_ORDER_LIST}</select>&nbsp;
		</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2">
			<input type="submit" name="submit" value="{S_SUBMIT_VALUE}" class="mainoption" />
		</td>
	</tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_EDIT_FORUM -->

<!-- IF TPL_EDIT_CATEGORY -->
<!--========================================================================-->

<h1>{L_EDIT_CATEGORY}</h1>

<p>{L_EDIT_CATEGORY_EXPLAIN}</p>
<br />

<form method="post" action="{S_FORUM_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1">
<tr>
	<th>{L_EDIT_CATEGORY}</th>
</tr>
<tr>
	<td class="pad_12 tCenter">
		{L_CATEGORY}:
		<input type="text" name="cat_title" size="60" value="{CAT_TITLE}" />&nbsp;
		<input type="submit" name="submit" value="{S_SUBMIT_VALUE}" class="bold" />
	</td>
</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_EDIT_CATEGORY -->

<!-- IF TPL_DELETE_FORUM -->
<!--========================================================================-->

<h1>{DELETE_TITLE}</h1>

<p>{L_FORUM_DELETE_EXPLAIN}</p>
<br />

<form method="post" action="{S_FORUM_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1" width="30%">
<col class="row1" width="70%">
<tr>
	<th colspan="2">{DELETE_TITLE}</th>
</tr>
<tbody class="pad_8">
<tr>
	<td>{CAT_FORUM_NAME}</td>
	<td><b>{WHAT_TO_DELETE}</b></td>
</tr>
<tr>
	<td>{L_MOVE_CONTENTS}</td>
	<td>
		<!-- IF NOWHERE_TO_MOVE -->
		{NOWHERE_TO_MOVE}
		<!-- ELSE -->
		<select name="to_id">{MOVE_TO_OPTIONS}</select>
		<!-- ENDIF -->
	</td>
</tr>
</tbody>
<tr>
	<td colspan="2" class="catBottom pad_4">
		<input type="submit" name="submit" value="{S_SUBMIT_VALUE}" class="bold" />
	</td>
</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_DELETE_FORUM -->

<!-- IF TPL_FORUMS_LIST -->
<!--========================================================================-->

<style type="text/css">
table.forumline + table.forumline { border-top-width: 0; }
</style>

<script type="text/javascript">
function hl (id, on)
{
	$p(id).style.color = (on == 1) ? '#FF4500' : '';
}
</script>

<h1>{L_FORUM_TITLE} <!-- IF FORUMS_COUNT --><b class="gen">[{L_FORUMS_IN_CAT}: {FORUMS_COUNT}]</b><!-- ENDIF --></h1>

<p>{L_FORUM_EDIT_DELETE_EXPLAIN}</p>
<br />

<form method="post" action="{S_FORUM_ACTION}">

<!-- BEGIN c -->
<table class="forumline">
	<tr class="row3">
		<td class="tCenter"><a class="gen" title="{L_MOVE_UP}" href="{c.U_CAT_MOVE_UP}"><b>&nbsp;&#8593;&nbsp;</b></a><a class="gen" title="{L_MOVE_DOWN}" href="{c.U_CAT_MOVE_DOWN}"><b>&nbsp;&#8595;&nbsp;</b></a></td>
		<td colspan="5" width="100%" class="nowrap">
			<span class="floatL">
				<a href="{c.U_VIEWCAT}"><span class="gen"><b>{c.CAT_DESC}</b></span></a>
			</span>
			<span class="nowrap floatR">
				<a class="gen" href="{c.U_CREATE_FORUM}">{L_CREATE_FORUM}</a>
				&middot;
				<a class="gen" href="{c.U_CAT_EDIT}">{L_EDIT}</a>
				&middot;
				<a class="gen" href="{c.U_CAT_DELETE}">{L_DELETE}</a>
			</span>
		</td>
		<td>{L_PRUNE}</td>
	</tr>
	<!-- BEGIN f -->
	<tr class="row1 hl-tr" onmouseover="hl('fname_{c.f.FORUM_ID}', 1);" onmouseout="hl('fname_{c.f.FORUM_ID}', 0);">
		<td class="gen" align="center"><a class="gen" title="{L_MOVE_UP}" href="{c.f.U_FORUM_MOVE_UP}"><b>&nbsp;&#8593;&nbsp;</b></a><a class="gen" title="{L_MOVE_DOWN}" href="{c.f.U_FORUM_MOVE_DOWN}"><b>&nbsp;&#8595;&nbsp;</b></a></td>
		<td class="small" align="center" nowrap="nowrap"><a class="small" href="{c.f.ADD_SUB_HREF}" title="Add subforum">&nbsp;+sub&nbsp;</a><!-- <span title="Order index">{c.f.ORDER} [{c.f.FORUM_ID}-{c.f.FORUM_PARENT}]</span> --></td>
		<td width="100%" {c.f.SF_PAD}><a title="{c.f.FORUM_DESC}" class="{c.f.FORUM_NAME_CLASS}" href="{c.f.U_VIEWFORUM}" target="_new"><!-- IF c.f.SHOW_ON_INDEX --><b><!-- ENDIF --><span id="fname_{c.f.FORUM_ID}">{c.f.FORUM_NAME}</span><!-- IF c.f.SHOW_ON_INDEX --></b><!-- ENDIF --></a></td>
		<td class="small tCenter" title="{L_TOPICS_SHORT}"><em class="med" style="color: grey">{L_TOPICS_SHORT}:</em> {c.f.NUM_TOPICS}</td>
		<td class="small tCenter" title="{L_POSTS_SHORT}"><em class="med" style="color: grey">{L_POSTS_SHORT}:</em> {c.f.NUM_POSTS}</td>
		<td class="med nowrap tCenter">
			&nbsp;
			<a class="med" href="{c.f.U_FORUM_EDIT}">edit</a>
			&nbsp;&middot;&nbsp;
			<a class="med" href="{c.f.U_FORUM_PERM}">perm</a>
			&nbsp;&middot;&nbsp;
			<a class="med" href="{c.f.U_FORUM_RESYNC}">sync</a>
			&nbsp;&middot;&nbsp;
			<a class="med" href="{c.f.U_FORUM_DELETE}">x</a>
			&nbsp;
		</td>
		<td class="small" align="center" nowrap="nowrap"><b>{c.f.PRUNE_DAYS}</b></td>
	</tr>
	<!-- END f -->
</table>
<!-- END c -->

<table class="forumline">
	<tr>
		<td class="row3">
			<input class="post" type="text" name="categoryname" />
			<input type="submit" name="addcategory" value="{L_CREATE_CATEGORY}" />
		</td>
	</tr>
</table>

</form>

<br />
<p><a href="{U_ALL_FORUMS}"><b>{L_SHOW_ALL_FORUMS_ON_ONE_PAGE}</b></a></p>
<br />

<!--========================================================================-->
<!-- ENDIF / TPL_FORUMS_LIST -->
