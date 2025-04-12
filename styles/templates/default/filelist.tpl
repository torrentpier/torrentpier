<h1 class="pagetitle">{PAGE_TITLE}</h1>
<ul>
    <li>{L_BT_FLIST_CREATION_DATE}: <b>{TORRENT_CREATION_DATE}</b></li>
    <li>{L_DL_CLIENT}: <b>{TORRENT_CLIENT}</b></li>
    <li>{L_BT_IS_PRIVATE}: <b>{TORRENT_PRIVATE}</b></li>
</ul>
<br/>

<div class="nav">
    <p class="floatR"><a href="#">← {L_PREVIOUS_PAGE}</a>&nbsp;|&nbsp;<a href="#">{L_NEXT_PAGE} →</a></p>
    <div class="clear"></div>
</div>

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
        <td class="catBottom warnColor1" colspan="2">{L_BT_FLIST_ANNOUNCERS_NOTICE}</td>
    </tr>
    </tfoot>
</table>
<br/>

<h1 class="pagetitle">{L_BT_FLIST}</h1>
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
        <td><span class="copyElement" data-clipboard-text="{filelist.FILE_HASH}" title="{L_COPY_TO_CLIPBOARD}">{filelist.FILE_HASH}</span></td>
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
