<!-- IF IS_ADMIN -->
<script type="text/javascript">
ajax.init.edit_user_profile = function(params){
	if (params.submit) {
		ajax.exec({
			action  : params.action,
			edit_id : params.id,
			user_id : params.user_id || {PROFILE_USER_ID},
			field   : params.field || params.id,
			value   : params.value
		});
	}
	else {
		editableType = params.editableType || "input";
		ajax.makeEditable(params.id, editableType);
	}
};
ajax.callback.edit_user_profile = function(data){
	ajax.restoreEditable(data.edit_id, data.new_value);
};

// avatar
ajax.avatar = function (mode, uid) {
	ajax.exec({
		action  : 'avatar',
		mode    : mode,
		user_id : uid
	});
};
ajax.callback.avatar = function (data) {
	$('#avatar-img').html(data.avatar_html);
	$('#avatar-adm').hide();
};

// change_user_rank
ajax.change_user_rank = function (uid, rank_id) {
	$('#rank-msg').html('<i class="loading-1">{L_LOADING}</i>');
	ajax.exec({
		action  : 'change_user_rank',
		user_id : uid,
		rank_id : rank_id
	});
};
ajax.callback.change_user_rank = function (data) {
	$('#rank-msg').html(data.html);
	$('#rank-name').html(data.rank_name);
};

ajax.user_opt = {AJAX_USER_OPT};

// change_user_opt
ajax.change_user_opt = function() {
	ajax.exec({
		action   : 'change_user_opt',
		user_id  : {PROFILE_USER_ID},
		user_opt : $.toJSON(ajax.user_opt)
	});
};
ajax.callback.change_user_opt = function (data) {
	$('#user-opt-resp').html(data.resp_html);
	$('#user-opt-save-btn').removeAttr('disabled');
};

$(document).ready(function(){
	$('#user-opt').find('input[type=checkbox]').click(function(){
		var $chbox = $(this);
		var opt_name = $chbox.attr('name');
		var opt_val  = $chbox.attr('checked') ? 1 : 0;
		ajax.user_opt[opt_name] = opt_val;
		$chbox.parents('label').toggleClass('bold');
		$('#user-opt-save').show();
	});
	$('#user-opt').find('input[type=checkbox]').each(function(){
		if (ajax.user_opt[ $(this).attr('name') ]) {
			$(this).attr({checked: 'checked'});
			$(this).parents('label').addClass('bold');
		}
	});
	$('#user-opt-save-btn').click(function(){
		this.disabled = 1;
		$('#user-opt-resp').html('&nbsp;');
		ajax.change_user_opt();
	});
});
</script>

