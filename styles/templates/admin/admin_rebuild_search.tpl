<!-- IF TPL_REBUILD_SEARCH_MAIN -->
<!--========================================================================-->

<script type="text/javascript">

// update the description on button and disable it
function update_button(button)
{
	button.value = '{L_PROCESSING}';
	button.disabled = true;

	return true;
}

// disable/enable the clear search options
function update_clear_search(myselect)
{
	// enable/disable radio buttons
	for (i = 0; i < 3; i++)
	{
		document.rebuild.clear_search[i].disabled = ( myselect.options[myselect.selectedIndex].value != 0 );
	}

	swap_values();
}

// swap the values for total and remaining
function swap_values()
{
	var temp_value;

	temp_value = document.rebuild.post_limit.value;
	document.rebuild.post_limit.value = document.rebuild.post_limit_stored.value;
	document.rebuild.post_limit_stored.value = temp_value;

	temp_value = document.rebuild.session_posts_processing.value;
	document.rebuild.session_posts_processing.value = document.rebuild.total_posts_stored.value;
	document.rebuild.total_posts_stored.value = temp_value;
}
</script>

<h1>{L_REBUILD_SEARCH}</h1>

<p>{L_REBUILD_SEARCH_DESC}</p>

<br />

<form name="rebuild" method="post" action="{S_REBUILD_SEARCH_ACTION}" onsubmit="update_button(rebuild.submit);">
{S_HIDDEN_FIELDS}

<table class="forumline wAuto med">
<col class="row1">
<col class="row2" align="center">
<tr>
	<th colspan="2">{L_REBUILD_SEARCH}</th>
</tr>
<tr>
	<td><h4>{L_STARTING_POST_ID}</h4><h6>{L_STARTING_POST_ID_EXPLAIN}</h6></td>
	<td>
		<!-- BEGIN start_text_input -->
		<input class="post" type="text" name="start_t" value="0" size="10" disabled="disabled" />
		<input type="hidden" name="start" value="0" />
		<!-- END start_text_input -->
		<!-- BEGIN start_select_input -->
		<select name="start" onchange="update_clear_search(this)">
			<option value="0">0 ({L_START_OPTION_BEGINNING})</option>
			<option value="{NEXT_START_POST_ID}" selected="selected">{NEXT_START_POST_ID} ({L_START_OPTION_CONTINUE})</option>
		</select>
		<!-- END start_select_input -->
	</td>
</tr>
<tr>
	<td><h4>{L_CLEAR_SEARCH_TABLES}</h4><h6>{L_CLEAR_SEARCH_TABLES_EXPLAIN}</h6></td>
	<td class="nowrap">
		<input type="radio" name="clear_search" value="0" {CLEAR_SEARCH_DISABLED} checked="checked" />{L_CLEAR_SEARCH_NO}&nbsp;
		<input type="radio" name="clear_search" value="1" {CLEAR_SEARCH_DISABLED} />{L_CLEAR_SEARCH_DELETE}&nbsp;
		<input type="radio" name="clear_search" value="2" {CLEAR_SEARCH_DISABLED} />{L_CLEAR_SEARCH_TRUNCATE}&nbsp;
	</td>
</tr>
<tr>
	<td><h4>{L_NUM_OF_POSTS}</h4><h6>{L_NUM_OF_POSTS_EXPLAIN}</h6></td>
	<td><input class="post" type="text" name="session_posts_processing" value="{SESSION_POSTS_PROCESSING}" size="10" /></td>
</tr>
<tr>
	<td><h4>{L_POSTS_PER_CYCLE}</h4><h6>{L_POSTS_PER_CYCLE_EXPLAIN}</h6></td>
	<td><input class="post" type="text" name="post_limit" value="{POST_LIMIT}" size="10" /></td>
</tr>
<tr>
	<td><h4>{L_TIME_LIMIT}</h4><h6>{L_TIME_LIMIT_EXPLAIN}</h6></td>
	<td><input class="post" type="text" name="time_limit" value="{TIME_LIMIT}" size="10" /></td>
</tr>
<tr>
	<td><h4>{L_REFRESH_RATE}</h4><h6>{L_REFRESH_RATE_EXPLAIN}</h6></td>
	<td><input class="post" type="text" name="refresh_rate" value="{REFRESH_RATE}" size="10" /></td>
</tr>
<!-- BEGIN last_saved_info -->
<tr>
	<td class="row3 tCenter" colspan="2">{LAST_SAVED_PROCESSING}</td>
</tr>
<!-- END last_saved_info -->
<tr>
	<td class="catBottom" colspan="2">
		<input type="hidden" name="sid" value="{SESSION_ID}" />
		<input class="mainoption" type="submit" name="submit" value="{L_REBUILD_SEARCH}" />
	</td>
</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_REBUILD_SEARCH_MAIN -->

<!-- IF TPL_REBUILD_SEARCH_PROGRESS -->
<!--========================================================================-->

<script type="text/javascript">
var refresh;

var ticker = {REFRESH_RATE};
var label_next = "{L_NEXT}";
var label = "{L_PROCESSING}";

