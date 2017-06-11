<!-- IF TPL_SEARCH_MAIN -->
<!--========================================================================-->

<a name="start"></a>
<h1 class="pagetitle">{PAGE_TITLE}</h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<form action="{SEARCH_ACTION}" method="POST" name="post">
{S_HIDDEN_FIELDS}

<table class="forumline fieldsets">
<tr>
	<th colspan="2">{L_SEARCH_OPTIONS}</th>
</tr>
<tr>
	<td class="row1" width="50%">
		<fieldset>
		<legend>{L_SEARCH_WORDS}<!-- IF $bb_cfg['search_help_url'] --> <a href="{$bb_cfg['search_help_url']}">[?]</a><!-- ENDIF --></legend>
		<div>
			<p class="input"><input id="text_match_input" type="text" style="width: 95%" class="post" name="{TEXT_MATCH_KEY}" size="30" /></p>
			<p class="chbox med">{TITLE_ONLY_CHBOX}&nbsp;&nbsp;{ALL_WORDS_CHBOX}</p>
		</div>
		</fieldset>
	</td>
	<td class="row1" width="50%">
		<fieldset>
		<legend>{L_SEARCH_AUTHOR}</legend>
		<div>
			<p class="input">
				<input style="width: 50%" id="author" type="text" class="post" name="{POSTER_NAME_KEY}" />&nbsp;
				<input style="width: 40%" type="button" value="{L_FIND_USERNAME}" onclick="window.open('{U_SEARCH_USER}', '_bbsearch', IWP_US); return false;" />
			</p>
			<p class="chbox med">
				<label>
				<input type="checkbox"
					onclick="
						toggle_disabled('author', !this.checked);
						toggle_disabled('{MY_TOPICS_ID}', this.checked);
						if (this.checked) { $p('author').value = '{THIS_USER_NAME}'; }
						else { $p('author').value = ''; $p('{MY_TOPICS_ID}').checked = 0; };
					"
					name="{POSTER_ID_KEY}" value="{THIS_USER_ID}" />
				{L_IN_MY_POSTS}
				</label>
				{MY_TOPICS_CHBOX}
			</p>
		</div>
		</fieldset>
	</td>
</tr>
<tr>
	<td colspan="2" class="row2 med pad_4 wrap">
		<p class="med pad_2">{L_SEARCH_WORDS_EXPL}</p>
	</td>
</tr>
<tr>
	<td class="row1">
		<fieldset>
		<legend>{L_FORUM}:</legend>
		<div>
			<p class="select">{FORUM_SELECT}</p>
		</div>
		</fieldset>
	</td>
	<td class="row1">
		<fieldset>
		<legend>{L_MY_DOWNLOADS}</legend>
		<div>
			<table class="borderless my_downloads" cellspacing="0">
			<tr>
				<td>{DL_COMPL_CHBOX}</td>
				<td>{DL_WILL_CHBOX}</td>
			</tr>
			<tr>
				<td>{DL_DOWN_CHBOX}</td>
				<td>{DL_CANCEL_CHBOX}</td>
			</tr>
			</table>
		</div>
		</fieldset>
		<fieldset>
		<legend>{L_SEARCH_PREVIOUS}:</legend>
		<div class="med">
			<p class="select">{TIME_SELECT}</p>
			<p class="chbox">{ONLY_NEW_CHBOX}&nbsp; {NEW_TOPICS_CHBOX}</p>
		</div>
		</fieldset>
		<fieldset>
		<legend>{L_DISPLAY_RESULTS_AS}:</legend>
		<div>
			<p class="select">{DISPLAY_AS_SELECT}&nbsp;{CHARS_SELECT}</p>
		</div>
		</fieldset>
		<fieldset>
		<legend>{L_SORT_BY}:</legend>
		<div>
			<p class="select">{ORDER_SELECT}&nbsp;{SORT_SELECT}</p>
		</div>
		</fieldset>
	</td>
</tr>
<tr>
	<td class="catBottom pad_4" colspan="2">
		<input class="bold long" type="submit" name="submit" value="&nbsp;&nbsp;{L_SEARCH}&nbsp;&nbsp;" />
	</td>
</tr>
</table>

</form>

<div class="bottom_info">

	<div class="spacer_4"></div>

	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->

<!--========================================================================-->
<!-- ENDIF / TPL_SEARCH_MAIN -->

<!-- IF TPL_SEARCH_USERNAME -->
<!--========================================================================-->

<script type="text/javascript">
function refresh_username(selected_username)
{
	opener.document.forms['post'].{INPUT_NAME}.value = selected_username;
	opener.focus();
	window.close();
}
</script>

<form method="post" name="search" action="{SEARCH_ACTION}">

<table class="bordered search_username">
<tr>
	<th class="thHead">{L_FIND_USERNAME}</th>
</tr>
<tr>
	<td class="row1 pad_12">
		<p>
			<input type="text" name="search_username" value="{USERNAME}" class="post" />&nbsp;
			<input type="submit" name="search" value="{L_SEARCH}" class="liteoption" />
		</p>
		<p class="small">{L_SEARCH_AUTHOR_EXPL}</p>
		<!-- IF USERNAME_OPTIONS -->
		<h5 style="margin: 1em 0 0.4em;">{L_SELECT_USERNAME}</h5>
		<p>
			<select name="username_list">{USERNAME_OPTIONS}</select>&nbsp;
			<input type="submit" class="main" onclick="refresh_username(this.form.username_list.options[this.form.username_list.selectedIndex].value);return false;" name="use" value="{L_SELECT}" />
		</p>
		<!-- ENDIF -->
		<br />
		<a href="javascript:window.close();" class="med">{L_CLOSE_WINDOW}</a>
	</td>
</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_SEARCH_USERNAME -->
