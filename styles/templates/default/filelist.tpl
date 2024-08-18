<h1 class="pagetitle">{PAGE_TITLE}</h1>
<ul>
    <li>{L_BT_FLIST_CREATION_DATE}: <b>{TORRENT_CREATION_DATE}</b></li>
    <li>{L_DL_CLIENT}: <b>{TORRENT_CLIENT}</b></li>
</ul>
<br>

<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>{FILES_COUNT}</th>
        <th>{L_SIZE}</th>
        <th>{L_BT_FLIST_BTMR_HASH}</th>
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
        <td class="catBottom warnColor1" colspan="4">{BTMR_NOTICE}</td>
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