// update the button description/status
function updateButton()
{
	if ( ticker >= 0)
	{
		if ( ticker == 0 )
		{
			document.form_rebuild_progress.submit_button.value = label;
			document.form_rebuild_progress.submit_button.disabled = true;
		}
		else
		{
			document.form_rebuild_progress.submit_button.value = label_next + " (" + ticker + ")";

			ticker--;;
			refresh = setTimeout("updateButton()", 1000);
		}
	}
}
</script>

<div class="spacer_2"></div>

<form name="form_rebuild_progress" method="post" action="{S_REBUILD_SEARCH_ACTION}">

<table class="forumline w80 med">
	<tr>
		<th>{L_REBUILD_SEARCH_PROGRESS}</th>
	</tr>
	<tr>
		<td class="row1 tCenter pad_6">
			<div>{PROCESSING_POSTS}</div>
			<div>{PROCESSING_MESSAGES}</div>
			<div><img name="progress_bar" src="{PROGRESS_BAR_IMG}" /></div>
		</td>
	</tr>

	<tr>
		<td class="row1">
			<table class="forumline">
				<tr>
					<th colspan="3" class="pad_4">{L_PROCESSING_POST_DETAILS}</th>
				</tr>
				<tr class="row1">
					<td class="tRight nowrap">{L_CURRENT_SESSION}&nbsp;</td>
					<td class="tCenter nowrap w100">{SESSION_DETAILS}</td>
					<td>
						<p class="tCenter bold pad_2">{SESSION_PERCENT}</p>
						<!-- IF SESSION_PERCENT_BOX -->
						<div style="width: 200px; border: 1px solid #6E3A20; background: #FFFFFF;">
							<div class="spacer_6" style="width: {SESSION_PERCENT_WIDTH}%; background: #2E8F58;"></div>
						</div>
						<!-- ENDIF / SESSION_PERCENT_BOX -->
					</td>
				</tr>
				<tr class="row2">
					<td class="tRight nowrap">{L_TOTAL}&nbsp;</td>
					<td class="tCenter">{TOTAL_DETAILS}</td>
					<td class="row2 tCenter bold nowrap">
						<p>{TOTAL_PERCENT}</p>
						<!-- IF TOTAL_PERCENT_BOX -->
						<div style="width: 200px; border: 1px solid #6E3A20; background: #FFFFFF;">
							<div class="spacer_6" style="width: {TOTAL_PERCENT_WIDTH}%; background: #2E8F58;"></div>
						</div>
						<!-- ENDIF / TOTAL_PERCENT_BOX -->
					</td>
				</tr>
			</table>

			<div class="spacer_4"></div>

			<table class="forumline med">
				<col class="row1" width="30%" align="right">
				<col class="row2" width="20%" align="center">
				<col class="row1" width="30%" align="right">
				<col class="row2" width="20%" align="center">
				<tr>
					<th colspan="4" class="pad_4">{L_PROCESSING_TIME_DETAILS}</th>
				</tr>
				<tr>
					<td>{L_TIME_LAST_POSTS_ADMIN}&nbsp;</td>
					<td>{LAST_CYCLE_TIME}</td>
					<td>{L_TIME_FROM_THE_BEGINNING}&nbsp;</td>
					<td>{SESSION_TIME}</td>
				</tr>
				<tr>
					<td>{L_TIME_AVERAGE}&nbsp;</td>
					<td>{SESSION_AVERAGE_CYCLE_TIME}</td>
					<td>{L_TIME_ESTIMATED}&nbsp;</td>
					<td>{SESSION_ESTIMATED_TIME}</td>
				</tr>
				<tr>
					<td>{L_SIZE_SEARCH_TABLES}&nbsp;</td>
					<td>{SEARCH_TABLES_SIZE}</td>
					<td>Data size&nbsp;</td>
					<td>{SEARCH_DATA_SIZE}</td>
				</tr>
				<tr>
					<td>{L_SIZE_ESTIMATED}&nbsp;</td>
					<td>{FINAL_SEARCH_TABLES_SIZE}</td>
					<td>Index size&nbsp;</td>
					<td>{SEARCH_INDEX_SIZE}</td>
				</tr>
				<tr>
					<td colspan="4" class="row3" align="center">
					{L_STARTING_POST_ID}: {START_POST},
					{L_POSTS_LAST_CYCLE}: {POST_LIMIT},
					{L_TIME_LIMIT}: {TIME_LIMIT}
					</td>
				</tr>
			</table>

		</td>
	</tr>
	<tr>
		<td class="row2 small tCenter">{L_INFO_ESTIMATED_VALUES}</td>
	</tr>

	<tr>
		<td class="catBottom">
			<input class="mainoption" type="submit" name="submit_button" value="{L_NEXT}" onClick="ticker=0" />&nbsp;
			<!-- IF CANCEL_BUTTON -->
			&nbsp;&nbsp;&nbsp;
			<input class="mainoption" type="submit" name="cancel_button" value="{L_CANCEL}" />
			<script type="text/javascript">updateButton();</script>
			<!-- ENDIF -->
		</td>
	</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_REBUILD_SEARCH_PROGRESS -->
