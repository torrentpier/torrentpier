<script type="text/javascript">
	ajax.modify_draft = function (tid, mode) {
		var _mode = (!mode) ? 0 : 1;

		ajax.exec({
			action		: "modify_draft",
			id_draft	: tid,
			mode		: _mode
		});
	};

	ajax.callback.modify_draft = function (data) {
		jQuery("tr#tr_" + data.tid).remove();
	};
</script>

<h1 class="pagetitle">{PAGE_TITLE}</h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a>&nbsp;<em>&raquo;</em>&nbsp;{PROFILE}</p>

<table class="bordered w100">
	<thead>
	<tr>
		<th class="thHead">{L_CATEGORY}</th>
		<th class="thHead">{L_FORUM}</th>
		<th class="thHead">{L_TOPIC}</th>
		<th class="thHead">{L_DATE}</th>
		<th class="thHead" colspan="3">{L_ACTION}</th>
	</tr>
	</thead>
	<tbody>
	<!-- BEGIN DRAFT -->
	<tr id="tr_{DRAFT.TOPIC_ID}">
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8">{DRAFT.CATEGORY}</td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8">{DRAFT.FORUM}</td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8">{DRAFT.TOPIC}</td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8">{DRAFT.DT_CREATE}</td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8"><a href="{DRAFT.EDIT_POST}">{L_EDIT}</a></td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8"><a href="javascript: void(0)" onclick="ajax.modify_draft({DRAFT.TOPIC_ID})">{L_DELETE}</a></td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8"><a href="javascript: void(0)" onclick="ajax.modify_draft({DRAFT.TOPIC_ID}, true);">Публиковать</a></td>
	</tr>
	<!-- END DRAFT -->
	</tbody>
	<tfoot>
	<tr>
		<td class="catBottom" colspan="9">&nbsp;</td>
	</tr>
	</tfoot>
</table>

<div class="bottom_info">
	<div class="nav">
		<p style="float: left"></p>
		<p style="float: right"></p>
		<div class="clear"></div>
	</div>
	<div class="spacer_4"></div>
	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>
</div><!--/bottom_info-->