<!-- IF TORHELP_TOPICS -->
	<!-- INCLUDE torhelp.tpl -->
	<div class="spacer_6"></div>
<!-- ENDIF -->

<div id="forums_list_wrap">

<div id="forums_top_nav">
	<h1 class="pagetitle"><a href="{U_INDEX}">{T_INDEX}</a></h1>
</div><!--/forums_top_nav-->

<!-- IF LOGGED_IN -->
<div id="forums_top_links">
	<div class="floatL">
		<a href="{U_SEARCH_LATEST}" class="med">{L_SEARCH_LATEST}</a> &#0183;
		<a href="{U_SEARCH_SELF_BY_LAST}" class="med">{L_SEARCH_SELF}</a> <a href="#search-my-posts" class="menu-root menu-alt1">{OPEN_MENU_IMG_ALT}</a> &#0183;
		<a href="{SITE_URL}internal_data/atom/f/0.atom" class="med">{FEED_IMG} {L_LATEST_RELEASES}</a> &#0183;
		<a href="{U_INDEX}?map=1" class="med bold">{FEED_IMG} {L_FORUM_MAP}</a>
	</div>
	<div class="floatR med bold">
		<a class="menu-root" href="#only-new-options">{L_DISPLAYING_OPTIONS}</a>
	</div>
	<div class="clear"></div>
</div><!--/forums_top_links-->

<div class="menu-sub" id="search-my-posts">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th>{L_SEARCH_SELF}</th>
	</tr>
	<tr>
		<td>
			<fieldset id="search-my">
			<legend>{L_SORT_BY}</legend>
			<div class="bold nowrap pad_2">
				<p class="mrg_4"><a class="med" href="{U_SEARCH_SELF_BY_LAST}">{L_SEARCH_SELF_BY_LAST}</a></p>
				<p class="mrg_4"><a class="med" href="{U_SEARCH_SELF_BY_MY}">{L_SEARCH_SELF_BY_MY}</a></p>
			</div>
			</fieldset>
		</td>
	</tr>
	</table>
</div><!--/search-my-posts-->
<!-- ENDIF -->

<img width="540" class="spacer" src="{SPACER}" alt="" />

<div id="forums_wrap">

<!-- IF H_C_AL_MESS -->
<div class="row1 med tCenter pad_4 border bw_TRBL" style="margin: 4px 0;">{L_HIDE_CAT_MESS}</div>
<div class="spacer_2"></div>
<!-- ENDIF -->

<!-- IF SHOW_FORUMS -->

<!-- IF SHOW_MAP -->
	<!-- INCLUDE index_map.tpl -->
<!-- ELSE -->

<!-- BEGIN c -->
<div class="category">
	<h3 class="cat_title"><a href="{c.U_VIEWCAT}">{c.CAT_TITLE}</a></h3>
	<div class="f_tbl_wrap">

		<table class="forums">
		<thead>
		<tr class="row3">
			<th class="f_icon">&nbsp;</th>
			<th class="f_titles">{L_FORUM}</th>
			<th class="f_topics">{L_TOPICS_SHORT}</th>
			<th class="f_posts">{L_POSTS_SHORT}</th>
			<th class="f_last_post last_td">{L_LASTPOST}</th>
		</tr>
		</thead>

		<tbody>
		<!-- BEGIN f -->
		<tr>
			<td class="row1 f_icon">
			<a href="search.php?f={c.f.FORUM_ID}&amp;new=1&amp;dm=1&amp;s=0&amp;o=1"><img class="forum_icon" src="{c.f.FORUM_FOLDER_IMG}" alt="{c.f.FORUM_FOLDER_ALT}" /></a>
			</td>
			<td class="row1 f_titles">

				<h4 class="forumlink"><a href="{FORUM_URL}{c.f.FORUM_ID}">{c.f.FORUM_NAME}</a></h4>

				<!-- IF c.f.FORUM_DESC -->
				<p class="forum_desc">{c.f.FORUM_DESC}</p>
				<!-- ENDIF -->

				<!-- IF c.f.LAST_SF_ID -->
				<p class="subforums">
					<em>{L_SUBFORUMS}:</em>
					<!-- BEGIN sf -->
					<span class="sf_title{c.f.sf.SF_NEW}"><a href="search.php?f={c.f.sf.SF_ID}&amp;new=1&amp;dm=1&amp;s=0&amp;o=1" class="dot-sf">&#9658;</a><a href="{FORUM_URL}{c.f.sf.SF_ID}" class="dot-sf">{c.f.sf.SF_NAME}</a></span><span class="sf_separator"></span>
					<!-- END sf -->
				</p>
				<!-- ENDIF -->

				<!-- IF c.f.MODERATORS && SHOW_MOD_INDEX -->
				<p class="moderators"><em>{L_MODERATORS}:</em> {c.f.MODERATORS}</p>
				<!-- ENDIF -->

			</td>
			<td class="row2 f_topics">{c.f.TOPICS}</td>
			<td class="row2 f_posts">{c.f.POSTS}</td>
			<td class="row2 f_last_post last_td">

			<!-- IF c.f.POSTS -->
				<!-- BEGIN last -->
					<!-- IF SHOW_LAST_TOPIC -->
					<h6 class="last_topic">
						<a href="{TOPIC_URL}{c.f.last.LAST_TOPIC_ID}{NEWEST_URL}" title="{c.f.last.LAST_TOPIC_TIP}">{c.f.last.LAST_TOPIC_TITLE}</a>
					</h6>
					<!-- ENDIF / SHOW_LAST_TOPIC -->

					<p class="last_post_time">
						<span class="last_time">{c.f.last.LAST_POST_TIME}</span>
						<span class="last_author">&middot;
							{c.f.last.LAST_POST_USER}
						</span>
					</p>
				<!-- END last -->

			<!-- ELSE / start of !c.f.POSTS -->
				{L_NO_POSTS}
			<!-- ENDIF -->

			</td>
		</tr>
		<!-- END f -->
		</tbody>
		</table>
	</div><!--/f_tbl_wrap-->
