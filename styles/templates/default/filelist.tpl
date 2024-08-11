<h1 class="pagetitle">{PAGE_TITLE}</h1>
<ul>
    <li>{L_BT_FLIST_CREATION_DATE}: {TORRENT_CREATION_DATE}</li>
    <li>{L_DL_CLIENT}: {TORRENT_CLIENT}</li>
</ul>
<br>

<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>Path ({FILES_COUNT} files)</th>
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
        <td class="catBottom warnColor1" colspan="4">
            BitTorrent Merkle Root is a hash of a file embedded in torrents with BitTorrent v2 support, tracker users
            can extract, calculate them, also download deduplicated torrents using desktop tools such as
            <a href="https://github.com/kovalensky/tmrr" target="_blank" referrerpolicy="origin">Torrent Merkle Root
                Reader</a>
        </td>
    </tr>
    </tfoot>
</table>

<!--bottom_info-->
<div class="bottom_info">
    <div class="spacer_8"></div>
    <a href="{U_TOPIC}">Вернутся назад в тему</a>
    <div id="timezone">
        <p>{CURRENT_TIME}</p>
        <p>{S_TIMEZONE}</p>
    </div>
    <div class="clear"></div>
</div><!--/bottom_info-->
