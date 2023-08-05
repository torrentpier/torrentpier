<?php if (!empty($V['TORHELP_TOPICS'])) { ?>
	<?php  $this->set_filename('xs_include_3c171192534a7b7c08827b49ecdea0d1', 'torhelp.tpl', true);  $this->pparse('xs_include_3c171192534a7b7c08827b49ecdea0d1');  ?>
	<div class="spacer_6"></div>
<?php } ?>

<div id="forums_list_wrap">

<div id="forums_top_nav">
	<h1 class="pagetitle"><a href="<?php echo isset($V['U_INDEX']) ? $V['U_INDEX'] : ''; ?>"><?php echo isset($V['T_INDEX']) ? $V['T_INDEX'] : ''; ?></a></h1>
</div><!--/forums_top_nav-->
<table border="0" cellpadding="7" cellspacing="0" width="100%">
<center>
<?php if (!empty($V['LOGGED_IN'])) { ?>
<?php if (!empty($bb_cfg['chat'])) { ?>
            <script type="text/javascript">
                var id = <?php echo isset($V['CHAT_ID']) ? $V['CHAT_ID'] : ''; ?>;
                ajax.callback.chat = function(data) {
                    if(data.up) $('#chat').scrollTop(0);
                    if(data.clear) {
                        $('.chat_message').attr('value', '');
                        get_message_chat(1);
                    }
                    <?php if (!empty($V['IS_AM'])) { ?>
                    else if(data.del) {
                        for(i=0; i < data.del.length; i++) {
                            $('#pp_'+ data.del[i]).hide();
                        }
                    }else if(data.html){
                        $('#pp_'+ data.post_id).show().html(data.html);
                        initPostBBCode('#pp_'+ data.post_id);
                        $('#pe_'+ data.post_id).hide();
                        ajax.open = false;
                    } else if(data.text){
                        ajax.open = data.post_id;
                        $('#pe_'+ data.post_id).html(data.text);
                    }
                    <?php } ?>
                    else {
                        if(data.message) $('#chat').prepend(data.message);
                        initPostBBCode('#chat');
                    }

                    if(data.id) id = data.id;
                };
                setInterval(function(){ get_message_chat(0); }, 25000);

                function get_message_chat(up){
                    ajax.exec({action : 'chat', mode: 'select', id: id, up: up});
                }
                function submit_click(e) {
                    e = e || window.event;
                    if (e.keyCode == 13 && e.ctrlKey) {
                        submit_chat();
                    };
                }
                function submit_chat(){
                    var message = $('.chat_message').val();
                    if (message.length < 3) {
                        alert('Вы должны ввести текст сообщения');
                        $('.chat_message').focus();
                        return false;
                    }
                    ajax.exec({action : 'chat', mode: 'insert', message: message});
                }
                function add_nick(text){
                    var message = $('.chat_message').val();
                    $('.chat_message').attr('value', message + text +' ');
                    $('.chat_message').focus();
                }
            </script>
            <?php if (!empty($V['IS_AM'])) { ?>
            <script type="text/javascript">
                ajax.open = false;
                function edit_comment (post_id, text, type) {
                    if(ajax.open && ajax.open != post_id) {
                        alert('У вас уже открыто одно быстрое редактирование!');
                    } else{
                        if(ajax.open && !text){
                            $('#pp_'+ post_id).show();
                            $('#pe_'+ post_id).hide();
                        } else{
                            $('#pp_'+ post_id).hide();
                            $('#pe_'+ post_id).show();

                            ajax.exec({
                                action  : 'chat',
                                mode    : 'edit',
                                post_id : post_id,
                                text    : text,
                                type    : type
                            });
                        }
                        ajax.open = false;
                    }
                }
                function del_message_chat(){
                    if(!confirm('Вы уверены, что хотите удалить эти сообщения?')) return false;
                    var ids = 0;
                    $('input.chat-post:checked').each(function(){
                        ids += ','+ this.value;
                    });
                    if(!ids) alert('Вы не выбрали сообщения.');
                    else ajax.exec({action : 'chat', mode: 'delete', ids: ids});
                }
                function set_hid_chbox(id)
                {
                    $('#pp_'+ id).toggleClass('hl-selected-post');
                    return false;
                }
            </script>
            <?php } ?>
            <style type="text/css">
                #chat { overflow: auto; height: 300px; }
                .chat-comment {
                    margin: 3px;
                    padding: 4px;
                    border: solid 1px #b7c0c5;
                    border-radius: 5px;
                    -moz-border-radius: 5px;
                    -webkit-border-radius: 5px;
                }
                textarea.chat_message {
                    height: 40px;
                    margin: 4px;
                    border-radius: 0px;
                    -moz-border-radius: 0px;
                    -webkit-border-radius: 0px;
                    font-size: 11px;
                }
                #submit_chat {
                    border: 1px solid #b7c0c5;
                    padding: 2px;
                    background-color: #EFEFEF;
                    font-size: 10px;
                    font-weight: bold;
                }
                #submit_chat:hover { color: #0080FF; }
            </style>
            <h3>Мини чат</h3>
            <div class="tCenter">
                <form name="post">
                    <textarea class="chat_message w90" id="message" onkeydown="submit_click(event)"></textarea>
                    <div class="buttons mrg_4">
                        <input type="button" value="B" name="codeB" title="<?php echo isset($L['BOLD']) ? $L['BOLD'] : (isset($SL['BOLD']) ? $SL['BOLD'] : $V['L_BOLD']); ?>" style="font-weight: bold; width: 30px;" />
                        <input type="button" value="i" name="codeI" title="<?php echo isset($L['ITALIC']) ? $L['ITALIC'] : (isset($SL['ITALIC']) ? $SL['ITALIC'] : $V['L_ITALIC']); ?>" style="width: 30px; font-style: italic;" />
                        <input type="button" value="u" name="codeU" title="<?php echo isset($L['UNDERLINE']) ? $L['UNDERLINE'] : (isset($SL['UNDERLINE']) ? $SL['UNDERLINE'] : $V['L_UNDERLINE']); ?>" style="width: 30px; text-decoration: underline;" />
                        <input type="button" value="s" name="codeS" title="<?php echo isset($L['STRIKEOUT']) ? $L['STRIKEOUT'] : (isset($SL['STRIKEOUT']) ? $SL['STRIKEOUT'] : $V['L_STRIKEOUT']); ?>" style="width: 30px; text-decoration: line-through;" />&nbsp;&nbsp;
                        <input type="button" value="<?php echo isset($L['QUOTE']) ? $L['QUOTE'] : (isset($SL['QUOTE']) ? $SL['QUOTE'] : $V['L_QUOTE']); ?>" name="codeQuote" title="<?php echo isset($L['QUOTE_TITLE']) ? $L['QUOTE_TITLE'] : (isset($SL['QUOTE_TITLE']) ? $SL['QUOTE_TITLE'] : $V['L_QUOTE_TITLE']); ?>" style="width: 57px;" />
                        <input type="button" value="Img" name="codeImg" title="<?php echo isset($L['IMG_TITLE']) ? $L['IMG_TITLE'] : (isset($SL['IMG_TITLE']) ? $SL['IMG_TITLE'] : $V['L_IMG_TITLE']); ?>" style="width: 40px;" />
                        <input type="button" value="<?php echo isset($L['URL']) ? $L['URL'] : (isset($SL['URL']) ? $SL['URL'] : $V['L_URL']); ?>" name="codeUrl" title="<?php echo isset($L['URL_TITLE']) ? $L['URL_TITLE'] : (isset($SL['URL_TITLE']) ? $SL['URL_TITLE'] : $V['L_URL_TITLE']); ?>" style="width: 63px; text-decoration: underline;" />
                        <input type="hidden" name="codeUrl2" />&nbsp;
                        <input type="button" value="<?php echo isset($L['CODE']) ? $L['CODE'] : (isset($SL['CODE']) ? $SL['CODE'] : $V['L_CODE']); ?>" name="codeCode" title="<?php echo isset($L['CODE_TITLE']) ? $L['CODE_TITLE'] : (isset($SL['CODE_TITLE']) ? $SL['CODE_TITLE'] : $V['L_CODE_TITLE']); ?>" style="width: 43px;" />
                        <input type="button" value="<?php echo isset($L['LIST']) ? $L['LIST'] : (isset($SL['LIST']) ? $SL['LIST'] : $V['L_LIST']); ?>" name="codeList" title="<?php echo isset($L['LIST_TITLE']) ? $L['LIST_TITLE'] : (isset($SL['LIST_TITLE']) ? $SL['LIST_TITLE'] : $V['L_LIST_TITLE']); ?>" style="width: 60px;" />
                        <input type="button" value="1." name="codeOpt" title="<?php echo isset($L['LIST_ITEM']) ? $L['LIST_ITEM'] : (isset($SL['LIST_ITEM']) ? $SL['LIST_ITEM'] : $V['L_LIST_ITEM']); ?>" style="width: 30px;" />&nbsp;
                        <input type="button" value="<?php echo isset($L['QUOTE_SEL']) ? $L['QUOTE_SEL'] : (isset($SL['QUOTE_SEL']) ? $SL['QUOTE_SEL'] : $V['L_QUOTE_SEL']); ?>" name="quoteselected" title="<?php echo isset($L['QUOTE_SELECTED']) ? $L['QUOTE_SELECTED'] : (isset($SL['QUOTE_SELECTED']) ? $SL['QUOTE_SELECTED'] : $V['L_QUOTE_SELECTED']); ?>" onmouseout="bbcode.refreshSelection(false);" onmouseover="bbcode.refreshSelection(true);" onclick="bbcode.onclickQuoteSel();" />&nbsp;
                    </div>
                    <script type="text/javascript">
                        var bbcode = new BBCode("message");
                        var ctrl = "ctrl";

                        bbcode.addTag("codeB", "b", null, "B", ctrl);
                        bbcode.addTag("codeI", "i", null, "I", ctrl);
                        bbcode.addTag("codeU", "u", null, "U", ctrl);
                        bbcode.addTag("codeS", "s", null, "S", ctrl);

                        bbcode.addTag("codeQuote", "quote", null, "Q", ctrl);
                        bbcode.addTag("codeImg", "img", null, "R", ctrl);
                        bbcode.addTag("codeUrl", "url", "/url", "", ctrl);
                        bbcode.addTag("codeUrl2", "url=", "/url", "W", ctrl);

                        bbcode.addTag("codeCode", "code", null, "K", ctrl);
                        bbcode.addTag("codeList",  "list", null, "L", ctrl);
                        bbcode.addTag("codeOpt", "*", "", "0", ctrl);
                    </script>

                    <div class="floatR pad_4">
                        <span title="Оправить (Ctrl+Enter)" id="submit_chat" onclick="submit_chat(); return false;">Отправить</span>
                        <span title="Очистить" id="submit_chat" onclick="$('.chat_message').attr('value', ''); $('.chat_message').focus();">&nbsp;X&nbsp;</span>
                        <span title="Смайлы" id="submit_chat" onclick="window.open('posting.php?mode=smilies', '_phpbbsmilies', 'height=540, resizable=yes, scrollbars=yes ,width=620, left=360, top=60'); return false;">&nbsp;:)&nbsp;</span>
                        <?php if (!empty($V['IS_AM'])) { ?><span title="Удалялка" id="submit_chat" onclick="del_message_chat();">&#8224;</span><?php } ?>
                        <img title="Обновить чат" onclick="get_message_chat(1);" src="/styles/images/pic_loading.gif">
                    </div>
                    <div class="clear"></div>
                    <div class="spacer_2"></div>
                    <div class="tLeft w100" id="chat">
                        <?php

$chat_count = ( isset($this->_tpldata['chat.']) ) ?  sizeof($this->_tpldata['chat.']) : 0;
for ($chat_i = 0; $chat_i < $chat_count; $chat_i++)
{
 $chat_item = &$this->_tpldata['chat.'][$chat_i];
 $chat_item['S_ROW_COUNT'] = $chat_i;
 $chat_item['S_NUM_ROWS'] = $chat_count;

?><?php echo isset($chat_item['TEXT']) ? $chat_item['TEXT'] : ''; ?><?php

} // END chat

if(isset($chat_item)) { unset($chat_item); } 

?>
                    </div>
                </form>
            </div>
        </div>
        <div class="cat_separator"></div>
        <?php } ?>
		</center></table>
