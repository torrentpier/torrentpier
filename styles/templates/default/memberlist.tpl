<h1 class="pagetitle">{PAGE_TITLE}</h1>

<form method="post" action="{S_MODE_ACTION}" name="post">
<table width="100%">
	<tr>
		<td align="right" class="med" nowrap="nowrap">{L_SORT_BY}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}" /></td>
	</tr>
	<tr>
		<td align="right" nowrap="nowrap">
			<span class="genmed">
				<input type="text" class="post" name="username" maxlength="25" size="25" tabindex="1" value="{S_USERNAME}" />&nbsp;<input type="submit" name="submituser" value="{L_FIND_USERNAME}" class="mainoption" />
			</span>
		</td>
	</tr>
 	<tr>
		<td align="right" class="med">{L_SORT_PER_LETTER}:&nbsp;{S_LETTER_SELECT}{S_LETTER_HIDDEN}</td>
	</tr>
</table>
</form>

<table class="forumline tablesorter">
<thead>
<tr>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">#</b></th>
	<th class="{sorter: 'text'}" ><b class="tbs-text">{L_USERNAME}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_PM}</b></th>
	<!-- IF IS_ADMIN --><th class="{sorter: 'text'}" ><b class="tbs-text">{L_EMAIL}</b></th><!-- ENDIF -->
	<th class="{sorter: 'text'}" ><b class="tbs-text">{L_LOCATION}</b></th>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">{L_JOINED}</b></th>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">{L_POSTS_SHORT}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_WEBSITE}</b></th>
	<!-- BEGIN no_username -->
	<tr>
		<td class="row1" align="center" colspan="9"><span class="gen">&nbsp;{no_username.NO_USER_ID_SPECIFIED}&nbsp;</span></td>
	</tr>
	<!-- END no_username -->
</tr>
</thead>
<!-- BEGIN memberrow -->
<tr class="{memberrow.ROW_CLASS} tCenter">
	<td>{memberrow.ROW_NUMBER}</td>
	<td><b>{memberrow.USER}</b></td>
	<td>{memberrow.PM}</td>
	<!-- IF IS_ADMIN --><td>{memberrow.EMAIL}</td><!-- ENDIF -->
	<td>{memberrow.FROM}</td>
	<td class="small">
		<u>{memberrow.JOINED_RAW}</u>
		{memberrow.JOINED}
	</td>
	<td>{memberrow.POSTS}</td>
	<td>{memberrow.WWW}</td>
</tr>
<!-- END memberrow -->
<tfoot>
<tr>
	<td class="catBottom" colspan="9">&nbsp;</td>
</tr>
</tfoot>
</table>

<div class="bottom_info">

	<div class="nav">
		<p style="float: left">{PAGE_NUMBER}</p>
		<p style="float: right">{PAGINATION}</p>
		<div class="clear"></div>
	</div>

	<div class="spacer_4"></div>

	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->
