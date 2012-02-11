<h1>{L_RELEASE_TEMPLATES}</h1>

<a href="./admin_topic_templates.php">{L_LIST_FORUMS}</a> &#0183;
<a href="./admin_topic_templates.php?mode=templates">{L_LIST_OF_PATTERNS}</a> &#0183;
<a href="./admin_topic_templates.php?mode=add">{L_ADD_TEMPLATE}</a>
<br /><br />

<!-- IF FORUM_LIST -->
<P>{L_RELEASE_EXP}</p>
<br />

<form method="post" action="{S_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline w70">
	<tr>
		<th>{L_FORUM}</th>
		<th>{L_TEMPLATE}</th>
	</tr>
	<!-- BEGIN forum -->
	<tr class="{forum.ROW_CLASS}">
		<td class="{forum.FORUM_CLASS}" style="{forum.SF_PAD}{forum.FORUM_STYLE}">{forum.FORUM_NAME}</td>
		<td>{forum.TPL_SELECT}</td>
	</tr>
	<!-- END forum -->
	<tr>
		<td class="catBottom" colspan="2">
			<input type="submit" name="submit" id="send" value="{L_SUBMIT}" class="mainoption" disabled="disabled" />&nbsp;&nbsp;
			<label for="confirm">{L_CONFIRM}&nbsp;<input onclick="toggle_disabled('send', this.checked)" id="confirm" type="checkbox" name="confirm" value="1" /></label>
		</td>
	</tr>
</table>

</form>
<!-- ENDIF -->

<!-- IF TPL_LIST -->
<P>На этой странице отображаются шаблоны</p>
<br />

<form method="post" action="{S_ACTION}">

<table class="forumline w70 tCenter">
	<tr>
		<th width="100%">{L_TEMPLATE}</th>
		<th>{L_DELETE}</th>
	</tr>
	<!-- BEGIN tpl -->
	<tr class="{tpl.ROW_CLASS}">
		<td class="tLeft"><div class="floatL">{tpl.NAME}</div> <div class="floatR"><a href="./admin_topic_templates.php?mode=edit&tpl={tpl.ID}">{L_EDIT_DELETE_POST_TXTB}</a></div></td>
		<td><input id="tpl" type="checkbox" name="tpl_id[]" value="{tpl.ID}" /></td>
	</tr>
	<!-- END tpl -->
	<tr>
		<td class="catBottom">
			<input type="submit" name="submit" id="send" value="{L_SUBMIT}" class="mainoption" disabled="disabled" />&nbsp;&nbsp;
			<label for="conf">{L_CONFIRM}&nbsp;<input onclick="toggle_disabled('send', this.checked)" id="confirm" type="checkbox" name="conf" value="1" /></label>
            <a href=""></a>
        </td>
        <td class="catBottom">
            <input type="checkbox" onclick="$('input#tpl').attr({ checked: this.checked });" />
		</td>
	</tr>
</table>

</form>
<!-- ENDIF -->

<!-- IF TPL -->
<form method="post" action="{S_ACTION}">

<table class="forumline w70 tCenter">
	<tr>
		<th width="50%">Описание</th>
		<th width="50%">Значение</th>
	</tr>
	<tr class="row3">
		<td class="tLeft">Имя</td>
		<td><input type="text" name="tpl_name" value="{NAME}" /></td>
	</tr>
	<tr class="row4">
		<td class="tLeft">Скрипт</td>
		<td><input type="text" name="tpl_script" value="{SCRIPT}" /></td>
	</tr>
	<tr class="row3">
		<td class="tLeft">{L_TEMPLATE}</td>
		<td><input type="text" name="tpl_template" value="{TEMP}" /></td>
	</tr>
	<tr class="row4">
		<td class="tLeft">{L_DESC}</td>
		<td><input type="text" name="tpl_desc" value="{DESC}" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2">
			<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />
        </td>
	</tr>
</table>

</form>
<!-- ENDIF -->

