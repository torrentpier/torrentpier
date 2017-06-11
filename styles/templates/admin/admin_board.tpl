<!-- IF CONFIG -->
<h1>{L_GENERAL_CONFIG}</h1>

<p>{L_CONFIG_EXPLAIN}</p>
<br />

<a href="admin_board.php?mode=config" class="bold">{L_GENERAL_CONFIG}</a> &#0183;
<a href="admin_board.php?mode=config_mods">{L_CONFIG_MODS}</a>
<br /><br />

<form action="{S_CONFIG_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_GENERAL_SETTINGS}</th>
</tr>
<tr>
	<td><h4>{L_SITE_NAME}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="sitename" value="{SITENAME}" /></td>
</tr>
<tr>
	<td><h4>{L_SITE_DESC}</h4></td>
	<td><input class="post" type="text" size="40" maxlength="255" name="site_desc" value="{CONFIG_SITE_DESCRIPTION}" /></td>
</tr>
<tr>
	<td><h4>{L_FORUMS_DISABLE}</h4><h6>{L_BOARD_DISABLE_EXPLAIN}</h6></td>
	<td>
		<label><input type="radio" name="board_disable" value="1" <!-- IF DISABLE_BOARD -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="board_disable" value="0" <!-- IF not DISABLE_BOARD -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_AUTOLOGIN}</h4><h6>{L_ALLOW_AUTOLOGIN_EXPLAIN}</h6></td>
	<td>
		<label><input type="radio" name="allow_autologin" value="1" <!-- IF ALLOW_AUTOLOGIN -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_autologin" value="0" <!-- IF not ALLOW_AUTOLOGIN -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_AUTOLOGIN_TIME}</h4><h6>{L_AUTOLOGIN_TIME_EXPLAIN}</h6></td>
	<td>
		<input class="post" type="text" size="3" maxlength="4" name="max_autologin_time" value="{AUTOLOGIN_TIME}" /> {L_DAYS}
	</td>
</tr>
<tr>
	<td><h4>{L_FLOOD_INTERVAL}</h4><h6>{L_FLOOD_INTERVAL_EXPLAIN}</h6></td>
	<td>
		<input class="post" type="text" size="3" maxlength="4" name="flood_interval" value="{FLOOD_INTERVAL}" /> {L_SEC}
	</td>
</tr>
<!--
<tr>
	<td><h4>{L_MAX_LOGIN_ATTEMPTS}</h4><h6>{L_MAX_LOGIN_ATTEMPTS_EXPLAIN}</h6></td>
	<td>
		<input class="post" type="text" size="3" maxlength="4" name="max_login_attempts" value="{MAX_LOGIN_ATTEMPTS}" />
	</td>
</tr>
<tr>
	<td><h4>{L_LOGIN_RESET_TIME}</h4><h6>{L_LOGIN_RESET_TIME_EXPLAIN}</h6></td>
	<td>
		<input class="post" type="text" size="3" maxlength="4" name="login_reset_time" value="{LOGIN_RESET_TIME}" />
	</td>
</tr>
-->
<tr>
	<td><h4>{L_TOPICS_PER_PAGE}</h4></td>
	<td>
		<input class="post" type="text" name="topics_per_page" size="5" maxlength="4" value="{TOPICS_PER_PAGE}" />
	</td>
</tr>
<tr>
	<td><h4>{L_POSTS_PER_PAGE}</h4></td>
	<td>
		<input class="post" type="text" name="posts_per_page" size="5" maxlength="4" value="{POSTS_PER_PAGE}" />
	</td>
</tr>
<tr>
	<td><h4>{L_HOT_THRESHOLD}</h4></td>
	<td><input class="post" type="text" name="hot_threshold" size="5" maxlength="4" value="{HOT_TOPIC}" /></td>
</tr>
<tr>
	<td><h4>{L_DEFAULT_LANGUAGE}</h4></td>
	<td>{LANG_SELECT}</td>
