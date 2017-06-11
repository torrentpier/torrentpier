<h1>{L_CONTROL_PANEL_TITLE}</h1>

<p>{L_CONTROL_PANEL_EXPLAIN}</p>
<br />

<!-- IF TPL_ATTACH_STATISTICS -->
<!--========================================================================-->

<form method="post" action="{S_MODE_ACTION}">
<table width="100%">
<tr>
	<td align="right" nowrap="nowrap">{L_VIEW}:&nbsp;{S_VIEW_SELECT}&nbsp;&nbsp;
		<input type="submit" name="submit" value="{L_SUBMIT}" class="liteoption" />
	</td>
</tr>
</table>

<table class="forumline">
<tr>
	<th width="50%">{L_STATISTIC}</th>
	<th width="50%">{L_VALUE}</th>
</tr>
<tr>
	<td class="row1" nowrap="nowrap">{L_NUMBER_OF_ATTACHMENTS}:</td>
	<td class="row2"><b>{NUMBER_OF_ATTACHMENTS}</b></td>
</tr>
<tr>
	<td class="row1" nowrap="nowrap">{L_TOTAL_FILESIZE}:</td>
	<td class="row2"><b>{TOTAL_FILESIZE}</b></td>
</tr>
<tr>
	<td class="row1" nowrap="nowrap">{L_ATTACH_QUOTA}:</td>
	<td class="row2"><b>{ATTACH_QUOTA}</b></td>
</tr>
<tr>
	<td class="row1" nowrap="nowrap">{L_NUMBER_POSTS_ATTACH}:</td>
	<td class="row2"><b>{NUMBER_OF_POSTS}</b></td>
</tr>
<tr>
	<td class="row1" nowrap="nowrap">{L_NUMBER_PMS_ATTACH}:</td>
	<td class="row2"><b>{NUMBER_OF_PMS}</b></td>
</tr>
<tr>
	<td class="row1" nowrap="nowrap">{L_NUMBER_TOPICS_ATTACH}:</td>
	<td class="row2"><b>{NUMBER_OF_TOPICS}</b></td>
</tr>
<tr>
	<td class="row1" nowrap="nowrap">{L_NUMBER_USERS_ATTACH}:</td>
	<td class="row2"><b>{NUMBER_OF_USERS}</b></td>
</tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_STATISTICS -->

<!-- IF TPL_ATTACH_SEARCH -->
<!--========================================================================-->