<div id="forums_top_links">
	<div class="floatL">
		<a href="<?php echo isset($V['U_SEARCH_LATEST']) ? $V['U_SEARCH_LATEST'] : ''; ?>" class="med"><?php echo isset($L['SEARCH_LATEST']) ? $L['SEARCH_LATEST'] : (isset($SL['SEARCH_LATEST']) ? $SL['SEARCH_LATEST'] : $V['L_SEARCH_LATEST']); ?></a> &#0183;
		<a href="<?php echo isset($V['U_SEARCH_SELF_BY_LAST']) ? $V['U_SEARCH_SELF_BY_LAST'] : ''; ?>" class="med"><?php echo isset($L['SEARCH_SELF']) ? $L['SEARCH_SELF'] : (isset($SL['SEARCH_SELF']) ? $SL['SEARCH_SELF'] : $V['L_SEARCH_SELF']); ?></a> <a href="#search-my-posts" class="menu-root menu-alt1"><?php echo isset($V['OPEN_MENU_IMG_ALT']) ? $V['OPEN_MENU_IMG_ALT'] : ''; ?></a> &#0183;
        <?php if (!empty($V['U_ATOM_FEED'])) { ?><a href="<?php echo isset($V['U_ATOM_FEED']) ? $V['U_ATOM_FEED'] : ''; ?>" class="med"><?php echo isset($V['FEED_IMG']) ? $V['FEED_IMG'] : ''; ?> <?php echo isset($L['LATEST_RELEASES']) ? $L['LATEST_RELEASES'] : (isset($SL['LATEST_RELEASES']) ? $SL['LATEST_RELEASES'] : $V['L_LATEST_RELEASES']); ?></a> &#0183;<?php } ?>
		<a href="<?php echo isset($V['U_INDEX']) ? $V['U_INDEX'] : ''; ?>?map=1" class="med bold"><?php echo isset($V['FEED_IMG']) ? $V['FEED_IMG'] : ''; ?> <?php echo isset($L['FORUM_MAP']) ? $L['FORUM_MAP'] : (isset($SL['FORUM_MAP']) ? $SL['FORUM_MAP'] : $V['L_FORUM_MAP']); ?></a>
	</div>
	<div class="floatR med bold">
	<?php if (!empty($V['LAST_ADDED'])) { ?><a class="menu-root" href="#hi-poster">Постеры</a> &middot; <?php } ?> 
		<a class="menu-root" href="#only-new-options"><?php echo isset($L['DISPLAYING_OPTIONS']) ? $L['DISPLAYING_OPTIONS'] : (isset($SL['DISPLAYING_OPTIONS']) ? $SL['DISPLAYING_OPTIONS'] : $V['L_DISPLAYING_OPTIONS']); ?></a>
	</div>
	<div class="clear"></div>
</div><!--/forums_top_links-->

<div class="menu-sub" id="search-my-posts">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th><?php echo isset($L['SEARCH_SELF']) ? $L['SEARCH_SELF'] : (isset($SL['SEARCH_SELF']) ? $SL['SEARCH_SELF'] : $V['L_SEARCH_SELF']); ?></th>
	</tr>
	<tr>
		<td>
			<fieldset id="search-my">
			<legend><?php echo isset($L['SORT_BY']) ? $L['SORT_BY'] : (isset($SL['SORT_BY']) ? $SL['SORT_BY'] : $V['L_SORT_BY']); ?></legend>
			<div class="bold nowrap pad_2">
				<p class="mrg_4"><a class="med" href="<?php echo isset($V['U_SEARCH_SELF_BY_LAST']) ? $V['U_SEARCH_SELF_BY_LAST'] : ''; ?>"><?php echo isset($L['SEARCH_SELF_BY_LAST']) ? $L['SEARCH_SELF_BY_LAST'] : (isset($SL['SEARCH_SELF_BY_LAST']) ? $SL['SEARCH_SELF_BY_LAST'] : $V['L_SEARCH_SELF_BY_LAST']); ?></a></p>
				<p class="mrg_4"><a class="med" href="<?php echo isset($V['U_SEARCH_SELF_BY_MY']) ? $V['U_SEARCH_SELF_BY_MY'] : ''; ?>"><?php echo isset($L['SEARCH_SELF_BY_MY']) ? $L['SEARCH_SELF_BY_MY'] : (isset($SL['SEARCH_SELF_BY_MY']) ? $SL['SEARCH_SELF_BY_MY'] : $V['L_SEARCH_SELF_BY_MY']); ?></a></p>
			</div>
			</fieldset>
		</td>
	</tr>
	</table>
</div><!--/search-my-posts-->
<?php } ?>

