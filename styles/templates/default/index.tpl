<!-- IF TORHELP_TOPICS -->
	<!-- INCLUDE torhelp.tpl -->
	<div class="spacer_6"></div>
<!-- ENDIF -->

<div id="forums_list_wrap">

<div id="forums_top_nav">
	<h1 class="pagetitle"><a href="{U_INDEX}">{T_INDEX}</a></h1>
</div><!--/forums_top_nav-->
<table border="0" cellpadding="7" cellspacing="0" width="100%">
<center>
<!-- IF LOGGED_IN -->
<!-- IF $bb_cfg['chat'] -->
            <script type="text/javascript">
                var id = {CHAT_ID};
                ajax.callback.chat = function(data) {
                    if(data.up) $('#chat').scrollTop(0);
                    if(data.clear) {
                        $('.chat_message').attr('value', '');
                        get_message_chat(1);
                    }
                    <!-- IF IS_AM -->
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
                    <!-- ENDIF -->
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
            <!-- IF IS_AM -->
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
            <!-- ENDIF -->
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
                        <input type="button" value="B" name="codeB" title="{L_BOLD}" style="font-weight: bold; width: 30px;" />
                        <input type="button" value="i" name="codeI" title="{L_ITALIC}" style="width: 30px; font-style: italic;" />
                        <input type="button" value="u" name="codeU" title="{L_UNDERLINE}" style="width: 30px; text-decoration: underline;" />
                        <input type="button" value="s" name="codeS" title="{L_STRIKEOUT}" style="width: 30px; text-decoration: line-through;" />&nbsp;&nbsp;
                        <input type="button" value="{L_QUOTE}" name="codeQuote" title="{L_QUOTE_TITLE}" style="width: 57px;" />
                        <input type="button" value="Img" name="codeImg" title="{L_IMG_TITLE}" style="width: 40px;" />
                        <input type="button" value="{L_URL}" name="codeUrl" title="{L_URL_TITLE}" style="width: 63px; text-decoration: underline;" />
                        <input type="hidden" name="codeUrl2" />&nbsp;
                        <input type="button" value="{L_CODE}" name="codeCode" title="{L_CODE_TITLE}" style="width: 43px;" />
                        <input type="button" value="{L_LIST}" name="codeList" title="{L_LIST_TITLE}" style="width: 60px;" />
                        <input type="button" value="1." name="codeOpt" title="{L_LIST_ITEM}" style="width: 30px;" />&nbsp;
                        <input type="button" value="{L_QUOTE_SEL}" name="quoteselected" title="{L_QUOTE_SELECTED}" onmouseout="bbcode.refreshSelection(false);" onmouseover="bbcode.refreshSelection(true);" onclick="bbcode.onclickQuoteSel();" />&nbsp;
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
                        <!-- IF IS_AM --><span title="Удалялка" id="submit_chat" onclick="del_message_chat();">&#8224;</span><!-- ENDIF -->
                        <img title="Обновить чат" onclick="get_message_chat(1);" src="/styles/images/pic_loading.gif">
                    </div>
                    <div class="clear"></div>
                    <div class="spacer_2"></div>
                    <div class="tLeft w100" id="chat">
                        <!-- BEGIN chat -->{chat.TEXT}<!-- END chat -->
                    </div>
                </form>
            </div>
        </div>
        <div class="cat_separator"></div>
        <!-- ENDIF -->
		</center></table>
<div id="forums_top_links">
	<div class="floatL">
		<a href="{U_SEARCH_LATEST}" class="med">{L_SEARCH_LATEST}</a> &#0183;
		<a href="{U_SEARCH_SELF_BY_LAST}" class="med">{L_SEARCH_SELF}</a> <a href="#search-my-posts" class="menu-root menu-alt1">{OPEN_MENU_IMG_ALT}</a> &#0183;
        <!-- IF U_ATOM_FEED --><a href="{U_ATOM_FEED}" class="med">{FEED_IMG} {L_LATEST_RELEASES}</a> &#0183;<!-- ENDIF -->
		<a href="{U_INDEX}?map=1" class="med bold">{FEED_IMG} {L_FORUM_MAP}</a>
	</div>
	<div class="floatR med bold">
	<!-- IF LAST_ADDED --><a class="menu-root" href="#hi-poster">Постеры</a> &middot; <!-- ENDIF --> 
		<a class="menu-root" href="#only-new-options">{L_DISPLAYING_OPTIONS}</a>
	</div>
	<div class="clear"></div>
</div><!--/forums_top_links-->

<div class="menu-sub" id="search-my-posts">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th>{L_SEARCH_SELF}</th>
	</tr>
	<tr>
		<td>
			<fieldset id="search-my">
			<legend>{L_SORT_BY}</legend>
			<div class="bold nowrap pad_2">
				<p class="mrg_4"><a class="med" href="{U_SEARCH_SELF_BY_LAST}">{L_SEARCH_SELF_BY_LAST}</a></p>
				<p class="mrg_4"><a class="med" href="{U_SEARCH_SELF_BY_MY}">{L_SEARCH_SELF_BY_MY}</a></p>
			</div>
			</fieldset>
		</td>
	</tr>
	</table>
</div><!--/search-my-posts-->
<!-- ENDIF -->

<img width="540" class="spacer" src="{SPACER}" alt="" />

<div id="forums_wrap">

<!-- IF H_C_AL_MESS -->
<div class="row1 med tCenter pad_4 border bw_TRBL" style="margin: 4px 0;">{L_HIDE_CAT_MESS} &middot; <a href="{U_INDEX}?sh=1">{L_SHOW_ALL}</a></div>
<div class="spacer_2"></div>
<!-- ENDIF -->

<!-- IF SHOW_FORUMS -->

<!-- IF SHOW_MAP -->
	<!-- INCLUDE index_map.tpl -->
<!-- ELSE -->

<!-- BEGIN c -->
<div class="category">
	<h3 class="cat_title"><a href="{c.U_VIEWCAT}">{c.CAT_TITLE}</a></h3>
	<div class="f_tbl_wrap">

		<table class="forums">
		<thead>
		<tr class="row3">
			<th class="f_icon">&nbsp;</th>
			<th class="f_titles">{L_FORUM}</th>
			<th class="f_topics">{L_TOPICS_SHORT}</th>
			<th class="f_posts">{L_POSTS_SHORT}</th>
			<th class="f_last_post last_td">{L_LASTPOST}</th>
		</tr>
		</thead>

		<tbody>
		<!-- BEGIN f -->
		<tr>
			<td class="row1 f_icon">
			<a href="search.php?f={c.f.FORUM_ID}&amp;new=1&amp;dm=1&amp;s=0&amp;o=1"><img class="forum_icon" src="{c.f.FORUM_FOLDER_IMG}" alt="{c.f.FORUM_FOLDER_ALT}" /></a>
			</td>
			<td class="row1 f_titles">

				<h4 class="forumlink"><a href="{FORUM_URL}{c.f.FORUM_ID}">{c.f.FORUM_NAME}</a></h4>

				<!-- IF c.f.FORUM_DESC -->
				<p class="forum_desc">{c.f.FORUM_DESC}</p>
				<!-- ENDIF -->

				<!-- IF c.f.LAST_SF_ID -->
				<p class="subforums">
					<em>{L_SUBFORUMS}:</em>
					<!-- BEGIN sf -->
					<span class="sf_title{c.f.sf.SF_NEW}"><a href="search.php?f={c.f.sf.SF_ID}&amp;new=1&amp;dm=1&amp;s=0&amp;o=1" class="dot-sf">&#9658;</a><a href="{FORUM_URL}{c.f.sf.SF_ID}" class="dot-sf">{c.f.sf.SF_NAME}</a></span><span class="sf_separator"></span>
					<!-- END sf -->
				</p>
				<!-- ENDIF -->

				<!-- IF c.f.MODERATORS && SHOW_MOD_INDEX -->
				<p class="moderators"><em>{L_MODERATORS}:</em> {c.f.MODERATORS}</p>
				<!-- ENDIF -->

			</td>
			<td class="row2 f_topics">{c.f.TOPICS}</td>
			<td class="row2 f_posts">{c.f.POSTS}</td>
			<td class="row2 f_last_post last_td">

			<!-- IF c.f.POSTS -->
				<!-- BEGIN last -->
					<!-- IF SHOW_LAST_TOPIC -->
					<h6 class="last_topic">
						<a href="{TOPIC_URL}{c.f.last.LAST_TOPIC_ID}{NEWEST_URL}" title="{c.f.last.LAST_TOPIC_TIP}">{c.f.last.LAST_TOPIC_TITLE}</a>
					</h6>
					<!-- ENDIF / SHOW_LAST_TOPIC -->

					<p class="last_post_time">
						<span class="last_time">{c.f.last.LAST_POST_TIME}</span>
						<span class="last_author">&middot;
							{c.f.last.LAST_POST_USER}
						</span>
					</p>
				<!-- END last -->

			<!-- ELSE / start of !c.f.POSTS -->
				{L_NO_POSTS}
			<!-- ENDIF -->

			</td>
		</tr>
		<!-- END f -->
		</tbody>
		</table>
	</div><!--/f_tbl_wrap-->
</div><!--/category-->
<div class="cat_footer"></div>
<div class="cat_separator"></div>
<!-- END c -->

<!-- ENDIF / SHOW_MAP -->

<!-- ELSE / SHOW_FORUMS -->

<table class="forumline">
	<tr><td class="row1 tCenter pad_8">{NO_FORUMS_MSG}</td></tr>
</table>
<div class="spacer_6"></div>

<!-- ENDIF -->

</div><!--/forums_wrap-->

<div id="forums_footer"></div>

<!-- IF LOGGED_IN and SHOW_FORUMS -->
<div id="mark_all_forums_read">
	<a href="{U_SEARCH_NEW}" class="med">{L_SEARCH_NEW}</a> &#0183;
	<a href="{U_INDEX}" class="med" onclick="setCookie('{#COOKIE_MARK#}', 'all_forums');">{L_MARK_ALL_FORUMS_READ}</a>
</div>
<!-- ENDIF -->

<div id="board_stats">
	<h3 class="cat_title">{L_STATISTICS}</h3>
	<div id="board_stats_wrap">

	<table class="forums">
	<tr>
		<td class="row1 f_icon"><img class="forum_icon" src="{IMG}whosonline.gif" alt="" /></td>
		<td class="row1 small last_td">
			<div class="med" style="line-height: 16px">
				<p>{TOTAL_TOPICS}</p>
				<p>{TOTAL_POSTS}</p>
				<p>{TOTAL_USERS}</p>
				<p>{TOTAL_GENDER}</p>
				<p>{NEWEST_USER}</p>

                <!-- IF BOARD_START -->
                <p style="margin-top: 4px;">{BOARD_START}</p>
                <!-- ENDIF -->

				<!-- IF $bb_cfg['tor_stats'] -->
				<div class="hr1" style="margin: 5px 0 4px;"></div>
				<p>{TORRENTS_STAT}</p>
				<p>{PEERS_STAT}</p>
				<p>{SPEED_STAT}</p>
				<!-- ENDIF -->

				<!-- IF $bb_cfg['birthday_enabled'] -->
				<script type="text/javascript">
				ajax.callback.index_data = function(data) {
					$('#'+ data.mode).html(data.html);
				};
				</script>
				<div class="hr1" style="margin: 5px 0 4px;"></div>
				<p id="birthday_today" class="birthday">{WHOSBIRTHDAY_TODAY}</p>
				<p id="birthday_week" class="birthday">{WHOSBIRTHDAY_WEEK}</p>
				<!-- ENDIF -->

				<div class="hr1" style="margin: 5px 0 4px;"></div>

				<p>{TOTAL_USERS_ONLINE}<!-- IF IS_ADMIN --> &nbsp;{USERS_ONLINE_COUNTS}<!-- ENDIF --></p>
				<p>{RECORD_USERS}</p>

				<!-- IF SHOW_ONLINE_LIST -->
					<style><!-- IF IS_ADMIN -->.colorISL, a.colorISL, a.colorISL:visited { color: #793D00; }<!-- ELSE -->.ou_stat { display: none; }<!-- ENDIF --></style>
					<a name="online"></a>
					<div id="online_userlist" style="margin-top: 4px;">{LOGGED_IN_USER_LIST}</div>

					<div class="hr1" style="margin: 5px 0 4px;"></div>

					<p id="online_time">{L_ONLINE_EXPLAIN}</p>
					<p id="online_explain">
						[ <span class="colorAdmin"><b>{L_ONLINE_ADMIN}</b></span> ]
						[ <span class="colorMod"><b>{L_ONLINE_MOD}</b></span> ]
						[ <span class="colorGroup"><b>{L_ONLINE_GROUP_MEMBER}</b></span> ]
					</p>
				<!-- ENDIF -->
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
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

	<table class="bCenter med" id="f_icons_legend">
	<tr>
		<td><img class="forum_icon" src="{IMG}folder_new_big.gif" alt="{L_NEW}"/></td>
		<td>{L_NEW_POSTS}</td>
		<td><img class="forum_icon" src="{IMG}folder_big.gif" alt="{L_OLD}" /></td>
		<td>{L_NO_NEW_POSTS}</td>
		<td><img class="forum_icon" src="{IMG}folder_locked_big.gif" alt="{L_FORUM_LOCKED_MAIN}" /></td>
		<td>{L_FORUM_LOCKED_MAIN}</td>
	</tr>
	</table>

</div><!--/bottom_info-->

</div><!--/forums_list_wrap-->
