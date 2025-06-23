<!-- IF TPL_BB_DIE -->
<!--========================================================================-->

<!-- IF IN_ADMIN --><br /><br /><!-- ELSE --><div class="spacer_10"></div><!-- ENDIF -->

<table class="forumline message">
	<tr><th>{L_INFORMATION}</th></tr>
	<tr><td>
		<div class="mrg_16">{MESSAGE_TEXT}</div>
		<div class="mrg_12">{BB_DIE_APPEND_MSG}</div>
	</td></tr>
</table>

<!-- IF IN_ADMIN --><br /><br /><!-- ELSE --><div class="spacer_10"></div><!-- ENDIF -->

<!--========================================================================-->
<!-- ENDIF / TPL_BB_DIE -->

<!-- IF TPL_CONFIRM -->
<!--========================================================================-->

<form action="{FORM_ACTION}" method="{FORM_METHOD}">
	{HIDDEN_FIELDS}

	<div class="spacer_10"></div>

	<table class="forumline message">
		<tr>
			<th>{CONFIRM_TITLE}</th>
		</tr>
		<tr>
			<td>
				<h4 style="margin-bottom: 1em;">{QUESTION}</h4>
				<!-- IF ITEMS_LIST -->
				<table class="borderless bCenter">
					<tr><td class="med tLeft">
						<ul style="margin-top: -1em;"><li>{ITEMS_LIST}</li></ul>
					</td></tr>
				</table>
				<!-- ENDIF -->
				<div style="padding-top: 0.3em; margin-bottom: -8px;">
					<input type="submit" name="confirm" value="{L_YES}" class="mainoption" />&nbsp;
					<input type="submit" name="cancel" value="{L_NO}" class="liteoption" />
				</div>
			</td>
		</tr>
	</table>

</form>
<div class="spacer_10"></div>

<!--========================================================================-->
<!-- ENDIF / TPL_CONFIRM -->
