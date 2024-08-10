<h1 class="pagetitle">{PAGE_TITLE}</h1>

<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>Path</th>
        <th>{L_SIZE}</th>
        <th>BTMR Hash</th>
    </tr>
    </thead>
    <!-- BEGIN filelist -->
    <tbody>
    <tr class="{filelist.ROW_CLASS} tCenter">
        <td>{filelist.ROW_NUMBER}</td>
        <td>{filelist.FILE_PATH}</td>
        <td>{filelist.FILE_LENGTH}</td>
        <td>{filelist.FILE_HASH}</td>
    </tr>
    </tbody>
    <!-- END filelist -->
    <tfoot>
    <tr>
        <td class="catBottom" colspan="4">&nbsp;</td>
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