<form method="post" action="{S_MODE_ACTION}">
	<table width="100%">
	<tr>
	  <td align="right" nowrap="nowrap"><span class="med">{L_VIEW}:&nbsp;{S_VIEW_SELECT}&nbsp;&nbsp;
		<input type="submit" name="submit" value="{L_SUBMIT}" class="liteoption" />
		</span></td>
	</tr>
  </table>

  <table class="forumline">
  <tr>
		<th colspan="4">{L_ATTACH_SEARCH_QUERY}</th>
	</tr>
	<tr>
		<td class="row1" colspan="2"><span class="gen">{L_FILENAME}:</span><br /><span class="small">{L_SEARCH_WILDCARD_EXPLAIN}</span></td>
		<td class="row2" colspan="2"><span class="med"><input type="text" style="width: 200px" class="post" name="search_keyword_fname" size="20" /></span></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><span class="gen">{L_FILE_COMMENT}:</span><br /><span class="small">{L_SEARCH_WILDCARD_EXPLAIN}</span></td>
		<td class="row2" colspan="2"><span class="med"><input type="text" style="width: 200px" class="post" name="search_keyword_comment" size="20" /></span></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><span class="gen">{L_SEARCH_AUTHOR}:</span><br /><span class="small">{L_SEARCH_WILDCARD_EXPLAIN}</span></td>
		<td class="row2" colspan="2"><span class="med"><input type="text" style="width: 200px" class="post" name="search_author" size="20" /></span></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><span class="gen">{L_SIZE_SMALLER_THAN}:</span></td>
		<td class="row2" colspan="2"><span class="med"><input type="text" style="width: 100px" class="post" name="search_size_smaller" size="10" /></span></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><span class="gen">{L_SIZE_GREATER_THAN}:</span></td>
		<td class="row2" colspan="2"><span class="med"><input type="text" style="width: 100px" class="post" name="search_size_greater" size="10" /></span></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><span class="gen">{L_COUNT_SMALLER_THAN}:</span></td>
		<td class="row2" colspan="2"><span class="med"><input type="text" style="width: 100px" class="post" name="search_count_smaller" size="10" /></span></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><span class="gen">{L_COUNT_GREATER_THAN}:</span></td>
		<td class="row2" colspan="2"><span class="med"><input type="text" style="width: 100px" class="post" name="search_count_greater" size="10" /></span></td>
	</tr>
	<tr>
		<td class="row1" colspan="2"><span class="gen">{L_MORE_DAYS_OLD}:</span></td>
		<td class="row2" colspan="2"><span class="med"><input type="text" style="width: 100px" class="post" name="search_days_greater" size="10" /></span></td>
	</tr>
	<tr>
		<th colspan="4">{L_SEARCH_OPTIONS}</th>
	</tr>
	<tr>
		<td class="row1" colspan="2" align="right"><span class="gen">{L_FORUM}:</span></td>
		<td class="row2" colspan="2"><select class="post" name="search_forum">{S_FORUM_OPTIONS}</select></td>
	</tr>
	<tr>
		<td class="row1" colspan="2" align="right"><span class="gen">{L_SORT_BY}:&nbsp;</span></td>
		<td class="row2" colspan="2">{S_SORT_OPTIONS}</td>
		<tr>
		<td class="row1" colspan="2" align="right"><span class="gen">{L_SORT}:&nbsp;</span></td>
		<td class="row2" colspan="2">{S_SORT_ORDER}</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="4">{S_HIDDEN_FIELDS}<input class="liteoption" type="submit" name="search" value="{L_SEARCH}" /></td>
	</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_SEARCH -->

<!-- IF TPL_ATTACH_USER -->
<!--========================================================================-->

<form method="post" action="{S_MODE_ACTION}">
	<table width="100%">
	<tr>
	  <td align="right" nowrap="nowrap">
		<span class="med">{L_VIEW}:&nbsp;{S_VIEW_SELECT}&nbsp;&nbsp;{L_SORT_BY}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp;
		<input type="submit" name="submit" value="{L_SUBMIT}" class="liteoption" />
		</span>
	  </td>
	</tr>
  </table>
	<table width="100%" cellpadding="3" cellspacing="1" border="0" class="forumline">
		<tr>
	  <th>#</th>
	  <th>{L_USERNAME}</th>
	  <th>{L_ATTACHMENTS}</th>
	  <th>{L_SIZE_IN_KB}</th>
	</tr>
	<!-- BEGIN memberrow -->
	<tr>
	  <td class="{memberrow.ROW_CLASS}" align="center"><span class="gen">&nbsp;{memberrow.ROW_NUMBER}&nbsp;</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center"><span class="gen"><a href="{memberrow.U_VIEW_MEMBER}" class="gen">{memberrow.USERNAME}</a></span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center">&nbsp;<b>{memberrow.TOTAL_ATTACHMENTS}</b>&nbsp;</td>
	  <td class="{memberrow.ROW_CLASS}" align="center">&nbsp;<b>{memberrow.TOTAL_SIZE}</b>&nbsp;</td>
	</tr>
	<!-- END memberrow -->
  </table>

<table width="100%">
  <tr>
	<td><span class="nav">{PAGE_NUMBER}</span></td>
	<td align="right"><span class="nav">{PAGINATION}&nbsp;</span></td>
  </tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_USER -->

<!-- IF TPL_ATTACH_ATTACHMENTS -->
<!--========================================================================-->

