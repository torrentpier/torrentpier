<?php if (!empty($V['SIMPLE_FOOTER'])) { ?><div class="copyright tCenter"><?php echo isset($V['POWERED']) ? $V['POWERED'] : ''; ?></div><?php } elseif (!empty($V['IN_ADMIN'])) { ?><?php } else { ?>

	</div><!--/main_content_wrap-->
	</td><!--/main_content-->

	<?php if (!empty($V['SHOW_SIDEBAR2'])) { ?>
		<!--sidebar2-->
		<td id="sidebar2">
		<div id="sidebar2-wrap">
			<?php if (!empty($V['HTML_SIDEBAR_2'])) { ?>
				<?php include($V['HTML_SIDEBAR_2']); ?>
			<?php } ?>
			<img width="210" class="spacer" src="<?php echo isset($V['SPACER']) ? $V['SPACER'] : ''; ?>" alt="" />
		</div><!--/sidebar2_wrap-->
		</td><!--/sidebar2-->
	<?php } ?>

	</tr></table>
	</div>
	<!--/page_content-->

	<!--page_footer-->
	<div id="page_footer">

		<div class="clear"></div>

		<br /><br />

		<div class="med bold tCenter">
			<?php if (!empty($V['HTML_AGREEMENT'])) { ?>
			<a href="<?php echo isset($bb_cfg['user_agreement_url']) ? $bb_cfg['user_agreement_url'] : ''; ?>" onclick="window.open(this.href, '', IWP); return false;"><?php echo isset($L['USER_AGREEMENT']) ? $L['USER_AGREEMENT'] : (isset($SL['USER_AGREEMENT']) ? $SL['USER_AGREEMENT'] : $V['L_USER_AGREEMENT']); ?></a>
			<?php } ?>
			<?php if (!empty($V['HTML_COPYRIGHT'])) { ?>
			<span class="normal">&nbsp;|&nbsp;</span>
			<a href="<?php echo isset($bb_cfg['copyright_holders_url']) ? $bb_cfg['copyright_holders_url'] : ''; ?>" onclick="window.open(this.href, '', IWP); return false;"><?php echo isset($L['COPYRIGHT_HOLDERS']) ? $L['COPYRIGHT_HOLDERS'] : (isset($SL['COPYRIGHT_HOLDERS']) ? $SL['COPYRIGHT_HOLDERS'] : $V['L_COPYRIGHT_HOLDERS']); ?></a>
			<?php } ?>
			<?php if (!empty($V['HTML_ADVERT'])) { ?>
			<span class="normal">&nbsp;|&nbsp;</span>
			<a href="<?php echo isset($bb_cfg['advert_url']) ? $bb_cfg['advert_url'] : ''; ?>" onclick="window.open(this.href, '', IWP); return false;"><?php echo isset($L['ADVERT']) ? $L['ADVERT'] : (isset($SL['ADVERT']) ? $SL['ADVERT'] : $V['L_ADVERT']); ?></a>
			<?php } ?>
		</div>
		<br />

		<?php if (!empty($V['SHOW_ADMIN_LINK'])) { ?>
		<div class="tiny tCenter"><a href="<?php echo isset($V['ADMIN_LINK_HREF']) ? $V['ADMIN_LINK_HREF'] : ''; ?>"><?php echo isset($L['ADMIN_PANEL']) ? $L['ADMIN_PANEL'] : (isset($SL['ADMIN_PANEL']) ? $SL['ADMIN_PANEL'] : $V['L_ADMIN_PANEL']); ?></a></div>
		<br />
		<?php } ?>

		<div class="copyright tCenter">
			<?php echo isset($V['POWERED']) ? $V['POWERED'] : ''; ?><br />
		</div>

	</div>

	<div class="copyright tCenter">
		<b style="color:rgb(204,0,0);"><?php echo isset($L['NOTICE']) ? $L['NOTICE'] : (isset($SL['NOTICE']) ? $SL['NOTICE'] : $V['L_NOTICE']); ?></b><br />
		<?php echo isset($L['COPY']) ? $L['COPY'] : (isset($SL['COPY']) ? $SL['COPY'] : $V['L_COPY']); ?>
	</div><br />

	<!--/page_footer -->

	</div>
	<!--/page_container -->

<?php } ?>

<script type="text/javascript">new ClipboardJS('.copyElement');</script>

<?php if (!empty($V['ONLOAD_FOCUS_ID'])) { ?>
<script type="text/javascript">
$p('<?php echo isset($V['ONLOAD_FOCUS_ID']) ? $V['ONLOAD_FOCUS_ID'] : ''; ?>').focus();
</script>
<?php } ?>
