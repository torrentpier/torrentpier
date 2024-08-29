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
        <td>{m3ulist.TITLE}</td>
        <td>
            <!-- IF m3ulist.IS_VALID -->
            <!-- IF m3ulist.IS_AUDIO -->
            <audio preload="none" src="{m3ulist.STREAM_LINK}" controls></audio>
            <!-- ELSE -->
            <video width="500" height="auto" src="{m3ulist.STREAM_LINK}" playsinline controls></video>
            <!-- ENDIF -->
            <!-- ELSE -->
            <a target="_blank" href="{m3ulist.STREAM_LINK}">{L_DOWNLOAD_M3U_FILE}</a>
            <!-- ENDIF -->
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
