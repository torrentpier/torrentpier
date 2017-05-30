<!-- IF TPL_ATTACH_SPECIAL_CATEGORIES -->
<!--========================================================================-->

<h1>{L_MANAGE_CATEGORIES}</h1>

<p>{L_MANAGE_CATEGORIES_EXPLAIN}</p>
<br />

<form action="{S_ATTACH_ACTION}" method="post">
<table class="forumline">
	<tr>
	  <th colspan="2">{L_SETTINGS_CAT_IMAGES}<br />{L_ASSIGNED_GROUP}: {S_ASSIGNED_GROUP_IMAGES}</th>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_DISPLAY_INLINED}<br /><span class="small">{L_DISPLAY_INLINED_EXPLAIN}</span></td>
		<td class="row2"><input type="radio" name="img_display_inlined" value="1" {DISPLAY_INLINED_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="img_display_inlined" value="0" {DISPLAY_INLINED_NO} /> {L_NO}</td>
	</tr>
<!-- BEGIN switch_thumbnail_support -->
	<tr>
		<td class="row1" width="80%">{L_IMAGE_CREATE_THUMBNAIL}<br /><span class="small">{L_IMAGE_CREATE_THUMBNAIL_EXPLAIN}</span></td>
		<td class="row2"><input type="radio" name="img_create_thumbnail" value="1" {CREATE_THUMBNAIL_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="img_create_thumbnail" value="0" {CREATE_THUMBNAIL_NO} /> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_IMAGE_MIN_THUMB_FILESIZE}<br /><span class="small">{L_IMAGE_MIN_THUMB_FILESIZE_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="7" maxlength="15" name="img_min_thumb_filesize" value="{IMAGE_MIN_THUMB_FILESIZE}" class="post" /> {L_BYTES}</td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_USE_GD2}<br /><span class="small">{L_USE_GD2_EXPLAIN}</span></td>
		<td class="row2"><input type="radio" name="use_gd2" value="1" {USE_GD2_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="use_gd2" value="0" {USE_GD2_NO} /> {L_NO}</td>
	</tr>
<!-- END switch_thumbnail_support -->
	<tr>
		<td class="row1" width="80%">{L_IMAGE_IMAGICK_PATH}<br /><span class="small">{L_IMAGE_IMAGICK_PATH_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="20" maxlength="200" name="img_imagick" value="{IMAGE_IMAGICK_PATH}" class="post" /></td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_MAX_IMAGE_SIZE} <br /><span class="small">{L_MAX_IMAGE_SIZE_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_max_width" value="{IMAGE_MAX_WIDTH}" class="post" /> x <input type="text" size="3" maxlength="4" name="img_max_height" value="{IMAGE_MAX_HEIGHT}" class="post" /></td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_IMAGE_LINK_SIZE} <br /><span class="small">{L_IMAGE_LINK_SIZE_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_link_width" value="{IMAGE_LINK_WIDTH}" class="post" /> x <input type="text" size="3" maxlength="4" name="img_link_height" value="{IMAGE_LINK_HEIGHT}" class="post" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" class="liteoption" />&nbsp;&nbsp;<input type="submit" name="search_imagick" value="{L_IMAGE_SEARCH_IMAGICK}" class="liteoption" />&nbsp;&nbsp;<input type="submit" name="cat_settings" value="{L_TEST_SETTINGS}" class="liteoption" /></td>
	</tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_SPECIAL_CATEGORIES -->

<!-- IF TPL_ATTACH_MANAGE -->
<!--========================================================================-->

<h1>{L_ATTACH_SETTINGS}</h1>

<p>{L_MANAGE_ATTACHMENTS_EXPLAIN}</p>
<br />

<form action="{S_ATTACH_ACTION}" method="post">
<table class="forumline">
	<tr>
	  <th colspan="2">{L_ATTACH_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_UPLOAD_DIRECTORY}<br /><span class="small">{L_UPLOAD_DIRECTORY_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="upload_dir" class="post" value="{UPLOAD_DIR}" /></td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_ATTACH_IMG_PATH}<br /><span class="small">{L_ATTACH_IMG_PATH_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="upload_img" class="post" value="{ATTACHMENT_IMG_PATH}" /></td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_ATTACH_TOPIC_ICON}<br /><span class="small">{L_ATTACH_TOPIC_ICON_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="topic_icon" class="post" value="{TOPIC_ICON}" /></td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_ATTACH_DISPLAY_ORDER}<br /><span class="small">{L_ATTACH_DISPLAY_ORDER_EXPLAIN}</span></td>
		<td class="row2">
		<table class="borderless">
			<tr>
				<td><input type="radio" name="display_order" value="0" {DISPLAY_ORDER_DESC} /> {L_DESC}</td>
      </tr>
      <tr>
        <td><input type="radio" name="display_order" value="1" {DISPLAY_ORDER_ASC} /> {L_ASC}</td>
       </tr>
		</table></td>
	</tr>
	<tr>
	  <th colspan="2">{L_ATTACH_FILESIZE_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_MAX_FILESIZE_ATTACH}<br /><span class="small">{L_MAX_FILESIZE_ATTACH_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="max_filesize" class="post" value="{MAX_FILESIZE}" /> {S_FILESIZE}</td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_ATTACH_QUOTA}<br /><span class="small">{L_ATTACH_QUOTA_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="attachment_quota" class="post" value="{ATTACHMENT_QUOTA}" /> {S_FILESIZE_QUOTA}</td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_MAX_FILESIZE_PM}<br /><span class="small">{L_MAX_FILESIZE_PM_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="8" maxlength="15" name="max_filesize_pm" class="post" value="{MAX_FILESIZE_PM}" /> {S_FILESIZE_PM}</td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_DEFAULT_QUOTA_LIMIT}<br /><span class="small">{L_DEFAULT_QUOTA_LIMIT_EXPLAIN}</span></td>
		<td class="row2">
		<table class="borderless">
		<tr>
			<td nowrap="nowrap">{S_DEFAULT_UPLOAD_LIMIT}</td>
			<td nowrap="nowrap"><span class="small">&nbsp;{L_UPLOAD_QUOTA}&nbsp;</span></td>
		</tr>
		<tr>
			<td nowrap="nowrap">{S_DEFAULT_PM_LIMIT}</td>
			<td nowrap="nowrap"><span class="small">&nbsp;{L_PM_QUOTA}&nbsp;</span></td>
		</tr>
		</table>
		</td>
	</tr>
	<tr>
	  <th colspan="2">{L_ATTACH_NUMBER_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_MAX_ATTACHMENTS}<br /><span class="small">{L_MAX_ATTACHMENTS_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="3" maxlength="3" name="max_attachments" class="post" value="{MAX_ATTACHMENTS}" /></td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_MAX_ATTACHMENTS_PM}<br /><span class="small">{L_MAX_ATTACHMENTS_PM_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="3" maxlength="3" name="max_attachments_pm" class="post" value="{MAX_ATTACHMENTS_PM}" /></td>
	</tr>
	<tr>
	  <th colspan="2">{L_ATTACH_OPTIONS_SETTINGS}</th>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_DISABLE_MOD}<br /><span class="small">{L_DISABLE_MOD_EXPLAIN}</span></td>
		<td class="row2"><input type="radio" name="disable_mod" value="1" {DISABLE_MOD_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="disable_mod" value="0" {DISABLE_MOD_NO} /> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_PM_ATTACHMENTS}<br /><span class="small">{L_PM_ATTACHMENTS_EXPLAIN}</span></td>
		<td class="row2"><input type="radio" name="allow_pm_attach" value="1" {PM_ATTACH_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_pm_attach" value="0" {PM_ATTACH_NO} /> {L_NO}</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" class="liteoption" />&nbsp;&nbsp;<input type="submit" name="settings" value="{L_TEST_SETTINGS}" class="liteoption" /></td>
	</tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_MANAGE -->

<!-- IF TPL_ATTACH_QUOTA -->
<!--========================================================================-->

<h1>{L_MANAGE_QUOTAS}</h1>

<p>{L_MANAGE_QUOTAS_EXPLAIN}</p>
<br />

<form method="post" action="{S_ATTACH_ACTION}">
<table class="forumline">
	<tr>
	  <td class="catTitle" colspan="3">{L_MANAGE_QUOTAS}
	  </td>
	</tr>
	<tr>
		<th>{L_DESCRIPTION}</th>
		<th>{L_SIZE}</th>
		<th>{L_ADD_NEW}</th>
	</tr>
	<tr>
		<td class="row1" align="center"><input type="text" size="20" maxlength="25" name="quota_description" class="post"/></td>
		<td class="row2" align="center"><input type="text" size="8" maxlength="15" name="add_max_filesize" class="post" value="{MAX_FILESIZE}" /> {S_FILESIZE}</td>
		<td class="row1" align="center"><input type="checkbox" name="add_quota_check" /></td>
	</tr>
	<tr align="right">
	  <td class="catBottom" colspan="3"> {S_HIDDEN_FIELDS} <input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}" /></td>
	</tr>
	<tr>
		<th>{L_DESCRIPTION}</th>
		<th>{L_SIZE}</th>
		<th>{L_DELETE}</th>
	</tr>
<!-- BEGIN limit_row -->
	<tr>
	  <td class="row1" align="center">
		<input type="hidden" name="quota_change_list[]" value="{limit_row.QUOTA_ID}" />
		<table width="100%" class="borderless">
		<tr>
		<td class="row1" align="center" width="10%" wrap="nowrap"><b><span class="small"><a href="{limit_row.U_VIEW}" class="small">{L_VIEW}</a></span></b></td>
		<td class="row1"><input type="text" size="20" maxlength="25" name="quota_desc_list[]" class="post" value="{limit_row.QUOTA_NAME}" /></td>
	  </tr>
	  </table>
	  </td>
	  <td class="row2" align="center"><input type="text" size="8" maxlength="15" name="max_filesize_list[]" class="post" value="{limit_row.MAX_FILESIZE}" /> {limit_row.S_FILESIZE}</td>
	  <td class="row1" align="center"><input type="checkbox" name="quota_id_list[]" value="{limit_row.QUOTA_ID}" /></td>
	</tr>
<!-- END limit_row -->
	<tr align="right">
	  <td class="catBottom" colspan="3"> <input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}" /></td>
	</tr>
</table>
</form>

<!-- {QUOTA_LIMIT_SETTINGS} -->

<!-- BEGIN switch_quota_limit_desc -->
<div align="center"><h1>{L_QUOTA_LIMIT_DESC}</h1></div>
<table width="99%" align="center">
	<tr>
		<td width="49%">
		<table class="forumline">
		<tr>
					<th>{L_ASSIGNED_USERS} - {L_UPLOAD_QUOTA}</th>
				</tr>
				<tr>
					<td class="row1" align="center">
						<select style="width:99%" name="entries[]" multiple size="5">
<!-- END switch_quota_limit_desc -->
						<!-- BEGIN users_upload_row -->
						<option value="{users_upload_row.USER_ID}">{users_upload_row.USERNAME}</option>
						<!-- END users_upload_row -->
<!-- BEGIN switch_quota_limit_desc -->
						</select>
					</td>
				</tr>
			</table>
		</td>
		<td width="2%">
			&nbsp;&nbsp;&nbsp;
		</td>
		<td align="right" width="49%">
		<table class="forumline">
		<tr>
					<th>{L_ASSIGNED_GROUPS} - {L_UPLOAD_QUOTA}</th>
				</tr>
				<tr>
					<td class="row1" align="center">
					<select style="width:99%" name="entries[]" multiple size="5">
<!-- END switch_quota_limit_desc -->
					<!-- BEGIN groups_upload_row -->
					<option value="{groups_upload_row.GROUP_ID}">{groups_upload_row.GROUPNAME}</option>
					<!-- END groups_upload_row -->
<!-- BEGIN switch_quota_limit_desc -->
					</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			&nbsp;&nbsp;&nbsp;
		</td>
	</tr>
	<tr>
		<td width="49%">
		<table class="forumline">
				<tr>
					<th>{L_ASSIGNED_USERS} - {L_PM_QUOTA}</th>
				</tr>
				<tr>
					<td class="row1" align="center">
						<select style="width:99%" name="entries[]" multiple size="5">
<!-- END switch_quota_limit_desc -->
						<!-- BEGIN users_pm_row -->
						<option value="{users_pm_row.USER_ID}">{users_pm_row.USERNAME}</option>
						<!-- END users_pm_row -->
<!-- BEGIN switch_quota_limit_desc -->
						</select>
					</td>
				</tr>
			</table>
		</td>
		<td width="2%">
			&nbsp;&nbsp;&nbsp;
		</td>
		<td align="right" width="49%">
		<table class="forumline">
				<tr>
					<th>{L_ASSIGNED_GROUPS} - {L_PM_QUOTA}</th>
				</tr>
				<tr>
					<td class="row1" align="center">
					<select style="width:99%" name="entries[]" multiple size="5">
<!-- END switch_quota_limit_desc -->
					<!-- BEGIN groups_pm_row -->
					<option value="{groups_pm_row.GROUP_ID}">{groups_pm_row.GROUPNAME}</option>
					<!-- END groups_pm_row -->
<!-- BEGIN switch_quota_limit_desc -->
					</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- END switch_quota_limit_desc -->

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_QUOTA -->

<br clear="all" />
