<!-- IF SIMPLE_FOOTER --><div class="copyright tCenter">{POWERED}</div><!-- ELSEIF IN_ADMIN --><!-- ELSE -->

	</div><!--/main_content_wrap-->
	</td><!--/main_content-->

	<!-- IF SHOW_SIDEBAR2 -->
		<!--sidebar2-->
		<td id="sidebar2">
		<div id="sidebar2-wrap">
			<!-- IF HTML_SIDEBAR_2 -->
				<?php include($V['HTML_SIDEBAR_2']); ?>
			<!-- ENDIF -->
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

		<div class="med bold tCenter">
			<!-- IF HTML_AGREEMENT -->
			<a href="{$bb_cfg['user_agreement_url']}" onclick="window.open(this.href, '', IWP); return false;">{L_USER_AGREEMENT}</a>
			<!-- ENDIF -->
			<!-- IF HTML_COPYRIGHT -->
			<span class="normal">&nbsp;|&nbsp;</span>
			<a href="{$bb_cfg['copyright_holders_url']}" onclick="window.open(this.href, '', IWP); return false;">{L_COPYRIGHT_HOLDERS}</a>
			<!-- ENDIF -->
			<!-- IF HTML_ADVERT -->
			<span class="normal">&nbsp;|&nbsp;</span>
			<a href="{$bb_cfg['advert_url']}" onclick="window.open(this.href, '', IWP); return false;">{L_ADVERT}</a>
			<!-- ENDIF -->
		</div>
		<br />

		<!-- IF SHOW_ADMIN_LINK -->
		<div class="tiny tCenter"><a href="{ADMIN_LINK_HREF}">{L_ADMIN_PANEL}</a></div>
		<br />
		<!-- ENDIF -->

		<div class="copyright tCenter">
			{POWERED}<br />
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
