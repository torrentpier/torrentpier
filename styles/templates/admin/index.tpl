<!-- IF TPL_ADMIN_FRAMESET -->
<!--========================================================================-->
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset={CONTENT_ENCODING}"/>
  <meta http-equiv="Content-Style-Type" content="text/css"/>
  <link rel="shortcut icon" href="{SITE_URL}favicon.png" type="image/x-icon">
  <title>{L_ADMIN}</title>
</head>

<frameset cols="220,*" rows="*" border="1" framespacing="1" frameborder="yes">
  <frame src="index.php?pane=left" name="nav" marginwidth="0" marginheight="0" scrolling="auto">
  <frame src="index.php?pane=right" name="main" marginwidth="0" marginheight="0" scrolling="auto">
</frameset>
</html>
<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_FRAMESET -->

<!-- IF TPL_ADMIN_NAVIGATE -->
<!--========================================================================-->

<style type="text/css">
  body {
    background: #E5E5E5;
    min-width: 10px;
  }

  #body_container {
    background: #E5E5E5;
    padding: 4px 3px 4px;
  }

  table.forumline {
    margin: 0 auto;
  }
</style>

<table class="forumline" id="acp_main_nav">
  <col class="row1">
  <tr>
    <th>{L_ADMIN}</th>
  </tr>
  <tr>
    <td><a href="{U_ADMIN_INDEX}" target="main" class="med">{L_ADMIN_INDEX}</a></td>
  </tr>
  <tr>
    <td><a href="{U_FORUM_INDEX}" target="_parent" class="med">{L_MAIN_INDEX}</a></td>
  </tr>
  <!-- BEGIN catrow -->
  <tr>
    <td class="catTitle">{catrow.ADMIN_CATEGORY}</td>
  </tr>
  <!-- BEGIN modulerow -->
  <tr>
    <td><a href="{catrow.modulerow.U_ADMIN_MODULE}" target="main" class="med">{catrow.modulerow.ADMIN_MODULE}</a></td>
  </tr>
  <!-- END modulerow -->
  <!-- END catrow -->
</table>

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_NAVIGATE -->

<!-- IF TPL_ADMIN_MAIN -->
<!--========================================================================-->
<script type="text/javascript">
  ajax.manage_admin = function (mode) {
    ajax.exec({
      action: 'manage_admin',
      mode: mode,
      user_id: ''
    });
  };
  ajax.callback.manage_admin = function (data) {
    $('#cache').html(data.cache_html);
    $('#datastore').html(data.datastore_html);
    $('#indexer').html(data.indexer_html);
    $('#template_cache').html(data.template_cache_html);
    $('#update_user_level').html(data.update_user_level_html);
    $('#sync_topics').html(data.sync_topics_html);
    $('#sync_user_posts').html(data.sync_user_posts_html);
    $('#unlock_cron').html(data.unlock_cron_html);
  }
</script>

<br/>

<!-- IF ADMIN_LOCK_CRON -->
<div class="alert alert-danger" style="width: 95%;">
  <h4 class="alert-heading">{L_ADMIN_DISABLE_CRON_TITLE}</h4>
  <hr>
  <a href="#" id="unlock_cron" onclick="ajax.manage_admin('unlock_cron'); return false;">{L_ADMIN_UNLOCK_CRON}</a>
  ({L_ADMIN_DISABLE_CRON})
</div>
<!-- ENDIF -->

<!-- IF ADMIN_LOCK -->
<div class="alert alert-danger" style="width: 95%;">
  <h4 class="alert-heading">{L_ADMIN_DISABLE_TITLE}</h4>
  <hr>
  <a href="admin_board.php?mode=config">{L_ADMIN_UNLOCK}</a>
  ({L_ADMIN_DISABLE})
</div>
<!-- ENDIF -->

<table>
  <tr>
    <td><b>{L_CLEAR_CACHE}:</b></td>
    <td>
      <a href="#" id="datastore" onclick="ajax.manage_admin('clear_datastore'); return false;">{L_DATASTORE}</a>,&nbsp;
      <a href="#" id="cache" onclick="ajax.manage_admin('clear_cache'); return false;">{L_ALL_CACHE}</a>,&nbsp;
      <a href="#" id="template_cache" onclick="ajax.manage_admin('clear_template_cache'); return false;">{L_TEMPLATES}</a>
    </td>
  </tr>
  <tr>
    <td><b>{L_UPDATE}:</b></td>
    <td>
      <a href="#" id="update_user_level" onclick="ajax.manage_admin('update_user_level'); return false;">{L_USER_LEVELS}</a>
      <!-- IF $bb_cfg['search_engine_type'] == "sphinx" -->,&nbsp;
      <a href="#" id="indexer" onclick="ajax.manage_admin('indexer'); return false;">{L_INDEXER}</a>
      <!-- ENDIF -->
    </td>
  </tr>
  <tr>
    <td><b>{L_SYNCHRONIZE}:</b></td>
    <td>
      <a href="#" id="sync_topics" onclick="ajax.manage_admin('sync_topics'); return false;">{L_TOPICS}</a>,&nbsp;
      <a href="#" id="sync_user_posts" onclick="ajax.manage_admin('sync_user_posts'); return false;">{L_USER_POSTS_COUNT}</a>
    </td>
  </tr>
  <tr>
    <td><b>{L_STATISTICS}:</b></td>
    <td>
      <a href="stats/tr_stats.php" target="_blank">tr_stats.php</a>,&nbsp;
      <a href="stats/tracker.php" target="_blank">tracker.php</a>
    </td>
  </tr>
  <tr>
    <td><b>{L_ADMIN}:</b></td>
    <td>
      <a href="../profile.php?mode=register&admin=1">{L_CREATE_PROFILE}</a>
    </td>
  </tr>
