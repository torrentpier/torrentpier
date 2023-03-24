<table class="forumline dl_list">

<col class="row1">
<col class="row1">

<script type="text/javascript">
$(document).ready(function(){
	$('table.tablesorter').tablesorter({
		sortList: [[4,1]]
	});
});
</script>

<tr>
	<td colspan="2" class="catTitle"><a href="{DL_LIST_HREF}">
		{L_DL_LIST_AND_TORRENT_ACTIVITY}
	</a></td>
</tr>

<!-- BEGIN dl_users -->
	<!-- BEGIN users_row -->
	<tr>
		<td width="5%" class="nowrap"><span title=" {dl_users.users_row.DL_COUNT} "><b>{dl_users.users_row.DL_OPTION_NAME}</b></span></td>
		<td width="95%" class="tLeft med pad_4"><div style="{dl_users.users_row.DL_USERS_DIV_STYLE}"><b>{dl_users.users_row.DL_OPTION_USERS}</b></div></td>
	</tr>
	<!-- END users_row -->
<!-- END dl_users -->

<!-- BEGIN dl_counts -->
<tr>
	<td colspan="2">
		<table class="borderless bCenter mrg_4">
		<!-- BEGIN count_row -->
		<tr>
			<td><b>{dl_counts.count_row.DL_OPTION_NAME}:</b></td>
			<td>[ <b>{dl_counts.count_row.DL_OPTION_USERS}</b> ]</td>
		</tr>
		<!-- END count_row -->
		</table>
	</td>
</tr>
<!-- END dl_counts -->

<!-- IF SHOW_DL_LIST_TOR_INFO -->
<tr>
	<td colspan="2" class="borderless bCenter pad_8">
			{L_SIZE}:&nbsp; <b>{TOR_SIZE}</b>&nbsp; &nbsp;|&nbsp; &nbsp;
			{L_IS_REGISTERED}:&nbsp; <b>{TOR_LONGEVITY}</b>&nbsp; &nbsp;|&nbsp; &nbsp;
			{L_COMPLETED}:&nbsp; <b>{TOR_COMPLETED}</b>
	</td>
</tr>
<!-- ENDIF / SHOW_DL_LIST_TOR_INFO -->

<!-- BEGIN dl_list_none -->
<tr>
	<td colspan="2" class="pad_6"><!-- IF SHOW_DL_LIST && SHOW_TOR_ACT -->DL-List: <!-- ENDIF -->{L_NONE}</td>
</tr>
<!-- END dl_list_none -->

