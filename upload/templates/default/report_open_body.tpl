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

<table cellpadding="2" cellspacing="2" border="0" width="80%" align="center">
	<tr> 
		<td class="nav"><a href="{U_INDEX}" class="nav">{L_HOME}</a></td>
	</tr>
</table>

<form action="{S_REPORT_ACTION}" method="post" id="report_list_open" style="margin: 0">
	<table cellspacing="1" cellpadding="4" border="0" width="80%" align="center" class="forumline">
		<tr>
			<th class="thCornerL" colspan="2" width="70%">{L_OPEN_REPORTS}</th>
		</tr>
		<!-- BEGIN open_reports -->
		<tr>
			<td class="{open_reports.ROW_CLASS}" nowrap="nowrap"><input type="checkbox" name="reports[]" value="{open_reports.ID}" /></td>
			<td class="{open_reports.ROW_CLASS}" width="100%">
				<a href="{open_reports.U_SHOW}" class="gen">{open_reports.TITLE}</a><br />
				<span class="gensmall">
					{L_REPORT_BY} <a href="{open_reports.U_AUTHOR}" class="gensmall">{open_reports.AUTHOR}</a>
				</span>
			</td>
		</tr>
		<!-- END open_reports -->
		<tr>
			<td class="catBottom" colspan="3" align="center">
				<select name="mode" class="report_mode" onchange="submit()">
					<option value="" selected="selected">{L_ACTION}</option>
					<option value="" disabled="disabled"></option>
					<option value="delete">{L_DELETE}</option>
					<optgroup label="{L_REPORT_MARK}">
						<option value="clear" class="report_cleared">{L_STATUS_CLEARED}</option>
						<option value="process" class="report_process">{L_STATUS_IN_PROCESS}</option>
						<option value="open" class="report_open">{L_STATUS_OPEN}</option>
					</option>
				</select>
				<noscript>
					<input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}" />
				</noscript>
			</td>
		</tr>
	</table>
</form>

<table cellspacing="2" cellpadding="2" border="0" width="80%" align="center">
	<tr> 
		<td class="gensmall">
			<a href="javascript:checked_toggle('report_list_open',true)" class="gensmall">{L_MARK_ALL}</a> ::
			<a href="javascript:checked_switch('report_list_open')" class="gensmall">{L_INVERT_SELECT}</a>
		</td>
	</tr>
</table>