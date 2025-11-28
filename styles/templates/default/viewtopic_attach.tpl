<div class="clear"></div>

<div class="spacer_8"></div>

<!-- BEGIN unregistered_torrent -->
<table class="attach bordered med">
	<tr class="row3">
		<th colspan="3">{unregistered_torrent.DOWNLOAD_NAME}</th>
	</tr>
	<tr class="row1">
		<td width="15%">{L_TORRENT}:</td>
		<td width="70%">{unregistered_torrent.TRACKER_LINK}</td>
		<td width="15%" rowspan="3" class="tCenter pad_6">
			<p>{unregistered_torrent.S_UPLOAD_IMAGE}</p>
			<p>{L_DOWNLOAD}</p>
			<p class="small">{unregistered_torrent.FILESIZE}</p>
		</td>
	</tr>
	<tr class="row1">
		<td>{L_ADDED}:</td>
		<td>{unregistered_torrent.POSTED_TIME}</td>
	</tr>
	<tr class="row1">
		<td>{L_DOWNLOADED}:</td>
		<td>{unregistered_torrent.DOWNLOAD_COUNT} <!-- IF SHOW_DL_LIST_LINK -->&nbsp;[ <a href="{DL_LIST_HREF}" class="med">{L_SHOW_DL_LIST}</a> ] <!-- ENDIF --></td>
	</tr>
    <!-- IF TOR_CONTROLS -->
    <tr class="row3 tCenter">
        <td colspan="3">
            <script type="text/javascript">
                ajax.callback.change_torrent = function (data) {
                    if (data.title) alert(data.title);
                    if (data.url) document.location.href = data.url;
                };
            </script>
            <script type="text/javascript">
                function change_torrents() {
                    ajax.exec({
                        action: 'change_torrent',
                        topic_id: {TOPIC_ID},
                        type: $('#tor-select-{TOPIC_ID}').val(),
                    });
                }
            </script>
            <select name="tor_action" id="tor-select-{TOPIC_ID}"
                    onchange="$('#tor-confirm-{TOPIC_ID}').attr('checked', false); $('#tor-submit-{TOPIC_ID}').attr('disabled', true);">
                <option value="" selected class="select-action">&raquo; {L_SELECT_ACTION}</option>
                <option value="del_torrent">{L_DELETE_TORRENT}</option>
                <option value="del_torrent_move_topic">{L_DELETE_MOVE_TORRENT}</option>
            </select>
            <a href="#"
               onclick="change_torrents($('#tor-{TOPIC_ID} select').val()); return false;"><input
                    type="submit" value="{L_SUBMIT}" class="liteoption"/></a>
        </td>
    </tr>
    <!-- ENDIF -->
</table>

<div class="spacer_12"></div>
<!-- END unregistered_torrent -->

<!-- BEGIN torrent -->

<!-- IF TOR_BLOCKED -->
<table id="tor_blocked" class="error">
	<tr><td><p class="error_msg">{TOR_BLOCKED_MSG}</p></td></tr>
</table>

<div class="spacer_12"></div>
<!-- ELSE -->
<!-- IF SHOW_RATIO_WARN -->
<table id="tor_blocked" class="error">
	<tr><td><p class="error_msg">{RATIO_WARN_MSG}</p></td></tr>
</table>

<div class="spacer_12"></div>
<!-- ENDIF -->

