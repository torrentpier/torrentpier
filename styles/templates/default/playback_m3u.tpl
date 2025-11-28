<h1 class="pagetitle">{PAGE_TITLE}</h1>

<script type="text/javascript">
    ajax.ffprobe_info = function (file_index, is_audio) {
        ajax.exec({
            action: 'ffprobe_info',
            topic_id: {TOPIC_ID},
            info_hash: '{INFO_HASH}',
            file_index: file_index,
            is_audio: is_audio
        });
    };
    ajax.callback.ffprobe_info = function (data) {
        if (data.ffprobe_data) {
            $('#ffprobe_' + data.file_index).html(data.ffprobe_data);
        } else {
            if ($('#ffprobe_' + data.file_index).find('span.warnColor2').length >= 3) {
                $('#ffprobe_' + data.file_index).html('<hr/><span class="warnColor2">{L_M3U_FFPROBE_NO_DATA}</span>');
            } else {
                $('#ffprobe_' + data.file_index).append('<hr/><span class="warnColor2">{L_PLEASE_TRY_AGAIN}</span>');
            }
        }
    }
</script>

<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>{FILES_COUNT_TITLE}</th>
        <th>{L_STREAM}</th>
    </tr>
    </thead>
    <!-- IF HAS_ITEMS -->
    <!-- BEGIN m3ulist -->
    <tbody>
    <tr class="{m3ulist.ROW_CLASS} tCenter">
        <td>{m3ulist.ROW_NUMBER}</td>
        <td width="40%"><b>{m3ulist.TITLE}</b>
            <div id="ffprobe_{m3ulist.FILE_INDEX}">
                <hr/>
                <input onclick="ajax.ffprobe_info({m3ulist.FILE_INDEX}, {m3ulist.IS_AUDIO});" type="button" value="{L_SHOW_MORE_INFORMATION_FILE}">
            </div>
        </td>
        <td>
            <a href="#" onclick="return false;" class="copyElement" data-clipboard-text="{m3ulist.STREAM_LINK}">{L_COPY_STREAM_LINK}</a>&nbsp;&middot;
            <a target="_blank" href="{m3ulist.M3U_DL_LINK}">{L_DOWNLOAD_M3U_FILE}</a>
            <hr/>
            <!-- IF m3ulist.IS_VALID --><!-- IF m3ulist.IS_AUDIO -->
            <audio preload="none" src="{m3ulist.STREAM_LINK}" controls></audio>
            <!-- ELSE -->
            <video preload="none" width="500" height="auto" src="{m3ulist.STREAM_LINK}" controls></video>
            <!-- ENDIF --><!-- ELSE --><span class="warnColor2">{L_M3U_NOT_SUPPORTED}</span><!-- ENDIF -->
        </td>
    </tr>
    </tbody>
    <!-- END m3ulist -->
    <!-- ELSE -->
    <tbody>
    <tr>
        <td class="row1 tCenter pad_8" colspan="3">{L_NO_ITEMS}</td>
    </tr>
    </tbody>
    <!-- ENDIF -->
    <tfoot>
    <tr>
        <td class="catBottom warnColor1" colspan="4">{L_M3U_NOTICE}</td>
    </tr>
    </tfoot>
</table>

<!--bottom_info-->
<div class="bottom_info">
    <div class="spacer_8"></div>
    <a href="{U_TOPIC}">{L_TOPIC_RETURN}</a>
    <div id="timezone">
        <p>{CURRENT_TIME}</p>
        <p>{S_TIMEZONE}</p>
    </div>
    <div class="clear"></div>
</div><!--/bottom_info-->
