<script type="text/javascript">
<!--
function checked_switch(form)
{
	var elements = document.forms[form].elements['reports[]'];
	var count = elements.length;

	if (count)
	{
		for (var i = 0; i < count; i++)
		{
			elements[i].checked = (elements[i].checked) ? false : true;
		}
	}
	else
	{
		elements.checked = (elements.checked) ? false : true;
	}
}

function checked_toggle(form, status)
{
	var elements = document.forms[form].elements['reports[]'];
	var count = elements.length;

	if (count)
	{
		for (var i = 0; i < count; i++)
		{
			elements[i].checked = status;
		}
	}
	else
	{
		elements.checked = status;
	}
}
-->
</script>

<table cellspacing="2" cellpadding="2" border="0" width="100%">
	<tr>
		<td class="nav"><a href="{U_INDEX}" class="nav">{T_INDEX}</a></td>
	</tr>
</table>

<table cellspacing="2" cellpadding="0" border="0" width="100%">
	<tr>
		<td width="250" valign="top" style="padding-right: 10px">
			<form action="{S_REPORT_ACTION}" method="post" id="report_list" style="margin-bottom: 2px">
				<table cellspacing="1" cellpadding="4" border="0" width="100%" class="forumline">
					<tr>
						<th class="thHead" colspan="2">{L_REPORTS}</th>
					</tr>
					<tr>
						<td class="row3" colspan="2"><a href="{U_REPORT_INDEX}" class="cattitle">{L_REPORT_INDEX}</a></td>
					</tr>
					<!-- BEGIN report_modules -->
					<tr>
						<td class="row3" colspan="2"><a href="{report_modules.U_SHOW}" class="cattitle">{report_modules.TITLE}</a></td>
					</tr>
					<!-- BEGIN reports -->
					<tr>
						<td class="{report_modules.reports.ROW_CLASS}" width="1" nowrap="nowrap"><input type="checkbox" name="reports[]" value="{report_modules.reports.ID}" /></td>
						<td class="{report_modules.reports.ROW_CLASS}">
							<!-- BEGIN switch_current -->
							<strong>
							<!-- END switch_current -->
							<a href="{report_modules.reports.U_SHOW}" class="gen">{report_modules.reports.TITLE}</a><br />
							<!-- BEGIN switch_current -->
							</strong>
							<!-- END switch_current -->
							<span class="gensmall">
								{L_REPORT_BY} {report_modules.reports.AUTHOR}
							</span>
						</td>
					</tr>
					<!-- END reports -->
					<!-- BEGIN no_reports -->
					<tr>
						<td class="row1" colspan="2"><span class="genmed">{L_NO_REPORTS}</span></td>
					</tr>
					<!-- END no_reports -->
					<!-- END report_modules -->
					<tr>
						<td class="catBottom" align="center" colspan="2">
							<select name="mode" class="report_mode" onchange="submit()">
								<option value="" selected="selected">{L_ACTION}</option>
								<option value="" disabled="disabled"></option>
								<!-- BEGIN switch_global_delete_option -->
								<option value="delete">{L_DELETE}</option>
								<!-- END switch_global_delete_option -->
								<optgroup label="{L_REPORT_MARK}">
									<option value="clear" class="report_cleared">{L_STATUS_CLEARED}</option>
									<option value="process" class="report_process">{L_STATUS_IN_PROCESS}</option>
									<option value="open" class="report_open">{L_STATUS_OPEN}</option>
								</optgroup>
							</select>
							<noscript>
								<input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}" />
							</noscript>
						</td>
					</tr>
				</table>
			</form>
			<span class="gensmall">
				<a href="javascript:checked_toggle('report_list',true)" class="gensmall">{L_MARK_ALL}</a> ::
				<a href="javascript:checked_switch('report_list')" class="gensmall">{L_INVERT_SELECT}</a>
			</span>
		</td>
		<td valign="top">
			{REPORT_VIEW}
		</td>
	</tr>
</table>