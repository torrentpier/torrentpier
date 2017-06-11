<h1>{L_TRACKER_CFG_TITLE}</h1>

<form action="{S_CONFIG_ACTION}" method="post">

<table class="forumline">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_TRACKER_SETTINGS}</th>
</tr>
<!-- IF DISABLE_SUBMIT -->
<tr>
	<td colspan="2" class="pad_0 tCenter">
		<p class="warnColor1 warnBorder1 pad_10">{L_CHANGES_DISABLED}</p>
	</td>
</tr>
<!-- ENDIF -->
<tr>
	<td width="70%"><h4>{L_OFF_TRACKER}</h4></td>
	<td width="30%"><label for="off1"><input type="radio" name="off" id="off1" value="1" {OFF_YES} /> {L_OFF_YES}&nbsp;</label><label for="off2">&nbsp;<input type="radio" name="off" id="off2" value="0" {OFF_NO} /> {L_OFF_NO} &nbsp;</label></td>
</tr>
<tr>
	<td style="padding-left: 20px"><h4>{L_OFF_REASON}</h4><h6>{L_OFF_REASON_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="30" maxlength="255" name="off_reason" value="{OFF_REASON}" /></span></td>
</tr>
<tr>
	<td><h4>{L_AUTOCLEAN}</h4><h6>Used in cron <b>Tracker cleanup and dlstat</b></h6><h6>{L_AUTOCLEAN_EXPL}</h6></td>
	<td><label for="autoclean1"><input type="radio" name="autoclean" id="autoclean1" value="1" {AUTOCLEAN_YES} /> {L_AUTOCLEAN_YES}&nbsp;</label><label for="autoclean2">&nbsp;<input type="radio" name="autoclean" id="autoclean2" value="0" {AUTOCLEAN_NO} /> {L_AUTOCLEAN_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_COMPACT_MODE}</h4><h6>{L_COMPACT_MODE_EXPL}</h6></td>
	<td><label for="compact_mode1"><input type="radio" name="compact_mode" id="compact_mode1" value="1" {COMPACT_MODE_YES} /> {L_COMPACT_MODE_YES}&nbsp;</label><label for="compact_mode2">&nbsp;<input type="radio" name="compact_mode" id="compact_mode2" value="0" {COMPACT_MODE_NO} /> {L_COMPACT_MODE_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BROWSER_REDIRECT_URL}</h4><h6>{L_BROWSER_REDIRECT_URL_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="35" maxlength="255" name="browser_redirect_url" value="{BROWSER_REDIRECT_URL}" /></span>&nbsp;</td>
</tr>

<tr>
	<th colspan="2">{L_USE_AUTH_KEY_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_USE_AUTH_KEY}</h4><h6>{L_USE_AUTH_KEY_EXPL}</h6></td>
	<td>&nbsp; {L_YES}</td>
</tr>
<tr>
	<td><h4>{L_AUTH_KEY_NAME}</h4><h5>$bb_cfg['passkey_key']</h5><h6>{L_AUTH_KEY_NAME_EXPL}</h6></td>
	<td>&nbsp; <b>{PASSKEY_KEY}</b></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_GUEST_DL}</h4><!-- IF L_ALLOW_GUEST_DL_EXPL --><h6>{L_ALLOW_GUEST_DL_EXPL}</h6><!-- ENDIF --></td>
	<td>&nbsp; <!-- IF $bb_cfg['bt_tor_browse_only_reg'] -->{L_NO}<!-- ELSE -->{L_YES}<!-- ENDIF --></td>
</tr>
<tr>
	<th colspan="2">{L_LIMIT_ACTIVE_TOR_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_NUMWANT}</h4><h6>{L_NUMWANT_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="3" maxlength="4" name="numwant" value="{NUMWANT}" /></td>
</tr>
<tr>
	<td><h4>{L_LIMIT_ACTIVE_TOR}</h4></td>
	<td><label for="limit_active_tor1"><input type="radio" name="limit_active_tor" id="limit_active_tor1" value="1" {LIMIT_ACTIVE_TOR_YES} /> {L_LIMIT_ACTIVE_TOR_YES}&nbsp;</label><label for="limit_active_tor2">&nbsp;<input type="radio" name="limit_active_tor" id="limit_active_tor2" value="0" {LIMIT_ACTIVE_TOR_NO} /> {L_LIMIT_ACTIVE_TOR_NO} &nbsp;</label></td>
</tr>
<tr>
	<td style="padding-left: 20px"><h4>{L_LIMIT_SEED_COUNT}</h4><h6>{L_LIMIT_SEED_COUNT_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="3" maxlength="4" name="limit_seed_count" value="{LIMIT_SEED_COUNT}" /> <span class="med">torrents</span></td>
</tr>
<tr>
	<td style="padding-left: 20px"><h4>{L_LIMIT_LEECH_COUNT}</h4><h6>{L_LIMIT_LEECH_COUNT_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="3" maxlength="4" name="limit_leech_count" value="{LIMIT_LEECH_COUNT}" /> <span class="med">torrents</span></td>
</tr>
<tr>
	<td style="padding-left: 20px"><h4>{L_LEECH_EXPIRE_FACTOR}</h4><h6>{L_LEECH_EXPIRE_FACTOR_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="3" maxlength="4" name="leech_expire_factor" value="{LEECH_EXPIRE_FACTOR}" /> <span class="med">minutes</span></td>
</tr>
<tr>
	<td><h4>{L_LIMIT_CONCURRENT_IPS}</h4><h6>{L_LIMIT_CONCURRENT_IPS_EXPL}</h6></td>
	<td><label for="limit_concurrent_ips1"><input type="radio" name="limit_concurrent_ips" id="limit_concurrent_ips1" value="1" {LIMIT_CONCURRENT_IPS_YES} /> {L_LIMIT_CONCURRENT_IPS_YES}&nbsp;</label><label for="limit_concurrent_ips2">&nbsp;<input type="radio" name="limit_concurrent_ips" id="limit_concurrent_ips2" value="0" {LIMIT_CONCURRENT_IPS_NO} /> {L_LIMIT_CONCURRENT_IPS_NO} &nbsp;</label></td>
</tr>
<tr>
	<td style="padding-left: 20px"><h4>{L_LIMIT_SEED_IPS}</h4><h6>{L_LIMIT_SEED_IPS_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="3" maxlength="4" name="limit_seed_ips" value="{LIMIT_SEED_IPS}" /> <span class="med">IP's</span></td>
</tr>
<tr>
	<td style="padding-left: 20px"><h4>{L_LIMIT_LEECH_IPS}</h4><h6>{L_LIMIT_LEECH_IPS_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="3" maxlength="4" name="limit_leech_ips" value="{LIMIT_LEECH_IPS}" /> <span class="med">IP's</span></td>
</tr>

<tr>
	<th colspan="2">{L_ANNOUNCE_INTERVAL_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_ANNOUNCE_INTERVAL}</h4><h5>$bb_cfg['announce_interval']</h5><h6>{L_ANNOUNCE_INTERVAL_EXPL}</h6></td>
	<td><span class="med">&nbsp; <b>{ANNOUNCE_INTERVAL}</b></span> <span class="med">seconds</span></td>
</tr>
<tr>
	<td><h4>{L_EXPIRE_FACTOR}</h4><h6>{L_EXPIRE_FACTOR_EXPL}</h6></td>
	<td>&nbsp; <input class="post" type="text" size="3" maxlength="4" name="expire_factor" value="{EXPIRE_FACTOR}" /></td>
</tr>
<tr>
	<td><h4>{L_UPDATE_DLSTAT}</h4><h6>Used in cron <b>Tracker cleanup and dlstat</b></h6></td>
	<td><label for="update_dlstat1"><input type="radio" name="update_dlstat" id="update_dlstat1" value="1" {UPDATE_DLSTAT_YES} /> {L_UPDATE_DLSTAT_YES}&nbsp;</label><label for="update_dlstat2">&nbsp;<input type="radio" name="update_dlstat" id="update_dlstat2" value="0" {UPDATE_DLSTAT_NO} /> {L_UPDATE_DLSTAT_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_ADD_RETRACKER}</h4></td>
	<td><label for="retracker_true"><input type="radio" name="retracker" id="retracker_true" value="1" {RETRACKER_YES} /> {L_YES}&nbsp;</label><label for="retracker_false">&nbsp;<input type="radio" name="retracker" id="retracker_false" value="0" {RETRACKER_NO} /> {L_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_GOLD} / {L_SILVER}</h4></td>
	<td>
		<label><input type="radio" name="gold_silver_enabled" value="1" <!-- IF GOLD_SILVER_ENABLED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>
		<label><input type="radio" name="gold_silver_enabled" value="0" <!-- IF not GOLD_SILVER_ENABLED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_IGNORE_GIVEN_IP}</h4><h5>$bb_cfg['ignore_reported_ip']</h5><h6><!-- IF L_IGNOR_GIVEN_IP_EXPL -->{L_IGNOR_GIVEN_IP_EXPL}<!-- ENDIF --></h6></td>
	<td>&nbsp; <!-- IF IGNORE_REPORTED_IP -->{L_YES}<!-- ELSE -->{L_NO}<!-- ENDIF --></td>
</tr>
<tr>
	<td colspan="2" class="catBottom">
<!-- IF not DISABLE_SUBMIT -->
	{S_HIDDEN_FIELDS}
		<input type="submit" name="set_defaults" id="def" value="{L_SET_DEFAULTS}" class="liteoption" disabled="disabled" />&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />&nbsp;
		<input type="submit" name="submit" id="send" value="{L_SUBMIT}" class="mainoption" disabled="disabled" />&nbsp;
		<label for="confirm">{L_CONFIRM}&nbsp;<input onclick="toggle_disabled('def', this.checked); toggle_disabled('send', this.checked)" id="confirm" type="checkbox" name="confirm" value="1" /></label>
<!-- ENDIF -->
	</td>
</tr>
</table>
</form>

<br clear="all" />
