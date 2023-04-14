<h1>{L_FORUM_PRUNE}</h1>

<p>{L_FORUM_PRUNE_EXPLAIN}</p>

<!-- IF FORUM_NAME -->
<h2>{L_FORUM}: {FORUM_NAME}</h2>
<!-- ENDIF -->

<br />

<form action="{S_PRUNE_ACTION}" method="post">

<table class="forumline wAuto">
<!-- IF PRUNED_TOTAL -->
<tr>
	<th>{L_FORUM}</th>
	<th>{L_TOPICS_PRUNED}</th>
</tr>
<!-- BEGIN pruned -->
<tr class="{pruned.ROW_CLASS} tCenter">
	<td>{pruned.FORUM_NAME}</td>
	<td>{pruned.PRUNED_TOPICS}</td>
</tr>
<!-- END pruned -->
<tr>
	<td colspan="2" class="row2 tCenter"><b>{L_PRUNE_SUCCESS}</b></td>
</tr>
<!-- ENDIF -->
<tr>
	<th colspan="2">{L_FORUM_PRUNE}</th>
</tr>
<tr>
	<td colspan="2" class="row2 tCenter">{SEL_FORUM}</td>
</tr>
<tr>
	<td colspan="2" class="row1 tCenter">
		<p>{L_PRUNE_TOPICS_NOT_POSTED} <input class="post" type="text" name="prunedays" size="4" /> {L_DAYS}<sup>1</sup></p>
		<p class="med"><label><input class="post" type="checkbox" name="prune_all_topic_types" value="1" />{L_DELETE_ALL_TOPICS}</label></p>
	</td>
</tr>
<tr>
	<td colspan="2" class="catBottom">
		<input type="submit" name="submit" value="{L_DO_PRUNE}" class="mainoption">
	</td>
</tr>
</table>

</form>

<br />

<div class="med">
	<p><sup>1</sup> Если вы не введёте число, то будут удалены <b>все темы</b>.</p>
</div>

<br />
