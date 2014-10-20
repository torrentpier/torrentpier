<!-- IF QUICK_REPLY -->
<!-- ELSE -->
<script type="text/javascript">
ajax.callback.posts = function(data) {
	$('#view_message').show();
	$('.view-message').html(data.message_html);
	initPostBBCode('.view-message');
	var maxH = screen.height - 490;
	$('.view-message').css({maxHeight: maxH});
};
</script>
<div class="mrg_4" style="padding-left:2px;">
<select name="fontFace">
	<option style="font-family: Verdana" value="-1" selected="selected">{L_QR_FONT_SEL}:</option>
	<option style="font-family: monospace" value="monospace">&nbsp;Monospace</option>
	<option style="font-family: serif" value="serif">&nbsp;Serif</option>
	<option style="font-family: sans-serif" value="sans-serif">&nbsp;Sans Serif</option>
	<option style="font-family: cursive" value="cursive">&nbsp;Cursive</option>
	<option style="font-family: Arial" value="Arial">&nbsp;Arial</option>
	<option style="font-family: Tahoma" value="Tahoma">&nbsp;Tahoma</option>
	<option style="font-family: Georgia" value="Georgia">&nbsp;Georgia</option>
	<option style="font-family: Fixedsys" value="Fixedsys">&nbsp;Fixedsys</option>
</select>
&nbsp;
<select name="codeColor" class="text_color">
	<option style="color: black; background: #fff;" value="black" selected="selected">{L_QR_COLOR_SEL}:</option>
	<option style="color: darkred;" value="darkred">&nbsp;{L_COLOR_DARK_RED}</option>
	<option style="color: brown;" value="brown">&nbsp;{L_COLOR_BROWN}</option>
	<option style="color: #996600;" value="#996600">&nbsp;{L_COLOR_ORANGE}</option>
	<option style="color: red;" value="red">&nbsp;{L_COLOR_RED}</option>
	<option style="color: #993399;" value="#993399">&nbsp;{L_COLOR_VIOLET}</option>
	<option style="color: green;" value="green">&nbsp;{L_COLOR_GREEN}</option>
	<option style="color: darkgreen;" value="darkgreen">&nbsp;{L_COLOR_DARK_GREEN}</option>
	<option style="color: gray;" value="gray">&nbsp;{L_COLOR_GRAY}</option>
	<option style="color: olive;" value="olive">&nbsp;{L_COLOR_OLIVE}</option>
	<option style="color: blue;" value="blue">&nbsp;{L_COLOR_BLUE}</option>
	<option style="color: darkblue;" value="darkblue">&nbsp;{L_COLOR_DARK_BLUE}</option>
	<option style="color: indigo;" value="indigo">&nbsp;{L_COLOR_INDIGO}</option>
	<option style="color: #006699;" value="#006699">&nbsp;{L_COLOR_STEEL_BLUE}</option>
</select>
&nbsp;
<select name="codeSize" class="text_size">
	<option value="12" selected="selected">{L_QR_SIZE_SEL}:</option>
	<option value="9" class="em">{L_FONT_SMALL}</option>
	<option value="10">&nbsp;size=10</option>
	<option value="11">&nbsp;size=11</option>
	<option value="12" class="em" disabled="disabled">{L_FONT_NORMAL}</option>
	<option value="14">&nbsp;size=14</option>
	<option value="16">&nbsp;size=16</option>
	<option value="18" class="em">{L_FONT_LARGE}</option>
	<option value="20">&nbsp;size=20</option>
	<option value="22">&nbsp;size=22</option>
	<option value="24" class="em">{L_FONT_HUGE}</option>
</select>
&nbsp;
<select name="codeAlign" class="text_size">
	<option value="left" selected="selected">{L_ALIGN}</option>
	<option value="left">&nbsp;{L_LEFT}</option>
	<option value="right">&nbsp;{L_RIGHT}</option>
	<option value="center">&nbsp;{L_CENTER}</option>
	<option value="justify">&nbsp;{L_JUSTIFY}</option>
</select>
&nbsp;
<span class="buttons">
	<input type="button" value="&#8212;" name="codeHR" title="{L_HOR_LINE}" style="font-weight: bold; width: 26px;" />
	<input type="button" value="&para;" name="codeBR" title="{L_NEW_LINE}" style="width: 26px;" />&nbsp;
	<input type="button" value="{L_SPOILER}" name="codeSpoiler" title="{L_SPOILER}" style="width: 65px;" />