</tr>
<tr>
	<td><h4>{L_DATE_FORMAT}</h4><h6>{L_DATE_FORMAT_EXPLAIN}</h6></td>
	<td><input class="post" type="text" name="default_dateformat" value="{DEFAULT_DATEFORMAT}" /></td>
</tr>
<tr>
	<td><h4>{L_SYSTEM_TIMEZONE}</h4></td>
	<td>{TIMEZONE_SELECT}</td>
</tr>
<tr>
	<td><h4>{L_ENABLE_PRUNE}</h4></td>
	<td>
		<label><input type="radio" name="prune_enable" value="1" <!-- IF PRUNE_ENABLE -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="prune_enable" value="0" <!-- IF not PRUNE_ENABLE -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<th colspan="2">{L_ABILITIES_SETTINGS}</th>
</tr>
<tr>
	<td><h4>{L_MAX_POLL_OPTIONS}</h4></td>
	<td><input class="post" type="text" name="max_poll_options" size="4" maxlength="4" value="{MAX_POLL_OPTIONS}" /></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_BBCODE}</h4></td>
	<td>
		<label><input type="radio" name="allow_bbcode" value="1" <!-- IF ALLOW_BBCODE -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_bbcode" value="0" <!-- IF not ALLOW_BBCODE -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_SMILIES}</h4></td>
	<td>
		<label><input type="radio" name="allow_smilies" value="1" <!-- IF ALLOW_SMILIES -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_smilies" value="0" <!-- IF not ALLOW_SMILIES -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_SMILIES_PATH}</h4><h6>{L_SMILIES_PATH_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="20" maxlength="255" name="smilies_path" value="{SMILIES_PATH}" /></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_SIG}</h4></td>
	<td>
		<label><input type="radio" name="allow_sig" value="1" <!-- IF ALLOW_SIG -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_sig" value="0" <!-- IF not ALLOW_SIG -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_MAX_SIG_LENGTH}</h4><h6>{L_MAX_SIG_LENGTH_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="5" maxlength="4" name="max_sig_chars" value="{SIG_SIZE}" /></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_NAME_CHANGE}</h4></td>
	<td>
		<label><input type="radio" name="allow_namechange" value="1" <!-- IF ALLOW_NAMECHANGE -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_namechange" value="0" <!-- IF not ALLOW_NAMECHANGE -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<!-- ENDIF / CONFIG -->

<!-- IF CONFIG_MODS -->
<h1>{L_CONFIG_MODS}</h1>

<p>{L_MODS_EXPLAIN}</p>
<br />

<a href="admin_board.php?mode=config">{L_GENERAL_CONFIG}</a> &#0183;
<a href="admin_board.php?mode=config_mods" class="bold">{L_CONFIG_MODS}</a>
<br /><br />

<form action="{S_CONFIG_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_CONFIG_MODS}</th>
</tr>
<tr>
	<td><h4>{L_MAGNET}</h4></td>
	<td>
		<label><input type="radio" name="magnet_links_enabled" value="1" <!-- IF MAGNET_LINKS_ENABLED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="magnet_links_enabled" value="0" <!-- IF not MAGNET_LINKS_ENABLED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_GENDER}</h4></td>
	<td>
		<label><input type="radio" name="gender" value="1" <!-- IF GENDER -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="gender" value="0" <!-- IF not GENDER -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_CALLSEED}</h4></td>
	<td>
		<label><input type="radio" name="callseed" value="1" <!-- IF CALLSEED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="callseed" value="0" <!-- IF not CALLSEED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_TRACKER_STATS}</h4></td>
	<td>
		<label><input type="radio" name="tor_stats" value="1" <!-- IF TOR_STATS -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="tor_stats" value="0" <!-- IF not TOR_STATS -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_WHOIS_INFO}</h4></td>
	<td><input class="post" type="text" size="28" maxlength="100" name="whois_info" value="{WHOIS_INFO}" /></td>
