<h1>{L_FORUM_CFG_TITLE}</h1>

<form action="{S_CONFIG_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline wAuto">
<col class="row4">
<col class="row5">
<col class="row4">
<tr>
	<th colspan="3">{L_BT_SELECT_FORUMS}</th>
</tr>
<tr class="row2 tCenter">
	<td>{L_ALLOW_REG_TRACKER} <input onclick="$('select').each(function(){ this.size += 5; }); return false;" class="mainoption" type="button" value="+" /></td>
	<td>{L_SELF_MODERATED}</td>
	<td>{L_ALLOW_PORNO_TOPIC}</td>
</tr>
<tr class="tCenter">
	<td>{S_ALLOW_REG_TRACKER}</td>
	<td>{S_SELF_MODERATED}</td>
	<td>{S_ALLOW_PORNO_TOPIC}</td>
</tr>
<tr class="row2 tCenter">
	<td colspan="3" class="small">{L_BT_SELECT_FORUMS_EXPL}</td>
</tr>
</table>

<div class="spacer_12"></div>

<table class="forumline">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_BT_ANNOUNCE_URL_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_BT_ANNOUNCE_URL}</h4><h6>{L_BT_ANNOUNCE_URL_EXPL}</h6></td>
	<td><input class="post" type="text" size="30" maxlength="255" name="bt_announce_url" value="{BT_ANNOUNCE_URL}" /></td>
</tr>
<tr>
	<td><h4>{L_BT_DISABLE_DHT}</h4><h6>{L_BT_DISABLE_DHT_EXPL}</h6></td>
	<td><label for="bt_disable_dht1"><input type="radio" name="bt_disable_dht" id="bt_disable_dht1" value="1" {BT_DISABLE_DHT_YES} /> {L_BT_DISABLE_DHT_YES}&nbsp;</label><label for="bt_disable_dht2">&nbsp;<input type="radio" name="bt_disable_dht" id="bt_disable_dht2" value="0" {BT_DISABLE_DHT_NO} /> {L_BT_DISABLE_DHT_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_CHECK_ANNOUNCE_URL}</h4><h6>{L_BT_CHECK_ANNOUNCE_URL_EXPL}</h6></td>
	<td><label for="bt_check_announce_url1"><input type="radio" name="bt_check_announce_url" id="bt_check_announce_url1" value="1" {BT_CHECK_ANNOUNCE_URL_YES} /> {L_BT_CHECK_ANNOUNCE_URL_YES}&nbsp;</label><label for="bt_check_announce_url2">&nbsp;<input type="radio" name="bt_check_announce_url" id="bt_check_announce_url2" value="0" {BT_CHECK_ANNOUNCE_URL_NO} /> {L_BT_CHECK_ANNOUNCE_URL_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_REPLACE_ANN_URL}</h4><h6>{L_BT_REPLACE_ANN_URL_EXPL}</h6></td>
	<td><label for="bt_replace_ann_url1"><input type="radio" name="bt_replace_ann_url" id="bt_replace_ann_url1" value="1" {BT_REPLACE_ANN_URL_YES} /> {L_BT_REPLACE_ANN_URL_YES}&nbsp;</label><label for="bt_replace_ann_url2">&nbsp;<input type="radio" name="bt_replace_ann_url" id="bt_replace_ann_url2" value="0" {BT_REPLACE_ANN_URL_NO} /> {L_BT_REPLACE_ANN_URL_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_DEL_ADDIT_ANN_URLS}</h4><h6>{L_BT_DEL_ADDIT_ANN_URLS_EXPL}</h6></td>
	<td><label for="bt_del_addit_ann_urls1"><input type="radio" name="bt_del_addit_ann_urls" id="bt_del_addit_ann_urls1" value="1" {BT_DEL_ADDIT_ANN_URLS_YES} /> {L_BT_DEL_ADDIT_ANN_URLS_YES}&nbsp;</label><label for="bt_del_addit_ann_urls2">&nbsp;<input type="radio" name="bt_del_addit_ann_urls" id="bt_del_addit_ann_urls2" value="0" {BT_DEL_ADDIT_ANN_URLS_NO} /> {L_BT_DEL_ADDIT_ANN_URLS_NO} &nbsp;</label></td>