<table class="attach bordered med">
	<tr class="row3">
		<th colspan="3" class="{torrent.DL_LINK_CLASS}">{torrent.DOWNLOAD_NAME}
		<a href="{#FILELIST_URL#}{TOPIC_ID}" title="{L_BT_FLIST_LINK_TITLE}" target="_blank"><img alt="{L_BT_FLIST_LINK_TITLE}" src="{torrent.FILELIST_ICON}" width="12" height="12" border="0"></a>
		<!-- IF torrent.MAGNET and not torrent.TOR_FROZEN -->&nbsp;{torrent.MAGNET}<!-- ENDIF --></th>
	</tr>
    <!-- IF torrent.TOR_TYPE -->
    <tr class="row4">
        <th colspan="3" class="row7">{torrent.TOR_TYPE}&nbsp;<!-- IF torrent.TOR_SILVER_GOLD == 2 -->{L_SILVER_STATUS}<!-- ELSEIF torrent.TOR_SILVER_GOLD == 1 -->{L_GOLD_STATUS}<!-- ENDIF -->&nbsp;{torrent.TOR_TYPE}</th>
    </tr>
    <!-- ENDIF -->
	<tr class="row1">
		<td width="15%">{L_TORRENT}:</td>
		<td width="70%">
			{torrent.TRACKER_LINK}
			[ <span title="{torrent.REGED_DELTA}">{torrent.REGED_TIME}</span> ]
            <!-- IF not torrent.TOR_FROZEN -->
            <br/><!-- IF torrent.HASH --><br/>info_hash: <span class="copyElement" data-clipboard-text="{torrent.HASH}" title="{L_COPY_TO_CLIPBOARD}">{torrent.HASH}</span><!-- ENDIF -->
            <!-- IF torrent.HASH_V2 --><br/>info_hash v2: <span class="copyElement" data-clipboard-text="{torrent.HASH_V2}" title="{L_COPY_TO_CLIPBOARD}">{torrent.HASH_V2}</span><!-- ENDIF -->
            <!-- ENDIF -->
        </td>
		<td width="15%" rowspan="4" class="tCenter pad_6">
			<!-- IF torrent.TOR_FROZEN -->
			<p>{torrent.S_UPLOAD_IMAGE}</p><p>{L_DOWNLOAD}</p>
			<!-- ELSE -->
			<a href="{torrent.U_DOWNLOAD_LINK}" class="{torrent.DL_LINK_CLASS}">
			<p>{torrent.S_UPLOAD_IMAGE}</p><p><b>{L_DOWNLOAD}</b></p></a>
			<!-- ENDIF -->
			<p class="small">{torrent.FILESIZE}</p>
			<p style="padding-top: 6px;"><input id="tor-filelist-btn" type="button" class="lite" value="{L_BT_FLIST}" /></p>
			<!-- IF not torrent.TOR_FROZEN -->
			<!-- BEGIN tor_server -->
			<!-- IF torrent.tor_server.TORR_SERVER_M3U_LINK -->
			<hr/>
			<a href="{torrent.tor_server.TORR_SERVER_M3U_LINK}" target="_blank"><p><img alt="{L_PLAYBACK_M3U}" src="{torrent.tor_server.TORR_SERVER_M3U_ICON}" width="21" height="21" border="0"></p>{L_PLAYBACK_M3U}</a>
			<!-- ENDIF -->
			<!-- END tor_server -->
			<!-- ENDIF -->
		</td>
	</tr>
	<tr class="row1">
		<td>{L_TOR_STATUS}:</td>
		<td>
			<span id="tor-{TOPIC_ID}-status">{torrent.TOR_STATUS_ICON} <b>{torrent.TOR_STATUS_TEXT}</b>
			<!-- IF torrent.TOR_STATUS_BY -->{torrent.TOR_STATUS_BY}<!-- ENDIF -->
			</span>
			<!-- IF torrent.TOR_STATUS_REPLY || AUTH_MOD -->
			<script type="text/javascript">
				ajax.change_tor_status = function(mode) {
					ajax.exec({
						action    : 'change_tor_status',
						topic_id  : {TOPIC_ID},
						mode      : mode,
						status    : $('#sel_status').val(),
						comment   : $('#comment').val(),
					});
				};
				ajax.callback.change_tor_status = function(data) {
				<!-- IF AUTH_MOD -->
					$('#tor-'+ data.topic_id +'-status').html(data.status);
				<!-- ELSEIF torrent.TOR_STATUS_REPLY -->
					$('#tor_comment').html('{L_TOR_AUTH_SENT_COMMENT}');
				<!-- ENDIF -->
					$('#comment').attr('value', '');
				};
			</script>

			<span id="tor_comment">
			<!-- IF $bb_cfg['tor_comment'] -->
			<input type="text" id="comment" placeholder="{L_COMMENT}" />
			<!-- ENDIF -->

			<!-- IF AUTH_MOD -->
			<span id="tor-{TOPIC_ID}">{torrent.TOR_STATUS_SELECT}</span>
			<a href="#" onclick="ajax.change_tor_status('status'); return false;"><input type="submit" value="{L_EDIT}" class="liteoption" /></a>
			<!-- ELSEIF torrent.TOR_STATUS_REPLY -->
			<a href="#" onclick="ajax.change_tor_status('status_reply'); return false;"><input type="submit" value="{L_TOR_AUTH_FIXED}" class="liteoption" /></a>
			<!-- ENDIF -->
			</span>
			<!-- ENDIF / AUTH_MOD -->
		</td>
	</tr>
	<tr class="row1">
		<td>{L_DOWNLOADED}:</td>
		<td><span title="{L_COMPLETED}: {torrent.COMPLETED}">{torrent.DOWNLOAD_COUNT}</span></td>
	</tr>
	<tr class="row1">
		<td>{L_SIZE}:</td>
		<td>{torrent.TORRENT_SIZE}</td>
	</tr>
    <!-- IF TOR_CONTROLS -->
    <tr class="row3 tCenter">
        <td colspan="3">
            <script type="text/javascript">
                ajax.callback.change_torrent = function (data) {
                    if (data.title) alert(data.title);
                    if (data.url) document.location.href = data.url;
                };
            </script>
            <script type="text/javascript">
                function change_torrents() {
                    ajax.exec({
                        action: 'change_torrent',
                        topic_id: {TOPIC_ID},
                        type: $('#tor-select-{TOPIC_ID}').val(),
                    });
                }
            </script>
            <select name="tor_action" id="tor-select-{TOPIC_ID}"
                    onchange="$('#tor-confirm-{TOPIC_ID}').attr('checked', false); $('#tor-submit-{TOPIC_ID}').attr('disabled', true);">
                <option value="" selected class="select-action">&raquo; {L_SELECT_ACTION}</option>
                <option value="del_torrent">{L_DELETE_TORRENT}</option>
                <option value="del_torrent_move_topic">{L_DELETE_MOVE_TORRENT}</option>
                <!-- IF AUTH_MOD -->
                <!-- IF $bb_cfg['tracker']['gold_silver_enabled'] -->
                <!-- IF torrent.TOR_SILVER_GOLD == 1 -->
                <option value="unset_silver_gold">{L_UNSET_GOLD_TORRENT} / {L_UNSET_SILVER_TORRENT}</option>
                <option value="set_silver">{L_SET_SILVER_TORRENT}</option>
                <!-- ELSEIF torrent.TOR_SILVER_GOLD == 2 -->
                <option value="unset_silver_gold">{L_UNSET_GOLD_TORRENT} / {L_UNSET_SILVER_TORRENT}</option>
                <option value="set_gold">{L_SET_GOLD_TORRENT}</option>
                <!-- ELSE -->
                <option value="set_gold">{L_SET_GOLD_TORRENT}</option>
                <option value="set_silver">{L_SET_SILVER_TORRENT}</option>
                <!-- ENDIF -->
                <!-- ENDIF -->
                <!-- ENDIF -->
            </select>
            <a href="#"
               onclick="change_torrents($('#tor-{TOPIC_ID} select').val()); return false;"><input
                    type="submit" value="{L_EDIT}" class="liteoption"/></a>
        </td>
    </tr>
    <!-- ENDIF -->
    <!-- IF TOR_HELP_LINKS -->
    <tr class="row3 tCenter">
        <td colspan="3">{TOR_HELP_LINKS}</td>
    </tr>
    <!-- ENDIF -->
