<script type="text/javascript">
function emoticon(text) {
	text = ' ' + text + ' ';
	if (opener.document.forms['post'].message.createTextRange && opener.document.forms['post'].message.caretPos) {
		var caretPos = opener.document.forms['post'].message.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) === ' ' ? text + ' ' : text;
		opener.document.forms['post'].message.focus();
	} else {
	opener.document.forms['post'].message.value  += text;
	opener.document.forms['post'].message.focus();
	}
}
</script>

<table width="100%" cellpadding="10">
	<tr>
		<td>
			<table class="forumline">
				<tr>
					<th>{L_EMOTICONS}</th>
				</tr>
				<tr>
					<td class="row1">
						<table class="borderless w100" cellpadding="5">
							<!-- BEGIN smilies_row -->
							<tr align="center">
								<!-- BEGIN smilies_col -->
								<td><a href="javascript:emoticon('{smilies_row.smilies_col.SMILEY_CODE}')"><img src="{smilies_row.smilies_col.SMILEY_IMG}" border="0" alt="{smilies_row.smilies_col.SMILEY_DESC}" title="{smilies_row.smilies_col.SMILEY_DESC}" /></a></td>
								<!-- END smilies_col -->
							</tr>
							<!-- END smilies_row -->
							<!-- BEGIN switch_smilies_extra -->
							<tr align="center">
								<td colspan="{S_SMILIES_COLSPAN}"><span  class="nav"><a href="{U_MORE_SMILIES}" onclick="open_window('{U_MORE_SMILIES}', 250, 300);return false" target="_smilies" class="nav">{L_MORE_EMOTICONS}</a></td>
							</tr>
							<!-- END switch_smilies_extra -->
						</table>
					</td>
				</tr>
				<tr>
					<td class="row2" align="center"><br /><span class="med"><a href="javascript:window.close();" class="med">{L_CLOSE_WINDOW}</a></span></td>
				</tr>
			</table>
		</td>
	</tr>
</table>