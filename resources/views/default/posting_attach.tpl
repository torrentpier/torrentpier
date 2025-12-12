<!-- IF TPL_ADD_ATTACHMENT -->
<!--========================================================================-->

<style>#clear_file_upload { display: none; }</style>

<tr>
	<th colspan="2" class="thHead"><!-- IF FILE_ATTACHED -->{L_UPDATE_ATTACHMENT}<!-- ELSE -->{L_ADD_ATTACHMENT}<!-- ENDIF --></th>
</tr>
<tr>
	<td class="pad_4"><b>{L_FILENAME}</b></td>
	<td>
		<table class="borderless" cellspacing="0">
		<tr>
			<td class="pad_4">
				<input type="button" id="clear_file_upload" value="{L_CLEAR}" />
				<input type="file" name="fileupload" size="45" maxlength="{FILESIZE}" />
				<input type="submit" class="bold" name="post" value="<!-- IF FILE_ATTACHED -->{L_UPDATE_ATTACHMENT}<!-- ELSE -->{L_ADD_ATTACHMENT}<!-- ENDIF -->" style="margin-left: 8px;" />
				<p class="small nowrap">{L_ADD_ATTACHMENT_EXPLAIN}</p>
			</td>
			<td class="med pad_4" style="padding-left: 12px;">{RULES}</td>
		</tr>
		</table>
	</td>
</tr>

<script type="text/javascript">
    $(document).ready(function () {
        $('input[name="fileupload"]').on('change', function () {
            if (this.files && this.files.length > 0) {
                $('input[type=button]#clear_file_upload').show();
            } else {
                $('input[type=button]#clear_file_upload').hide();
            }
        });

        $('input[type=button]#clear_file_upload').on('click', function () {
            $('input[name="fileupload"]').val('');
            $('input[type=button]#clear_file_upload').hide();
        });
    });
</script>

<!--========================================================================-->
<!-- ENDIF / TPL_ADD_ATTACHMENT -->

<!-- IF FILE_ATTACHED -->
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
	<td class="row1">{L_OPTIONS}</td>
	<td class="row1">
		<input type="submit" name="del_attachment[{attach_row.ATTACH_FILENAME}]" value="{L_DELETE_ATTACHMENT}" />&nbsp;
	</td>
</tr>
<tr>
	<td colspan="2" class="spaceRow"><div class="spacer_4"></div></td>
</tr>
<!-- END attach_row -->
</tbody>

<!--========================================================================-->
<!-- ENDIF / FILE_ATTACHED -->
