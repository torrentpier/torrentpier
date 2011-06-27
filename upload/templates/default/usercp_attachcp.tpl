<h1>{L_UACP} :: {USERNAME}</h1>

<form method="post" name="attach_list" action="{S_MODE_ACTION}">
{S_USER_HIDDEN}

<table width="100%">
<tr>
	<td width="100%" class="nav vBottom"><a href="{U_INDEX}">{T_INDEX}</a></td>
	<td class="tRight nowrap med">
		{L_SORT_BY}: {S_MODE_SELECT}
		{L_ORDER} {S_ORDER_SELECT}
		<input type="submit" name="submit" value="{L_SUBMIT}" class="lite" />
	</td>
</tr>
</table>

<script type="text/javascript">
$(document).ready(function(){
	$('input.a-chbox').click(function(){ $('#a-'+this.value).toggleClass('hl-selected-topic'); });
});
</script>

<table class="forumline tablesorter">
<col class="row2">
<col class="row1">
<col class="row1">
<col class="row1">
<col class="row2">
<col class="row2">
<col class="row2">
<col class="row2">
<col class="row2">
<thead>
<tr>
	<th class="{sorter: 'digit'}">#</th>
	<th class="{sorter: false}">{L_POSTED_IN_TOPIC}</th>
	<th class="{sorter: 'text'}">{L_FILENAME}</th>
	<th class="{sorter: false}">{L_FILECOMMENT}</th>
	<th class="{sorter: 'text'}">{L_EXTENSION}</th>
	<th class="{sorter: 'digit'}">{L_SIZE}</th>
	<th class="{sorter: 'digit'}">{L_DOWNLOADS}</th>
	<th class="{sorter: 'digit'}">{L_POST_TIME}</th>
	<th class="{sorter: false}">{L_DELETE}</th>
</tr>
</thead>

<tbody>
<!-- BEGIN attachrow -->
<tr id="a-{attachrow.ATTACH_ID}" class="tCenter">
	<td>{attachrow.ROW_NUMBER}</td>
	<td><span class="gen">{attachrow.POST_TITLE}</span></td>
	<td><a href="{attachrow.U_VIEW_ATTACHMENT}" class="gen">{attachrow.FILENAME}</a></td>
	<td>{attachrow.COMMENT}</td>
	<td>{attachrow.EXTENSION}</td>
	<td>
		<u>{attachrow.SIZE_RAW}</u>
		<p>{attachrow.SIZE}</p>
	</td>
	<td>{attachrow.DOWNLOAD_COUNT}</td>
	<td>
		<u>{attachrow.POST_TIME_RAW}</u>
		<span class="small">{attachrow.POST_TIME}</span>
	</td>
	<td>{attachrow.S_DELETE_BOX}{attachrow.S_HIDDEN}</td>
</tr>
<!-- END attachrow -->
</tbody>
<tr>
	<td class="catBottom tRight" colspan="9">
		<input type="submit" name="delete" value="{L_DELETE_MARKED}" class="lite" />
 </td>
</tr>
</table>

</form>

<p class="small bold tRight">
	<a href="javascript:select_switch(true);" class="small">{L_MARK_ALL}</a>
	::
	<a href="javascript:select_switch(false);" class="small">{L_UNMARK_ALL}</a>
</p>

<!--bottom_info-->
<div class="bottom_info">

	<div class="spacer_6"></div>

	<div class="nav">
		<p style="float: left">{PAGE_NUMBER}</p>
		<p style="float: right">{PAGINATION}</p>
		<div class="clear"></div>
	</div>

	<div class="spacer_4"></div>

	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->

<script type="text/javascript">
function select_switch(status)
{
	for (i = 0; i < document.attach_list.length; i++)
	{
		document.attach_list.elements[i].checked = status;
	}
}
</script>