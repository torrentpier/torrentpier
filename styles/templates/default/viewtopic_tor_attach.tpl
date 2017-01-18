<div class="clear"></div>

<!-- IF #IS_CP_HOLDER -->
<div class="spacer_12"></div>
<table id="tor_blocked" class="error">
    <tr><td><p class="error_msg">Правила для правообладателей</p></td></tr>
</table>
<!-- ELSE -->

<!-- IF SHOW_TOR_BLOCKED -->
<div class="spacer_12"></div>
<table id="tor_blocked" class="error">
    <tr><td><p class="error_msg">{TOR_BLOCKED_MSG}</p></td></tr>
</table>
<!-- ENDIF -->

<!-- IF SHOW_RATIO_WARN -->
<div class="spacer_12"></div>
<table id="tor_blocked" class="error">
    <tr><td><p class="error_msg">{RATIO_WARN_MSG}</p></td></tr>
</table>
<!-- ENDIF -->
<!-- ENDIF / ! #IS_CP_HOLDER -->

<div class="spacer_12"></div>

<!-- IF SHOW_TOR_REGGED -->
<div id="tor-reged">
	<table class="attach bordered med">
        <tr class="row3">
            <th colspan="3" class="med">{DOWNLOAD_NAME}<!-- IF TOR_FROZEN == 0 --><!-- IF MAGNET_LINKS -->&nbsp;{MAGNET}<!-- ENDIF --><!-- ENDIF --></th>
        </tr>
        <!-- IF TOR_SILVER_GOLD == 2 && $tr_cfg['gold_silver_enabled'] -->
        <tr class="row4">
            <th colspan="3" class="row7"><img src="styles/images/tor_silver.gif" width="16" height="15" title="{L_SILVER}" />&nbsp;{L_SILVER_STATUS}&nbsp;<img src="styles/images/tor_silver.gif" width="16" height="15" title="{L_SILVER}" /></th>
        </tr>
        <!-- ELSEIF TOR_SILVER_GOLD == 1 && $tr_cfg['gold_silver_enabled'] -->
        <tr class="row4">
            <th colspan="3" class="row7"><img src="styles/images/tor_gold.gif" width="16" height="15" title="{L_GOLD}" />&nbsp;{L_GOLD_STATUS}&nbsp;<img src="styles/images/tor_gold.gif" width="16" height="15" title="{L_GOLD}" /></th>
        </tr>
        <!-- ENDIF -->
        <tr class="row1">
            <td width="15%">{L_TORRENT}:</td>
            <td width="70%">
                {TRACKER_REG_LINK}
                [ <span title="{TOR_LONGEVITY}">{REGED_TIME}</span> ]
                &#0183; {HASH}
            </td>
            <td width="15%" rowspan="4" class="tCenter pad_6">
                <!-- IF TOR_FROZEN and not AUTH_MOD -->
                <p>{S_UPLOAD_IMAGE}</p>
                <p>{L_DOWNLOAD}</p>
                <!-- ELSE -->
                <p><a href="{$di->config->get('dl_url')}{TOPIC_ID}" class="dl-stub"><img src="styles/images/icon_dn.gif" /></a></p>
                <p><a href="{$di->config->get('dl_url')}{TOPIC_ID}" class="dl-stub dl-link">{L_DOWNLOAD} .torrent</a></p>
                <!-- ENDIF -->
                <p class="small">{FILESIZE}</p>
                <p style="padding-top: 6px;"><input id="tor-filelist-btn" type="button" class="lite" value="{L_FILELIST}" /></p>
            </td>
        </tr>
        <tr class="row1">
            <td>{L_TOR_STATUS}:</td>
            <td>
                <span id="tor-{TOPIC_ID}-status">{TOR_STATUS_ICON} <b>{TOR_STATUS_TEXT}</b>
                    <!-- IF TOR_STATUS_BY -->{TOR_STATUS_BY}<!-- ENDIF -->
                </span>
                <!-- IF TOR_STATUS_REPLY || AUTH_MOD -->
                <script type="text/javascript">
                    ajax.change_tor_status = function(mode) {
                        ajax.exec({
                            action    : 'change_tor_status',
                            topic_id  : {TOPIC_ID},
                            mode      : mode,
                            status    : $('#sel_status').val(),
                            comment   : $('#comment').val()
                        });
                    };
                    ajax.callback.change_tor_status = function(data) {
                        <!-- IF AUTH_MOD -->
                        $('#tor-'+ {TOPIC_ID} +'-status').html(data.status);
                        <!-- ELSEIF TOR_STATUS_REPLY -->
                        $('#tor_comment').html('{L_TOR_AUTH_SENT_COMMENT}');
                        <!-- ENDIF -->
                        $('#comment').attr('value', '');
                    };
                </script>
                <span id="tor_comment">
                    <!-- IF $di->config->get('tor_comment') -->
                        <input type="text" id="comment" placeholder="{L_COMMENT}" />
                    <!-- ENDIF -->
                    <!-- IF AUTH_MOD -->
                    <span id="tor-{TOPIC_ID}">{TOR_STATUS_SELECT}</span>
                    <a href="#" onclick="ajax.change_tor_status('status'); return false;"><input type="submit" value="{L_EDIT}" class="liteoption" /></a>
                    <!-- ELSEIF TOR_STATUS_REPLY -->
                    <a href="#" onclick="ajax.change_tor_status('status_reply'); return false;"><input type="submit" value="{L_TOR_AUTH_FIXED}" class="liteoption" /></a>
                    <!-- ENDIF -->
                 </span>
                <!-- ENDIF / AUTH_MOD -->
            </td>
        </tr>
        <tr class="row1">
            <td>{L_COMPLETED}:</td>
            <td>{DOWNLOAD_COUNT}</td>
        </tr>
        <tr class="row1">
            <td>{L_SIZE}:</td>
            <td>{TOR_SIZE}</td>
        </tr>
        <tr class="row3 tCenter">
            <td colspan="3">
                <script type="text/javascript">
                    ajax.callback.change_torrent = function(data) {
                        if (data.title) alert(data.title);
                        if (data.url) document.location.href = data.url;
                    };
                </script>
                <!-- IF TOR_CONTROLS -->
                <script type="text/javascript">
                    function change_torrents()
                    {
                        ajax.exec({
                            action : 'change_torrent',
                            t      : {TOPIC_ID},
                            type   : $('#tor-select-{TOPIC_ID}').val()
                        });
                    }
                </script>
                <select name="tor_action" id="tor-select-{TOPIC_ID}" onchange="$('#tor-confirm-{TOPIC_ID}').attr('checked', false); $('#tor-submit-{TOPIC_ID}').attr('disabled', true)">
                    <option value="" selected="selected" class="select-action">&raquo; {L_SELECT_ACTION}</option>
                    <option value="del_torrent">{L_DELETE_TORRENT}</option>
                    <option value="del_torrent_move_topic">{L_DELETE_MOVE_TORRENT}</option>
                    <!-- IF AUTH_MOD -->
                    <!-- IF $tr_cfg['gold_silver_enabled'] -->
                    <!-- IF TOR_SILVER_GOLD == 1 -->
                    <option value="unset_silver_gold">{L_UNSET_GOLD_TORRENT} / {L_UNSET_SILVER_TORRENT}</option>
                    <option value="set_silver">{L_SET_SILVER_TORRENT}</option>
                    <!-- ELSEIF TOR_SILVER_GOLD == 2 -->
                    <option value="unset_silver_gold">{L_UNSET_GOLD_TORRENT} / {L_UNSET_SILVER_TORRENT}</option>
                    <option value="set_gold">{L_SET_GOLD_TORRENT}</option>
                    <!-- ELSE -->
                    <option value="set_gold">{L_SET_GOLD_TORRENT}</option>
                    <option value="set_silver">{L_SET_SILVER_TORRENT}</option>
                    <!-- ENDIF -->
                    <!-- ENDIF -->
                    <!-- ENDIF -->
                    <!-- IF SHOW_CPHOLD_OPT -->
                    <option value="copyright_close"> &nbsp;??? Закрыть по просьбе правообладателя &nbsp;</option>
                    <!-- ENDIF -->
                </select>
                <a href="#" onclick="change_torrents($('#tor-{TOPIC_ID} select').val()); return false;"><input type="submit" value="{L_EDIT}" class="liteoption" /></a>
                <!-- ELSEIF TOR_HELP_LINKS -->
                {TOR_HELP_LINKS}
                <!-- ELSE -->
                &nbsp;
                <!-- ENDIF -->
            </td>
        </tr>
	</table>

    <script type="text/javascript">
        function humn_size (size) {
            var i = 0;
            var units = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
            while ((size/1024) >= 1) {
                size = size/1024;
                i++;
            }
            size = String(size);
            if (size.indexOf('.') !== -1) {
                size = size.substring(0, size.indexOf('.') + 3);
            }
            return size + ' ' + units[i];
        }

        ajax.tor_filelist_loaded = false;
        $('#tor-filelist-btn').click(function(){
            if (ajax.tor_filelist_loaded) {
                $('#tor-fl-wrap').toggle();
                return false;
            }
            $('#tor-fl-wrap').show();

            ajax.exec({action: 'view_torrent', t: {TOPIC_ID} });
            ajax.callback.view_torrent = function(data) {
                $('#tor-filelist').html(data.html);
                $('#tor-filelist > ul.tree-root').treeview({
                    control: "#tor-fl-treecontrol"
                });
                $('#tor-filelist li.collapsable').each(function(){
                    var $li = $(this);
                    var dir_size = 0;
                    $('i', $li).each(function(){ dir_size += parseInt(this.innerHTML) });
                    $('span.b:first', $li).append(' &middot; <s>' + humn_size(dir_size) + '</s>');
                });
                $('#tor-filelist i').each(function(){
                    var size_bytes = this.innerHTML;
                    this.innerHTML = '('+ size_bytes +')';
                    $(this).prepend('<s>'+ humn_size(size_bytes) +'</s> ');
                });
                ajax.tor_filelist_loaded = true;
            };
            $('#tor-fl-treecontrol a').click(function(){ this.blur(); });
            return false;
        });
    </script>

    <style type="text/css">
        #tor-fl-wrap {
            margin: 12px auto 0; width: 95%;
        }
        #fl-tbl-wrap { margin: 2px 14px 16px 14px; }
        #tor-filelist {
            margin: 0 2px; padding: 8px 6px;
            max-height: 284px; overflow: auto;
        }
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
                        <div id="tor-fl-treecontrol">
                            <a href="#">{L_COLLAPSE}</a>&middot;
                            <a href="#">{L_EXPAND}</a>&middot;
                            <a href="#">{L_SWITCH}</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="vTop" style="width: 100%;"><div id="tor-filelist" class="border bw_TRBL med row1"><span class="loading-1">{L_LOADING}</span></div></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="spacer_12"></div>
</div><!--/tor-reged-->
<!-- ENDIF / SHOW_TOR_REGGED -->