<!-- IF SHOW_TOR_ACT -->
	<!-- IF S_MODE_COUNT -->
		<tr>
			<td colspan="2" class="<!-- IF SHOW_DL_LIST -->row2<!-- ELSE -->row1<!-- ENDIF --> pad_2">
				<!-- IF not SEED_COUNT -->
				<p class="mrg_10">{SEEDER_LAST_SEEN}</p>
				<!-- ENDIF -->

				<!-- IF PEER_EXIST -->
				<div class="mrg_4 pad_4">
					<!-- IF SEED_COUNT || LEECH_COUNT -->
					<!-- IF SEED_COUNT -->
					<span class="seed">{L_SEEDERS}:&nbsp; <b>{SEED_COUNT}</b> &nbsp;[&nbsp; {TOR_SPEED_UP} &nbsp;]</span> &nbsp;
					<!-- ENDIF -->
					<!-- IF LEECH_COUNT -->
					<span class="leech">{L_LEECHERS}:&nbsp;	<b>{LEECH_COUNT}</b> &nbsp;[&nbsp; {TOR_SPEED_DOWN} &nbsp;]</span> &nbsp;
					<!-- ENDIF -->
					<!-- ENDIF / SEED_COUNT || LEECH_COUNT -->

					<!-- IF PEERS_FULL_LINK -->
					<a href="{SPMODE_FULL_HREF}" class="gen">{L_SPMODE_FULL}</a>
					<!-- ENDIF -->
				</div>
				<!-- ENDIF -->
			</td>
		</tr>

	<!-- ELSEIF S_MODE_NAMES -->

		<!-- IF SEED_LIST -->
		<tr>
			<td width="5%" class="seed"><b>{L_SEEDERS}</b><img width="116" class="spacer" src="{SPACER}" alt="" /></td>
			<td width="95%" class="seedsmall tLeft">{SEED_LIST}</td>
		</tr>
		<!-- ELSE -->
		<tr>
			<td colspan="2" class="row2 pad_4">
				<p class="mrg_10">{SEEDER_LAST_SEEN}</p>
			</td>
		</tr>
		<!-- ENDIF -->
		<!-- IF LEECH_LIST -->
		<tr>
			<td width="5%" class="leech"><b>{L_LEECHERS}</b><img width="116" class="spacer" src="{SPACER}" alt="" /></td>
			<td width="95%" class="leechsmall tLeft">{LEECH_LIST}</td>
		</tr>
		<!-- ENDIF -->

	<!-- ELSEIF S_MODE_FULL -->

		<tr>
		<td colspan="2">

		<!-- IF SEEDER_LAST_SEEN -->
		<div id="last_seed_info" class="row5 w60 mrg_4 bCenter">
			<p class="mrg_10">{SEEDER_LAST_SEEN}</p>
		</div>
		<!-- ENDIF -->

		<!-- IF PEER_EXIST -->
		<div id="full_details" class="bCenter" style="width: 97%;{PEERS_DIV_STYLE}">

		<!-- BEGIN sfull -->
		<a name="seeders"></a>
		<table class="borderless w60 bCenter">
		<tr>
			<td><p class="floatL" style="margin-top: 4px;"><b>{L_SEEDERS}</b>:</p></td>
		</tr>
		<tr>
			<td class="pad_0">

			<table cellpadding="0" class="peers w100 med tablesorter" id="seeders-tbl">
			<thead>
			<tr>
				<th class="{sorter: 'text'}"><b class="tbs-text">{L_DL_USER}</b><img width="130" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}" title="{L_DL_FORMULA}"><b class="tbs-text">{L_DL_ULR}</b><img width="40" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_UL}</b><img width="70" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_DL}</b><img width="70" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_UL_SPEED}</b><img width="75" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_DL_SPEED}</b><img width="75" class="spacer" src="{SPACER}" alt="" /></th>
				<!-- BEGIN iphead -->
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_IP_ADDRESS}</b><img width="75" class="spacer" src="{SPACER}" alt="" /></th>
				<!-- END iphead -->
				<!-- BEGIN porthead -->
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_PORT}</b><img width="75" class="spacer" src="{SPACER}" alt="" /></th>
				<!-- END porthead -->
			</tr>
			</thead>
			<!-- BEGIN srow -->
			<tr{sfull.srow.ROW_BGR}>
				<td class="tLeft" title="{sfull.srow.UPD_EXP_TIME}"><b>{sfull.srow.NAME}</b></td>
				<td>{sfull.srow.COMPL_PRC}</td>
				<td><u>{sfull.srow.UP_TOTAL_RAW}</u>{sfull.srow.UP_TOTAL}</td>
				<td><u>{sfull.srow.DOWN_TOTAL_RAW}</u>{sfull.srow.DOWN_TOTAL}</td>
				<td class="seedmed"><u>{sfull.srow.SPEED_UP_RAW}</u>{sfull.srow.SPEED_UP}</td>
				<td class="leechmed"><u>{sfull.srow.SPEED_DOWN_RAW}</u>{sfull.srow.SPEED_DOWN}</td>
				<!-- BEGIN ip -->
				<td>{sfull.srow.ip.IP}</td>
				<!-- END ip -->
				<!-- BEGIN port -->
				<td>{sfull.srow.port.PORT}</td>
				<!-- END port -->
			</tr>
			<!-- END srow -->
			</table>

			</td>
		</tr>
		</table>
		<!-- END sfull -->

		<!-- BEGIN lfull -->
		<a name="leechers"></a>
		<table class="borderless w60 bCenter">
		<tr>
			<td><p class="floatL" style="margin-top: 4px;"><b>{L_LEECHERS}</b>:</p></td>
		</tr>
		<tr>
			<td class="pad_0">

			<table cellpadding="0" class="peers w100 med tablesorter" id="leechers-tbl">
			<thead>
			<tr>
				<th class="{sorter: 'text'}"><b class="tbs-text">{L_DL_USER}</b><img width="130" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}" title="{L_DL_PERCENT}"><b class="tbs-text">%</b><img width="40" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_UL}</b><img width="70" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_DL}</b><img width="70" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_UL_SPEED}</b><img width="75" class="spacer" src="{SPACER}" alt="" /></th>
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_DL_SPEED}</b><img width="75" class="spacer" src="{SPACER}" alt="" /></th>
				<!-- BEGIN iphead -->
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_IP_ADDRESS}</b><img width="75" class="spacer" src="{SPACER}" alt="" /></th>
				<!-- END iphead -->
				<!-- BEGIN porthead -->
				<th class="{sorter: 'digit'}"><b class="tbs-text">{L_DL_PORT}</b><img width="75" class="spacer" src="{SPACER}" alt="" /></th>
				<!-- END porthead -->
			</tr>
			</thead>
			<!-- BEGIN lrow -->
			<tr{lfull.lrow.ROW_BGR}>
				<td class="tLeft" title="{lfull.lrow.UPD_EXP_TIME}"><b>{lfull.lrow.NAME}</b></td>
				<td title="{lfull.lrow.TOR_RATIO}">{lfull.lrow.COMPL_PRC}</td>
				<td><u>{lfull.lrow.UP_TOTAL_RAW}</u>{lfull.lrow.UP_TOTAL}</td>
				<td><u>{lfull.lrow.DOWN_TOTAL_RAW}</u>{lfull.lrow.DOWN_TOTAL}</td>
				<td class="seedmed"><u>{lfull.lrow.SPEED_UP_RAW}</u>{lfull.lrow.SPEED_UP}</td>
				<td class="leechmed"><u>{lfull.lrow.SPEED_DOWN_RAW}</u>{lfull.lrow.SPEED_DOWN}</td>
				<!-- BEGIN ip -->
				<td>{lfull.lrow.ip.IP}</td>
				<!-- END ip -->
				<!-- BEGIN port -->
				<td>{lfull.lrow.port.PORT}</td>
				<!-- END port -->
			</tr>
			<!-- END lrow -->
			</table>

			</td>
		</tr>
		</table>
		<!-- END lfull -->

		<div class="med tCenter mrg_4 warnColor1">{L_DL_INFO}</div>

		</div><!--/full_details-->
		<!-- ENDIF / PEER_EXIST -->

		</td>
		</tr>
	<!-- ENDIF / S_MODE_FULL -->