</table>

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

ajax.tor_filelist_loaded = false;
$('#tor-filelist-btn').click(function () {
    if (ajax.tor_filelist_loaded) {
        $('#tor-fl-wrap').toggle();
        return false;
    } else {
        $("#tor-filelist-btn").attr('disabled', true);
    }
    $('#tor-fl-wrap').show();

    ajax.exec({
        action: 'view_torrent',
        topic_id: {TOPIC_ID}
    });
    ajax.callback.view_torrent = function (data) {
        $('#tor-filelist').html(data.html);
        $('#tor-filelist > ul.tree-root').treeview({
            control: "#tor-fl-treecontrol"
        });
        $('#tor-filelist li.collapsable').each(function () {
            var $li = $(this);
            var dir_size = 0;
            $('i', $li).each(function () {
                dir_size += parseInt(this.innerHTML)
            });
            $('span.b:first', $li).append(' &middot; <s>' + humn_size(dir_size) + '</s>');
        });
        $('#tor-filelist i').each(function () {
            var size_bytes = this.innerHTML;
            this.innerHTML = '(' + size_bytes + ')';
            $(this).prepend('<s>' + humn_size(size_bytes) + '</s> ');
        });
        ajax.tor_filelist_loaded = true;
        $("#tor-filelist-btn").attr('disabled', false);
    };
    $('#tor-fl-treecontrol a').click(function () {
        this.blur();
    });
    return false;
});
</script>