<var class="ajax-params">{action: "edit_user_profile", id: "username"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_email"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_regdate"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_lastvisit"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_from"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_website"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_occ"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_interests"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_icq"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_skype"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_twitter",  editableType: "yesno-twitter"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_gender",   editableType: "yesno-gender"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_birthday", editableType: "yesno-birthday"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "u_up_total"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "u_down_total"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "u_up_release"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "u_up_bonus"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_points"}</var>

<script type="text/javascript">
ajax.callback.manage_user = function(data) {
	if(data.info) alert(data.info);
	if(data.url) document.location.href = data.url;
};
</script>
<!-- ENDIF / IS_ADMIN -->

<!-- IF IS_AM -->
<script type="text/javascript">
ajax.ip_load = false;
ajax.mod_action = function(mode) {
if (!ajax.ip_load) {
	ajax.exec({
		action  : 'mod_action',
		mode    : mode,
		user_id : {PROFILE_USER_ID}
	});
}
else
{
	$('#ip_list').toggle();
}};
ajax.callback.mod_action = function(data) {
	$('#ip_list').html(data.ip_list_html);
	ajax.ip_load = true;
}
</script>

<script type="text/javascript">
ajax.group_membership = function(mode) {
	$('#gr-mem-list').html('<i class="loading-1">{L_LOADING}</i>');
	ajax.exec({
		action  : 'group_membership',
		mode    : mode,
		user_id : {PROFILE_USER_ID}
	});
};
ajax.callback.group_membership = function(data) {
	$('#gr-mem-list').html(data.group_list_html);
};
</script>
<!-- ENDIF / IS_AM -->

<!-- IF TRAF_STATS -->
<script type="text/javascript">
ajax.index_data = function(mode) {
	ajax.exec({
		action  : 'index_data',
		mode    : mode,
		user_id : {PROFILE_USER_ID}
	});
};
ajax.callback.index_data = function(data) {
	$('#traf-stats-tbl').html(data.html);
	$('#bt_user_ratio').html(data.user_ratio);
	$('#traf-stats-span').hide();
	$('#traf-stats-tbl').show();
	$('#bt_user_ratio').show();
};
</script>
<!-- ENDIF -->

<!-- IF SHOW_PASSKEY -->
<script type="text/javascript">
ajax.callback.gen_passkey = function(data){
	$('#passkey').text(data.passkey);
};
</script>
<!-- ENDIF / SHOW_PASSKEY -->

<style type="text/css">
#traf-stats-tbl { width: 468px; background: #F9F9F9; border: 1px solid #A5AFB4; border-collapse: separate; }
#traf-stats-tbl th, #traf-stats-tbl td { padding: 2px 10px 3px; text-align: center; white-space: nowrap; font-size: 11px; }
#traf-stats-tbl th { padding: 2px <!-- IF $bb_cfg['seed_bonus_enabled'] -->11<!-- ELSE -->22<!-- ENDIF -->px 3px; }
<!-- IF TRAF_STATS -->
#traf-stats-tbl th { padding: 2px 30px 3px; }
<!-- ENDIF -->
.pagetitle a { font-size: 16px; }
</style>

<h1 class="pagetitle"><!-- IF PROFILE_USER -->{L_MY_PROFILE}<!-- ELSE -->{L_VIEWING_PROFILE}<!-- ENDIF --></h1>

<div class="nav">
	<p class="floatL"><a href="{U_INDEX}">{T_INDEX}</a></p>
	<!-- IF IS_ADMIN -->
	<p class="floatR">
		<a href="{U_MANAGE}">{L_PROFILE}</a> &middot;
		<a href="{U_PERMISSIONS}">{L_PERMISSIONS}</a>
	</p>
	<!-- ENDIF -->
	<div class="clear"></div>
</div>

<table class="user_profile bordered w100" cellpadding="0">
<tr>
	<th colspan="2" class="thHead">{L_VIEWING_PROFILE}</th>
</tr>
<tr>
	<td class="row1 vTop tCenter" width="30%">

		<div id="avatar-img" class="mrg_4 med">
			{AVATAR_IMG}
			<!-- IF IS_ADMIN || PROFILE_USER -->
			<p id="avatar-adm" class="med mrg_4">[ <a href="#" onclick="if (window.confirm('{L_AVATAR_DELETE}?')){ ajax.avatar('delete', {PROFILE_USER_ID}); } return false;" class="adm">{L_AVATAR_DELETE}</a> ]</p>
			<!-- ENDIF -->
		</div>
		<p class="small mrg_4">
		<!-- IF IS_ADMIN -->
			{RANK_SELECT}
			<script type="text/javascript">
			$('#rank-sel').bind('change', function(){ ajax.change_user_rank( {PROFILE_USER_ID}, $(this).val() ); });
			</script>
			<div id="rank-msg" class="mrg_6"></div>
		<!-- ELSE IF POSTER_RANK -->
			{RANK_IMAGE}
		<!-- ENDIF -->
		</p>
		<h4 class="cat border bw_TB" id="username">{L_CONTACT} <span class="editable bold">{USERNAME}</span></h4>

		<div class="spacer_4"></div>

		<table class="nowrap borderless user_contacts w100">
		<!-- IF EMAIL -->
		<tr>
			<th>{L_EMAIL_ADDRESS}:</th>
			<td class="tLeft med" id="user_email">{EMAIL}</td>
		</tr>
		<!-- ENDIF -->
		<!-- IF PM -->
		<tr>
			<th>{L_PRIVATE_MESSAGE}:</th>
			<td class="tLeft med">{PM}</td>
		</tr>
		<!-- ENDIF -->
		<!-- IF ICQ -->
		<tr>
			<th>{L_ICQ}:</th>
			<td class="tLeft med" id="user_icq">
				<span class="editable">{ICQ}
					<a href="http://www.icq.com/people/{ICQ}"><img align="middle" src="http://web.icq.com/whitepages/online?icq={ICQ}&img=5"></a>
				</span>
			</td>
		</tr>
		<!-- ENDIF -->
		<!-- IF SKYPE -->
		<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
		<tr>
			<th>{L_SKYPE}:</th>
			<td class="tLeft med" id="user_skype">
				<span class="editable">{SKYPE}
					<a href="skype:{SKYPE}"><img align="middle" src="http://mystatus.skype.com/smallicon/{SKYPE}" width="16" height="16"></a>
				</span>
			</td>
		</tr>
		<!-- ENDIF -->
		<!-- IF TWITTER -->
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
		<tr>
			<th>{L_TWITTER}:</th>
			<td class="tLeft med" id="user_twitter">
				<span class="editable">
					<a href="https://twitter.com/{TWITTER}" class="twitter-follow-button" data-show-count="false" data-lang="{USER_LANG}">{TWITTER}</a>
				</span>
			</td>
		</tr>
		<!-- ENDIF -->
		</table>
		<!--/user_contacts-->

		<!-- IF USER_RESTRICTIONS -->
		<fieldset class="mrg_6">
		<legend>{L_USER_NOT_ALLOWED}:</legend>
			<div class="tLeft" style="padding: 4px 6px 8px 2px;">
			<ul><li>{USER_RESTRICTIONS}</li></ul>
			</div>
		</fieldset>
		<!-- ENDIF -->

	</td>
	<td class="row1" valign="top" width="70%">

		<div class="spacer_4"></div>

		<!-- IF not USER_ACTIVE -->
		<h4 class="mrg_4 tCenter warnColor1">{L_DISABLED_USER}</h4>
		<!-- ENDIF -->

		<table class="user_details borderless w100">
			<!-- IF SHOW_ROLE -->
			<tr>
				<th>{L_ROLE}</th>
				<td id="role">
					<b id="rank-name">{POSTER_RANK}</b>
					<!-- IF GROUP_MEMBERSHIP and IS_MOD -->
					<span id="gr-mod-a">[ <a href="#" class="med" onclick="ajax.group_membership('get_group_list'); $('#gr-mem-tr').show(); $('#gr-mod-a').hide(); return false;">{L_MEMBERSHIP_IN}</a> ]</span>
					<!-- ENDIF -->
				</td>
			</tr>
			<!-- ENDIF -->
			<!-- IF GROUP_MEMBERSHIP -->
			<tr id="gr-mem-tr"<!-- IF IS_MOD --> style="display: none;"<!-- ENDIF -->>
				<th>{L_MEMBERSHIP_IN}:</th>
				<td id="gr-mem-list">
					<!-- IF IS_ADMIN --><a href="#" class="med" onclick="ajax.group_membership('get_group_list'); return false;">{GROUP_MEMBERSHIP_TXT}</a><!-- ENDIF -->
				</td>
			</tr>
			<!-- ENDIF -->
			<tr>
				<th>{L_JOINED}:</th>
				<td id="user_regdate">
					<span class="editable bold">{USER_REGDATE}</span>
					<!-- IF IS_ADMIN -->
					[ <a href="#admin" class="menu-root menu-alt1"><span class="adm">{L_MANAGE_USER}</span></a> ]
					<div class="menu-sub row1 border bw_TRBL" id="admin">
						<fieldset class="mrg_6">
							<div class="tLeft" style="padding: 5px 6px 6px; display: block; font-size: 13px;">
								<a href="#" onclick="ajax.exec({action : 'manage_user', mode: '<!-- IF USER_ACTIVE -->user_deactivate<!-- ELSE -->user_activate<!-- ENDIF -->', user_id : {PROFILE_USER_ID}}); return false;" class="<!-- IF USER_ACTIVE -->adm<!-- ELSE -->seed bold<!-- ENDIF -->"><!-- IF USER_ACTIVE -->{L_USER_DEACTIVATE}<!-- ELSE -->{L_USER_ACTIVATE}<!-- ENDIF --></a> <br />
								<a href="#" onclick="ajax.exec({action : 'manage_user', mode: 'delete_profile', user_id : '{PROFILE_USER_ID}'}); return false;" class="adm">{L_USER_DELETE_EXPLAIN}</a> <br />
								<a href="#" onclick="ajax.exec({action : 'manage_user', mode: 'delete_topics', user_id : '{PROFILE_USER_ID}'}); return false;" class="adm">{L_DELETE_USER_ALL_POSTS}</a> <br />
								<a href="#" onclick="ajax.exec({action : 'manage_user', mode: 'delete_message', user_id : '{PROFILE_USER_ID}'}); return false;" class="adm">{L_DELETE_USER_POSTS}</a> <br />
							</div>
						</fieldset>
						<fieldset class="mrg_6">
							<legend>{L_BAN_USER}</legend>
							<div class="tLeft" style="padding: 2px 6px 6px; display: block;" id="user-opt">
								<label><input type="checkbox" name="dis_avatar"/>{L_HIDE_AVATARS}</label>
								<label><input type="checkbox" name="dis_sig"/>{L_SHOW_CAPTION}</label>
								<label><input type="checkbox" name="dis_passkey"/>{L_DOWNLOAD_TORRENT}</label>
								<label><input type="checkbox" name="dis_pm"/>{L_SEND_PM}</label>
								<label><input type="checkbox" name="dis_post"/>{L_SEND_MESSAGE}</label>
								<label><input type="checkbox" name="dis_post_edit"/>{L_EDIT_POST}</label>
								<label><input type="checkbox" name="dis_topic"/>{L_NEW_THREADS}</label>
							</div>
						</fieldset>
						<div id="user-opt-save" class="hidden tCenter">
							<p><input id="user-opt-save-btn" class="bold long" type="button" value="{L_SUBMIT}" /></p>
							<p id="user-opt-resp" class="mrg_6"></p>
						</div>
					</div>
					<!-- ENDIF -->
					<!-- IF IS_AM -->[ <a href="#" class="adm" onclick="ajax.mod_action('profile_ip'); return false;">{L_IP_ADDRESS}</a> ]<!-- ENDIF -->
				</td>
			</tr>
			<tr>
				<th>{L_LAST_VISITED}:</th>
				<td id="user_lastvisit">
					<span class="editable bold">{LAST_VISIT_TIME}</span>
				</td>
			</tr>

			<tr>
				<th class="nowrap">{L_LAST_ACTIVITY}:</th>
				<td>
					<b>{LAST_ACTIVITY_TIME}</b>
					<!-- IF TRAF_STATS --><span id="traf-stats-span">[ <a href="#" id="traf-stats-btn" class="med" onclick="ajax.index_data('get_traf_stats'); return false;">{L_VIEWING_USER_BT_PROFILE}</a> ]</span><!-- ENDIF -->
				</td>
			</tr>
			<tr>
				<th>{L_TOTAL_POSTS}:</th>
				<td>
					<p>
						<b>{POSTS}</b>
						[ <a href="{U_SEARCH_USER}" class="med">{L_SEARCH_USER_POSTS}</a> ]
						[ <a href="{U_SEARCH_TOPICS}" class="med">{L_SEARCH_USER_TOPICS}</a> ]
						[ <a href="{U_SEARCH_RELEASES}" class="med">{L_SEARCH_RELEASES}</a> ]
						<!-- IF PROFILE_USER -->[ <a href="{U_WATCHED_TOPICS}" class="med">{L_WATCHED_TOPICS}</a> ]<!-- ENDIF -->
						[ <a title="{L_ATOM_SUBSCRIBE}" href="#" onclick="return post2url('feed.php', {mode: 'get_feed_url', type: 'u', id: {PROFILE_USER_ID}})">{FEED_IMG}</a> ]
					</p>
				</td>
			</tr>

			<tr id="bt_user_ratio" <!-- IF TRAF_STATS -->style="display: none;"<!-- ENDIF -->>
				<th>{L_USER_RATIO}:</th>
				<td>
					<!-- IF DOWN_TOTAL_BYTES gt MIN_DL_BYTES -->
					<b id="u_ratio" class="gen">{USER_RATIO}</b>
					[<a class="gen" href="#" onclick="toggle_block('ratio-expl'); return false;">?</a>]
					<!-- ELSE -->
					<span class="med" title="{L_IT_WILL_BE_DOWN} {MIN_DL_FOR_RATIO}"><b>{L_NONE}</b> (DL < {MIN_DL_FOR_RATIO})</span>
					<!-- ENDIF -->

					<!-- IF SHOW_PASSKEY -->
					[ {L_BT_PASSKEY}:  <span id="passkey-btn"><a class="med" href="#" onclick="$('#passkey-gen').show(); $('#passkey-btn').hide(); return false;">{L_BT_PASSKEY_VIEW}</a></span>
					<span id="passkey-gen" class="med" style="display: none;">
						<b id="passkey" class="med bold">{AUTH_KEY}</b>&nbsp;
						<a href="#" onclick="ajax.exec({ action: 'gen_passkey', user_id  : {PROFILE_USER_ID} }); return false;">{L_BT_GEN_PASSKEY}</a>
					</span> ]
					<!-- ENDIF -->
				</td>
			</tr>

			<tr id="ratio-expl" style="display: none;">
				<td colspan="2" class="med tCenter">
				( {L_UPLOADED} <b class="seedmed">{UP_TOTAL}</b> + {L_RELEASED} <b class="seedmed">{RELEASED}</b> + {L_BONUS} <b class="seedmed">{UP_BONUS}</b> ) / {L_DOWNLOADED} <b class="leechmed">{DOWN_TOTAL}</b>
				</td>
			</tr>

			<!-- IF LOCATION -->
			<tr>
				<th class="vBottom">{L_LOCATION}:</th>
				<td id="user_from"><b class="editable">{LOCATION}</b></td>
			</tr>
			<!-- ENDIF -->
			<!-- IF WWW -->
			<tr>
				<th>{L_WEBSITE}:</th>
				<td id="user_website"><a href="{WWW}" class="editable" target="_blank">{WWW}</a></td>
			</tr>
			<!-- ENDIF -->
			<!-- IF OCCUPATION -->
			<tr>
				<th>{L_OCCUPATION}:</th>
				<td id="user_occ"><b class="editable">{OCCUPATION}</b></td>
			</tr>
			<!-- ENDIF -->
			<!-- IF INTERESTS -->
			<tr>
				<th>{L_INTERESTS}:</th>
				<td id="user_interests"><b class="editable">{INTERESTS}</b></td>
			</tr>
			<!-- ENDIF -->
			<!-- IF GENDER -->
			<tr>
				<th>{L_GENDER}:</th>
				<td id="user_gender"><span class="editable">{GENDER}</span></td>
			</tr>
			<!-- ENDIF -->
			<!-- IF BIRTHDAY -->
			<tr>
				<th>{L_BIRTHDAY}:</th>
				<td id="user_birthday"><b class="editable">{BIRTHDAY}</b></td>
			</tr>
			<tr>
				<th>{L_AGE}:</th>
				<td><b>{AGE}</b></td>
			</tr>
			<!-- ENDIF -->
			<tr>
				<td colspan="2" class="pad_4">
					<table id="traf-stats-tbl" <!-- IF TRAF_STATS -->style="display: none;"<!-- ENDIF --> class="bCenter borderless" cellspacing="1">
						<tr class="row3">
							<th></th>
							<th>{L_DOWNLOADED}</th>
							<th>{L_UPLOADED}</th>
							<th>{L_RELEASED}</th>
							<th>{L_BONUS}</th>
							<!-- IF $bb_cfg['seed_bonus_enabled'] --><th>{L_SEED_BONUS}</th><!-- ENDIF -->
						</tr>
						<tr class="row1">
							<td>{L_TD_TRAF}</td>
							<td class="leech">{TD_DL}</td>
							<td class="seed">{TD_UL}</td>
							<td class="seed">{TD_REL}</td>
							<td class="seed">{TD_BONUS}</td>
							<!-- IF $bb_cfg['seed_bonus_enabled'] --><td class="points">{TD_POINTS}</td><!-- ENDIF -->
						</tr>
						<tr class="row5">
							<td>{L_YS_TRAF}</td>
							<td class="leech">{YS_DL}</td>
							<td class="seed">{YS_UL}</td>
							<td class="seed">{YS_REL}</td>
							<td class="seed">{YS_BONUS}</td>
							<!-- IF $bb_cfg['seed_bonus_enabled'] --><td class="points">{YS_POINTS}</td><!-- ENDIF -->
						</tr>
						<tr class="row1">
							<td>{L_TOTAL_TRAF}</td>
							<td id="u_down_total"><span class="editable bold leechmed">{DOWN_TOTAL}</span></td>
							<td id="u_up_total"><span class="editable bold seedmed">{UP_TOTAL}</span></td>
							<td id="u_up_release"><span class="editable bold seedmed">{RELEASED}</span></td>
							<td id="u_up_bonus"><span class="editable bold seedmed">{UP_BONUS}</span></td>
							<!-- IF $bb_cfg['seed_bonus_enabled'] --><td id="user_points"><span class="editable bold points">{USER_POINTS}</span></td><!-- ENDIF -->
						</tr>
						<tr class="row5">
							<td colspan="1">{L_MAX_SPEED}</td>
							<td colspan="2">{L_DL_DL_SPEED}: {SPEED_DOWN}</td>
							<td colspan="2">{L_DL_UL_SPEED}: {SPEED_UP}</td>
							<!-- IF $bb_cfg['seed_bonus_enabled'] --><td colspan="1"><!-- IF PROFILE_USER --><a href="profile.php?mode=bonus">{L_EXCHANGE}</a><!-- ENDIF --></td><!-- ENDIF -->
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<!--/user_details-->
	<!-- IF IS_AM --><span id="ip_list"></span><!-- ENDIF -->
	</td>
</tr>
<!-- IF SIGNATURE -->
<tr>
	<td class="row1 pad_4" colspan="2">
		<div class="signature">{SIGNATURE}</div>
	</td>
</tr>
<!-- ENDIF -->
</table>
<!--/user_profile-->

<a name="torrent"></a>
<div class="spacer_8"></div>
<!-- IF USER_DLS -->
<style type="text/css">
#dls-tbl u { display: none; }
.dls-type, .dls-f { padding: 4px; text-align: center; }
.dls-cnt { font-size: 16px; font-weight: normal; }
</style>
<script type="text/javascript">
$(function(){
	$('#dls-tbl').tablesorter();
});
</script>

<h1 class="pagetitle tCenter">{L_CUR_ACTIVE_DLS} <span class="dls-cnt">({L_RELEASINGS}, {L_SEEDINGS}, {L_LEECHINGS})</span></h1>

<div class="sectionMain">
<table class="forumline tablesorter" id="dls-tbl">
<thead>
<tr>
	<th class="{sorter: 'digit'}"><b class="tbs-text">{L_TYPE}</b></th>
	<th class="{sorter: 'text'}" width="25%"><b class="tbs-text">{L_FORUM}</b></th>
	<th class="{sorter: 'text'}" width="75%"><b class="tbs-text">{L_TOPICS}</b></th>
	<th class="{sorter: false}">{L_TORRENT}</th>
</tr>
</thead>

<!-- BEGIN released -->
<tr class="{released.ROW_CLASS}">
	<td class="dls-type"><u>0</u><b class="seedmed">{L_RELEASING}</b></td>
	<td class="dls-f"><a class="gen" href="{released.U_VIEW_FORUM}">{released.FORUM_NAME}</a></td>
	<td class="pad_4"><a class="med tLink" href="{released.U_VIEW_TOPIC}">{released.TOR_TYPE}<b>{released.TOPIC_TITLE}</a></td>
	<td class="tCenter med nowrap pad_2">
		<p><b class="seedmed">{released.TOPIC_SEEDERS}</b> | <b class="leechmed">{released.TOPIC_LEECHERS}</b></p>
		<p style="padding-top: 2px" class="seedsmall">{released.SPEED_UP}</p>
	</td>
</tr>
<!-- END released -->

<!-- BEGIN seed -->
<tr class="{seed.ROW_CLASS}">
	<td class="dls-type"><u>1</u><span class="seedmed">{L_SEEDING}</span></td>
	<td class="dls-f"><a class="gen" href="{seed.U_VIEW_FORUM}">{seed.FORUM_NAME}</a></td>
	<td class="pad_4"><a class="med tLink" href="{seed.U_VIEW_TOPIC}">{seed.TOR_TYPE}<b>{seed.TOPIC_TITLE}</b></a></td>
	<td class="tCenter med nowrap pad_2">
		<p><b class="seedmed">{seed.TOPIC_SEEDERS}</b> | <b class="leechmed">{seed.TOPIC_LEECHERS}</b></p>
		<p style="padding-top: 2px" class="seedsmall">{seed.SPEED_UP}</p>
	</td>
</tr>
<!-- END seed -->

<!-- BEGIN leech -->
<tr class="{leech.ROW_CLASS}">
	<td class="dls-type"><u>2</u><span class="leechmed">{L_LEECHING}</span></td>
	<td class="dls-f"><a class="gen" href="{leech.U_VIEW_FORUM}">{leech.FORUM_NAME}</a></td>
	<td class="pad_4"><a class="med tLink" href="{leech.U_VIEW_TOPIC}">{leech.TOR_TYPE}<b>{leech.TOPIC_TITLE}</b></a></td>
	<td class="tCenter med nowrap pad_2">
		<p><b class="seedmed">{leech.TOPIC_SEEDERS}</b> | <b class="leechmed">{leech.TOPIC_LEECHERS}</b></p>
		<p style="padding-top: 2px" class="seedsmall">{leech.SPEED_DOWN}</p>
	</td>
</tr>
<!-- END leech -->
<tfoot>
<tr class="row2 tCenter">
	<td colspan="4" class="catBottom pad_6">&nbsp;</td>
</tr>
</tfoot>
</table>
</div>
<br />
<!-- ELSE -->
	<h1 class="pagetitle tCenter">{L_CUR_ACTIVE_DLS}: <span class="normal">{L_NO}</span></h1>
<!-- ENDIF -->

<!-- IF SHOW_SEARCH_DL -->
<div class="tCenter">
	<a class="gen" href="{U_SEARCH}?dlu={PROFILE_USER_ID}&dlw=1">{L_SEARCH_DL_WILL_DOWNLOADS}</a> ::
	<a class="gen" href="{U_SEARCH}?dlu={PROFILE_USER_ID}&dlc=1">{L_SEARCH_DL_COMPLETE_DOWNLOADS}</a>
</div>
<!-- ENDIF -->
