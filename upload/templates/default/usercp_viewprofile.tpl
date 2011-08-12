<!-- IF SHOW_ADMIN_OPTIONS -->
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

// change_user_rank
ajax.change_user_rank = function(uid, rank_id) {
	$('#rank-msg').html('<i class="loading-1">выполняется...</i>');
	ajax.exec({
		action  : 'change_user_rank',
		user_id : uid,
		rank_id : rank_id
	});
}
ajax.callback.change_user_rank = function(data) {
	$('#rank-msg').html(data.html);
}

ajax.user_opt = {AJAX_USER_OPT};

// change_user_opt
ajax.change_user_opt = function() {
	ajax.exec({
		action   : 'change_user_opt',
		user_id  : {PROFILE_USER_ID},
		user_opt : $.toJSON(ajax.user_opt)
	});
};
ajax.callback.change_user_opt = function(data){
	$('#user-opt-resp').html(data.resp_html);
	$('#user-opt-save-btn').attr({ disabled: 0 });
}

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
	$('#user-opt').show();
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
<var class="ajax-params">{action: "edit_user_profile", id: "user_gender", editableType: "yesno-gender"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "user_birthday"}</var>

<!-- IF IGNORE_SRV_LOAD_EDIT -->
<var class="ajax-params">{action: "edit_user_profile", id: "ignore_srv_load", editableType: "yesno-radio"}</var>
<!-- ENDIF -->
<!-- IF CAN_EDIT_RATIO -->
<var class="ajax-params">{action: "edit_user_profile", id: "u_up_total"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "u_down_total"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "u_up_release"}</var>
<var class="ajax-params">{action: "edit_user_profile", id: "u_up_bonus"}</var>
<!-- ENDIF -->

<!-- ENDIF / SHOW_ADMIN_OPTIONS -->

<a name="editprofile"></a>

<h1 class="pagetitle">{L_VIEWING_PROFILE}</h1>

<div class="nav">
	<p class="floatL"><a href="{U_INDEX}">{T_INDEX}</a></p>
	<!-- IF IS_ADMIN -->
	<p class="floatR">
	<a href="{U_MANAGE}">{L_PROFILE}</a> &middot; 
	<a href="{U_PERMISSIONS}">{L_PERMISSIONS}</a>&nbsp;</p>
	<!-- ENDIF -->
	<div class="clear"></div>
</div>

<table class="user_profile bordered w100" cellpadding="0" border=1>
<tr>
	<th colspan="2" class="thHead">{L_VIEWING_PROFILE}</th>
</tr>
<tr>
	<td colspan="2" class="catTitle">{L_ABOUT_USER_PROFILE}</td>
</tr>
<tr>
	<td class="row1 vTop tCenter" width="30%">

		<p class="mrg_4">{AVATAR_IMG}</p>
		<p class="small mrg_4">
		<!-- IF IS_ADMIN -->
			{RANK_SELECT}
			<script type="text/javascript">
			$('#rank-sel').bind('change', function(){ ajax.change_user_rank( {PROFILE_USER_ID}, $(this).val() ); });
			</script>
			<div id="rank-msg" class="mrg_6"></div>
		<!-- ELSE IF POSTER_RANK -->
			{POSTER_RANK}
		<!-- ENDIF -->
		</p>
		<h4 class="cat border bw_TB" id="username">{L_CONTACT} <span class="editable bold">{USERNAME}</span></h4>

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
					<a href="http://www.icq.com/people/searched=1&uin={ICQ}"><img align="middle" src="http://web.icq.com/whitepages/online?icq={ICQ}&img=5"><a>
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
		</table><!--/user_contacts-->

		<!-- IF IS_ADMIN -->
		<div id="user-opt" style="display: none;">
			<fieldset class="mrg_6">
			<style type="text/css"> #user-opt label { display: block; } </style>
			<legend>{L_BAN_USER}</legend>
			<div class="tLeft" style="padding: 2px 6px 6px; display: block;">
				<label><input type="checkbox" name="allow_avatar"/>{L_HIDE_AVATARS}</label>
				<label><input type="checkbox" name="allow_sig"/>{L_SHOW_CAPTION}</label>
				<label><input type="checkbox" name="allow_passkey"/>{L_DOWNLOAD_TORRENT}</label>
				<label><input type="checkbox" name="allow_pm"/>{L_SEND_PM}</label>
				<label><input type="checkbox" name="allow_post"/>{L_SEND_MESSAGE}</label>
				<label><input type="checkbox" name="allow_post_edit"/>{L_EDIT_POST}</label>
				<label><input type="checkbox" name="allow_topic"/>{L_NEW_THREADS}</label>
			</div>
			</fieldset>
			<div id="user-opt-save" class="hidden">
				<p><input id="user-opt-save-btn" class="bold long" type="button" value="{L_SUBMIT}" /></p>
				<p id="user-opt-resp" class="mrg_6"></p>
			</div>
		</div>
		<!-- ELSEIF USER_RESTRICTIONS -->
		<fieldset class="mrg_6">
		<legend>{L_USER_NOT_ALLOWED}</legend>
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
			<tr>
				<th>{L_JOINED}:</th>
				<td id="user_regdate">
					<span class="editable bold">{USER_REGDATE}</span>
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
				<td><b>{LAST_ACTIVITY_TIME}</b></td>
			</tr>
			<tr>
				<th>{L_TOTAL_POSTS}:</th>
				<td>
					<p>
						<b>{POSTS}</b>&nbsp;
						[ <a href="{U_SEARCH_USER}" class="med">{L_SEARCH_USER_POSTS}</a> ]
						[ <a href="{U_SEARCH_TOPICS}" class="med">{L_SEARCH_USER_TOPICS}</a> ]
						[ <a class="med" href={U_SEARCH_RELEASES}>{L_SEARCH_RELEASES}</a> ]
					</p>					
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
				<td id="user_website"><a href="{WWW}" class="editable">{WWW}</a></td>
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
				<td id="user_gender"><b class="editable">{GENDER}</b></td>
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
			<!-- IF SHOW_ACCESS_PRIVILEGE -->
			<tr>
				<th>{L_ACCESS}:</th>
				<td id="ignore_srv_load">{L_ACCESS_SRV_LOAD}: <b class="editable">{IGNORE_SRV_LOAD}</b></td>
			</tr>
			<!-- ENDIF -->
		</table><!--/user_details-->

	</td>