<style>
#tor-fl-wrap {
	margin: 12px auto 0; width: 95%;
}
#fl-tbl-wrap { margin: 2px 14px 16px 14px; }
#tor-filelist {
	margin: 0 2px; padding: 8px 6px;
	max-height: 284px; overflow: auto;
}
.tor-filelist-fullsize { max-height: unset !important; }
#tor-filelist i { color: #7A7A7A; padding-left: 4px; }
#tor-filelist s { color: #0000FF; text-decoration: none; }
#tor-filelist .b > s { color: #800000; }
#tor-filelist .b { font-weight: bold; padding-left: 20px; background: transparent url('styles/images/folder.gif') no-repeat 3px 50%;}
#tor-filelist ul li span { padding-left: 20px; background: transparent url('styles/images/page.gif') no-repeat 3px 50%;}
#tor-filelist .tor-root-dir { font-size: 13px; font-weight: bold; line-height: 12px; padding-left: 4px; }
#tor-fl-treecontrol { padding: 2px 0 4px; }
#tor-fl-treecontrol a { padding: 0 8px; font-size: 11px; text-decoration: none; }
#tor-fl-bgn { width: 200px; height: 300px; margin-right: 6px; border: 1px solid #B5BEC4;}
</style>

<div id="tor-fl-wrap" class="border bw_TRBL row2 hidden">
<div id="fl-tbl-wrap">
	<table class="w100 borderless" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<div style="float: left;" id="tor-fl-treecontrol">
				<a href="#">{L_COLLAPSE}</a>&middot;<a href="#">{L_EXPAND}</a>&middot;<a href="#">{L_SWITCH}</a>
			</div>
			<div style="float: right; padding: 2px 0 4px;">
				<a style="padding: 0 8px; font-size: 11px; text-decoration: none;" href="#" onclick="$('#tor-filelist').toggleClass('tor-filelist-fullsize'); return false;">{L_TOGGLE_WINDOW_FULL_SIZE}</a>
			</div>
		</td>
	</tr>
	<tr>
		<td class="vTop" style="width: 100%;"><div id="tor-filelist" class="border bw_TRBL med row1"><span class="loading-1">{L_LOADING}</span></div></td>
	</tr>
	</table>
</div>
</div>

<!-- IF $bb_cfg['tor_thank'] -->
<style type="text/css">
    #thx-block {
        width: 95%;
        margin: 12px auto 0;
    }

    #thx-block .sp-wrap {
        width: 100% !important;
    }

    #thx-btn-div {
        text-align: center;
        margin: 0 0 12px;
    }

    #thx-list a {
        text-decoration: none;
    }

    #thx-list b {
        font-size: 11px;
        color: #2E2E2E;
        white-space: nowrap;
    }

    #thx-list i {
        font-weight: normal;
        color: #000000;
    }

    #thx-list u {
        display: none;
    }
</style>
<script type="text/javascript">
    $(function () {
        $thx_head = $('#thx-block').find('.sp-head');
        $thx_btn = $('#thx-btn');
        close_thx_list();

        $thx_btn.one('click', function () {
            ajax.thx('add');
            $(this).prop({disabled: true});
        });
        $thx_head.one('click', function () {
            ajax.thx('get');
        });
    });

    ajax.thx = function (mode) {
        ajax.exec({
            action: 'thx',
            mode: mode,
            topic_id: {TOPIC_ID},
            poster_id: {postrow.POSTER_ID}
        });
    }
    ajax.callback.thx = function (data) {
        if (data.mode === 'add') {
            $thx_btn.hide().after('<h2 style="color: green;">{$lang['THANKS_GRATITUDE']}!<h2>');
            open_thx_list();
        } else {
            $('#thx-list').html(data.html);
        }
    }

    function thx_is_visible() {
        return $('#thx-list').is(':visible');
    }

    function open_thx_list() {
        ajax.thx('get');
        if (!thx_is_visible()) {
            $thx_head.click();
        }
    }

    function close_thx_list() {
        if (thx_is_visible()) {
            $thx_head.click();
        }
    }
</script>
<div id="thx-block">
    <!-- IF not IS_GUEST -->
    <div id="thx-btn-div">
        <input id="thx-btn" type="button" class="bold" style="width: 200px;" value="{L_THANK_TOPIC}">
    </div>
    <!-- ENDIF -->
    <!-- IF not IS_GUEST or $bb_cfg['tor_thanks_list_guests'] -->
    <div class="sp-wrap">
        <div id="thx-list" class="sp-body" data-no-sp-open="true" title="{L_LAST_LIKES}"></div>
    </div>
    <!-- ENDIF -->
</div>
<!-- ENDIF -->

<div class="spacer_12"></div>
<!-- ENDIF -->

<!-- END torrent -->
