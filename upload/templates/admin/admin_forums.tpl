
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

<form name="frm" action="{S_FORUM_ACTION}" method="post">
{S_HIDDEN_FIELDS}
{SID_HIDDEN}

<table class="forumline">
	<tr>
		<th colspan="2">{L_FORUM_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1">{L_FORUM_NAME}</td>
		<td class="row2"><input style="width: 96%;" type="text" name="forumname" value="{FORUM_NAME}" class="post" /></td>
	</tr>
	<tr>
		<td class="row1">{L_FORUM_DESC}</td>
		<td class="row2"><textarea style="width: 96%;" rows="2" wrap="virtual" name="forumdesc" class="post">{DESCRIPTION}</textarea></td>
	</tr>
	<tr>
		<td class="row1">{L_SF_PARENT_FORUM}</td>
		<td class="row2"><select onchange="toggle_cat_list(this.value)" name="forum_parent">{S_PARENT_FORUM}</select></td>
	</tr>
	<tr id="cat_list" class="{CAT_LIST_CLASS}">
		<td class="row1">{L_CATEGORY}</td>
		<td class="row2"><select name="c">{S_CAT_LIST}</select></td>
	</tr>
	<tr id="show_on_index" class="{SHOW_ON_INDEX_CLASS}">
		<td class="row1">{L_SF_SHOW_ON_INDEX}</td>	
		<td class="row2">
		    <label><input type="radio" name="show_on_index" value="1" <!-- IF SHOW_ON_INDEX -->checked="checked"<!-- ENDIF -->  />{L_YES}</label>&nbsp;&nbsp;
			<label><input type="radio" name="show_on_index" value="0" <!-- IF not SHOW_ON_INDEX -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
		</td>		
	</tr>
	<tr>
		<td class="row1">{L_FORUM_STATUS}</td>
		<td class="row2"><select name="forumstatus">{S_STATUS_LIST}</select></td>
	</tr>
	<tr>
	    <td class="row1">{L_REG_TORRENTS}</td>
	    <td class="row2">{ALLOW_REG_TRACKER} &nbsp; {L_SELF_MODERATED}: {SELF_MODERATED}  &nbsp; {L_ALLOW_PORNO_TOPIC}: {ALLOW_PORNO_TOPIC}</td>
    </tr>
	<tr>
	    <td class="row1">{L_FORUM_PRUNING}</td>
	    <td class="row2">{L_PRUNE_DAYS} <input type="text" name="prune_days" value="{PRUNE_DAYS}" size="4" class="post" /> {L_DAYS} &nbsp;<i class="med">(0 = {L_DISABLED})</span></td>
    </tr>
	<tr>
		<td class="row1">{L_SORT_BY}</td>
		<td class="row2">
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

<form action="{S_FORUM_ACTION}" method="post">
{S_HIDDEN_FIELDS}
{SID_HIDDEN}

<table class="forumline">
<col class="row1">
<tr>
	<th>{L_EDIT_CATEGORY}</th>
</tr>
<tr>
	<td class="pad_12 tCenter">
		{L_CATEGORY}:
		<input type="text" name="cat_title" size="35" value="{CAT_TITLE}" />&nbsp;
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

<form action="{S_FORUM_ACTION}" method="post">
{S_HIDDEN_FIELDS}
{SID_HIDDEN}

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
table.forumline + table.forumline { border-top-width: 0px; }
</style>

<script type="text/javascript">
function hl (id, on)
{
	$p(id).style.color = (on == 1) ? '#FF4500' : '';
}
</script>

<h1>{L_FORUM_TITLE}</h1>

<p>{L_FORUM_EDIT_DELETE_EXPLAIN}</p>
<br />

<form method="post" action="{S_FORUM_ACTION}">
{SID_HIDDEN}

<!-- BEGIN catrow -->
<table class="forumline">
	<tr class="row3">
		<td class="tCenter"><a class="gen" title="{L_MOVE_UP}" href="{catrow.U_CAT_MOVE_UP}"><b>&nbsp;&#8593;&nbsp;</b></a><a class="gen" title="{L_MOVE_DOWN}" href="{catrow.U_CAT_MOVE_DOWN}"><b>&nbsp;&#8595;&nbsp;</b></a></td>
		<td colspan="5" width="100%" class="nowrap">
			<span class="floatL">
				<a href="{catrow.U_VIEWCAT}"><span class="gen"><b>{catrow.CAT_DESC}</b></span></a>
			</span>
			<span class="nowrap floatR">
				<a class="gen" href="{catrow.U_CREATE_FORUM}">{L_CREATE_FORUM}</a>
				|
				<a class="gen" href="{catrow.U_CAT_EDIT}">{L_EDIT}</a>
				|
				<a class="gen" href="{catrow.U_CAT_DELETE}">{L_DELETE}</a>
			</span>
		</td>
		<td class="small">{L_PRUNE}</td>
	</tr>
	<!-- BEGIN forumrow -->
	<tr class="row1 hl-tr" onmouseover="hl('fname_{catrow.forumrow.FORUM_ID}', 1);" onmouseout="hl('fname_{catrow.forumrow.FORUM_ID}', 0);">
		<td class="gen" align="center"><a class="gen" title="{L_MOVE_UP}" href="{catrow.forumrow.U_FORUM_MOVE_UP}"><b>&nbsp;&#8593;&nbsp;</b></a><a class="gen" title="{L_MOVE_DOWN}" href="{catrow.forumrow.U_FORUM_MOVE_DOWN}"><b>&nbsp;&#8595;&nbsp;</b></a></td>
		<td width="100%" {catrow.forumrow.SF_PAD}><a {catrow.forumrow.FORUM_DESC} class="{catrow.forumrow.FORUM_NAME_CLASS}" href="{catrow.forumrow.U_VIEWFORUM}" target="_new"><!-- IF catrow.forumrow.SHOW_ON_INDEX --><b><!-- ENDIF --><span id="fname_{catrow.forumrow.FORUM_ID}">{catrow.forumrow.FORUM_NAME}</span><!-- IF catrow.forumrow.SHOW_ON_INDEX --></b><!-- ENDIF --></a>&nbsp;&nbsp;<em class="med" style="color: grey">{L_TOPICS_SHORT}:</em> <span class="med">{catrow.forumrow.NUM_TOPICS}</span> <em class="med" style="color: grey">{L_POSTS_SHORT}:</em> <span class="med">{catrow.forumrow.NUM_POSTS}</span></td>
		<td class="med" align="center"><a class="med" href="{catrow.forumrow.U_FORUM_EDIT}">&nbsp;{L_EDIT}&nbsp;</a></td>
		<td class="small" align="center" nowrap="nowrap"><a class="small" href="{catrow.forumrow.ADD_SUB_HREF}" title="Add subforum">&nbsp;+sub&nbsp;</a><!-- <span title="Order index">{catrow.forumrow.ORDER} [{catrow.forumrow.FORUM_ID}-{catrow.forumrow.FORUM_PARENT}]</span> --></td>
		<td class="med" align="center"><a class="med" href="{catrow.forumrow.U_FORUM_RESYNC}">&nbsp;{L_RESYNC}&nbsp;</a></td>
		<td class="med" align="center"><a class="med" href="{catrow.forumrow.U_FORUM_DELETE}">&nbsp;{L_REMOVE}&nbsp;</a></td>
		<td class="small" align="center" nowrap="nowrap">{catrow.forumrow.PRUNE_DAYS}</td>
	</tr>
	<!-- END forumrow -->
</table>
<!-- END catrow -->

<table class="forumline">
	<tr>
		<td colspan="7" class="row3"><input class="post" type="text" name="categoryname" /> <input type="submit" name="addcategory" value="{L_CREATE_CATEGORY}" /></td>
	</tr>
</table>

</form>

<br />
<p><a href="{U_ALL_FORUMS}"><b>{L_SHOW_ALL_FORUMS_ON_ONE_PAGE}</b></a></p>
<br />


<!--========================================================================-->
<!-- ENDIF / TPL_FORUMS_LIST -->
