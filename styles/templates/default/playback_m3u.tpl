<h1 class="pagetitle">{PAGE_TITLE}</h1>

<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>{FILES_COUNT}</th>
        <th>{L_STREAM}</th>
    </tr>
    </thead>
    <!-- BEGIN m3ulist -->
    <tbody>
    <tr class="{m3ulist.ROW_CLASS} tCenter">
        <td>{m3ulist.ROW_NUMBER}</td>
        <td width="40%">{m3ulist.TITLE}</td>
        <td>
            <a href="#" onclick="return false;" class="copyElement" data-clipboard-text="{m3ulist.STREAM_LINK}">{L_COPY_STREAM_LINK}</a>&nbsp;&middot;
            <a target="_blank" href="{m3ulist.M3U_DL_LINK}">{L_DOWNLOAD_M3U_FILE}</a>
            <!-- IF m3ulist.IS_VALID --><hr><!-- IF m3ulist.IS_AUDIO -->
            <audio preload="none" src="{m3ulist.STREAM_LINK}" controls></audio>
            <!-- ELSE -->
            <video preload="none" width="500" height="auto" src="{m3ulist.STREAM_LINK}" controls></video>
            <!-- ENDIF --><!-- ENDIF -->
        </td>
    </tr>
    </tbody>
    <!-- END m3ulist -->
    <tfoot>
    <tr>
        <td class="catBottom warnColor1" colspan="4">{L_M3U_NOTICE}</td>
    </tr>
    </tfoot>
</table>

<!--bottom_info-->
<div class="bottom_info">
    <div class="spacer_8"></div>
    <div id="timezone">
        <p>{CURRENT_TIME}</p>
        <p>{S_TIMEZONE}</p>
    </div>
    <div class="clear"></div>
</div><!--/bottom_info-->
