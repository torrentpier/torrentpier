<!-- BEGIN ulist_open --><ul><!-- END ulist_open -->
<!-- BEGIN ulist_close --></ul><!-- END ulist_close -->

<!-- BEGIN olist_open --><ol type="{LIST_TYPE}"><!-- END olist_open -->
<!-- BEGIN olist_close --></ol><!-- END olist_close -->

<!-- BEGIN listitem --><li><!-- END listitem -->

<!-- BEGIN quote_username_open -->
<div class="q-wrap">
	<p class="q-head"><b>{USERNAME}</b> {L_WROTE}:</p>
	<div class="q">
<!-- END quote_username_open -->

<!-- BEGIN quote_open -->
<div class="q-wrap">
	<p class="q-head"><b>{L_QUOTE}:</b></p>
	<div class="q">
<!-- END quote_open -->

<!-- BEGIN quote_close -->
	<div class="clear"></div>
	</div><!--/q-->
</div><!--/q_wrap-->
<!-- END quote_close -->

<!-- BEGIN code_open -->
<div class="c-wrap">
	<div class="c-head">
		<p style="float: left;"><b>{L_CODE}:</b></p>
		<script type="text/javascript">
			copyText_writeLink('this.parentNode.parentNode.nextSibling.nextSibling');
		</script>
	</div><!--/code_head-->
	<div class="c-body">
<!-- END code_open -->

<!-- BEGIN code_close -->
	</div><!--/code-->
</div><!--/code_wrap-->
<!-- END code_close -->

<!-- BEGIN spoiler_title_open -->
<div class="sp-wrap">
	<div class="sp-body" title="{SPOILER_HEAD}">
<!-- END spoiler_open -->

<!-- BEGIN spoiler_open -->
<div class="sp-wrap">
	<div class="sp-body" title="{SPOILER_HEAD}">
<!-- END spoiler_open -->

<!-- BEGIN spoiler_close -->
	</div><!--/spoiler-body-->
</div><!--/spoiler-wrap-->
<!-- END spoiler_close -->

<!-- BEGIN b_open --><span style="font-weight: bold;"><!-- END b_open -->
<!-- BEGIN b_close --></span><!-- END b_close -->

<!-- BEGIN u_open --><span style="text-decoration: underline;"><!-- END u_open -->
<!-- BEGIN u_close --></span><!-- END u_close -->

<!-- BEGIN i_open --><span style="font-style: italic;"><!-- END i_open -->
<!-- BEGIN i_close --></span><!-- END i_close -->

<!-- BEGIN s_open --><span class="post-s"><!-- END s_open -->
<!-- BEGIN s_close --></span><!-- END s_close -->

<!-- BEGIN color_open --><span style="color: {COLOR};"><!-- END color_open -->
<!-- BEGIN color_close --></span><!-- END color_close -->

<!-- BEGIN size_open --><span style="font-size: {SIZE}px; line-height: normal;"><!-- END size_open -->
<!-- BEGIN size_close --></span><!-- END size_close -->

<!-- BEGIN font_open --><span style="font-family: {FONT}"><!-- END font_open -->
<!-- BEGIN font_close --></span><!-- END font_close -->

<!-- BEGIN align_open --><div style="text-align: {ALIGN};"><!-- END align_open -->
<!-- BEGIN align_close --></div><!-- END align_close -->

<!-- BEGIN img --><var class="postImg" title="{URL}">&#10;</var><!-- END img -->
<!-- BEGIN img_aligned --><var class="postImg postImgAligned img-{ALIGN}" title="{URL}">&#10;</var><!-- END img_aligned -->

<!-- BEGIN url --><a href="{URL}" class="postLink">{DESCRIPTION}</a><!-- END url -->

<!-- BEGIN email --><a href="mailto:{EMAIL}">{EMAIL}</a><!-- END email -->