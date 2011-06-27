<h1>{L_REPORTS_TITLE}</h1>

<p>{L_REPORTS_EXPLAIN}</p>

<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
	<tr>
		<th class="thCornerL" width="50%">{L_REPORT_MODULE}</th>
		<th class="thTop">{L_REPORT_COUNT}</th>
		<th class="thCornerR" width="50%">{L_ACTION}</th>
	</tr>
	<!-- BEGIN installed_modules -->
	<tr>
		<td class="catSides" colspan="3"><span class="cattitle">{L_INSTALLED_MODULES}</span></td>
	</tr>
	<!-- BEGIN modules -->
	<tr>
		<td class="row1">
			<span class="gen">{installed_modules.modules.MODULE_TITLE}</span><br />
			<span class="gensmall">{installed_modules.modules.MODULE_EXPLAIN}</span>
		</td>
		<td class="row1" align="center"><span class="genmed">{installed_modules.modules.REPORT_COUNT}</span></td>
		<td class="row1" align="center"><span class="genmed">
			<a href="{installed_modules.modules.U_EDIT}" class="genmed">{L_EDIT}</a> |
			<a href="{installed_modules.modules.U_REASONS}" class="genmed">{installed_modules.modules.L_REASONS}</a> |
			<!-- BEGIN switch_sync -->
			<a href="{installed_modules.modules.U_SYNC}" class="genmed">{L_SYNC}</a> |
			<!-- END switch_sync -->
			<a href="{installed_modules.modules.U_MOVE_UP}" class="genmed">{L_MOVE_UP}</a> |
			<a href="{installed_modules.modules.U_MOVE_DOWN}" class="genmed">{L_MOVE_DOWN}</a> |
			<a href="{installed_modules.modules.U_UNINSTALL}" class="genmed">{L_UNINSTALL}</a>
		</span></td>
	</tr>
	<!-- END modules -->
	<!-- BEGIN switch_no_modules -->
	<tr>
		<td class="row1" colspan="3" align="center" style="padding: 5px"><span class="genmed">{L_NO_MODULES_INSTALLED}</span></td>
	</tr>
	<!-- END switch_no_modules -->
	<!-- END installed_modules -->
	
	<!-- BEGIN inactive_modules -->
	<tr>
		<td class="catSides" colspan="3"><span class="cattitle">{L_INACTIVE_MODULES}</span></td>
	</tr>
	<!-- BEGIN modules -->
	<tr>
		<td class="row1">
			<span class="gen">{inactive_modules.modules.MODULE_TITLE}</span><br />
			<span class="gensmall">{inactive_modules.modules.MODULE_EXPLAIN}</span>
		</td>
		<td class="row1" align="center"><span class="genmed">{inactive_modules.modules.REPORT_COUNT}</span></td>
		<td class="row1" align="center"><span class="genmed">
			<a href="{inactive_modules.modules.U_INSTALL}" class="genmed">{L_INSTALL}</a>
		</span></td>
	</tr>
	<!-- END modules -->
	<!-- BEGIN switch_no_modules -->
	<tr>
		<td class="row1" colspan="3" align="center" style="padding: 5px"><span class="genmed">{L_NO_MODULES_INACTIVE}</span></td>
	</tr>
	<!-- END switch_no_modules -->
	<!-- END inactive_modules -->
</table>

<br />