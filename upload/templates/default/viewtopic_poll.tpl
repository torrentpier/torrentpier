<div id="poll" class="row5" style="padding: 0 10%;">

<!-- IF TPL_POLL_BALLOT -->
<!--========================================================================-->

	<form method="POST" action="{S_POLL_ACTION}">
	{S_HIDDEN_FIELDS}

		<p class="mrg_12 tCenter"><b>{POLL_QUESTION}</b></p>

		<table cellpadding="0" class="borderless bCenter">
		<!-- BEGIN poll_option -->
		<tr>
			<td><input type="radio" name="vote_id" id="vote_{poll_option.POLL_OPTION_ID}" value="{poll_option.POLL_OPTION_ID}" /></td>
			<td><label for="vote_{poll_option.POLL_OPTION_ID}" class="wrap">{poll_option.POLL_OPTION_CAPTION}</label></td>
		</tr>
		<!-- END poll_option -->
		</table>

		<p class="mrg_6 tCenter"><input type="submit" name="submit" value="{L_SUBMIT_VOTE}" class="liteoption" /></p>

		<p class="small mrg_8 tCenter"><b><a href="{U_VIEW_RESULTS}" class="small">{L_VIEW_RESULTS}</a></b></p>

	</form>

<!--========================================================================-->
<!-- ENDIF / TPL_POLL_BALLOT -->

<!-- IF TPL_POLL_RESULT -->
<!--========================================================================-->

		<p class="mrg_12 tCenter"><b>{POLL_QUESTION}</b></p>

		<table class="borderless bCenter">
		<!-- BEGIN poll_option -->
		<tr>
			<td class="tLeft">{poll_option.POLL_OPTION_CAPTION}</td>
			<td>&nbsp;</td>
			<td class="nowrap"><img src="{IMG}vote_lcap.gif" width="4" alt="" height="12" /><img src="{poll_option.POLL_OPTION_IMG}" width="{poll_option.POLL_OPTION_IMG_WIDTH}" height="12" alt="{poll_option.POLL_OPTION_PERCENT}" /><img src="{IMG}vote_rcap.gif" width="4" alt="" height="12" /></td>
			<td class="nowrap tRight"><b>&nbsp;{poll_option.POLL_OPTION_PERCENT}&nbsp;</b></td>
			<td class="nowrap tCenter">[ {poll_option.POLL_OPTION_RESULT} ]</td>
		</tr>
		<!-- END poll_option -->
		</table>

		<p class="mrg_8 tCenter"><b>{L_TOTAL_VOTES} : {TOTAL_VOTES}</b></p>

<!--========================================================================-->
<!-- ENDIF / TPL_POLL_RESULT -->

</div>