</tr>

<tr>
	<th colspan="2">{L_BT_SHOW_PEERS_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_BT_SHOW_PEERS}</h4><h6>{L_BT_SHOW_PEERS_EXPL}</h6></td>
	<td><label for="bt_show_peers1"><input type="radio" name="bt_show_peers" id="bt_show_peers1" value="1" {BT_SHOW_PEERS_YES} /> {L_BT_SHOW_PEERS_YES}&nbsp;</label><label for="bt_show_peers2">&nbsp;<input type="radio" name="bt_show_peers" id="bt_show_peers2" value="0" {BT_SHOW_PEERS_NO} /> {L_BT_SHOW_PEERS_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_SHOW_PEERS_MODE}</h4></td>
	<td>
		<div><label for="bt_show_peers_mode_count"><input type="radio" name="bt_show_peers_mode" id="bt_show_peers_mode_count" value="{BT_SHOW_PEERS_MODE_COUNT_VAL}" {BT_SHOW_PEERS_MODE_COUNT_SEL} /> {L_BT_SHOW_PEERS_MODE_COUNT}&nbsp;</label></div>
		<div><label for="bt_show_peers_mode_names"><input type="radio" name="bt_show_peers_mode" id="bt_show_peers_mode_names" value="{BT_SHOW_PEERS_MODE_NAMES_VAL}" {BT_SHOW_PEERS_MODE_NAMES_SEL} /> {L_BT_SHOW_PEERS_MODE_NAMES}&nbsp;</label></div>
		<div><label for="bt_show_peers_mode_full"><input type="radio" name="bt_show_peers_mode" id="bt_show_peers_mode_full" value="{BT_SHOW_PEERS_MODE_FULL_VAL}" {BT_SHOW_PEERS_MODE_FULL_SEL} /> {L_BT_SHOW_PEERS_MODE_FULL}&nbsp;</label></div>
	</td>
</tr>
<tr>
	<td><h4>{L_BT_ALLOW_SPMODE_CHANGE}</h4><h6>{L_BT_ALLOW_SPMODE_CHANGE_EXPL}</h6></td>
	<td><label for="bt_allow_spmode_change1"><input type="radio" name="bt_allow_spmode_change" id="bt_allow_spmode_change1" value="1" {BT_ALLOW_SPMODE_CHANGE_YES} /> {L_BT_ALLOW_SPMODE_CHANGE_YES}&nbsp;</label><label for="bt_allow_spmode_change2">&nbsp;<input type="radio" name="bt_allow_spmode_change" id="bt_allow_spmode_change2" value="0" {BT_ALLOW_SPMODE_CHANGE_NO} /> {L_BT_ALLOW_SPMODE_CHANGE_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_SHOW_IP_ONLY_MODER}</h4></td>
	<td><label for="bt_show_ip_only_moder1"><input type="radio" name="bt_show_ip_only_moder" id="bt_show_ip_only_moder1" value="1" {BT_SHOW_IP_ONLY_MODER_YES} /> {L_BT_SHOW_IP_ONLY_MODER_YES}&nbsp;</label><label for="bt_show_ip_only_moder2">&nbsp;<input type="radio" name="bt_show_ip_only_moder" id="bt_show_ip_only_moder2" value="0" {BT_SHOW_IP_ONLY_MODER_NO} /> {L_BT_SHOW_IP_ONLY_MODER_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_SHOW_PORT_ONLY_MODER}</h4></td>
	<td><label for="bt_show_port_only_moder1"><input type="radio" name="bt_show_port_only_moder" id="bt_show_port_only_moder1" value="1" {BT_SHOW_PORT_ONLY_MODER_YES} /> {L_BT_SHOW_PORT_ONLY_MODER_YES}&nbsp;</label><label for="bt_show_port_only_moder2">&nbsp;<input type="radio" name="bt_show_port_only_moder" id="bt_show_port_only_moder2" value="0" {BT_SHOW_PORT_ONLY_MODER_NO} /> {L_BT_SHOW_PORT_ONLY_MODER_NO} &nbsp;</label></td>
