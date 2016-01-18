
<div class="clear"></div>

<!-- IF #IS_CP_HOLDER -->

<div class="spacer_12"></div>
<table id="tor_blocked" class="error">
	<tr><td><p class="error_msg" style="height: 200px; overflow: auto;"><?php echo file_get_contents(BB_PATH .'/misc/html/copyright_holders_terms.html'); ?></p></td></tr>
</table>

<!-- ELSE -->

<!-- IF SHOW_TOR_BLOCKED -->
<div class="spacer_12"></div>
<table id="tor_blocked" class="error">
	<tr><td><p class="error_msg">{TOR_BLOCKED_MSG}</p></td></tr>
</table>
<div class="spacer_12"></div>
<!-- ENDIF -->

<!-- IF SHOW_RATIO_WARN -->
<div class="spacer_12"></div>
<table id="tor_blocked" class="error">
	<tr><td><p class="error_msg">{$bb_cfg['bt_ratio_warning_msg']}</p></td></tr>
</table>
<!-- ENDIF -->

<!-- ENDIF / ! #IS_CP_HOLDER -->

<div class="spacer_12"></div>

<!-- IF SHOW_TOR_REGGED -->
<div id="tor-reged">
	<table class="attach bordered med">
		<tr class="row3 tCenter">
			<td colspan="3" class="med">{$bb_cfg['tor_help_links_top']}</td>
		</tr>
		<tr class="row1">
			<td width="15%">Торрент:</td>
			<td width="70%"><!-- IF TOR_FROZEN -->Зарегистрирован<!-- ELSE -->{TRACKER_REG_LINK}<!-- ENDIF --> &nbsp;<span title="Зарегистрирован">[ {REGED_TIME} ]</span></td>
			<td width="15%" rowspan="4" class="tCenter pad_6">
				<!-- IF TOR_FROZEN and not AUTH_MOD -->
				<p><img src="{IMG_URL}/icon_attach.gif" /></p>
				<p>Скачать .torrent</p>
				<!-- ELSE -->
				<p><a href="{$bb_cfg['dl_url']}{TOPIC_ID}" class="dl-stub"><img src="{IMG_URL}/attach_big.gif" /></a></p>
				<p><a href="{$bb_cfg['dl_url']}{TOPIC_ID}" class="dl-stub dl-link">Скачать .torrent</a></p>
				<!-- ENDIF -->
				<p class="small">{FILESIZE}</p>
				<!-- IF SHOW_TOR_FILELIST --><p style="padding-top: 6px;"><input id="tor-filelist-btn" type="button" class="lite" style="width: 120px;" value="Список файлов" /></p><!-- ENDIF -->
			</td>
		</tr>
		<tr class="row1">
			<td>Статус:</td>
			<td<!-- IF #IS_AM --> style="padding: 6px 4px;"<!-- ENDIF -->>
			<span id="tor-{TOPIC_ID}-text">{TOR_STATUS_ICON} <a href="viewtopic.php?t={$bb_cfg['tor_status_topic_id']}" class="med"><b>{TOR_STATUS_TEXT}</b></a>
			<!-- IF TOR_STATUS_TIME -->
			&nbsp;&middot;&nbsp; <a href="{PROFILE_URL}{TOR_STATUS_UID}" class="med"><i>{TOR_STATUS_USERNAME}</i></a>&nbsp; &middot;&nbsp; <i>{TOR_STATUS_TIME}</i> назад
			<!-- ENDIF -->
			</span>
			<!-- IF AUTH_MOD -->
			<div class="spacer_6"></div>
			Изменить на: <span id="tor-{TOPIC_ID}"></span>
			[ <a href="#" onclick="ajax.change_tor_status( {TOPIC_ID}, $('#tor-{TOPIC_ID} select').val() ); return false;" class="med bold nowrap">Изменить</a> ]

			<script type="text/javascript">$('#tor-{TOPIC_ID}').html( $('#tor-status-sel').html() );</script>
			<!-- ENDIF -->
			</td>
		</tr>
		<tr class="row1">
			<td>.torrent скачан:</td>
			<td>{TOR_COMPLETED}</td>
		</tr>
		<tr class="row1">
			<td>Размер:</td>
			<td>{TOR_SIZE}</td>
		</tr>
		<tr class="row3 tCenter">
			<td colspan="3" height="20">
				<!-- IF TOR_CONTROLS -->
				<form method="post" action="{TOR_ACTION}" class="tokenized">
					<input type="hidden" name="t" value="{TOPIC_ID}" />

					<select name="tor_action">
						<!-- IF AUTH_MOD -->
						<option value="unreg_tor_and_close_topic" selected="selected"> &nbsp;Разрегистрировать и закрыть &nbsp;</option>
						<option value="unreg_tor_and_move_topic"> &nbsp;Разрегистрировать и перенести &nbsp;</option>
						<!-- ENDIF -->
						<!-- IF SHOW_CPHOLD_OPT -->
						<option value="copyright_close"> &nbsp;Закрыть по просьбе правообладателя &nbsp;</option>
						<!-- ENDIF -->
					</select>
					<label>
						<input name="confirm" id="tor-confirm" type="checkbox" value="1" />&nbsp;Подтвердите&nbsp;
					</label>
					<input name="" type="submit" value="Отправить" style="width: 110px;"
							onclick="var $cfm = $('#tor-confirm'); if( !$cfm.attr('checked') ){ $cfm.parent('label').addClass('hl-err-label'); return false; }"
							/>&nbsp;

				</form>
				<!-- ELSEIF SHOW_ADS -->
				{$bb_cfg['tor_help_links_bottom']}
				<!-- ELSE -->
				&nbsp;
				<!-- ENDIF -->
			</td>
		</tr>
	</table>

	<!-- IF SHOW_TOR_FILELIST -->
	<script type="text/javascript">
		function humn_size (size) {
			var i = 0;
			var units = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
			while ((size/1024) >= 1) {
				size = size/1024;
				i++;
			}
			size = new String(size);
			if (size.indexOf('.') != -1) {
				size = size.substring(0, size.indexOf('.') + 3);
			}
			return size + ' ' + units[i];
		}

		$(document).ready(function(){
			$('#tor-filelist-btn').click(function(){
				$('#tor-fl-wrap').show();
				$('#tor-filelist').load('viewtorrent.php', {t: {TOPIC_ID}}, function(){
					$('#tor-filelist > ul.tree-root').treeview({
						control: "#tor-fl-treecontrol"
					});
					$('#tor-filelist i').each(function(){
						var size_bytes = this.innerHTML.replace(/\D/g, '');
						$(this).prepend('<s>'+ humn_size(size_bytes) +'</s> ');
					});
				});
				$(this).hide();
				$('#tor-fl-treecontrol a').click(function(){ this.blur(); });
				return false;
			});
			<!-- IF OPEN_TOR_FILELIST -->
			$('#tor-filelist-btn').click();
			<!-- ENDIF -->
		});
	</script>

	<style type="text/css">
		#tor-fl-wrap { width: 95%; margin: 12px auto 0; display: none; }
		#tor-filelist {
			margin: 2px; padding: 10px 8px 4px; border: 1px solid #A5AFB4; background: #F8F8F8; max-height: 300px; overflow: auto;
		}
		#tor-filelist i { color: #7A7A7A; padding-left: 4px; }
		#tor-filelist s { color: #0000FF; text-decoration: none; }
		#tor-filelist .b { font-weight: bold; }
		#tor-filelist .tor-root-dir { font-size: 13px; font-weight: bold; line-height: 12px; padding-left: 4px; }
		#tor-fl-treecontrol a { padding: 0 8px; font-size: 11px; text-decoration: none; }
		.tor-fl-hide { height: 12px; }
	</style>
	<div id="tor-fl-wrap">
		<div id="tor-fl-treecontrol">
			<a href="#">Свернуть директории</a>&middot;<a href="#">Развернуть</a>&middot;<a href="#">Переключить</a>&middot;<a href="#" onclick="$('#tor-filelist').toggleClass('tor-fl-hide'); return false;">Спрятать/Открыть</a>
		</div>
		<div id="tor-filelist" class="med"><span class="loading-1">загружается...</span></div>
	</div>
	<!-- ENDIF -->

	<div class="spacer_12"></div>
</div><!--/tor-reged-->
<!-- ENDIF / SHOW_TOR_REGGED -->