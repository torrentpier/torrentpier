<h1 class="pagetitle">{PAGE_TITLE}</h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<table class="forumline" style="table-layout: fixed;">
<tr>
	<th>{L_FAQ_TITLE}</th>
</tr>
<tr>
	<td class="row1 pad_8">
		<br />
		<!-- BEGIN faq_block_link -->
		<h4>{faq_block_link.BLOCK_TITLE}</h4>
		<!-- BEGIN faq_row_link -->
		<p><a href="{faq_block_link.faq_row_link.U_FAQ_LINK}" class="postlink">{faq_block_link.faq_row_link.FAQ_LINK}</a></p>
		<!-- END faq_row_link -->
		<br />
		<!-- END faq_block_link -->
	</td>
</tr>
<tr>
	<td class="catBottom">&nbsp;</td>
</tr>
</table>

<br clear="all" />

<table class="forumline">
<!-- BEGIN faq_block -->
<tr>
	<td class="catTitle">{faq_block.BLOCK_TITLE}</td>
</tr>
<!-- BEGIN faq_row -->
<tr>
	<td class="{faq_block.faq_row.ROW_CLASS}">
		<a name="{faq_block.faq_row.U_FAQ_ID}"></a>
		<h4 style="margin: 0.5em 0;">{faq_block.faq_row.FAQ_QUESTION}</h4>
		<p>{faq_block.faq_row.FAQ_ANSWER}</p>
		<p class="med tRight"><a href="#top">{L_BACK_TO_TOP}</a></p></td>
</tr>
<!-- END faq_row -->
<!-- END faq_block -->
</table>