</span>
&nbsp;
<div class="buttons floatR">
	<input type="button" value="+" onclick="$('#message').css({height: parseInt($('#message').css('height')) + 100}); return false;">
	<input type="button" value="-" onclick="$('#message').css({height: parseInt($('#message').css('height')) - 100}); return false;">
</div>
</div>
<!-- ENDIF / !QUICK_REPLY -->

<div class="buttons mrg_4">
	<input type="button" value="B" name="codeB" title="{L_BOLD}" style="font-weight: bold; width: 25px;" />
	<input type="button" value="i" name="codeI" title="{L_ITALIC}" style="width: 25px; font-style: italic;" />
	<input type="button" value="u" name="codeU" title="{L_UNDERLINE}" style="width: 25px; text-decoration: underline;" />
	<input type="button" value="s" name="codeS" title="{L_STRIKEOUT}" style="width: 25px; text-decoration: line-through;" />&nbsp;&nbsp;
	<input type="button" value="{L_QUOTE}" name="codeQuote" title="{L_QUOTE_TITLE}" style="width: 57px;" />
	<input type="button" value="Img" name="codeImg" title="{L_IMG_TITLE}" style="width: 40px;" />
	<input type="button" value="{L_URL}" name="codeUrl" title="{L_URL_TITLE}" style="width: 63px; text-decoration: underline;" />&nbsp;
	<input type="button" value="{L_CODE}" name="codeCode" title="{L_CODE_TITLE}" style="width: 43px;" />
	<input type="button" value="{L_LIST}" name="codeList" title="{L_LIST_TITLE}"  style="width: 60px;"/>
	<input type="button" value="1." name="codeOpt" title="{L_LIST_ITEM}" style="width: 30px;" />&nbsp;
	<input type="button" value="{L_QUOTE_SEL}" name="quoteselected" title="{L_QUOTE_SELECTED}" onclick="bbcode.onclickQuoteSel();" />&nbsp;
	<!-- IF USER_LANG != 'en' -->
	<input type="button" value="{L_TRANSLIT}" name="Translit" title="{L_TRANSLIT_TITLE}" style="width: 68px;" onclick="transliterate(document.post.message, this);" /> <a href="#" onclick="toggle_block('translit_opt'); return false"><span style="color: darkred"><b>?</b></span></a>
	<!-- ENDIF -->
</div>

	<textarea
		class="editor mrg_4" name="message" id="message" rows="18" cols="92"
		onfocus  = "storeCaret(this);"
		onselect = "storeCaret(this);"
		onclick  = "storeCaret(this);"
		onkeyup  = "storeCaret(this);"
	>{MESSAGE}</textarea>

<!-- IF USER_LANG != 'en' -->
<div id="translit_opt" class="mrg_4" style="display: none;">
	<table cellspacing="0" class="translit_expl borderless bCenter">
	<tr>
		<th>А</th><td>-</td><td>a</td>
		<th>Д</th><td>-</td><td>d</td>
		<th>И</th><td>-</td><td>i</td>
		<th>М</th><td>-</td><td>m</td>
		<th>Р</th><td>-</td><td>r</td>
		<th>Ф</th><td>-</td><td>f</td>
		<th>Ш</th><td>-</td><td>sh</td>
		<th>Ы</th><td>-</td><td>y</td>
	</tr><tr>
		<th>Б</th><td>-</td><td>b</td>
		<th>Е</th><td>-</td><td>e</td>
		<th>Й</th><td>-</td><td>j</td>
		<th>Н</th><td>-</td><td>n</td>
		<th>С</th><td>-</td><td>s</td>
		<th>Х</th><td>-</td><td>h,x</td>
		<th>Щ</th><td>-</td><td>sz,w</td>
		<th>Э</th><td>-</td><td>eh</td>
	</tr><tr>
		<th>В</th><td>-</td><td>v</td>
		<th>Ж</th><td>-</td><td>zh</td>
		<th>К</th><td>-</td><td>k</td>
		<th>О</th><td>-</td><td>o</td>
		<th>Т</th><td>-</td><td>t</td>
		<th>Ц</th><td>-</td><td>c</td>
		<th>Ь</th><td>-</td><td>'</td>
		<th>Ю</th><td>-</td><td>ju</td>
	</tr><tr>
		<th>Г</th><td>-</td><td>g</td>
		<th>З</th><td>-</td><td>z</td>
		<th>Л</th><td>-</td><td>l</td>
		<th>П</th><td>-</td><td>p</td>
		<th>У</th><td>-</td><td>u</td>
		<th>Ч</th><td>-</td><td>ch</td>
		<th>Ъ</th><td>-</td><td>#</td>
		<th>Я</th><td>-</td><td>ja</td>
	</tr>
	</table>
