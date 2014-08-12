<style type="text/css">
#f-map a  { font-size: 12px; text-decoration: none; }
#f-map li { margin: 4px 0 0 6px; }
#f-map .b { font-weight: bold; }
ul.tree-root { margin-left: 0; }
ul.tree-root ul > li { list-style: disc; }
ul.tree-root ul ul > li { list-style: circle; }
ul.tree-root > li { list-style: none !important; padding: 0; margin-left: 0 !important; }
.c_title {
	display: block; margin: 9px 0; padding: 0 0 5px 3px; border-bottom: 1px solid #B7C0C5;
	color: #800000; font-size: 13px; font-weight: bold; letter-spacing: 1px;
}
a.hl, a.hl:visited { color: #1515FF; }
</style>

<script type="text/javascript">
function qs_highlight_found ()
{
	this.style.display = '';
	var a = $('a:first', this)[0];
	var q = $('#q-search').val().toLowerCase();
	if (q != '' && a.innerHTML.toLowerCase().indexOf(q) != -1) {
		a.className = 'hl';
	}
	else {
		a.className = '';
	}
}
$(function(){
	$('#q-search').focus().quicksearch('#f-map li', {
		delay     : 300,
		noResults : '#f-none',
		show      : qs_highlight_found,
		onAfter   : function(){ $('#f-load').hide(); $('#f-map').show(); }
	});
	$.each($('#f-map a'), function(i,a){
		$(a).attr('href', 'viewforum.php?f='+ $(a).attr('href'));
	});
	$.each($('span.c_title'), function(i,el){
		$(el).text( this.title );
		this.title = '';
	});
});
</script>

<div class="row1 pad_8 border bw_TRBL" style="margin-top: 4px;">
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
				<li><span class="b"><span class="c_title" title="{c.CAT_TITLE}" name="{c.CAT_TITLE}"></span></span>
				<!-- BEGIN f -->
				<ul>
					<li><span class="b"><a href="{c.f.FORUM_ID}">{c.f.FORUM_NAME}</a></span>
						<!-- IF c.f.LAST_SF_ID -->
						<ul>
							<!-- BEGIN sf -->
							<li><span><a href="{c.f.sf.SF_ID}">{c.f.sf.SF_NAME}</a></span></li>
							<!-- END sf -->
						</ul>
						<!-- ENDIF -->
					</li>
				</ul>
				<!-- END f -->
			</ul>
			<!-- END c -->
		</div>
	</div>
</div>
<br />