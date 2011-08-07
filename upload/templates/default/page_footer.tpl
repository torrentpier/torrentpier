<!-- IF SIMPLE_FOOTER --><!-- ELSEIF IN_ADMIN --><!-- ELSE -->

	</div><!--/main_content_wrap-->
	</td><!--/main_content-->

	<!-- IF SHOW_SIDEBAR2 -->
		<!--sidebar2-->
		<td id="sidebar2">
		<div id="sidebar2_wrap">
		
		
			<?php if (!empty($bb_cfg['sidebar2_static_content_path'])) include($bb_cfg['sidebar2_static_content_path']); ?>
			<img width="210" class="spacer" src="{SPACER}" alt="" />
			
		</div><!--/sidebar2_wrap-->
		</td><!--/sidebar2-->
	<!-- ENDIF -->

	</tr></table>
	</div>
	<!--/page_content-->

	<!--page_footer-->
	<div id="page_footer">

		<div class="clear"></div>

		<br /><br />

		<!-- IF $bb_cfg['user_agreement_html_path'] -->
		<div class="med bold tCenter">
			<a href="{$bb_cfg['user_agreement_url']}" onclick="window.open(this.href, '', InfoWinParams); return false;">{L_USER_AGREEMENT}</a>
			<!-- IF $bb_cfg['copyright_holders_html_path'] -->
			<span class="normal">&nbsp;|&nbsp;</span>
			<a href="{$bb_cfg['copyright_holders_url']}" onclick="window.open(this.href, '', InfoWinParams); return false;">{L_COPYRIGHT_HOLDERS}</a>
			<!-- ENDIF -->
			<!-- IF $bb_cfg['advert_html_path'] -->
			<span class="normal">&nbsp;|&nbsp;</span>
			<a href="{$bb_cfg['advert_url']}" onclick="window.open(this.href, '', InfoWinParams); return false;">{L_ADVERT}</a>
			<!-- ENDIF -->
		</div>
		<br />
		<!-- ENDIF -->

		<!-- IF SHOW_ADMIN_LINK -->
		<div class="tiny tCenter"><a href="{ADMIN_LINK_HREF}">{L_ADMIN_PANEL}</a></div>
		<br />
		<!-- ENDIF -->

		<div class="copyright tCenter">
			{L_POWERED} <br />
			{L_DIVE} <br />
		</div>

	</div>

	<div class="copyright tCenter">
		<b style="color:rgb(204,0,0);">{L_NOTICE}</b><br />
		{L_COPY}
	</div>

	<!--/page_footer -->

	</div>
	<!--/page_container -->

<!-- ENDIF -->

<!-- IF ONLOAD_FOCUS_ID -->

<script type="text/javascript">
$p('{ONLOAD_FOCUS_ID}').focus();
</script>

<!-- ENDIF -->