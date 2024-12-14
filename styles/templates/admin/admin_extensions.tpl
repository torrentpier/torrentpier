<!-- IF TPL_ATTACH_EXTENSIONS -->
<!--========================================================================-->

<h1>{L_MANAGE_EXTENSIONS}</h1>

<p>{L_MANAGE_EXTENSIONS_EXPLAIN}</p>
<br />

<form method="post" action="{S_ATTACH_ACTION}">
    <table class="forumline">
        <tr>
            <td class="catTitle" colspan="4">{L_MANAGE_EXTENSIONS}
            </td>
        </tr>
        <tr>
            <th>{L_EXPLANATION}</th>
            <th>{L_EXTENSION}</th>
            <th>{L_EXTENSION_GROUP}</th>
            <th>{L_ADD_NEW}</th>
        </tr>
        <tr>
            <td class="row1 tCenter"><input type="text" size="30" maxlength="100" name="add_extension_explain" class="post" value="{ADD_EXTENSION_EXPLAIN}"></td>
            <td class="row2 tCenter"><input type="text" size="20" maxlength="100" name="add_extension" class="post" value="{ADD_EXTENSION}"></td>
            <td class="row1 tCenter">{S_ADD_GROUP_SELECT}</td>
            <td class="row2 tCenter"><input type="checkbox" name="add_extension_check"></td>
        </tr>
        <tr class="tRight">
            <td class="catBottom" colspan="4"> {S_HIDDEN_FIELDS} <input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}"></td>
        </tr>
        <tr>
            <th>{L_EXPLANATION}</th>
            <th>{L_EXTENSION}</th>
            <th>{L_EXTENSION_GROUP}</th>
            <th>{L_DELETE}</th>
        </tr>
        <!-- BEGIN extension_row -->
        <tr>
            <input type="hidden" name="extension_change_list[]" value="{extension_row.EXT_ID}">
            <td class="row1 tCenter"><input type="text" size="30" maxlength="100" name="extension_explain_list[]" class="post" value="{extension_row.EXTENSION_EXPLAIN}"></td>
            <td class="row2 tCenter"><b><span class="gen">{extension_row.EXTENSION}</span></b></td>
            <td class="row1 tCenter">{extension_row.S_GROUP_SELECT}</td>
            <td class="row2 tCenter"><input type="checkbox" name="extension_id_list[]" value="{extension_row.EXT_ID}"></td>
        </tr>
        <!-- END extension_row -->
        <tr class="tRight">
            <td class="catBottom" colspan="4">
                <input type="submit" name="{L_CANCEL}" class="liteoption" value="{L_CANCEL}" onClick="self.location.href='{S_CANCEL_ACTION}'">
                <input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}">
            </td>
        </tr>
    </table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_EXTENSIONS -->

<!-- IF TPL_ATTACH_EXTENSION_GROUPS -->
<!--========================================================================-->

{GROUP_PERMISSIONS_BOX}

<h1>{L_MANAGE_EXTENSION_GROUPS}</h1>

<p>{L_MANAGE_EXTENSION_GROUPS_EXPLAIN}</p>
<br />