<!-- ENDIF / SHOW_TOR_ACT -->

<tr>
	<td colspan="2" class="row3 pad_4">
	&nbsp;
	<!-- IF DL_BUTTONS -->
	<form method="POST" action="{S_DL_ACTION}">{DL_HIDDEN_FIELDS}
		<!-- IF DL_BUT_WILL --><input type="submit" name="dl_set_will" value="{L_DLWILL}" class="liteoption" />&nbsp;<!-- ENDIF -->
		<!-- IF DL_BUT_DOWN --><input type="submit" name="dl_set_down" value="{L_DLDOWN}" class="liteoption" />&nbsp;<!-- ENDIF -->
		<!-- IF DL_BUT_COMPL --><input type="submit" name="dl_set_complete" value="{L_DLCOMPLETE}" class="liteoption" />&nbsp;<!-- ENDIF -->
		<!-- IF DL_BUT_CANCEL --><input type="submit" name="dl_set_cancel" value="{L_DLCANCEL}" class="liteoption" /><!-- ENDIF -->
	</form>
	<!-- ENDIF -->
	<!-- IF CALL_SEED --><form action="callseed.php?t={TOPIC_ID}" method="post"><input type="submit" value="{L_CALLSEED}" class="liteoption" />&nbsp;</form><!-- ENDIF -->
	&nbsp;
	</td>
</tr>

</table>
<div class="spacer_6"></div>
