<h1>{L_TERMS}</h1>

<p>{L_TERMS_EXPLAIN}</p>
<br />

<form action="{S_ACTION}" method="post">
	<table class="forumline">
		<tr>
			<th>{L_TERMS}</th>
		</tr>
		<tr id="view_message"<!-- IF not PREVIEW_HTML --> class="hidden"<!-- ENDIF -->>
			<td class="row1">
				<div class="view-message">{PREVIEW_HTML}</div>
			</td>
		</tr>
		<tr class="row2">
			<td>
				<!-- INCLUDE posting_editor.tpl -->
			</td>
		</tr>
	</table>
</form>

<br clear="all"/>