</div>
<!-- ENDIF -->

<div class="mrg_8 tCenter">
	<div id="post-buttons-block" style="display: none;">
		<div class="pad_4">{CAPTCHA_HTML}</div>
		<input type="submit" name="preview" value="{L_PREVIEW}" id="post-preview-btn" onclick="$('#post-submit').remove();">&nbsp;&nbsp;
		<input onclick="submitted = true;" title="Ctrl+Enter" type="submit" name="post" class="bold" value="{L_SUBMIT}" id="post-submit-btn">&nbsp;&nbsp;
		<input type="button" value="{L_AJAX_PREVIEW}" onclick="ajax.exec({ action: 'posts', type: 'view_message', message: $('textarea#message').val()});">
	</div>
	<div id="post-js-warn">{L_JAVASCRIPT_ON}</div>
</div>

<script type="text/javascript">
function dis_submit_btn ()
{
	$('#post-submit-btn').attr('disabled', 1);
	//debounce('post-submit-btn', 3000);
}

function debounce (el_id, time_ms)
{
	var $el = $('#'+el_id);
	if ( $el.attr('disabled') == false ) {
		$el.attr('disabled', 1);
		setTimeout(function(){ $el.attr('disabled', 0); }, time_ms);
	}
}

$('#post-submit-btn').click(function(event){
	$('#post-submit-btn').after('<input id="post-submit" type="hidden" name="post" value="1" />');
});
$('#post-js-warn').hide();
$('#post-buttons-block').show();
$('#post-submit-btn').removeAttr('disabled');

// Called before form submitting.
var submitted = false;

function checkForm(form) {
	var formErrors = false;
	if (form.message.value.length < 2) {
		formErrors = "{L_EMPTY_MESSAGE}";
	}
	if (formErrors) {
		setTimeout(function() { alert(formErrors) }, 100);
		return false;
	}
<!-- IF QUICK_REPLY -->
<!-- IF IN_PM -->
<!-- ELSE -->
<!-- IF $bb_cfg['use_ajax_posts'] && !IS_GUEST -->
	if(form.message.value.length < 100 && submitted)
	{
		setTimeout(function() {
			if ($('input[name="notify"]').attr('checked') == 'checked') {
				var notify = 1;
			}

			ajax.exec({
				action   : 'posts',
				type     : 'add',
				message  : $('textarea#message').val(),
				topic_id : {TOPIC_ID},
				notify   : notify,
			});
		}, 100);
		return false;
	}
<!-- ENDIF -->
<!-- ENDIF -->
<!-- ENDIF -->
	return true;
}
</script>

<script type="text/javascript">
var bbcode = new BBCode("message");
var ctrl = "ctrl";

bbcode.addTag("codeB", "b", null, "B", ctrl);
bbcode.addTag("codeI", "i", null, "I", ctrl);
bbcode.addTag("codeU", "u", null, "U", ctrl);
bbcode.addTag("codeS", "s", null, "S", ctrl);

bbcode.addTag("codeQuote", "quote", null, "Q", ctrl);
bbcode.addTag("codeImg", "img", null, "R", ctrl);
bbcode.addTag("codeUrl", "url", "/url", "W", ctrl);

bbcode.addTag("codeCode", "code", null, "K", ctrl);
bbcode.addTag("codeList",  "list", null, "L", ctrl);
bbcode.addTag("codeOpt", "*", "", "0", ctrl);
</script>

<!-- IF QUICK_REPLY -->
<!-- ELSE -->
<script type="text/javascript">
bbcode.addTag("codeHR",      "hr",      "",   "8", ctrl);
bbcode.addTag("codeBR",      "br",      "",   "",  ctrl);
bbcode.addTag("codeSpoiler", "spoiler", null, "",  ctrl);

bbcode.addTag("fontFace", function(e) { var v=e.value; e.selectedIndex=0; return "font=\""+v+"\"" }, "/font");
bbcode.addTag("codeColor", function(e) { var v=e.value; e.selectedIndex=0; return "color="+v }, "/color");
bbcode.addTag("codeSize", function(e) { var v=e.value; e.selectedIndex=0; return "size="+v }, "/size");
bbcode.addTag("codeAlign", function(e) { var v=e.value; e.selectedIndex=0; return "align="+v }, "/align");
</script>
<!-- ENDIF -->