</tr>
<!-- IF SIGNATURE -->
<tr>
	<td class="row1 pad_4" colspan="2">
	    <div class="signature">{SIGNATURE}</div>
	</td>
</tr>
<!-- ENDIF -->
<!-- Report -->
<!-- BEGIN switch_report_user -->
<tr>
	<td class="catBottom" align="center" colspan="2"><a href="{U_REPORT_USER}" class="gen">{L_REPORT_USER}</a></td>
</tr>
<!-- END switch_report_user -->
<!-- Report [END] -->
</table><!--/user_profile-->

<a name="torrent"></a>
<div class="spacer_8"></div>

<table class="bordered w100">
<tr>
	<th colspan="4" class="thHead">{L_VIEW_TOR_PROF}</th>
</tr>
<tr>
	<td colspan="4" class="row2">

		<table class="ratio_details borderless bCenter mrg_4">
		<tr>
			<th><b>{L_DOWN_TOTAL}:</b></th>
			<td id="u_down_total" class="leech">
				<span class="editable bold">{DOWN_TOTAL}</span>
			</td>
		</tr>
		<tr>
			<th width="50%"><b>{L_UP_TOTAL}:</b></th>
			<td width="50%" id="u_up_total" class="seed">
				<span class="editable bold">{UP_TOTAL}</span>
			</td>
		</tr>
		<tr>
			<th>{L_TOTAL_RELEASED}:</th>
			<td id="u_up_release">
				<span class="editable seed">{RELEASED}</span>
			</td>
		</tr>
		<tr>
			<th width="50%">{L_BONUS}:</th>
			<td width="50%" id="u_up_bonus">
				<span class="editable seed">{UP_BONUS}</span>
			</td>
		</tr>

		<tr>
			<th width="50%">{L_MAX_SPEED}:</th>
			<td width="50%" id="u_up_bonus">
				<span>{SPEED_UP}</span> / <span>{SPEED_DOWN}</span>
			</td>
		</tr>

		<tr>
			<th>{L_USER_RATIO}:</th>
			<td id="u_ratio" class="gen">
				<!-- IF DOWN_TOTAL_BYTES gt MIN_DL_BYTES -->
				<b class="gen">{USER_RATIO}</b>&nbsp;
				<a class="gen" href="#" onclick="$('#ratio-expl').show(); $(this).hide(); return false;">[?]</a>
				<!-- ELSE -->
				<span class="med">{L_IT_WILL_BE_DOWN} <b>{MIN_DL_FOR_RATIO}</b></span>
				<!-- ENDIF -->
			</td>
		</tr>
		<tr id="ratio-expl" style="display: none;">
			<td colspan="2" class="med tCenter">
				(
					{L_UP_TOTAL} <b class="seedmed">{UP_TOTAL}</b>
					+ {L_TOTAL_RELEASED} <b class="seedmed">{RELEASED}</b>
					+ {L_BONUS} <b class="seedmed">{UP_BONUS}</b>
				) / {L_DOWNLOADED} <b class="leechmed">{DOWN_TOTAL}</b>
			</td>
		</tr>
		<!-- IF SHOW_PASSKEY -->
		<script type="text/javascript">
		ajax.callback.gen_passkey = function(data){
			$('#passkey').text(data.passkey);
		};
		</script>
		<tr>
			<th><a class="med" href="#" onclick="toggle_block('gen_passkey'); return false;" class="gen">Passkey:</a></th>
			<td id="passkey">{AUTH_KEY}</td>
		</tr>
		<tr id="gen_passkey" style="display: none;">
			<td colspan="2" class="med tCenter">{S_GEN_PASSKEY}</td>
		</tr>
		<!-- ENDIF / SHOW_PASSKEY -->
		</table><!--/ratio_details-->

		</td>
	</tr>

	<tr>
		<th colspan="4" class="thHead">{L_CUR_ACTIVE_DLS}</th>
	</tr>

	<tr>
		<td {RELEASED_ROWSPAN} class="row1 tCenter dlComplete lh_150 pad_4">{L_RELEASINGS}</td>
		<!-- BEGIN switch_releasing_none -->
			<td colspan="3" class="row1 w100 tCenter pad_8">{L_NONE}</td>
		</tr>
		<!-- END switch_releasing_none -->
		<!-- BEGIN released -->
		<td class="row3 tCenter">{L_FORUM}</td>
		<td colspan="2" class="row3 tCenter">{L_TOPICS}</td>
	</tr>
	<!-- BEGIN releasedrow -->
	<tr class="row1">
		<td class="tCenter pad_4"><a class="gen" href="{seed.releasedrow.U_VIEW_FORUM}">{seed.releasedrow.FORUM_NAME}</a></td>
		<td colspan="2" class="pad_4"><a class="med" href="{seed.releasedrow.U_VIEW_TOPIC}"><b>{seed.releasedrow.TOPIC_TITLE}</b></a></td>
	</tr>
	<!-- END releasedrow -->
	<!-- END released -->

	<tr>
		<td colspan="4" class="row2 pad_0"><div class="spacer_4"></div></td>
	</tr>

	<tr>
		<td {SEED_ROWSPAN} class="row1 tCenter dlComplete lh_150 pad_4">{L_SEEDINGS}</td>
		<!-- BEGIN switch_seeding_none -->
			<td colspan="3" class="row1 w100 tCenter pad_8">{L_NONE}</td>
		</tr>
		<!-- END switch_seeding_none -->
		<!-- BEGIN seed -->
		<td class="row3 tCenter">{L_FORUM}</td>
		<td colspan="2" class="row3 tCenter">{L_TOPICS}</td>
	</tr>
	<!-- BEGIN seedrow -->
	<tr class="row1">
		<td class="tCenter pad_4"><a class="gen" href="{seed.seedrow.U_VIEW_FORUM}">{seed.seedrow.FORUM_NAME}</a></td>
		<td colspan="2" class="pad_4"><a class="med" href="{seed.seedrow.U_VIEW_TOPIC}"><b>{seed.seedrow.TOPIC_TITLE}</b></a></td>
	</tr>
	<!-- END seedrow -->
	<!-- END seed -->

	<tr>
		<td colspan="4" class="row2 pad_0"><div class="spacer_4"></div></td>
	</tr>

	<tr>
		<td {LEECH_ROWSPAN} class="row1 tCenter dlDown lh_150 pad_4">{L_LEECHINGS}</td>
		<!-- BEGIN switch_leeching_none -->
		<td colspan="3" class="row1 w100 tCenter pad_8">{L_NONE}</td>
		</tr>
		<!-- END switch_leeching_none -->
		<!-- BEGIN leech -->
		<td class="row3 tCenter">{L_FORUM}</td>
		<td class="row3 tCenter">{L_TOPICS}</td>
		<td class="row3 tCenter">%</td>
	</tr>
	<!-- BEGIN leechrow -->
	<tr class="row1">
		<td class="tCenter pad_4"><a class="gen" href="{leech.leechrow.U_VIEW_FORUM}">{leech.leechrow.FORUM_NAME}</a></td>
		<td class="pad_4"><a class="med" href="{leech.leechrow.U_VIEW_TOPIC}"><b>{leech.leechrow.TOPIC_TITLE}</b></a></td>
		<td class="tCenter med"><b>{leech.leechrow.COMPL_PERC}</b></td>
	</tr>
	<!-- END leechrow -->
	<!-- END leech -->

	<tr class="row2 tCenter">
		<td class="catBottom" colspan="4" class="pad_6">
			<!-- IF SHOW_SEARCH_DL -->
				<a href="{U_SEARCH_DL_WILL}" class="med">{L_SEARCH_DL_WILL_DOWNLOADS}</a>
				::
				<a href="{U_SEARCH_DL_DOWN}" class="med">{L_SEARCH_DL_DOWN}</a>
				::
				<a href="{U_SEARCH_DL_COMPLETE}" class="med">{L_SEARCH_DL_COMPLETE}</a>
				::
				<a href="{U_SEARCH_DL_CANCEL}" class="med">{L_SEARCH_DL_CANCEL}</a>
			<!-- ELSE -->
			&nbsp;
			<!-- ENDIF -->
		</td>
	</tr>

</table>

<!--bottom_info-->
<div class="bottom_info">

	<!-- IF EDIT_PROF -->
	<form method="post" action="{EDIT_PROF_HREF}">
	<p class="tCenter mrg_10">
		<input type="submit" value="{L_EDIT_PROFILE}" class="main gen" />
	</p>
	</form>
	<!-- ELSE -->
	<br />
	<!-- ENDIF -->

	<div class="spacer_6"></div>

	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->