</tr>

<tr>
	<th colspan="2">{L_BT_SHOW_DL_LIST_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_BT_SHOW_DL_LIST}</h4></td>
	<td><label for="bt_show_dl_list1"><input type="radio" name="bt_show_dl_list" id="bt_show_dl_list1" value="1" {BT_SHOW_DL_LIST_YES} /> {L_BT_SHOW_DL_LIST_YES}&nbsp;</label><label for="bt_show_dl_list2">&nbsp;<input type="radio" name="bt_show_dl_list" id="bt_show_dl_list2" value="0" {BT_SHOW_DL_LIST_NO} /> {L_BT_SHOW_DL_LIST_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_DL_LIST_ONLY_1ST_PAGE}</h4></td>
	<td><label for="bt_dl_list_only_1st_page1"><input type="radio" name="bt_dl_list_only_1st_page" id="bt_dl_list_only_1st_page1" value="1" {BT_DL_LIST_ONLY_1ST_PAGE_YES} /> {L_BT_DL_LIST_ONLY_1ST_PAGE_YES}&nbsp;</label><label for="bt_dl_list_only_1st_page2">&nbsp;<input type="radio" name="bt_dl_list_only_1st_page" id="bt_dl_list_only_1st_page2" value="0" {BT_DL_LIST_ONLY_1ST_PAGE_NO} /> {L_BT_DL_LIST_ONLY_1ST_PAGE_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_DL_LIST_ONLY_COUNT}</h4></td>
	<td><label for="bt_dl_list_only_count1"><input type="radio" name="bt_dl_list_only_count" id="bt_dl_list_only_count1" value="1" {BT_DL_LIST_ONLY_COUNT_YES} /> {L_BT_DL_LIST_ONLY_COUNT_YES}&nbsp;</label><label for="bt_dl_list_only_count2">&nbsp;<input type="radio" name="bt_dl_list_only_count" id="bt_dl_list_only_count2" value="0" {BT_DL_LIST_ONLY_COUNT_NO} /> {L_BT_DL_LIST_ONLY_COUNT_NO} &nbsp;</label></td>
