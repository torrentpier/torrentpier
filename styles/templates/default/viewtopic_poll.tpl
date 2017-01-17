<script type="text/javascript">
var bb_poll = {};
bb_poll.data        = {POLL_VOTES_JS}; // [["заголовок", "result"], ...]
bb_poll.title       = '';
bb_poll.votes_data  = {};
bb_poll.votes_sum   = 0;
bb_poll.max_img_len = 205; // 100% = this length

$(function(){
	$.each(bb_poll.data, function(vote_id, vote_data){
		var vote_text   = vote_data[0];
		var vote_result = parseInt(vote_data[1]);

		if (vote_id === 0) {
			bb_poll.title = vote_text;
		}
		else {
			bb_poll.votes_sum += vote_result;
			bb_poll.votes_data[vote_id] = [vote_text, vote_result];
		}
	});

	$('#poll-title').html(bb_poll.title);
	$('#votes-sum-val').text(bb_poll.votes_sum);

	$.each(bb_poll.votes_data, function(vote_id, vote_data){
		var vote_caption   = vote_data[0];
		var vote_result    = parseInt(vote_data[1]);
		var vote_percent   = (bb_poll.votes_sum) ? Math.round(vote_result / bb_poll.votes_sum * 100) : 0;
		var vote_img_width = Math.round(vote_percent * bb_poll.max_img_len / 100);

		$('#poll-results-tpl tbody')
			.clone()
			.find('span.poll-vote-caption').html(vote_caption).end()
			.find('img.poll-vote-img').css({ width: vote_img_width }).end()
			.find('span.poll-vote-percent').text(vote_percent+'%').end()
			.find('span.poll-vote-result').text(vote_result).end()
			.appendTo('#poll-results-block')
		;
	});

	$('#poll').show();
});

function build_votes ()
{
	$.each(bb_poll.votes_data, function(vote_id, vote_data){
		var vote_caption = vote_data[0];
		var vote_el_id   = 'vote-'+ vote_id;

		$('#poll-results-block').hide();
		$('#poll-votes-tpl tbody')
			.clone()
			.find('input').val(vote_id).attr({id: vote_el_id}).end()
			.find('label').html(vote_caption).attr({'for': vote_el_id}).end()
			.appendTo('#poll-votes-block')
		;
	});
	$('#votes-sum-block, #vote-btn-a, #poll-manage').hide();
	$('#vote-btn-input').show();
}

function submit_vote ()
{
	var $voted_id = $('input.vote-inp:checked');

	if ($voted_id.length === 0) {
		alert('{L_NEW_POLL_U_NOSEL}');
	}
	else {
		$('#poll-mode').val('poll_vote');
		$voted_id.clone().appendTo('#poll-form');
		$('#vote-id').val( $voted_id.val() );
		$('#poll-submit-btn').click();
	}
}

function build_poll_edit_form ()
{
	$('#poll').empty().append($('#poll-edit-tpl').contents());
	$('#poll-legend').html('{L_NEW_POLL_U_CHANGE}');
	$('#poll-edit-submit-btn').click(function(){
		return poll_manage('poll_edit', '{L_NEW_POLL_U_EDIT}?');
	});

	$('#poll-caption-inp').val( html2text(bb_poll.title) );

	var votes_text = [];
	$.each(bb_poll.votes_data, function(vote_id, vote_data){
		votes_text.push( html2text(vote_data[0]) );
	});
	$('#poll-votes-inp').val( votes_text.join('\n') );

	return false;
}

function html2text (str)
{
	return $('<span></span>').html(str).text();
}
</script>

<table id="poll-votes-tpl" style="display: none;">
<tbody>
<tr>
	<td><input type="radio" name="vote_id" class="vote-inp" value="" /></td>
	<td><label class="wrap"></label></td>
</tr>
</tbody>
</table>

<table id="poll-results-tpl" style="display: none;">
<tbody>
<tr>
	<td class="tLeft"><span class="poll-vote-caption"></span></td>
	<td>&nbsp;</td>
	<td class="nowrap"><img src="{IMG}/vote_lcap.gif" width="4" height="12" alt="" /><img src="{IMG}/voting_bar.gif" class="poll-vote-img" width="1" height="12" alt="" /><img src="{IMG}/vote_rcap.gif" width="4" height="12" alt="" /></td>
	<td class="nowrap tRight bold">&nbsp;<span class="poll-vote-percent"></span>&nbsp;</td>
	<td class="nowrap tCenter">[ <span class="poll-vote-result"></span> ]</td>
</tr>
</tbody>
</table>

<div class="mrg_12 tCenter"><b id="poll-title"></b></div>

<table id="poll-results-block" class="borderless bCenter"></table>
<table id="poll-votes-block" class="borderless bCenter"></table>

<!-- IF SHOW_VOTE_BTN -->
<div id="vote-btn-a" class="mrg_8 tCenter">[ <a href="#" onclick="build_votes(); return false;" class="gen"><b>{L_SUBMIT_VOTE}</b></a> ]</div>
<div id="vote-btn-input" class="mrg_6 tCenter" style="display: none;"><input type="button" onclick="submit_vote(); return false;" value="{L_SUBMIT_VOTE}" class="bold" /></div>
<!-- ELSE -->
<div class="mrg_8 tCenter">[ <b>{L_NEW_POLL_END}</b> ]</div>
<!-- ENDIF -->

<div id="votes-sum-block" class="mrg_8 tCenter">{L_NEW_POLL_U_VOTED}: <span id="votes-sum-val"></span><b></b></div>

<!-- IF CAN_MANAGE_POLL -->
<div id="poll-manage" class="mrg_8 tCenter">
[ <a href="#" onclick="return poll_manage('poll_delete', '{L_CONFIRM_DELETE_POLL}');" class="med">{L_DELETE_POLL}</a> ]&nbsp;&nbsp;
	<!-- IF POLL_IS_EDITABLE -->
	[ <a href="#" onclick="return build_poll_edit_form();" class="med">{L_EDIT}</a> ]&nbsp;&nbsp;
		<!-- IF POLL_IS_FINISHED -->
		[ <a href="#" onclick="return poll_manage('poll_start', '{L_NEW_POLL_U_START}?');" class="med">{L_NEW_POLL_U_START}</a> ]
		<!-- ELSE -->
		[ <a href="#" onclick="return poll_manage('poll_finish', '{L_NEW_POLL_U_END}?');" class="med">{L_NEW_POLL_U_END}</a> ]
		<!-- ENDIF -->
	<!-- ENDIF -->
</div>
<!-- ENDIF -->