</table>
<br/>

<table class="forumline">
  <tr>
    <th colspan="2">{L_VERSION_INFORMATION}</th>
  </tr>
  <tr>
    <td class="row1" nowrap="nowrap" width="25%">{L_TP_VERSION}:</td>
    <td class="row2"><b>{$bb_cfg['tp_release_codename']} ({$bb_cfg['tp_version']})</b></td>
  </tr>
  <tr>
    <td class="row1" nowrap="nowrap" width="25%">{L_TP_RELEASE_DATE}:</td>
    <td class="row2"><b>{$bb_cfg['tp_release_date']}</b></td>
  </tr>
</table>
<br/>

<h3>{L_FORUM_STATS}</h3>

<table class="forumline">
  <tr>
    <th width="25%">{L_STATISTIC}</th>
    <th width="25%">{L_VALUE}</th>
    <th width="25%">{L_STATISTIC}</th>
    <th width="25%">{L_VALUE}</th>
  </tr>
  <tr>
    <td class="row1" nowrap="nowrap">{L_NUMBER_POSTS}:</td>
    <td class="row2"><b>{NUMBER_OF_POSTS}</b></td>
    <td class="row1" nowrap="nowrap">{L_POSTS_PER_DAY}:</td>
    <td class="row2"><b>{POSTS_PER_DAY}</b></td>
  </tr>
  <tr>
    <td class="row1" nowrap="nowrap">{L_NUMBER_TOPICS}:</td>
    <td class="row2"><b>{NUMBER_OF_TOPICS}</b></td>
    <td class="row1" nowrap="nowrap">{L_TOPICS_PER_DAY}:</td>
    <td class="row2"><b>{TOPICS_PER_DAY}</b></td>
  </tr>
  <tr>
    <td class="row1" nowrap="nowrap">{L_NUMBER_USERS}:</td>
    <td class="row2"><b>{NUMBER_OF_USERS}</b></td>
    <td class="row1" nowrap="nowrap">{L_USERS_PER_DAY}:</td>
    <td class="row2"><b>{USERS_PER_DAY}</b></td>
  </tr>
  <tr>
    <td class="row1" nowrap="nowrap">{L_BOARD_STARTED}:</td>
    <td class="row2"><b>{START_DATE}</b></td>
    <td class="row1" nowrap="nowrap">{L_AVATAR_DIR_SIZE}:</td>
    <td class="row2"><b>{AVATAR_DIR_SIZE}</b></td>
  </tr>
</table>
<br/>

<a name="online"></a>
<h3>{L_WHOSONLINE}</h3>

<!-- IF SHOW_USERS_ONLINE -->
<table class="forumline">
  <tr>
    <th>{L_USERNAME}</th>
    <th>{L_LOGIN} / {L_LAST_UPDATED}</th>
    <th>{L_IP_ADDRESS}</th>
  </tr>
  <!-- BEGIN reg_user_row -->
  <tr class="{reg_user_row.ROW_CLASS}">
    <td class="bold" nowrap="nowrap">{reg_user_row.USER}</td>
    <td align="center" nowrap="nowrap">{reg_user_row.STARTED}-{reg_user_row.LASTUPDATE}</td>
    <td class="tCenter"><a href="{reg_user_row.U_WHOIS_IP}" class="gen" target="_blank">{reg_user_row.IP_ADDRESS}</a>
    </td>
  </tr>
  <!-- END reg_user_row -->
  <tr>
    <td colspan="3" class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
  </tr>
  <!-- BEGIN guest_user_row -->
  <tr class="{guest_user_row.ROW_CLASS}">
    <td nowrap="nowrap">{L_GUEST}</td>
    <td align="center">{guest_user_row.STARTED}-{guest_user_row.LASTUPDATE}</td>
    <td class="tCenter"><a href="{guest_user_row.U_WHOIS_IP}" target="_blank">{guest_user_row.IP_ADDRESS}</a></td>
  </tr>
  <!-- END guest_user_row -->
</table>
<!-- ELSE -->
<a href="{USERS_ONLINE_HREF}#online">{L_SHOW_ONLINE_USERLIST}</a>
<!-- ENDIF -->

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_MAIN -->