<form method="post" action="{S_ATTACH_ACTION}">
    <table class="forumline">
        <tr>
            <td class="catTitle" colspan="8">{L_MANAGE_EXTENSION_GROUPS}
            </td>
        </tr>
        <tr>
            <th>{L_EXTENSION_GROUP}</th>
            <th>{L_SPECIAL_CATEGORY}</th>
            <th>{L_ALLOWED}</th>
            <th>{L_DOWNLOAD_MODE}</th>
            <th>{L_UPLOAD_ICON}</th>
            <th>{L_MAX_FILESIZE_ATTACH}</th>
            <th>{L_ALLOWED_FORUMS}</th>
            <th>{L_ADD_NEW}</th>
        </tr>
        <tr>
            <td class="row1 tCenter">
                <table width="100%" class="borderless">
                    <tr>
                        <td class="row1 nowrap tCenter" width="10%">&nbsp;</td>
                        <td class="row1"><input type="text" size="20" maxlength="100" name="add_extension_group" class="post" value="{ADD_GROUP_NAME}"></td>
                    </tr>
                </table>
            </td>
            <td class="row2 tCenter">{S_SELECT_CAT}</td>
            <td class="row1 tCenter"><input type="checkbox" name="add_allowed"></td>
            <td class="row2 tCenter">{S_ADD_DOWNLOAD_MODE}</td>
            <td class="row1 tCenter"><input type="text" size="15" maxlength="100" name="add_upload_icon" class="post" value="{UPLOAD_ICON}"></td>
            <td class="row2 tCenter"><input type="text" size="3" maxlength="15" name="add_max_filesize" class="post" value="{MAX_FILESIZE}">{S_FILESIZE}</td>
            <td class="row1 tCenter">&nbsp;</td>
            <td class="row2 tCenter"><input type="checkbox" name="add_extension_group_check"></td>
        </tr>
        <tr class="tRight">
            <td class="catBottom" colspan="8"><input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}"></td>
        </tr>
        <tr>
            <th>{L_EXTENSION_GROUP}</th>
            <th>{L_SPECIAL_CATEGORY}</th>
            <th>{L_ALLOWED}</th>
            <th>{L_DOWNLOAD_MODE}</th>
            <th>{L_UPLOAD_ICON}</th>
            <th>{L_MAX_GROUPS_FILESIZE}</th>
            <th>{L_ALLOWED_FORUMS}</th>
            <th>{L_DELETE}</th>
        </tr>
        <!-- BEGIN grouprow -->
        <tr>
        <input type="hidden" name="group_change_list[]" value="{grouprow.GROUP_ID}">
        <td class="row1 tCenter">
            <table width="100%" class="borderless">
                <tr>
                    <td class="row1 tCenter nowrap" width="10%"><b><span class="small"><a href="{grouprow.U_VIEWGROUP}" class="small">{grouprow.CAT_BOX}</a></span></b></td>
                    <td class="row1"><input type="text" size="20" maxlength="100" name="extension_group_list[]" class="post" value="{grouprow.EXTENSION_GROUP}"/></td>
                </tr>
            </table>
        </td>
        <td class="row2 tCenter">{grouprow.S_SELECT_CAT}</td>
        <td class="row1 tCenter"><input type="checkbox" name="allowed_list[]" value="{grouprow.GROUP_ID}" {grouprow.S_ALLOW_SELECTED}></td>
        <td class="row2 tCenter">{grouprow.S_DOWNLOAD_MODE}</td>
        <td class="row1 tCenter"><input type="text" size="15" maxlength="100" name="upload_icon_list[]" class="post" value="{grouprow.UPLOAD_ICON}"></td>
        <td class="row2 tCenter"><input type="text" size="3" maxlength="15" name="max_filesize_list[]" class="post" value="{grouprow.MAX_FILESIZE}">{grouprow.S_FILESIZE}</td>
        <td class="row1 tCenter"><span class="small"><a href="{grouprow.U_FORUM_PERMISSIONS}" class="small">{L_EXT_GROUP_PERMISSIONS}</a></span></td>
        <td class="row2 tCenter"><input type="checkbox" name="group_id_list[]" value="{grouprow.GROUP_ID}"/></td>
        </tr>
        <!-- BEGIN extensionrow -->
        <tr>
            <td class="row2 tCenter"><span class="small">{grouprow.extensionrow.EXTENSION}</span></td>
            <td class="row2 tCenter"><span class="small">{grouprow.extensionrow.EXPLANATION}</span></td>
            <td class="row2 tCenter">&nbsp;</td>
            <td class="row2 tCenter">&nbsp;</td>
            <td class="row2 tCenter">&nbsp;</td>
            <td class="row2 tCenter">&nbsp;</td>
            <td class="row2 tCenter">&nbsp;</td>
            <td class="row2 tCenter">&nbsp;</td>
        </tr>
        <!-- END extensionrow -->
        <!-- END grouprow -->
        <tr class="tRight">
            <td class="catBottom" colspan="8">
                <input type="submit" name="{L_CANCEL}" class="liteoption" value="{L_CANCEL}" onClick="self.location.href='{S_CANCEL_ACTION}'">
                <input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}">
            </td>
        </tr>
    </table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_EXTENSION_GROUPS -->

<!-- IF TPL_ATTACH_EXTENSION_GROUPS_PERMISSIONS -->
<!--========================================================================-->

<h1>{L_GROUP_PERMISSIONS_TITLE}</h1>

<p>{L_GROUP_PERMISSIONS_EXPLAIN}</p>
<br />

<table width="100%">
	<tr>
		<td class="tCenter">
			<form method="post" action="{A_PERM_ACTION}">
			<table width="90%" class="forumline">
				<tr>
					<th>{L_ALLOWED_FORUMS}</th>
				</tr>
				<tr>
					<td class="row1 tCenter">
						<select style="width:560px" name="entries[]" multiple size="5">
						<!-- BEGIN allow_option_values -->
						<option value="{allow_option_values.VALUE}">{allow_option_values.OPTION}</option>
						<!-- END allow_option_values -->
						</select>
					</td>
				</tr>
                <tr>
                    <td class="cat tCenter"><input class="liteoption" type="submit" name="del_forum" value="{L_REMOVE_SELECTED}">&nbsp;<input class="liteoption" type="submit" name="close_perm" value="{L_CLOSE_WINDOW}"><input type="hidden" name="e_mode" value="perm"></td>
                </tr>
			</table>
			</form>
		</td>
	</tr>
	<tr>
		<td>
			<form method="post" action="{A_PERM_ACTION}">
			<table width="90%" class="forumline">
				<tr>
					<th>{L_ADD_FORUMS}</th>
				</tr>
				<tr>
                    <td class="row1 tCenter">
                        <select style="width:560px" name="entries[]" multiple size="5">
                            <!-- BEGIN forum_option_values -->
                            <option value="{forum_option_values.VALUE}">{forum_option_values.OPTION}</option>
                            <!-- END forum_option_values -->
                        </select>
                    </td>
				</tr>
                <tr>
                    <td class="cat tCenter"><input type="submit" name="add_forum" value="{L_ADD_SELECTED}" class="mainoption">&nbsp;<input type="reset" value="{L_RESET}" class="liteoption"/>&nbsp;<input type="hidden" name="e_mode" value="perm"></td>
                </tr>
			</table>
			</form>
		</td>
	</tr>
</table>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_EXTENSION_GROUPS_PERMISSIONS -->

<br clear="all" />