</div><!--/category-->
<div class="cat_footer"></div>
<div class="cat_separator"></div>
<!-- END c -->

<!-- ENDIF / SHOW_MAP -->

<!-- ELSE / SHOW_FORUMS -->

<table class="forumline">
	<tr><td class="row1 tCenter pad_8">{NO_FORUMS_MSG}</td></tr>
</table>
<div class="spacer_6"></div>

<!-- ENDIF -->

</div><!--/forums_wrap-->

<div id="forums_footer"></div>

<!-- IF LOGGED_IN and SHOW_FORUMS -->
<div id="mark_all_forums_read">
	<a href="{U_SEARCH_NEW}" class="med">{L_SEARCH_NEW}</a> &#0183;
	<a href="{U_INDEX}" class="med" onclick="setCookie('{COOKIE_MARK}', 'all_forums');">{L_MARK_ALL_FORUMS_READ}</a>
</div>
<!-- ENDIF -->

<div id="board_stats">
	<h3 class="cat_title">{L_WHOSONLINE}</h3>
	<div id="board_stats_wrap">

	<table class="forums">
	<tr>
		<td class="row1 f_icon"><img class="forum_icon" src="{IMG}whosonline.gif" alt="" /></td>
		<td class="row1 small last_td">
			<div class="med" style="line-height: 16px">
				<p>{TOTAL_TOPICS}</p>
				<p>{TOTAL_POSTS}</p>
				<p>{TOTAL_USERS}</p>
				<p>{TOTAL_GENDER}</p>
				<p>{NEWEST_USER}</p>

				<!-- IF $bb_cfg['tor_stats'] -->
				<div class="hr1" style="margin: 5px 0 4px;"></div>
				<p>{TORRENTS_STAT}</p>
				<p>{PEERS_STAT}</p>
				<p>{SPEED_STAT}</p>
				<!-- ENDIF -->

				<!-- IF $bb_cfg['birthday_enabled'] -->
				<script type="text/javascript">
				ajax.callback.index_data = function(data) {
					$('#'+ data.mode).html(data.html);
				};
				</script>
				<div class="hr1" style="margin: 5px 0 4px;"></div>
				<p id="birthday_today" class="birthday">{WHOSBIRTHDAY_TODAY}</p>
				<p id="birthday_week" class="birthday">{WHOSBIRTHDAY_WEEK}</p>
				<!-- ENDIF -->

				<div class="hr1" style="margin: 5px 0 4px;"></div>

				<p>{TOTAL_USERS_ONLINE}<!-- IF IS_ADMIN --> &nbsp;{USERS_ONLINE_COUNTS}<!-- ENDIF --></p>
				<p>{RECORD_USERS}</p>

				<!-- IF SHOW_ONLINE_LIST -->
					<style type="text/css"><!-- IF IS_ADMIN -->.colorISL, a.colorISL, a.colorISL:visited { color: #793D00; }<!-- ELSE -->.ou_stat { display: none; }<!-- ENDIF --></style>
					<a name="online"></a>
					<div id="online_userlist" style="margin-top: 4px;">{LOGGED_IN_USER_LIST}</div>

					<div class="hr1" style="margin: 5px 0 4px;"></div>

					<p id="online_time">{L_ONLINE_EXPLAIN}</p>
					<p id="online_explain">
						[ <span class="colorAdmin"><b>{L_ONLINE_ADMIN}</b></span> ]
						[ <span class="colorMod"><b>{L_ONLINE_MOD}</b></span> ]
						[ <span class="colorGroup"><b>{L_ONLINE_GROUP_MEMBER}</b></span> ]
					</p>
				<!-- ENDIF -->
			</div>
		</td>
	</tr>
	</table>
	</div><!--/board_stats_wrap-->
</div><!--/board_stats-->
<div class="cat_footer"></div>

<div class="spacer_4"></div>

<!--bottom_info-->
<div class="bottom_info">

	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

	<table class="bCenter med" id="f_icons_legend">
	<tr>
		<td><img class="forum_icon" src="{IMG}folder_new_big.gif" alt="{L_NEW}"/></td>
		<td>{L_NEW_POSTS}</td>
		<td><img class="forum_icon" src="{IMG}folder_big.gif" alt="{L_OLD}" /></td>
		<td>{L_NO_NEW_POSTS}</td>
		<td><img class="forum_icon" src="{IMG}folder_locked_big.gif" alt="{L_FORUM_LOCKED_MAIN}" /></td>
		<td>{L_FORUM_LOCKED_MAIN}</td>
	</tr>
	</table>

</div><!--/bottom_info-->

</div><!--/forums_list_wrap-->
