<span class="maintitle">{PAGE_TITLE}</span>

<table width="100%">
<tr>
	<td width="100%" class="nav vBottom">
		<a href="{U_INDEX}">{T_INDEX}</a>
		<em>&raquo;</em>&nbsp;<a href="{U_USER_PROFILE}">{L_RETURN_PROFILE}</a>
	</td>
</tr>
</table>

<form method="post" name="bonus_list" action="{S_MODE_ACTION}">

<table class="forumline tCenter">
<tr>
	<th colspan="3">{MY_BONUS}</th>
</tr>
<tr class="row3 med">
	<td class="bold tCenter">{L_DESCRIPTION}</td>
	<td class="bold tCenter">{L_PRICE}</td>
	<td class="bold tCenter">{L_BONUS_SELECT}</td>
</tr>

<!-- BEGIN bonus_upload -->
<tr class="{bonus_upload.ROW_CLASS} med">
	<td class="tLeft">{bonus_upload.DESC}</td>
	<td>{bonus_upload.PRICE}</td>
	<td style="width: 5%;"><input type="radio" name="bonus_id" value="{bonus_upload.ID}" /></td>
</tr>
<!-- END bonus_upload -->

<tr>
	<td class="catBottom tCenter" colspan="3">
		<input type="submit" name="submit" value="{L_EXCHANGE}" class="lite" />
	</td>
</tr>
</table>

</form>

<!--bottom_info-->
<div class="bottom_info">

	<div class="spacer_4"></div>

	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->
