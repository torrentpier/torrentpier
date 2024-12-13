<h1 class="pagetitle">{PAGE_TITLE}</h1>
<ul>
    <li>{L_BT_FLIST_CREATION_DATE}: <b>{TORRENT_CREATION_DATE}</b></li>
    <li>{L_DL_CLIENT}: <b>{TORRENT_CLIENT}</b></li>
</ul>
<br />

<h1 class="pagetitle">{L_BT_FLIST_ANNOUNCERS_LIST}</h1>
<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>{L_BT_FLIST_ANNOUNCERS}</th>
    </tr>
    </thead>
    <!-- BEGIN announcers -->
    <tbody>
    <tr class="{announcers.ROW_CLASS} tCenter">
        <td>{announcers.ROW_NUMBER}</td>
        <td>{announcers.ANNOUNCER}</td>
    </tr>
    </tbody>
    <!-- END announcers -->
    <tfoot>
    <tr>
        <td class="catBottom warnColor1" colspan="2"></td>
    </tr>
    </tfoot>
</table>
<br />

<h1 class="pagetitle">File list</h1>
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
