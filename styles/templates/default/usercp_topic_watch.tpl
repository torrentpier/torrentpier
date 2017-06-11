<script type="text/javascript">
ajax.in_edit_mode = false;

$(document).ready(function(){
	$('#show-edit-btn a').click(function(){
		show_edit_options();
		$('#show-edit-btn').html( $('#edit-sel-topics').html() );
		return false;
	});

	$('td.topic_id').click(function(){
		if (!ajax.in_edit_mode) {
			$('#show-edit-btn a').click();
			$(this).find('input').click();
		}
	});
});

function show_edit_options ()
{
	$('td.topic_id').each(function(){
		var topic_id = $(this).attr('id');
		var input = '<input id="sel-'+ topic_id +'" type="checkbox" value="'+ topic_id +'" class="topic-chbox" />';
		$(this).html(input);
	});

	$('input.topic-chbox').click(function(){
		if ($.browser.mozilla) {
			$('#tr-'+this.value+' td').toggleClass('hl-selected-row');
		}	else {
			$('#tr-'+this.value).toggleClass('hl-selected-row');
		}
	});
	$('#pagination a.pg').each(function(){ this.href += '&mod=1'; });
	$('#ed-list-desc').hide();
	$('#mod-action-cell').append( $('#mod-action-content')[0] );
	$('#mod-action-row, #mod-action-content').show();

	$('#mod-action').submit(function(){
		var $form = $(this);
		$('input[name~=topic_id_list]', $form).remove();
		$('input.topic-chbox:checked').each(function(){
			$form.append('<input type="hidden" name="topic_id_list[]" value="'+ this.value +'" />');
			$('#tr-'+this.value).remove();
		});
	});
	ajax.in_edit_mode = true;
}
</script>

<style type="text/css">
td.topic_id { cursor: pointer; }
</style>

<div id="mod-action-content" style="display: none;">
<form id="mod-action" name="watch_form" method="post" action="{S_FORM_ACTION}">
	<table class="borderless pad_0" cellpadding="0" cellspacing="0">
	<tr><td class="pad_4">
		<input type="submit" name="del_from_ut" value="{L_DEL_LIST_MY_MESSAGE}" onclick="if (!window.confirm( this.value +'?' )){ return false };" />
	</tr></table>
</form>
</div>

<table id="post-row" style="display: none;">
<tr>
	<td class="row2" colspan="7">
		<div class="post_watch_wrap row1">
			<div class="post_body pad_6"></div>
			<div class="clear"></div>
		</div>
	</td>
</tr>
</table>

<table cellpadding="2" cellspacing="0" width="100%">
<tr>
	<td width="100%">
		<h1 class="maintitle">{PAGE_TITLE}</h1>
		<div id="forums_top_links" class="nav">
			<a href="{U_INDEX}">{T_INDEX}</a>&nbsp;<em>&middot;</em>
			<span id="show-edit-btn"><a href="#">{L_EDIT_MY_MESSAGE_LIST}</a></span>
			<span id="edit-sel-topics" style="display: none;"><a href="#" class="bold adm" onclick="$('input.topic-chbox').trigger('click'); return false;">{L_SELECT_INVERT}</a></span><em>&middot;</em>
			<a href="#" class="med normal" onclick="setCookie('{COOKIE_MARK}', 'all_forums');">{L_MARK_ALL_FORUMS_READ}</a>
		</div>
	</td>
	<td class="vBottom tLeft nowrap med"><b>{PAGINATION}</b></td>
</tr>
</table>

<table width="100%" class="forumline tablesorter">
<thead>
<tr>
	<th class="{sorter: 'text'}"></th>
	<th class="{sorter: 'text'}" width="25%"><b class="tbs-text">{L_FORUM}</b></th>
	<th class="{sorter: 'text'}" width="75%"><b class="tbs-text">{L_TOPIC}</b></th>
	<th class="{sorter: 'text'}"><b class="tbs-text">{L_AUTHOR}</b></th>
	<th width="80" class="{sorter: 'text'}"><b class="tbs-text">{L_REPLIES}</b></th>
	<th width="120" class="{sorter: 'text'} nowrap"><b class="tbs-text">{L_LASTPOST}</b></th>
</tr>
</thead>
<!-- BEGIN watch -->
<tr class="tCenter {watch.ROW_CLASS}" id="tr-{watch.TOPIC_ID}">
	<td id="{watch.TOPIC_ID}" class="topic_id">
		<span style="display: none;">{watch.TOPIC_ICON}</span>
		<img class="topic_icon" src="{watch.TOPIC_ICON}">
	</td>
	<td><a href="{watch.U_FORUM}" class="genmed">{watch.FORUM_TITLE}</a></td>
	<td class="tLeft nowrap">
		<a class="topictitle" title="{watch.FULL_TOPIC_TITLE}" href="{watch.U_TOPIC}">{watch.TOPIC_TITLE}</a>
		<!-- IF watch.PAGINATION --><br /><span class="topicPG">&nbsp;[{ICON_GOTOPOST}{L_GOTO_SHORT} {watch.PAGINATION} ]</span><!-- ENDIF -->
	</td>
	<td>{watch.AUTHOR}</td>
	<td class="gensmall">{watch.REPLIES}</td>
	<td class="gensmall nowrap">
		{watch.LAST_POST}<!-- IF watch.IS_UNREAD --><a href="{TOPIC_URL}{watch.TOPIC_ID}{NEWEST_URL}">{ICON_NEWEST_REPLY}</a><!-- ELSE -->
		<a href="{POST_URL}{watch.LAST_POST_ID}#{watch.LAST_POST_ID}">{ICON_LATEST_REPLY}</a><!-- ENDIF -->
	</td>
</tr>
<!-- END watch -->
<tfoot>
<tr id="mod-action-row">
	<td colspan="6" id="mod-action-cell" class="row2">
		<span id="ed-list-desc" class="small">{L_DEL_LIST_INFO}<span class="floatR">{MATCHES}</span></span>
	</td>
</tr>
</tfoot>
</table>
