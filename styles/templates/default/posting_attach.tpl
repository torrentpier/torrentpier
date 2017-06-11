<!-- IF TPL_ADD_ATTACHMENT -->
<!--========================================================================-->

<tr>
	<th colspan="2" class="thHead">{L_ADD_ATTACHMENT_TITLE}</th>
</tr>
<tr>
	<td class="pad_4"><b>{L_FILENAME}</b></td>
	<td>
		<table class="borderless" cellspacing="0">
		<tr>
			<td class="pad_4">
				<input type="file" name="fileupload" size="45" maxlength="{FILESIZE}" />
				<p class="small nowrap">{L_ADD_ATTACHMENT_EXPLAIN}</p>
			</td>
			<td class="med pad_4" style="padding-left: 12px;">{RULES}</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="pad_4">{L_FILE_COMMENT}</td>
	<td class="pad_4">
		<input type="text" name="filecomment" size="45" maxlength="255" value="{FILE_COMMENT}" />
		<input type="submit" class="bold" name="add_attachment" value="{L_ADD_ATTACHMENT}" />
	</td>
</tr>

<!--========================================================================-->
<!-- ENDIF / TPL_ADD_ATTACHMENT -->

<!-- IF TPL_POSTED_ATTACHMENTS -->
<!--========================================================================-->

<tbody class="pad_4">
<tr>
	<th colspan="2" class="thHead">{L_POSTED_ATTACHMENTS}</th>
</tr>
<!-- BEGIN attach_row -->
<tr>
	<td class="row5"><b>{L_FILENAME}</b></td>
	<td class="row5"><a class="gen" href="{attach_row.U_VIEW_ATTACHMENT}" target="_blank"><b>{attach_row.FILE_NAME}</b></a></td>
</tr>
<tr>
	<td class="row1">{L_FILE_COMMENT}</td>
	<td class="row1">
		<input type="text" name="comment_list[]" size="45" maxlength="255" value="{attach_row.FILE_COMMENT}" />&nbsp;
		<input type="submit" name="edit_comment[{attach_row.ATTACH_FILENAME}]" value="{L_UPDATE_COMMENT}" />
	</td>
</tr>
<tr>
	<td class="row1">{L_OPTIONS}</td>
	<td class="row1">
		<!-- BEGIN switch_update_attachment -->
		<input type="submit" name="update_attachment[{attach_row.ATTACH_ID}]" value="{L_UPLOAD_NEW_VERSION}" />&nbsp;
		<!-- END switch_update_attachment -->
		<input type="submit" name="del_attachment[{attach_row.ATTACH_FILENAME}]" value="{L_DELETE_ATTACHMENT}" />&nbsp;
		<!-- BEGIN switch_thumbnail -->
		<input type="submit" name="del_thumbnail[{attach_row.ATTACH_FILENAME}]" value="{L_DELETE_THUMBNAIL}" />&nbsp;
		<!-- END switch_thumbnail -->
	</td>
</tr>
<tr>
	<td colspan="2" class="spaceRow"><div class="spacer_4"></div></td>
</tr>
<!-- END attach_row -->
</tbody>

<!--========================================================================-->
<!-- ENDIF / TPL_POSTED_ATTACHMENTS -->
