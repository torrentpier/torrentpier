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
		<td class="row1" width="80%">{L_IMAGE_CREATE_THUMBNAIL}<br /></td>
		<td class="row2"><input type="radio" name="img_create_thumbnail" value="1" {CREATE_THUMBNAIL_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="img_create_thumbnail" value="0" {CREATE_THUMBNAIL_NO} /> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_IMAGE_MIN_THUMB_FILESIZE}<br /><span class="small">{L_IMAGE_MIN_THUMB_FILESIZE_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="7" maxlength="15" name="img_min_thumb_filesize" value="{IMAGE_MIN_THUMB_FILESIZE}" class="post" /> {L_BYTES}</td>
	</tr>
<!-- END switch_thumbnail_support -->
	<tr>
		<td class="row1" width="80%">{L_MAX_IMAGE_SIZE} <br /><span class="small">{L_MAX_IMAGE_SIZE_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_max_width" value="{IMAGE_MAX_WIDTH}" class="post" /> x <input type="text" size="3" maxlength="4" name="img_max_height" value="{IMAGE_MAX_HEIGHT}" class="post" /></td>
	</tr>
	<tr>
		<td class="row1" width="80%">{L_IMAGE_LINK_SIZE} <br /><span class="small">{L_IMAGE_LINK_SIZE_EXPLAIN}</span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="img_link_width" value="{IMAGE_LINK_WIDTH}" class="post" /> x <input type="text" size="3" maxlength="4" name="img_link_height" value="{IMAGE_LINK_HEIGHT}" class="post" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" class="liteoption" />&nbsp;&nbsp;<input type="submit" name="cat_settings" value="{L_TEST_SETTINGS}" class="liteoption" /></td>
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
            <td class="row1" width="80%">{L_UPLOAD_DIRECTORY}<br/><span class="small">{L_UPLOAD_DIRECTORY_EXPLAIN}</span></td>
            <td class="row2"><input type="text" size="25" maxlength="100" name="upload_dir" class="post" value="{UPLOAD_DIR}"/></td>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_ATTACH_IMG_PATH}<br/><span class="small">{L_ATTACH_IMG_PATH_EXPLAIN}</span></td>
            <td class="row2"><input type="text" size="25" maxlength="100" name="upload_img" class="post" value="{ATTACHMENT_IMG_PATH}"/></td>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_ATTACH_TOPIC_ICON}<br/><span class="small">{L_ATTACH_TOPIC_ICON_EXPLAIN}</span></td>
            <td class="row2"><input type="text" size="25" maxlength="100" name="topic_icon" class="post" value="{TOPIC_ICON}"/></td>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_ATTACH_DISPLAY_ORDER}<br/><span class="small">{L_ATTACH_DISPLAY_ORDER_EXPLAIN}</span></td>
            <td class="row2">
                <table class="borderless">
                    <tr>
                        <td><input type="radio" name="display_order" value="0" {DISPLAY_ORDER_DESC} /> {L_DESC}</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="display_order" value="1" {DISPLAY_ORDER_ASC} /> {L_ASC}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th colspan="2">{L_ATTACH_FILESIZE_SETTINGS}</th>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_MAX_FILESIZE_ATTACH}<br/><span class="small">{L_MAX_FILESIZE_ATTACH_EXPLAIN}</span></td>
            <td class="row2"><input type="text" size="8" maxlength="15" name="max_filesize" class="post" value="{MAX_FILESIZE}"/>{S_FILESIZE}</td>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_MAX_FILESIZE_PM}<br/><span class="small">{L_MAX_FILESIZE_PM_EXPLAIN}</span></td>
            <td class="row2"><input type="text" size="8" maxlength="15" name="max_filesize_pm" class="post" value="{MAX_FILESIZE_PM}"/>{S_FILESIZE_PM}</td>
        </tr>
        <tr>
            <th colspan="2">{L_ATTACH_NUMBER_SETTINGS}</th>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_MAX_ATTACHMENTS}<br/><span class="small">{L_MAX_ATTACHMENTS_EXPLAIN}</span></td>
            <td class="row2"><input type="text" size="3" maxlength="3" name="max_attachments" class="post" value="{MAX_ATTACHMENTS}"/></td>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_MAX_ATTACHMENTS_PM}<br/><span class="small">{L_MAX_ATTACHMENTS_PM_EXPLAIN}</span></td>
            <td class="row2"><input type="text" size="3" maxlength="3" name="max_attachments_pm" class="post" value="{MAX_ATTACHMENTS_PM}"/></td>
        </tr>
        <tr>
            <th colspan="2">{L_ATTACH_OPTIONS_SETTINGS}</th>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_DISABLE_MOD}<br/><span class="small">{L_DISABLE_MOD_EXPLAIN}</span></td>
            <td class="row2"><input type="radio" name="disable_mod" value="1" {DISABLE_MOD_YES} />&nbsp;{L_YES}&nbsp;&nbsp;<input type="radio" name="disable_mod" value="0" {DISABLE_MOD_NO} />&nbsp;{L_NO}</td>
        </tr>
        <tr>
            <td class="row1" width="80%">{L_PM_ATTACHMENTS}<br/><span class="small">{L_PM_ATTACHMENTS_EXPLAIN}</span></td>
            <td class="row2"><input type="radio" name="allow_pm_attach" value="1" {PM_ATTACH_YES} />&nbsp;{L_YES}&nbsp;&nbsp;<input type="radio" name="allow_pm_attach" value="0" {PM_ATTACH_NO} />&nbsp;{L_NO}</td>
        </tr>
        <tr>
            <td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption"/>&nbsp;&nbsp;<input type="reset" value="{L_RESET}" class="liteoption"/>&nbsp;&nbsp;<input type="submit" name="settings" value="{L_TEST_SETTINGS}" class="liteoption"/></td>
        </tr>
    </table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_MANAGE -->

<br clear="all" />
