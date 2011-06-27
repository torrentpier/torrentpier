<script>function f2(x){ x.focus(); x.select(); }

function add_image_field(i)
{
	function create_input(i, prefix, input_type, mode)
	{
		var cnt = document.getElementById('f' + i + '_cnt');
		if(!cnt) return;

		var div_ = document.createElement('div');
		var input_ = document.createElement('input');
		var hidden_ = document.createElement('input');
		var img_ = document.createElement('img');

		// DIV
		div_.id = 'up' + prefix + '_' + i + '_' + cnt.value;
		if (( global_mode != mode ) && !( mode==1 && global_mode == 0 ))
		{
			div_.style.display = 'none';
		}

		// HIDDEN
		hidden_.name = 'MAX_FILE_SIZE';
		hidden_.type = 'hidden';
		hidden_.value = '2097152';

		// INPUT
		input_.name = 'f' + i + '_' + cnt.value + 'l_' + prefix;
		input_.type = input_type;
		input_.id = 'fileupload';
		input_.size = '70';
		input_.style.height = '20px';
		input_.onchange = new Function('check_' + prefix + '(this, ' + i + ', ' + cnt.value + ')');
		if ( mode==2 )
		{
			input_.style.color = '#3333ff';
			input_.style.textDecoration = 'underline';
		}

		// IMG
		img_.id = 'img_up' + prefix + '_' + i + '_' + cnt.value;
		img_.src = 'images/img_alert.gif';
		img_.alt = 'Внимание! Ошибка!';
		img_.width = '20';
		img_.height = '17';
		img_.border = '0';
		img_.style.display = 'none';
		img_.onclick = new Function('show_alert(this)');

		// PLACE
		if ( mode==1 ) div_.appendChild(hidden_);
		div_.appendChild(input_);
		div_.appendChild(img_);

		return div_;
	}

	var cnt = document.getElementById('f' + i + '_cnt');
	if(!cnt) return;

	var W3CDOM = (document.createElement && document.getElementsByTagName);
	if (W3CDOM)
	{
		var place = document.getElementById('f' + i + '_place');
		if(!place) return;

		var table_ = document.createElement('table');
		var tr_ = document.createElement('tr');
		var td_ = document.createElement('td');

		// TABLE
		table_.border = '0';
		table_.cellSpacing = '0';
		table_.cellPadding = '0';
		tr_.valign='center';
		td_.height='25';


		td_.appendChild( create_input(i, 'file', 'file', 1) );
		td_.appendChild( create_input(i, 'url', 'text', 2) );
		td_.appendChild( create_input(i, 'bbcode', 'text', 3) );
		tr_.appendChild( td_ );
		table_.appendChild( tr_ );
		place.appendChild( table_ );
		cnt.value++;
	}
}
</script>
<body> 
<table width="100%" cellpadding="3" cellspacing="1" border="0" class="forumline">
	<tr> 
		<th height="25" class="thCornerL" nowrap>{L_GALLERY}</th>
	</tr>
	<tr>
		<td class="row1" align="center">
			<span class="gen">
				<h3 align="Center">{MAX_SIZE_HINT} {MAX_SIZE}</h3>
				{MSG}
				<hr>
				<center>
					<form enctype="multipart/form-data" method="post" action="?go=upload">
						<span id="fileupload"><input name="imgfile[]" type="file" /><br /></span><a href="#" onclick="$('#fileupload').clone(true).insertBefore(this); return false;" style="text-decoration: underline;"  align="right">{MORE}</a><br />
						<br />
						<label><input type="checkbox" name="create_thumb" value="0"  />&nbsp;{CREATE_THUMB}&nbsp;</label>
						<hr />
						<input type="submit" value="{UPLOAD}" />
					</form>
				</center>
			</span>
		</td>
	</tr>
</table>