</tr>
<tr>
	<td><h4>{L_SHOW_MOD_HOME_PAGE}</h4></td>
	<td>
		<label><input type="radio" name="show_mod_index" value="1" <!-- IF SHOW_MOD_INDEX -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="show_mod_index" value="0" <!-- IF not SHOW_MOD_INDEX -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td>{L_PREMOD_HELP}</td>
	<td>
		<label><input type="radio" name="premod" value="1" <!-- IF PREMOD -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="premod" value="0" <!-- IF not PREMOD -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td>{L_TOR_COMMENT}</td>
	<td>
		<label><input type="radio" name="tor_comment" value="1" <!-- IF TOR_COMMENT -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="tor_comment" value="0" <!-- IF not TOR_COMMENT -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>

<tr class="row3 med">
	<td class="bold tCenter" colspan="2">{L_LATEST_NEWS}</td>
</tr>
<tr>
	<td><h4>{L_LATEST_NEWS}</h4></td>
	<td>
		<label><input type="radio" name="show_latest_news" value="1" <!-- IF SHOW_LATEST_NEWS -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="show_latest_news" value="0" <!-- IF not SHOW_LATEST_NEWS -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_MAX_NEWS_TITLE}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="max_news_title" value="{MAX_NEWS_TITLE}" /></td>
</tr>
<tr>
	<td><h4>{L_NEWS_COUNT}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="latest_news_count" value="{LATEST_NEWS_COUNT}" /></td>
</tr>
<tr>
	<td><h4>{L_NEWS_FORUM_ID}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="latest_news_forum_id" value="{LATEST_NEWS_FORUM_ID}" /></td>
</tr>

<tr class="row3 med">
	<td class="bold tCenter" colspan="2">{L_NETWORK_NEWS}</td>
</tr>
<tr>
	<td><h4>{L_NETWORK_NEWS}</h4></td>
	<td>
		<label><input type="radio" name="show_network_news" value="1" <!-- IF SHOW_NETWORK_NEWS -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="show_network_news" value="0" <!-- IF not SHOW_NETWORK_NEWS -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_MAX_NEWS_TITLE}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="max_net_title" value="{MAX_NET_TITLE}" /></td>
</tr>
<tr>
	<td><h4>{L_NEWS_COUNT}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="network_news_count" value="{NETWORK_NEWS_COUNT}" /></td>
</tr>
<tr>
	<td><h4>{L_NEWS_FORUM_ID}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="network_news_forum_id" value="{NETWORK_NEWS_FORUM_ID}" /></td>
</tr>

<tr class="row3 med">
	<td class="bold tCenter" colspan="2">{L_BIRTHDAY}</td>
</tr>
<tr>
	<td><h4>{L_BIRTHDAY_ENABLE}</h4></td>
	<td>
		<label><input type="radio" name="birthday_enabled" value="1" <!-- IF BIRTHDAY_ENABLED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="birthday_enabled" value="0" <!-- IF not BIRTHDAY_ENABLED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_BIRTHDAY_MAX_AGE}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="birthday_max_age" value="{BIRTHDAY_MAX_AGE}" />&nbsp;{L_YEARS}</td>
</tr>
<tr>
	<td><h4>{L_BIRTHDAY_MIN_AGE}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="birthday_min_age" value="{BIRTHDAY_MIN_AGE}" />&nbsp;{L_YEARS}</td>
</tr>
<tr>
	<td><h4>{L_BIRTHDAY_CHECK_DAY}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="birthday_check_day" value="{BIRTHDAY_CHECK_DAY}" />&nbsp;{L_DAYS}</td>
</tr>

<tr class="row3 med">
	<td class="bold tCenter" colspan="2">{L_SEED_BONUS}</td>
</tr>
<tr>
	<td><h4>{L_SEED_BONUS}</h4></td>
	<td>
		<label><input type="radio" name="seed_bonus_enabled" value="1" <!-- IF SEED_BONUS_ENABLED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="seed_bonus_enabled" value="0" <!-- IF not SEED_BONUS_ENABLED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr class="row3 med">
	<td class="bold tCenter warnColor1" colspan="2">{L_SEED_BONUS_WARNING}</td>
