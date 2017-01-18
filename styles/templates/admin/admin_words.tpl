<!-- IF TPL_ADMIN_WORDS_LIST -->
<!--========================================================================-->

<h1>{L_WORDS_TITLE}</h1>

<P>{L_WORDS_EXPLAIN}</p>
<br />

<form method="post" action="{S_WORDS_ACTION}">

<table class="forumline">
	<tr>
		<th>{L_WORD}</th>
		<th>{L_REPLACEMENT}</th>
		<th colspan="2">{L_ACTION}</th>
	</tr>
	<!-- BEGIN words -->
	<tr>
		<td class="{words.ROW_CLASS}" align="center">{words.WORD}</td>
		<td class="{words.ROW_CLASS}" align="center">{words.REPLACEMENT}</td>
		<td class="{words.ROW_CLASS}"><a href="{words.U_WORD_EDIT}">{L_EDIT}</a></td>
		<td class="{words.ROW_CLASS}"><a href="{words.U_WORD_DELETE}">{L_DELETE}</a></td>
	</tr>
	<!-- END words -->
	<tr>
		<td colspan="5" class="catBottom">{S_HIDDEN_FIELDS}<input type="submit" name="add" value="{L_ADD_NEW_WORD}" class="mainoption" /></td>
	</tr>
</table></form>

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_WORDS_LIST -->

<!-- IF TPL_ADMIN_WORDS_EDIT -->
<!--========================================================================-->

<h1>{L_WORDS_TITLE}</h1>

<p>{L_WORDS_EXPLAIN}</p>
<br />

<form method="post" action="{S_WORDS_ACTION}">

<table class="forumline">
	<tr>
		<th colspan="2">{L_EDIT_WORD_CENSOR}</th>
	</tr>
	<tr>
		<td class="row1">{L_WORD}</td>
		<td class="row2"><input class="post" type="text" name="word" value="{WORD}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_REPLACEMENT}</td>
		<td class="row2"><input class="post" type="text" name="replacement" value="{REPLACEMENT}" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" name="save" value="{L_SUBMIT}" class="mainoption" /></td>
	</tr>
</table></form>

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_WORDS_EDIT -->
