<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<table class="forumline tablesorter">
<thead>
<tr>
	<th width="35%" class="{sorter: 'text'}"><b class="tbs-text">{L_USERNAME}</b></th>
	<th width="35%" class="{sorter: 'digit'}"><b class="tbs-text">{L_LAST_UPDATED}</b></th>
	<!-- IF IS_ADMIN --><th width="30%" class="{sorter: 'digit'}"><b class="tbs-text">{L_IP_ADDRESS}</b></th><!-- ENDIF -->
</tr>
<tr>
	<td class="catTitle" colspan="3">{TOTAL_REGISTERED_USERS_ONLINE} | {L_ALL_USERS} {TOTAL_USERS_ONLINE}</td>
</tr>
</thead>
<!-- BEGIN reg_user_row -->
<tr class="{reg_user_row.ROW_CLASS}">
	<td><b>{reg_user_row.USER}</b></td>
	<td class="tCenter"><u>{reg_user_row.LASTUPDATE_RAW}</u>{reg_user_row.LASTUPDATE}</td>
	<!-- IF IS_ADMIN --><td class="tCenter"><a href="{reg_user_row.U_WHOIS_IP}" class="gen" target="_blank">{reg_user_row.USERIP}</a></td><!-- ENDIF -->
</tr>
<!-- END reg_user_row -->
<tfoot>
<tr>
	<td class="catTitle" colspan="3">{TOTAL_GUEST_USERS_ONLINE}</td>
</tr>
<!-- BEGIN guest_user_row -->
<tr class="{guest_user_row.ROW_CLASS}">
	<td>{guest_user_row.USER}</td>
	<td class="tCenter">{guest_user_row.LASTUPDATE}</td>
	<!-- IF IS_ADMIN --><td class="tCenter"><a href="{guest_user_row.U_WHOIS_IP}" class="gen" target="_blank">{guest_user_row.USERIP}</a></td><!-- ENDIF -->
</tr>
<!-- END guest_user_row -->
</tfoot>
</table>

<div class="spacer_4"></div>

<div class="bottom_info">

	<p style="float: left">{L_ONLINE_EXPLAIN}</p>

	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->