</tr>
<tr>
	<td>
		<h4>{L_BT_SHOW_DL_LIST_BUTTONS}</h4>
		<table class="forumline wAuto med" style="margin: 4px 12px;">
			<col class="row1">
			<col class="row2">
			<tr>
				<td><b>{L_BT_SHOW_DL_BUT_WILL}</b></td>
				<td><label for="bt_show_dl_but_will1"><input type="radio" name="bt_show_dl_but_will" id="bt_show_dl_but_will1" value="1" {BT_SHOW_DL_BUT_WILL_YES} /> {L_BT_SHOW_DL_BUT_WILL_YES}&nbsp;</label><label for="bt_show_dl_but_will2">&nbsp;<input type="radio" name="bt_show_dl_but_will" id="bt_show_dl_but_will2" value="0" {BT_SHOW_DL_BUT_WILL_NO} /> {L_BT_SHOW_DL_BUT_WILL_NO} &nbsp;</label></td>
			</tr>
			<tr>
				<td><b>{L_BT_SHOW_DL_BUT_DOWN}</b></td>
				<td><label for="bt_show_dl_but_down1"><input type="radio" name="bt_show_dl_but_down" id="bt_show_dl_but_down1" value="1" {BT_SHOW_DL_BUT_DOWN_YES} /> {L_BT_SHOW_DL_BUT_DOWN_YES}&nbsp;</label><label for="bt_show_dl_but_down2">&nbsp;<input type="radio" name="bt_show_dl_but_down" id="bt_show_dl_but_down2" value="0" {BT_SHOW_DL_BUT_DOWN_NO} /> {L_BT_SHOW_DL_BUT_DOWN_NO} &nbsp;</label></td>
			</tr>
			<tr>
				<td><b>{L_BT_SHOW_DL_BUT_COMPL}</b></td>
				<td><label for="bt_show_dl_but_compl1"><input type="radio" name="bt_show_dl_but_compl" id="bt_show_dl_but_compl1" value="1" {BT_SHOW_DL_BUT_COMPL_YES} /> {L_BT_SHOW_DL_BUT_COMPL_YES}&nbsp;</label><label for="bt_show_dl_but_compl2">&nbsp;<input type="radio" name="bt_show_dl_but_compl" id="bt_show_dl_but_compl2" value="0" {BT_SHOW_DL_BUT_COMPL_NO} /> {L_BT_SHOW_DL_BUT_COMPL_NO} &nbsp;</label></td>
			</tr>
			<tr>
				<td><b>{L_BT_SHOW_DL_BUT_CANCEL}</b></td>
				<td><label for="bt_show_dl_but_cancel1"><input type="radio" name="bt_show_dl_but_cancel" id="bt_show_dl_but_cancel1" value="1" {BT_SHOW_DL_BUT_CANCEL_YES} /> {L_BT_SHOW_DL_BUT_CANCEL_YES}&nbsp;</label><label for="bt_show_dl_but_cancel2">&nbsp;<input type="radio" name="bt_show_dl_but_cancel" id="bt_show_dl_but_cancel2" value="0" {BT_SHOW_DL_BUT_CANCEL_NO} /> {L_BT_SHOW_DL_BUT_CANCEL_NO} &nbsp;</label></td>
			</tr>
		</table>
	</td>
	<td><label for="bt_show_dl_list_buttons1"><input type="radio" name="bt_show_dl_list_buttons" id="bt_show_dl_list_buttons1" value="1" {BT_SHOW_DL_LIST_BUTTONS_YES} /> {L_BT_SHOW_DL_LIST_BUTTONS_YES}&nbsp;</label><label for="bt_show_dl_list_buttons2">&nbsp;<input type="radio" name="bt_show_dl_list_buttons" id="bt_show_dl_list_buttons2" value="0" {BT_SHOW_DL_LIST_BUTTONS_NO} /> {L_BT_SHOW_DL_LIST_BUTTONS_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_SET_DLTYPE_ON_TOR_REG}</h4><h6>{L_BT_SET_DLTYPE_ON_TOR_REG_EXPL}</h6></td>
	<td><label for="bt_set_dltype_on_tor_reg1"><input type="radio" name="bt_set_dltype_on_tor_reg" id="bt_set_dltype_on_tor_reg1" value="1" {BT_SET_DLTYPE_ON_TOR_REG_YES} /> {L_BT_SET_DLTYPE_ON_TOR_REG_YES}&nbsp;</label><label for="bt_set_dltype_on_tor_reg2">&nbsp;<input type="radio" name="bt_set_dltype_on_tor_reg" id="bt_set_dltype_on_tor_reg2" value="0" {BT_SET_DLTYPE_ON_TOR_REG_NO} /> {L_BT_SET_DLTYPE_ON_TOR_REG_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_UNSET_DLTYPE_ON_TOR_UNREG}</h4></td>
	<td><label for="bt_unset_dltype_on_tor_unreg1"><input type="radio" name="bt_unset_dltype_on_tor_unreg" id="bt_unset_dltype_on_tor_unreg1" value="1" {BT_UNSET_DLTYPE_ON_TOR_UNREG_YES} /> {L_BT_UNSET_DLTYPE_ON_TOR_UNREG_YES}&nbsp;</label><label for="bt_unset_dltype_on_tor_unreg2">&nbsp;<input type="radio" name="bt_unset_dltype_on_tor_unreg" id="bt_unset_dltype_on_tor_unreg2" value="0" {BT_UNSET_DLTYPE_ON_TOR_UNREG_NO} /> {L_BT_UNSET_DLTYPE_ON_TOR_UNREG_NO} &nbsp;</label></td>
</tr>