<img width="540" class="spacer" src="<?php echo isset($V['SPACER']) ? $V['SPACER'] : ''; ?>" alt="" />

<div id="forums_wrap">

<?php if (!empty($V['H_C_AL_MESS'])) { ?>
<div class="row1 med tCenter pad_4 border bw_TRBL" style="margin: 4px 0;"><?php echo isset($L['HIDE_CAT_MESS']) ? $L['HIDE_CAT_MESS'] : (isset($SL['HIDE_CAT_MESS']) ? $SL['HIDE_CAT_MESS'] : $V['L_HIDE_CAT_MESS']); ?> &middot; <a href="<?php echo isset($V['U_INDEX']) ? $V['U_INDEX'] : ''; ?>?sh=1"><?php echo isset($L['SHOW_ALL']) ? $L['SHOW_ALL'] : (isset($SL['SHOW_ALL']) ? $SL['SHOW_ALL'] : $V['L_SHOW_ALL']); ?></a></div>
<div class="spacer_2"></div>
<?php } ?>

<?php if (!empty($V['SHOW_FORUMS'])) { ?>

<?php if (!empty($V['SHOW_MAP'])) { ?>
	<?php  $this->set_filename('xs_include_2bb0dfd158b329521a07057db0e7e8fa', 'index_map.tpl', true);  $this->pparse('xs_include_2bb0dfd158b329521a07057db0e7e8fa');  ?>
<?php } else { ?>

<?php

$c_count = ( isset($this->_tpldata['c.']) ) ?  sizeof($this->_tpldata['c.']) : 0;
for ($c_i = 0; $c_i < $c_count; $c_i++)
{
 $c_item = &$this->_tpldata['c.'][$c_i];
 $c_item['S_ROW_COUNT'] = $c_i;
 $c_item['S_NUM_ROWS'] = $c_count;

?>
<div class="category">
	<h3 class="cat_title"><a href="<?php echo isset($c_item['U_VIEWCAT']) ? $c_item['U_VIEWCAT'] : ''; ?>"><?php echo isset($c_item['CAT_TITLE']) ? $c_item['CAT_TITLE'] : ''; ?></a></h3>
	<div class="f_tbl_wrap">

		<table class="forums">
		<thead>
		<tr class="row3">
			<th class="f_icon">&nbsp;</th>
			<th class="f_titles"><?php echo isset($L['FORUM']) ? $L['FORUM'] : (isset($SL['FORUM']) ? $SL['FORUM'] : $V['L_FORUM']); ?></th>
			<th class="f_topics"><?php echo isset($L['TOPICS_SHORT']) ? $L['TOPICS_SHORT'] : (isset($SL['TOPICS_SHORT']) ? $SL['TOPICS_SHORT'] : $V['L_TOPICS_SHORT']); ?></th>
			<th class="f_posts"><?php echo isset($L['POSTS_SHORT']) ? $L['POSTS_SHORT'] : (isset($SL['POSTS_SHORT']) ? $SL['POSTS_SHORT'] : $V['L_POSTS_SHORT']); ?></th>
			<th class="f_last_post last_td"><?php echo isset($L['LASTPOST']) ? $L['LASTPOST'] : (isset($SL['LASTPOST']) ? $SL['LASTPOST'] : $V['L_LASTPOST']); ?></th>
		</tr>
		</thead>

		<tbody>
		<?php

$f_count = ( isset($c_item['f.']) ) ? sizeof($c_item['f.']) : 0;
for ($f_i = 0; $f_i < $f_count; $f_i++)
{
 $f_item = &$c_item['f.'][$f_i];
 $f_item['S_ROW_COUNT'] = $f_i;
 $f_item['S_NUM_ROWS'] = $f_count;

?>
		<tr>
			<td class="row1 f_icon">
			<a href="search.php?f=<?php echo isset($f_item['FORUM_ID']) ? $f_item['FORUM_ID'] : ''; ?>&amp;new=1&amp;dm=1&amp;s=0&amp;o=1"><img class="forum_icon" src="<?php echo isset($f_item['FORUM_FOLDER_IMG']) ? $f_item['FORUM_FOLDER_IMG'] : ''; ?>" alt="<?php echo isset($f_item['FORUM_FOLDER_ALT']) ? $f_item['FORUM_FOLDER_ALT'] : ''; ?>" /></a>
			</td>
			<td class="row1 f_titles">

				<h4 class="forumlink"><a href="<?php echo isset($V['FORUM_URL']) ? $V['FORUM_URL'] : ''; ?><?php echo isset($f_item['FORUM_ID']) ? $f_item['FORUM_ID'] : ''; ?>"><?php echo isset($f_item['FORUM_NAME']) ? $f_item['FORUM_NAME'] : ''; ?></a></h4>

				<?php if ($f_item['FORUM_DESC']) { ?>
				<p class="forum_desc"><?php echo isset($f_item['FORUM_DESC']) ? $f_item['FORUM_DESC'] : ''; ?></p>
				<?php } ?>

				<?php if ($f_item['LAST_SF_ID']) { ?>
				<p class="subforums">
					<em><?php echo isset($L['SUBFORUMS']) ? $L['SUBFORUMS'] : (isset($SL['SUBFORUMS']) ? $SL['SUBFORUMS'] : $V['L_SUBFORUMS']); ?>:</em>
					<?php

$sf_count = ( isset($f_item['sf.']) ) ? sizeof($f_item['sf.']) : 0;
for ($sf_i = 0; $sf_i < $sf_count; $sf_i++)
{
 $sf_item = &$f_item['sf.'][$sf_i];
 $sf_item['S_ROW_COUNT'] = $sf_i;
 $sf_item['S_NUM_ROWS'] = $sf_count;

?>
					<span class="sf_title<?php echo isset($sf_item['SF_NEW']) ? $sf_item['SF_NEW'] : ''; ?>"><a href="search.php?f=<?php echo isset($sf_item['SF_ID']) ? $sf_item['SF_ID'] : ''; ?>&amp;new=1&amp;dm=1&amp;s=0&amp;o=1" class="dot-sf">&#9658;</a><a href="<?php echo isset($V['FORUM_URL']) ? $V['FORUM_URL'] : ''; ?><?php echo isset($sf_item['SF_ID']) ? $sf_item['SF_ID'] : ''; ?>" class="dot-sf"><?php echo isset($sf_item['SF_NAME']) ? $sf_item['SF_NAME'] : ''; ?></a></span><span class="sf_separator"></span>
					<?php

} // END sf

if(isset($sf_item)) { unset($sf_item); } 

?>
				</p>
				<?php } ?>

				<?php if ($f_item['MODERATORS'] && $V['SHOW_MOD_INDEX']) { ?>
				<p class="moderators"><em><?php echo isset($L['MODERATORS']) ? $L['MODERATORS'] : (isset($SL['MODERATORS']) ? $SL['MODERATORS'] : $V['L_MODERATORS']); ?>:</em> <?php echo isset($f_item['MODERATORS']) ? $f_item['MODERATORS'] : ''; ?></p>
				<?php } ?>

			</td>
			<td class="row2 f_topics"><?php echo isset($f_item['TOPICS']) ? $f_item['TOPICS'] : ''; ?></td>
			<td class="row2 f_posts"><?php echo isset($f_item['POSTS']) ? $f_item['POSTS'] : ''; ?></td>
			<td class="row2 f_last_post last_td">

			<?php if ($f_item['POSTS']) { ?>
				<?php

$last_count = ( isset($f_item['last.']) ) ? sizeof($f_item['last.']) : 0;
for ($last_i = 0; $last_i < $last_count; $last_i++)
{
 $last_item = &$f_item['last.'][$last_i];
 $last_item['S_ROW_COUNT'] = $last_i;
 $last_item['S_NUM_ROWS'] = $last_count;

?>
					<?php if (!empty($V['SHOW_LAST_TOPIC'])) { ?>
					<h6 class="last_topic">
						<a href="<?php echo isset($V['TOPIC_URL']) ? $V['TOPIC_URL'] : ''; ?><?php echo isset($last_item['LAST_TOPIC_ID']) ? $last_item['LAST_TOPIC_ID'] : ''; ?><?php echo isset($V['NEWEST_URL']) ? $V['NEWEST_URL'] : ''; ?>" title="<?php echo isset($last_item['LAST_TOPIC_TIP']) ? $last_item['LAST_TOPIC_TIP'] : ''; ?>"><?php echo isset($last_item['LAST_TOPIC_TITLE']) ? $last_item['LAST_TOPIC_TITLE'] : ''; ?></a>
					</h6>
					<?php } ?>

					<p class="last_post_time">
						<span class="last_time"><?php echo isset($last_item['LAST_POST_TIME']) ? $last_item['LAST_POST_TIME'] : ''; ?></span>
						<span class="last_author">&middot;
							<?php echo isset($last_item['LAST_POST_USER']) ? $last_item['LAST_POST_USER'] : ''; ?>
						</span>
					</p>
				<?php

} // END last

if(isset($last_item)) { unset($last_item); } 

?>

			<?php } else { ?>
				<?php echo isset($L['NO_POSTS']) ? $L['NO_POSTS'] : (isset($SL['NO_POSTS']) ? $SL['NO_POSTS'] : $V['L_NO_POSTS']); ?>
			<?php } ?>

			</td>
		</tr>
		<?php

} // END f

if(isset($f_item)) { unset($f_item); } 

?>
		</tbody>
		</table>
	</div><!--/f_tbl_wrap-->
</div><!--/category-->
<div class="cat_footer"></div>
<div class="cat_separator"></div>
<?php

} // END c

if(isset($c_item)) { unset($c_item); } 

?>

<?php } ?>

<?php } else { ?>

<table class="forumline">
	<tr><td class="row1 tCenter pad_8"><?php echo isset($V['NO_FORUMS_MSG']) ? $V['NO_FORUMS_MSG'] : ''; ?></td></tr>
</table>
<div class="spacer_6"></div>

<?php } ?>