<!-- BEGIN switch_user_based -->
<b>{L_STATISTICS_FOR_USER}</b>
<!-- END switch_user_based -->

<script type="text/javascript">
	//
	// Should really check the browser to stop this whining ...
	//
	function select_switch(status)
	{
		for (i = 0; i < document.attach_list.length; i++)
		{
			document.attach_list.elements[i].checked = status;
		}
	}
</script>

<form method="post" name="attach_list" action="{S_MODE_ACTION}">
<table width="100%">
	<tr>
		<td align="right" nowrap="nowrap">
			<span class="med">{L_VIEW}:&nbsp;{S_VIEW_SELECT}&nbsp;&nbsp;{L_SORT_BY}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp;
				<input type="submit" name="submit" value="{L_SUBMIT}" class="liteoption" />
			</span>
		</td>
	</tr>
</table>
<table class="forumline">
	<tr>
		<th>#</th>
		<th>{L_FILENAME}</th>
		<th>{L_FILE_COMMENT}</th>
		<th>{L_EXTENSION}</th>
		<th>{L_SIZE}</th>
		<th>{L_DOWNLOADS}</th>
		<th>{L_POST_TIME}</th>
		<th>{L_POSTED_IN_TOPIC}</th>
		<th>{L_DELETE}</th>
	</tr>
	<!-- BEGIN attachrow -->
	<tr>
		<td class="{attachrow.ROW_CLASS}" align="center"><span class="gen">&nbsp;{attachrow.ROW_NUMBER}&nbsp;</span></td>
		<td class="{attachrow.ROW_CLASS}" align="center"><span class="gen"><a href="{attachrow.U_VIEW_ATTACHMENT}" class="gen" target="_blank">{attachrow.FILENAME}</a></span></td>
		<td class="{attachrow.ROW_CLASS}" align="center"><span class="gen"><input type="text" size="40" maxlength="200" name="attach_comment_list[]" value="{attachrow.COMMENT}" class="post" /></span></td>
		<td class="{attachrow.ROW_CLASS}" align="center"><span class="gen">{attachrow.EXTENSION}</span></td>
		<td class="{attachrow.ROW_CLASS}" align="center"><span class="gen"><b>{attachrow.SIZE}</b></span></td>
		<td class="{attachrow.ROW_CLASS}" align="center"><span class="gen"><input type="text" size="5" maxlength="10" name="attach_count_list[]" value="{attachrow.DOWNLOAD_COUNT}" class="post" /></span></td>
		<td class="{attachrow.ROW_CLASS}" align="center"><span class="small">{attachrow.POST_TIME}</span></td>
		<td class="{attachrow.ROW_CLASS}" align="center"><span class="gen">{attachrow.POST_TITLE}</span></td>
		<td class="{attachrow.ROW_CLASS}" align="center">{attachrow.S_DELETE_BOX}</td>
		{attachrow.S_HIDDEN}
	</tr>
	<!-- END attachrow -->
	<tr>
		<td class="catBottom" colspan="9">
			<input type="submit" name="submit_change" value="{L_SUBMIT_CHANGES}" class="mainoption" />
			&nbsp;
			<input type="submit" name="delete" value="{L_DELETE_MARKED}" class="liteoption" />
		</td>
	</tr>
</table>

<!-- BEGIN switch_user_based -->
	{S_USER_HIDDEN}
<!-- END switch_user_based -->

<table width="100%">
<tr>
	<td align="right" valign="top" nowrap="nowrap"><b><span class="small"><a href="javascript:select_switch(true);" class="small">{L_MARK_ALL}</a> :: <a href="javascript:select_switch(false);" class="small">{L_UNMARK_ALL}</a></span></b></td>
</tr>
</table>

<table width="100%">
<tr>
	<td><span class="nav">{PAGE_NUMBER}</span></td>
	<td align="right"><span class="nav">{PAGINATION}&nbsp;</span></td>
</tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ATTACH_ATTACHMENTS -->

<br />
