<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>{FILES_COUNT}</th>
        <th></th>
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
            <a href="{m3ulist.STREAM_LINK}">DL</a>
            <!-- ENDIF -->
        </td>
    </tr>
    </tbody>
    <!-- END m3ulist -->
    <tfoot>
    <tr>
        <td class="catBottom warnColor1" colspan="4">TODO</td>
    </tr>
    </tfoot>
</table>
