<script type="text/javascript">
	ajax.modify_draft = function (tid, mode) {
		var _mode = (!mode) ? 0 : 1;

		ajax.exec({
			action:"modify_draft",
			id_draft:tid,
			mode:_mode
		});
	};

	ajax.callback.modify_draft = function (data) {
		jQuery("tr#tr_" + data.tid).remove();
	};
</script>

<table class="bordered w100">
	<tbody>
	<tr>
		<th class="thHead" colspan="5">Черновики пользователя {USERNAME}</th>
	</tr>
	<tr>
		<th class="thHead" width="50%">Название темы</th>
		<th class="thHead">Дата создания</th>
		<th class="thHead" colspan="3">Модифицировать</th>
	</tr>
	<!-- BEGIN DRAFT -->
	<tr id="tr_{DRAFT.T_ID}">
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8"><a href="./viewtopic.php?t={DRAFT.T_ID}">{DRAFT.TITLE}</a></td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8">{DRAFT.DT_CREATE}</td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8"><a href="./posting.php?mode=editpost&p={DRAFT.POST_FIRST_ID}">Редактировать</a>
		</td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8"><a href="javascript: void(0)"
														  onclick="ajax.modify_draft({DRAFT.T_ID})">Удалить</a></td>
		<td class="row{DRAFT.ROW_CLASS} tCenter pad_8"><a href="javascript: void(0)" onclick="ajax.modify_draft({DRAFT.T_ID}, true);">Публиковать</a></td>
	</tr>
	<!-- END DRAFT -->
	</tbody>
</table>