<tr>
	<th colspan="2">{L_BT_ADD_AUTH_KEY_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_BT_ADD_AUTH_KEY}</h4></td>
	<td><label for="bt_add_auth_key1"><input type="radio" name="bt_add_auth_key" id="bt_add_auth_key1" value="1" {BT_ADD_AUTH_KEY_YES} /> {L_BT_ADD_AUTH_KEY_YES}&nbsp;</label><label for="bt_add_auth_key2">&nbsp;<input type="radio" name="bt_add_auth_key" id="bt_add_auth_key2" value="0" {BT_ADD_AUTH_KEY_NO} /> {L_BT_ADD_AUTH_KEY_NO} &nbsp;</label></td>
</tr>

<tr>
	<th colspan="2">{L_BT_TOR_BROWSE_ONLY_REG_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_BT_TOR_BROWSE_ONLY_REG}</h4></td>
	<td><label for="bt_tor_browse_only_reg1"><input type="radio" name="bt_tor_browse_only_reg" id="bt_tor_browse_only_reg1" value="1" {BT_TOR_BROWSE_ONLY_REG_YES} /> {L_BT_TOR_BROWSE_ONLY_REG_YES}&nbsp;</label><label for="bt_tor_browse_only_reg2">&nbsp;<input type="radio" name="bt_tor_browse_only_reg" id="bt_tor_browse_only_reg2" value="0" {BT_TOR_BROWSE_ONLY_REG_NO} /> {L_BT_TOR_BROWSE_ONLY_REG_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_SEARCH_BOOL_MODE}</h4><h6>{L_BT_SEARCH_BOOL_MODE_EXPL}</h6></td>
	<td><label for="bt_search_bool_mode1"><input type="radio" name="bt_search_bool_mode" id="bt_search_bool_mode1" value="1" {BT_SEARCH_BOOL_MODE_YES} /> {L_BT_SEARCH_BOOL_MODE_YES}&nbsp;</label><label for="bt_search_bool_mode2">&nbsp;<input type="radio" name="bt_search_bool_mode" id="bt_search_bool_mode2" value="0" {BT_SEARCH_BOOL_MODE_NO} /> {L_BT_SEARCH_BOOL_MODE_NO} &nbsp;</label></td>
</tr>

<tr>
	<th colspan="2">{L_BT_SHOW_DL_STAT_ON_INDEX_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_BT_SHOW_DL_STAT_ON_INDEX}</h4></td>
	<td><label for="bt_show_dl_stat_on_index1"><input type="radio" name="bt_show_dl_stat_on_index" id="bt_show_dl_stat_on_index1" value="1" {BT_SHOW_DL_STAT_ON_INDEX_YES} /> {L_BT_SHOW_DL_STAT_ON_INDEX_YES}&nbsp;</label><label for="bt_show_dl_stat_on_index2">&nbsp;<input type="radio" name="bt_show_dl_stat_on_index" id="bt_show_dl_stat_on_index2" value="0" {BT_SHOW_DL_STAT_ON_INDEX_NO} /> {L_BT_SHOW_DL_STAT_ON_INDEX_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_BT_NEWTOPIC_AUTO_REG}</h4></td>
	<td><label for="bt_newtopic_auto_reg1"><input type="radio" name="bt_newtopic_auto_reg" id="bt_newtopic_auto_reg1" value="1" {BT_NEWTOPIC_AUTO_REG_YES} /> {L_BT_NEWTOPIC_AUTO_REG_YES}&nbsp;</label><label for="bt_newtopic_auto_reg2">&nbsp;<input type="radio" name="bt_newtopic_auto_reg" id="bt_newtopic_auto_reg2" value="0" {BT_NEWTOPIC_AUTO_REG_NO} /> {L_BT_NEWTOPIC_AUTO_REG_NO} &nbsp;</label></td>
</tr>
<tr>
	<td colspan="2" class="catBottom">
		<input type="reset" value="{L_RESET}" class="liteoption" />&nbsp;&nbsp;
		<input type="submit" name="submit" id="send" value="{L_SUBMIT}" class="mainoption" disabled="disabled" />&nbsp;&nbsp;
		<label for="confirm">{L_CONFIRM}&nbsp;<input onclick="toggle_disabled('send', this.checked)" id="confirm" type="checkbox" name="confirm" value="1" /></label>
	</td>
</tr>
</table>

</form>

<br clear="all" />
