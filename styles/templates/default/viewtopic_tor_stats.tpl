<table class="forumline dl_list">

	<col class="row1">

	<tr>
		<td class="catTitle">Статистика раздачи</td>
	</tr>

	<!-- IF TOR_CLOSED_BY_CPHOLD -->

	<tr>
		<td style="text-align: center;">
			<div id="closed-by-ch-msg" style="font-size: 24px; margin: 18px; color: red;"><!-- IF THIS_USER_ID == 193436 -->Ссылка закрыта по просьбе правообладателя<!-- ELSE -->Закрыто по просьбе правообладателя<!-- ENDIF --><!-- IF CPHOLD_MANE --> <a href="{PROFILE_URL}{CPHOLD_UID}"><i>{CPHOLD_MANE}</i></a><!-- ENDIF --></div>
			<!-- IF CAN_OPEN_CH_RELEASE -->
			<div style="font-size: 18px; margin: 12px;">[ <a href="#" onclick="this.onclick=''; ajax.change_tor_status( {TOPIC_ID}, {#TOR_NOT_APPROVED} ); return false;">Открыть</a> ]</div>
			<!-- ENDIF -->
		</td>
	</tr>

	<!-- ELSE -->

	<tr>
		<td class="borderless bCenter pad_8">
			Размер:&nbsp; <b>{TOR_SIZE}</b>&nbsp; &nbsp;|&nbsp; &nbsp;Зарегистрирован:&nbsp; <b>{TOR_LONGEVITY}</b>&nbsp; &nbsp;|&nbsp; &nbsp;.torrent скачан:&nbsp; <b>{TOR_COMPLETED}</b>
		</td>
	</tr>

	<!-- IF S_MODE_COUNT -->
	<tr>
		<td class="row5 pad_2 tCenter">

			<!-- IF SEEDER_LAST_SEEN -->
			<p class="mrg_10">{SEEDER_LAST_SEEN}</p>
			<!-- ENDIF -->

			<!-- IF SEED_COUNT || LEECH_COUNT -->
			<div class="mrg_4 pad_4">
				<!-- IF SEED_COUNT -->
				<span class="seed">Сиды:&nbsp; <b>{SEED_COUNT}</b> &nbsp;[&nbsp; {TOR_SPEED_UP} &nbsp;]</span>
				<!-- ENDIF -->
				<!-- IF LEECH_COUNT -->
				&nbsp;
				<span class="leech">Личи:&nbsp; <b>{LEECH_COUNT}</b> &nbsp;[&nbsp; {TOR_SPEED_DOWN} &nbsp;]</span>
				<!-- ENDIF -->
				<!-- IF PEERS_FULL_LINK && PEER_EXIST -->
				&nbsp;
				<a href="{SPMODE_FULL_HREF}" class="gen">Подробная статистика пиров</a>
				<!-- ENDIF -->
			</div>
			<!-- ENDIF / SEED_COUNT || LEECH_COUNT -->

		</td>
	</tr>
	<!-- ELSEIF S_MODE_FULL -->

	<tr>
		<td>

			<!-- IF SEEDER_LAST_SEEN -->
			<div id="last_seed_info" class="row5 w60 mrg_4 bCenter">
				<p class="mrg_10">{SEEDER_LAST_SEEN}</p>
			</div>
			<!-- ENDIF -->

			<!-- IF PEER_EXIST -->
			<style type="text/css">
				tbody.p-body u { display: none; }
				a.p-owner, a.p-owner:visited { color: #0000FF; font-size: 12px; text-decoration: none; }
			</style>
			<script type="text/javascript">
				$(document).ready(function(){
					$('table.tablesorter').tablesorter({
						<!-- IF NO_SORT_JS --><!-- ELSE -->sortList: [[4,1]]<!-- ENDIF -->
					});
				});
			</script>

			<div id="full_details" class="bCenter" style="width: 97%;{PEERS_DIV_STYLE}">

				<!-- IF SEED_EXIST -->
				<!-- BEGIN sfull -->
				<a name="seeders"></a>
				<table class="borderless w60 bCenter">
					<tr>
						<td>
							<p class="floatL" style="margin-top: 4px;"><b>Сиды</b>:</p>
							<!-- IF IS_AM -->
							<form method="post" action="{sfull.SEED_ORD_ACT}">
								<!-- BEGIN sorder -->
								<p class="floatR med">
									Упорядочить по: {sfull.sorder.SEED_ORDER_SELECT}
									<input type="submit" value="&#9660;" class="pOrdSel" name="psortdesc" /><input type="submit" value="&#9650;" class="pOrdSel" name="psortasc" />
								</p>
								<!-- END sorder -->
							</form>
							<!-- ENDIF / IS_AM -->
						</td>
					</tr>
					<tr>
						<td class="pad_0">

							<table cellpadding="0" class="peers w100 med tablesorter" id="seeders-tbl">
								<thead>
								<tr>
									<th class="{sorter: 'text'}"><b class="tbs-text">Username</b><img width="130" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}" title="Uploaded/TorrentSize"><b class="tbs-text">ULR</b><img width="40" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}"><b class="tbs-text">UL</b><img width="70" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}"><b class="tbs-text">DL</b><img width="70" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}"><b class="tbs-text">UL speed</b><img width="75" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}"><b class="tbs-text">DL speed</b><img width="75" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<!-- BEGIN iphead -->
									<th class="{sorter: 'digit'}" width="30%"><b class="tbs-text">IP</b><img width="105" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<!-- END iphead -->
									<!-- BEGIN porthead -->
									<th class="{sorter: 'digit'}"><b class="tbs-text">Port</b><img width="50" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<!-- END porthead -->
								</tr>
								</thead>
								<tbody class="p-body">
								<!-- BEGIN srow -->
								<tr>
									<td class="tLeft" title="{sfull.srow.UPD_EXP_TIME}"><b>{sfull.srow.NAME}</b></td>
									<td>{sfull.srow.COMPL_PRC}</td>
									<td><u>{sfull.srow.UP_TOTAL_INT}</u>{sfull.srow.UP_TOTAL}</td>
									<td><u>{sfull.srow.DOWN_TOTAL_INT}</u>{sfull.srow.DOWN_TOTAL}</td>
									<td class="seedmed"><u>{sfull.srow.SPEED_UP_INT}</u>{sfull.srow.SPEED_UP}</td>
									<td class="leechmed"><u>{sfull.srow.SPEED_DOWN_INT}</u>{sfull.srow.SPEED_DOWN}</td>
									<!-- BEGIN ip -->
									<td class="tRight"><u>{sfull.srow.ip.IP_INT}</u>{sfull.srow.ip.IP}</td>
									<!-- END ip -->
									<!-- BEGIN port -->
									<td>{sfull.srow.port.PORT}</td>
									<!-- END port -->
								</tr>
								<!-- END srow -->
								</tbody>
							</table>

						</td>
					</tr>
				</table>
				<!-- END sfull -->
				<!-- ENDIF / SEED_EXIST -->

				<!-- IF LEECH_EXIST -->
				<!-- BEGIN lfull -->
				<a name="leechers"></a>
				<table class="borderless w60 bCenter">
					<tr>
						<td>
							<p class="floatL" style="margin-top: 4px;"><b>Личи</b>:</p>
							<!-- IF IS_AM -->
							<form method="post" action="{lfull.LEECH_ORD_ACT}">
								<!-- BEGIN lorder -->
								<p class="floatR med">
									Упорядочить по: {lfull.lorder.LEECH_ORDER_SELECT}
									<input type="submit" value="&#9660;" class="pOrdSel" name="psortdesc" /><input type="submit" value="&#9650;" class="pOrdSel" name="psortasc" />
								</p>
								<!-- END lorder -->
							</form>
							<!-- ENDIF / IS_AM -->
						</td>
					</tr>
					<tr>
						<td class="pad_0">

							<table cellpadding="0" class="peers w100 med tablesorter" id="leechers-tbl">
								<thead>
								<tr>
									<th class="{sorter: 'text'}"><b class="tbs-text">Username</b><img width="130" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}" title="Complete percent"><b class="tbs-text">%</b><img width="40" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}"><b class="tbs-text">UL</b><img width="70" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}"><b class="tbs-text">DL</b><img width="70" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}"><b class="tbs-text">UL speed</b><img width="75" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<th class="{sorter: 'digit'}"><b class="tbs-text">DL speed</b><img width="75" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<!-- BEGIN iphead -->
									<th class="{sorter: 'digit'}" width="30%"><b class="tbs-text">IP</b><img width="105" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<!-- END iphead -->
									<!-- BEGIN porthead -->
									<th class="{sorter: 'digit'}"><b class="tbs-text">Port</b><img width="50" class="spacer" src="{IMG_URL}/spacer.gif" alt="" /></th>
									<!-- END porthead -->
								</tr>
								</thead>
								<tbody class="p-body">
								<!-- BEGIN lrow -->
								<tr{lfull.lrow.ROW_BGR}>
									<td class="tLeft" title="{lfull.lrow.UPD_EXP_TIME}"><b>{lfull.lrow.NAME}</b></td>
									<td title="{lfull.lrow.TOR_RATIO}">{lfull.lrow.COMPL_PRC}</td>
									<td><u>{lfull.lrow.UP_TOTAL_INT}</u>{lfull.lrow.UP_TOTAL}</td>
									<td><u>{lfull.lrow.DOWN_TOTAL_INT}</u>{lfull.lrow.DOWN_TOTAL}</td>
									<td class="seedmed"><u>{lfull.lrow.SPEED_UP_INT}</u>{lfull.lrow.SPEED_UP}</td>
									<td class="leechmed"><u>{lfull.lrow.SPEED_DOWN_INT}</u>{lfull.lrow.SPEED_DOWN}</td>
									<!-- BEGIN ip -->
									<td class="tRight"><u>{lfull.lrow.ip.IP_INT}</u>{lfull.lrow.ip.IP}</td>
									<!-- END ip -->
									<!-- BEGIN port -->
									<td>{lfull.lrow.port.PORT}</td>
									<!-- END port -->
								</tr>
								<!-- END lrow -->
								</tbody>
							</table>

						</td>
					</tr>
				</table>
				<!-- END lfull -->
				<!-- ENDIF / LEECH_EXIST -->

				<div class="med tCenter mrg_4 warnColor1">показаны данные <i><b>только за текущую сессию</b></i></div>

			</div><!--/full_details-->
			<!-- ENDIF / PEER_EXIST -->

		</td>
	</tr>
	<!-- ENDIF / S_MODE_FULL -->
	<!-- ENDIF / !TOR_CLOSED_BY_CPHOLD -->

	<!-- IF DL_BUTTONS -->
	<tr>
		<td class="row3 pad_4">
			<form method="post" action="dl_list.php" style="display: none;">
				<input type="hidden" name="f" value="{FORUM_ID}" />
				<input type="hidden" name="t" value="{TOPIC_ID}" />
				<input type="hidden" name="mode" value="set_dl_status" />

				<input id="will-btn" type="submit" name="dl_set_will" value="1" />&nbsp;
				<input id="canc-btn" type="submit" name="dl_set_cancel" value="1" />
			</form>
			<a href="#" onclick="if( window.confirm('Добавить раздачу в список ваших «Будущих закачек»?') ){ $('#will-btn').click() } return false;" class="med">Добавить в «Будущие закачки»</a>
			&nbsp;&middot;&nbsp;
			<a href="#" onclick="if( window.confirm('Удалить раздачу из списка ваших закачек?') ){ $('#canc-btn').click() } return false;" class="med">Удалить из списка закачек</a>
		</td>
	</tr>
	<!-- ENDIF -->

</table>
<div class="spacer_6"></div>
