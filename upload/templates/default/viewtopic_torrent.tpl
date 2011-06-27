<table class="forumline dl_list">

<col class="row1">
<col class="row1">

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
			<td>
			<form method="POST" action="{sfull.SEED_ORD_ACT}">

				<p class="floatL" style="margin-top: 4px;"><b>{L_SEEDERS}</b>:</p>
				<!-- BEGIN sorder -->
				<p class="floatR med">
					{L_SORT_BY}: {sfull.sorder.SEED_ORDER_SELECT}
					<input type="submit" value="&#9660;" class="pOrdSel" name="psortdesc" /><input type="submit" value="&#9650;" class="pOrdSel" name="psortasc" />
				</p>
				<!-- END sorder -->

			</form>
			</td>
		</tr>
		<tr>
			<td class="pad_0">

			<table cellpadding="0" class="peers w100 med">
			<tr>
				<th rowspan="2">Username<img width="130" class="spacer" src="{SPACER}" alt="" /></td>
				<th rowspan="2" title="Uploaded/TorrentSize">UL<br />Ratio<img width="40" class="spacer" src="{SPACER}" alt="" /></td>
				<th colspan="2" width="30%">Transfers<img width="140" class="spacer" src="{SPACER}" alt="" /></td>
				<th colspan="2" width="35%">Speed<img width="150" class="spacer" src="{SPACER}" alt="" /></td>
				<!-- BEGIN iphead -->
				<th rowspan="2" width="30%">IP<img width="105" class="spacer" src="{SPACER}" alt="" /></td>
				<!-- END iphead -->
				<!-- BEGIN porthead -->
				<th rowspan="2">Port<img width="50" class="spacer" src="{SPACER}" alt="" /></td>
				<!-- END porthead -->
			</tr>
			<tr>
				<th>up<img width="70" class="spacer" src="{SPACER}" alt="" /></td>
				<th>down<img width="70" class="spacer" src="{SPACER}" alt="" /></td>
				<th title="{sfull.SEEDERS_UP_TOT}">up<img width="75" class="spacer" src="{SPACER}" alt="" /></td>
				<th>down<img width="75" class="spacer" src="{SPACER}" alt="" /></td>
			</tr>
			<!-- BEGIN srow -->
			<tr{sfull.srow.ROW_BGR}>
				<td class="tLeft" title="{sfull.srow.UPD_EXP_TIME}"><b>{sfull.srow.NAME}</b></td>
				<td>{sfull.srow.COMPL_PRC}</td>
				<td>{sfull.srow.UP_TOTAL}</td>
				<td>{sfull.srow.DOWN_TOTAL}</td>
				<td class="seedmed">{sfull.srow.SPEED_UP}</td>
				<td class="leechmed">{sfull.srow.SPEED_DOWN}</td>
				<!-- BEGIN ip -->
				<td class="tRight">{sfull.srow.ip.IP}</td>
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
			<td>
			<form method="POST" action="{lfull.LEECH_ORD_ACT}">

				<p class="floatL" style="margin-top: 4px;"><b>{L_LEECHERS}</b>:</p>
				<!-- BEGIN lorder -->
				<p class="floatR med">
					{L_SORT_BY}: {lfull.lorder.LEECH_ORDER_SELECT}
					<input type="submit" value="&#9660;" class="pOrdSel" name="psortdesc" /><input type="submit" value="&#9650;" class="pOrdSel" name="psortasc" />
				</p>
				<!-- END lorder -->

			</form>
			</td>
		</tr>
		<tr>
			<td class="pad_0">

			<table cellpadding="0" class="peers w100 med">
			<tr>
				<th rowspan="2">Username<img width="130" class="spacer" src="{SPACER}" alt="" /></td>
				<th rowspan="2" title="Complete percent"><b>%</b><img width="40" class="spacer" src="{SPACER}" alt="" /></td>
				<th colspan="2" width="30%">Transfers<img width="140" class="spacer" src="{SPACER}" alt="" /></td>
				<th colspan="2" width="35%">Speed<img width="150" class="spacer" src="{SPACER}" alt="" /></td>
				<!-- BEGIN iphead -->
				<th rowspan="2" width="30%">IP<img width="105" class="spacer" src="{SPACER}" alt="" /></td>
				<!-- END iphead -->
				<!-- BEGIN porthead -->
				<th rowspan="2">Port<img width="50" class="spacer" src="{SPACER}" alt="" /></td>
				<!-- END porthead -->
			</tr>
			<tr>
				<th>up<img width="70" class="spacer" src="{SPACER}" alt="" /></td>
				<th>down<img width="70" class="spacer" src="{SPACER}" alt="" /></td>
				<th title="{lfull.LEECHERS_UP_TOT}">up<img width="75" class="spacer" src="{SPACER}" alt="" /></td>
				<th title="{lfull.LEECHERS_DOWN_TOT}">down<img width="75" class="spacer" src="{SPACER}" alt="" /></td>
			</tr>
			<!-- BEGIN lrow -->
			<tr{lfull.lrow.ROW_BGR}>
				<td class="tLeft" title="{lfull.lrow.UPD_EXP_TIME}"><b>{lfull.lrow.NAME}</b></td>
				<td title="{lfull.lrow.TOR_RATIO}">{lfull.lrow.COMPL_PRC}</td>
				<td>{lfull.lrow.UP_TOTAL}</td>
				<td>{lfull.lrow.DOWN_TOTAL}</td>
				<td class="seedmed">{lfull.lrow.SPEED_UP}</td>
				<td class="leechmed">{lfull.lrow.SPEED_DOWN}</td>
				<!-- BEGIN ip -->
				<td class="tRight">{lfull.lrow.ip.IP}</td>
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

		<div class="med tCenter mrg_4 warnColor1">показаны данные <i><b>только за текущую сессию</b></i></div>

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
		<!-- IF DL_BUT_WILL --><input type="submit" name="dl_set_will" value="{L_DL_WILL}" class="liteoption" />&nbsp;<!-- ENDIF -->
		<!-- IF DL_BUT_DOWN --><input type="submit" name="dl_set_down" value="{L_DL_DOWN}" class="liteoption" />&nbsp;<!-- ENDIF -->
		<!-- IF DL_BUT_COMPL --><input type="submit" name="dl_set_complete" value="{L_DL_COMPLETE}" class="liteoption" />&nbsp;<!-- ENDIF -->
		<!-- IF DL_BUT_CANCEL --><input type="submit" name="dl_set_cancel" value="{L_DL_CANCEL}" class="liteoption" /><!-- ENDIF -->
	</form>
	<!-- ENDIF -->
	&nbsp;
	</td>
</tr>

</table>
<div class="spacer_6"></div>