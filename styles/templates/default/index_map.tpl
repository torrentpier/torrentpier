<style type="text/css">
#f-map a  { font-size: 12px; text-decoration: none; padding-left: 8px; }
#f-map li { margin: 4px 0 0 6px; }
#f-map .b { font-weight: bold; }
ul.tree-root { margin-left: 0; }
ul.tree-root > li > ul { margin-left: 0; }
ul.tree-root li { list-style: none; }
ul.tree-root > li { padding: 0; margin-left: 0 !important; }
.c_title {
	display: block; margin: 9px 0; padding: 0 0 5px 5px; border-bottom: 1px solid #B7C0C5;
	color: #800000; font-size: 13px; font-weight: bold; letter-spacing: 1px;
}
ul.tree-root img   { cursor: pointer; }
a.hl, a.hl:visited { color: #1515FF; }
</style>

<script type="text/javascript">
function qs_highlight_found ()
{
	this.style.display = '';
	var a = $('a:first', this);
	var q = $('#q-search').val().toLowerCase();
	if (q != '' && a.text().toLowerCase().indexOf(q) != -1) {
		a.html(a.text().replace(q, '<b style="color:#1515ff">' + q + '</b>'));
	}
	else {
		a.html(a.text());
	}
}
function open_feed (f_id)
{
	$('#feed-id').val(f_id);
	$('#feed-form').submit();
}
$(function(){
	$('#q-search').focus().quicksearch('#f-map li', {
		delay     : 300,
		noResults : '#f-none',
		show      : qs_highlight_found,
		onAfter   : function(){ $('#f-load').hide(); $('#f-map').show(); }
	});
	$.each($('#f-map a'), function(i,a) {
		var f_id = $(a).attr('href');
		$(a)
			.attr('href', 'viewforum.php?f='+ f_id)
			.before('<img class="feed-small" src="{IMG}feed.png" alt="feed" onclick="open_feed('+ f_id +')">')
		;
	});
	$.each($('span.c_title'), function(i,el) {
		$(el).text( this.title );
		this.title = '';
	});
});
</script>

<form id="feed-form" method="post" action="feed.php" target="_blank" style="display: none;">
	<input type="hidden" name="mode" value="get_feed_url">
	<input type="hidden" name="type" value="f">
	<input id="feed-id" type="hidden" name="id" value="">
</form>

<div class="f-map-wrap row1 pad_8">
	<div style="margin: 20px 56px;">
		<div style="padding: 0 0 12px 3px;">
			<form autocomplete="off">
				<i>{L_FILTER_BY_NAME}:</i> &nbsp;<input type="text" id="q-search" style="width: 200px;">
			</form>
			<div id="f-none" style="padding: 25px 0 0 0; display: none;">{L_NO_MATCH}</div>
		</div>
		<div id="f-load" style="padding: 6px;"><i class="loading-1">{L_LOADING}</i></div>
		<div id="f-map" style="display: none;">
			<!-- BEGIN c -->
			<ul class="tree-root">
				<li>
					<span class="b">
						<span class="c_title" title="{c.CAT_TITLE}"></span>
					</span>
					<!-- BEGIN f -->
					<ul>
						<li>
							<span class="b">
								<a href="{c.f.FORUM_ID}">{c.f.FORUM_NAME}</a>
							</span>
							<!-- IF c.f.LAST_SF_ID -->
							<ul>
								<!-- BEGIN sf -->
								<li>
									<span><a href="{c.f.sf.SF_ID}">{c.f.sf.SF_NAME}</a></span>
								</li>
								<!-- END sf -->
							</ul>
							<!-- ENDIF -->
						</li>
					</ul>
					<!-- END f -->
				</li>
			</ul>
			<!-- END c -->
		</div>
	</div>
</div>
<br />