</tr>
<!-- BEGIN seed_bonus -->
<tr id="seed_bonus_{seed_bonus.RELEASE}">
	<td>{L_SEED_BONUS_ADD}</td>
	<td>
		<input class="post" type="text" size="5" name="seed_bonus_release[]" value="{seed_bonus.RELEASE}" />&nbsp;{L_SEED_BONUS_RELEASE} <br />
		<input class="post" type="text" size="5" name="seed_bonus_points[]" value="{seed_bonus.POINTS}" />&nbsp;{L_SEED_BONUS_POINTS} &nbsp;
		<input onclick="$('#seed_bonus_{seed_bonus.RELEASE}').remove();" class="post" type="button" size="2" value="{L_DELETE}" />
	</td>
</tr>
<!-- END seed_bonus -->
<tr class="row3 med"><td colspan="2"></td></tr>
<tr id="seed_bonus">
	<td>{L_SEED_BONUS_ADD}</td>
	<td>
		<input class="post" type="text" size="5" name="seed_bonus_release[]" value="" />&nbsp;{L_SEED_BONUS_RELEASE} <br />
		<input class="post" type="text" size="5" name="seed_bonus_points[]" value="" />&nbsp;{L_SEED_BONUS_POINTS}
		<input onclick="$('#seed_bonus').clone().appendTo('.seed_bonus');" class="post" type="button" size="2" value="+" />
		<input onclick="$('#seed_bonus').remove();" class="post" type="button" size="2" value="-" />
	</td>
</tr>
<tbody class="seed_bonus"></tbody>
<tr class="row3 med"><td colspan="2"></td></tr>
<tr>
	<td>{L_SEED_BONUS_TOR_SIZE}</td>
	<td><input class="post" type="text" size="25" maxlength="100" name="seed_bonus_tor_size" value="{SEED_BONUS_TOR_SIZE}" />&nbsp;{L_GB}</td>
</tr>
<tr>
	<td>{L_SEED_BONUS_USER_REGDATA}</td>
	<td><input class="post" type="text" size="25" maxlength="100" name="seed_bonus_user_regdate" value="{SEED_BONUS_USER_REGDATE}" />&nbsp;{L_DAYS}</td>
</tr>
<tr class="row3 med">
	<td class="bold tCenter" colspan="2">{L_SEED_BONUS_EXCHANGE}</td>
</tr>
<!-- BEGIN bonus_upload -->
<tr id="bonus_upload_{bonus_upload.UP}">
	<td><h4>{L_SEED_BONUS_ROPORTION}</h4><h6></h6></td>
	<td>
		<input class="post" type="text" size="5" name="bonus_upload[]" value="{bonus_upload.UP}" />&nbsp;{L_GB} <br />
		<input class="post" type="text" size="5" name="bonus_upload_price[]" value="{bonus_upload.PRICE}" />&nbsp;{L_PRICE}
		<input onclick="$('#bonus_upload_{bonus_upload.UP}').remove();" class="post" type="button" size="2" value="{L_DELETE}" />
	</td>
</tr>
<!-- END bonus -->
<tr class="row3 med"><td colspan="2"></td></tr>
<tr id="bonus_upload">
	<td><h4>{L_SEED_BONUS_ROPORTION}</h4><h6></h6></td>
	<td>
		<input class="post" type="text" size="5" name="bonus_upload[]" value="" />&nbsp;{L_GB} <br />
		<input class="post" type="text" size="5" name="bonus_upload_price[]" value="" />&nbsp;{L_PRICE}
		<input onclick="$('#bonus_upload').clone().appendTo('.bonus_upload');" class="post" type="button" size="2" value="+" />
		<input onclick="$('#bonus_upload').remove();" class="post" type="button" size="2" value="-" />
	</td>
</tr>
<tbody class="bonus_upload"></tbody>
<tr class="row3 med"><td colspan="2"></td></tr>
<!-- ENDIF / CONFIG_MODS -->
<tr>
	<td class="catBottom" colspan="2">
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	</td>
</tr>
</table>

</form>

<br clear="all" />