</div><!--/forums_wrap-->

<div id="forums_footer"></div>

<?php if ($V['LOGGED_IN'] && $V['SHOW_FORUMS']) { ?>
<div id="mark_all_forums_read">
	<a href="<?php echo isset($V['U_SEARCH_NEW']) ? $V['U_SEARCH_NEW'] : ''; ?>" class="med"><?php echo isset($L['SEARCH_NEW']) ? $L['SEARCH_NEW'] : (isset($SL['SEARCH_NEW']) ? $SL['SEARCH_NEW'] : $V['L_SEARCH_NEW']); ?></a> &#0183;
	<a href="<?php echo isset($V['U_INDEX']) ? $V['U_INDEX'] : ''; ?>" class="med" onclick="setCookie('<?php echo defined('COOKIE_MARK') ? COOKIE_MARK : ''; ?>', 'all_forums');"><?php echo isset($L['MARK_ALL_FORUMS_READ']) ? $L['MARK_ALL_FORUMS_READ'] : (isset($SL['MARK_ALL_FORUMS_READ']) ? $SL['MARK_ALL_FORUMS_READ'] : $V['L_MARK_ALL_FORUMS_READ']); ?></a>
</div>
<?php } ?>

<div id="board_stats">
	<h3 class="cat_title"><?php echo isset($L['STATISTICS']) ? $L['STATISTICS'] : (isset($SL['STATISTICS']) ? $SL['STATISTICS'] : $V['L_STATISTICS']); ?></h3>
	<div id="board_stats_wrap">

	<table class="forums">
	<tr>
		<td class="row1 f_icon"><img class="forum_icon" src="<?php echo isset($V['IMG']) ? $V['IMG'] : ''; ?>whosonline.gif" alt="" /></td>
		<td class="row1 small last_td">
			<div class="med" style="line-height: 16px">
				<p><?php echo isset($V['TOTAL_TOPICS']) ? $V['TOTAL_TOPICS'] : ''; ?></p>
				<p><?php echo isset($V['TOTAL_POSTS']) ? $V['TOTAL_POSTS'] : ''; ?></p>
				<p><?php echo isset($V['TOTAL_USERS']) ? $V['TOTAL_USERS'] : ''; ?></p>
				<p><?php echo isset($V['TOTAL_GENDER']) ? $V['TOTAL_GENDER'] : ''; ?></p>
				<p><?php echo isset($V['NEWEST_USER']) ? $V['NEWEST_USER'] : ''; ?></p>

                <?php if (!empty($V['BOARD_START'])) { ?>
                <p style="margin-top: 4px;"><?php echo isset($V['BOARD_START']) ? $V['BOARD_START'] : ''; ?></p>
                <?php } ?>

				<?php if (!empty($bb_cfg['tor_stats'])) { ?>
				<div class="hr1" style="margin: 5px 0 4px;"></div>
				<p><?php echo isset($V['TORRENTS_STAT']) ? $V['TORRENTS_STAT'] : ''; ?></p>
				<p><?php echo isset($V['PEERS_STAT']) ? $V['PEERS_STAT'] : ''; ?></p>
				<p><?php echo isset($V['SPEED_STAT']) ? $V['SPEED_STAT'] : ''; ?></p>
				<?php } ?>

				<?php if (!empty($bb_cfg['birthday_enabled'])) { ?>
				<script type="text/javascript">
				ajax.callback.index_data = function(data) {
					$('#'+ data.mode).html(data.html);
				};
				</script>
				<div class="hr1" style="margin: 5px 0 4px;"></div>
				<p id="birthday_today" class="birthday"><?php echo isset($V['WHOSBIRTHDAY_TODAY']) ? $V['WHOSBIRTHDAY_TODAY'] : ''; ?></p>
				<p id="birthday_week" class="birthday"><?php echo isset($V['WHOSBIRTHDAY_WEEK']) ? $V['WHOSBIRTHDAY_WEEK'] : ''; ?></p>
				<?php } ?>

				<div class="hr1" style="margin: 5px 0 4px;"></div>

				<p><?php echo isset($V['TOTAL_USERS_ONLINE']) ? $V['TOTAL_USERS_ONLINE'] : ''; ?><?php if (!empty($V['IS_ADMIN'])) { ?> &nbsp;<?php echo isset($V['USERS_ONLINE_COUNTS']) ? $V['USERS_ONLINE_COUNTS'] : ''; ?><?php } ?></p>
				<p><?php echo isset($V['RECORD_USERS']) ? $V['RECORD_USERS'] : ''; ?></p>

				<?php if (!empty($V['SHOW_ONLINE_LIST'])) { ?>
					<style><?php if (!empty($V['IS_ADMIN'])) { ?>.colorISL, a.colorISL, a.colorISL:visited { color: #793D00; }<?php } else { ?>.ou_stat { display: none; }<?php } ?></style>
					<a name="online"></a>
					<div id="online_userlist" style="margin-top: 4px;"><?php echo isset($V['LOGGED_IN_USER_LIST']) ? $V['LOGGED_IN_USER_LIST'] : ''; ?></div>

					<div class="hr1" style="margin: 5px 0 4px;"></div>

					<p id="online_time"><?php echo isset($L['ONLINE_EXPLAIN']) ? $L['ONLINE_EXPLAIN'] : (isset($SL['ONLINE_EXPLAIN']) ? $SL['ONLINE_EXPLAIN'] : $V['L_ONLINE_EXPLAIN']); ?></p>
					<p id="online_explain">
						[ <span class="colorAdmin"><b><?php echo isset($L['ONLINE_ADMIN']) ? $L['ONLINE_ADMIN'] : (isset($SL['ONLINE_ADMIN']) ? $SL['ONLINE_ADMIN'] : $V['L_ONLINE_ADMIN']); ?></b></span> ]
						[ <span class="colorMod"><b><?php echo isset($L['ONLINE_MOD']) ? $L['ONLINE_MOD'] : (isset($SL['ONLINE_MOD']) ? $SL['ONLINE_MOD'] : $V['L_ONLINE_MOD']); ?></b></span> ]
						[ <span class="colorGroup"><b><?php echo isset($L['ONLINE_GROUP_MEMBER']) ? $L['ONLINE_GROUP_MEMBER'] : (isset($SL['ONLINE_GROUP_MEMBER']) ? $SL['ONLINE_GROUP_MEMBER'] : $V['L_ONLINE_GROUP_MEMBER']); ?></b></span> ]
					</p>
				<?php } ?>
			</div>
		</td>
	</tr>
	</table>
	</div><!--/board_stats_wrap-->
</div><!--/board_stats-->
<div class="cat_footer"></div>

<div class="spacer_4"></div>

<!--bottom_info-->
<div class="bottom_info">

	<div id="timezone">
		<p><?php echo isset($V['CURRENT_TIME']) ? $V['CURRENT_TIME'] : ''; ?></p>
		<p><?php echo isset($V['S_TIMEZONE']) ? $V['S_TIMEZONE'] : ''; ?></p>
	</div>
	<div class="clear"></div>

	<table class="bCenter med" id="f_icons_legend">
	<tr>
		<td><img class="forum_icon" src="<?php echo isset($V['IMG']) ? $V['IMG'] : ''; ?>folder_new_big.gif" alt="<?php echo isset($L['NEW']) ? $L['NEW'] : (isset($SL['NEW']) ? $SL['NEW'] : $V['L_NEW']); ?>"/></td>
		<td><?php echo isset($L['NEW_POSTS']) ? $L['NEW_POSTS'] : (isset($SL['NEW_POSTS']) ? $SL['NEW_POSTS'] : $V['L_NEW_POSTS']); ?></td>
		<td><img class="forum_icon" src="<?php echo isset($V['IMG']) ? $V['IMG'] : ''; ?>folder_big.gif" alt="<?php echo isset($L['OLD']) ? $L['OLD'] : (isset($SL['OLD']) ? $SL['OLD'] : $V['L_OLD']); ?>" /></td>
		<td><?php echo isset($L['NO_NEW_POSTS']) ? $L['NO_NEW_POSTS'] : (isset($SL['NO_NEW_POSTS']) ? $SL['NO_NEW_POSTS'] : $V['L_NO_NEW_POSTS']); ?></td>
		<td><img class="forum_icon" src="<?php echo isset($V['IMG']) ? $V['IMG'] : ''; ?>folder_locked_big.gif" alt="<?php echo isset($L['FORUM_LOCKED_MAIN']) ? $L['FORUM_LOCKED_MAIN'] : (isset($SL['FORUM_LOCKED_MAIN']) ? $SL['FORUM_LOCKED_MAIN'] : $V['L_FORUM_LOCKED_MAIN']); ?>" /></td>
		<td><?php echo isset($L['FORUM_LOCKED_MAIN']) ? $L['FORUM_LOCKED_MAIN'] : (isset($SL['FORUM_LOCKED_MAIN']) ? $SL['FORUM_LOCKED_MAIN'] : $V['L_FORUM_LOCKED_MAIN']); ?></td>
	</tr>
	</table>

</div><!--/bottom_info-->

</div><!--/forums_list_wrap-->
