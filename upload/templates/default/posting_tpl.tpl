
<style type="text/css">
td.rel-inputs { padding-left: 0; }
.rel-el      { margin: 2px 0px 2px 6px; }
.rel-title   { font-weight: bold; }
.rel-input   { }
.rel-free-el { font-size: 11px; line-height: 12px; }

textarea.rel-input { width: 98%; }
#rel-create textarea, #tpl-row-src { font-size: 13px; font-family: "Lucida Console","Courier New",Courier,monospace; }

.tpl-err-hl-input { border: 1px solid #8C0000; background: #FFF9F2; }
.tpl-err-hl-tr    { background: #FFEAD5; }
.tpl-err-hint     { color: #8C0000; margin-left: 6px; }
.tpl-adm-block    { padding: 6px 20px; margin: 8px 0; border: 4px ridge #808080; }
.tpl-adm-block div.label { padding: 2px 0; }
.attr-list    { color: #0000BB; }
.tpl-help-msg { padding: 8px; border: 2px solid #A5AFB4; background: #DEE3E7; font-size: 11px; }
.hlp-1        { color: blue; }
.hlp-2        { color: red; }

#rel-preview .rel-input { background: #DEE3E7; border: 1px solid #9BA8AE; }
#rel-preview td { padding: 5px 10px 6px; }
#tpl-row-src    { border: 1px dashed #CFD6D8; background: #F8F8F8; color: #0000D5; font-size: 14px; }

#row-preview-win { height: 45px; overflow: auto; }
#tpl-row-preview textarea.rel-input { height: 20px; border: 2px inset; }
.hid-el > * { color: red !important; border-color: red !important; }
</style>

<script type="text/javascript">

$(document).ready(function(){

	// инициализация значений #tpl-src
	$('#tpl-src-form').val( $('#tpl-src-form-val').val() );
	$('#tpl-src-title').val( $('#tpl-src-title-val').val() );
	$('#tpl-src-msg').val( $('#tpl-src-msg-val').val() );

<!-- IF EDIT_TPL -->
	<!-- IF NO_TPL_ASSIGNED -->
	$('#toggle-info-a').click();
	<!-- ENDIF -->

	// список-подсказка атрибутов для #tpl-src-msg (HEAD, POSTER, req..)
	var ls = [];
	$.each(TPL.msg_attr, function(at,desc){
		ls.push( '<span class="attr-list" title="'+ desc +'">'+ at +'</span>' );
	});
	$('#msg-attr-list').html( ls.join(', ') );

	// конструктор элементов
	// #tpl-row-src - строка, из которой строится #tpl-row-preview (текущая строка #tpl-src-form либо произвольная)
	$('#tpl-row-src').bind('keypress', function(e, keyCode){
		var k = keyCode || e.keyCode;
		var v = $.trim( $(this).val() );
		if ( /^<\-.*\->$/.test(v) ) {
			v = v.substring(2, v.length-2);
		}
		if (k == 13 /* Enter */) {
			TPL.build_tpl_form( '<-'+ v +' ->', 'tpl-row-preview' );
			$('#tpl-row-src').val(v);
		}
	});

	// подстановка текущей строки в #tpl-row-src и обновление предпросмора элемента
	$('#tpl-src-form').bind('mouseup keyup focus', function(e){
		if (!$('#rel-preview:visible')[0]) return;
		if (e.keyCode) {
			if ( !(e.keyCode == 38 /*up*/ || e.keyCode == 40 /*down*/) ) return;
		}
		var ss = this.selectionStart;
		if (ss == null) return;
		var v = this.value;
		var v = v.substring(0, ss).match(/.*$/)[0] + v.substring(ss, v.length).match(/^.*/)[0];  // текущая строка под курсором
		v = v.substring(2, v.length-2);
		$('#tpl-row-src').val(v).trigger('keypress', [13]);
	});

	// select для выбора TPL.attr_el элементов в конструкторе
	var $attr_el_sel = $( TPL.build_el_attr_select() );

	$attr_el_sel.bind('change', function(){
		var $sel = $(this);
		var el   = $sel.val();
		var src  = $.trim( $('#tpl-row-src').val() );

		if (src == '') {
			src += str_pad(el, 15);
		}
		src += ' '+ TPL.el_attr[el][0] +'['+ el +'] ';
		$('#tpl-row-src').val(src);

		$sel[0].selectedIndex = 0;
		$('#tpl-row-src').trigger('keypress', [13]).focus();
	});
	$('#tpl-el_attr-sel').append($attr_el_sel);

	// select для выбора TPL.el_id элементов в конструкторе и других (BR и т.д.)
	var $el_id_sel = $( TPL.build_el_id_select() );

	$el_id_sel.bind('change', function(){
		var $sel = $(this);
		var el   = $sel.val();
		var src  = $.trim( $('#tpl-row-src').val() );

		src += ' '+ el +' ';
		$('#tpl-row-src').val(src);

		$sel[0].selectedIndex = 0;
		$('#tpl-row-src').trigger('keypress', [13]).focus();
	});
	$('#tpl-el_id-sel').append($el_id_sel);
<!-- ENDIF / EDIT_TPL -->
});

var TPL = {
	<!-- IF EDIT_TPL -->
	el_def_val: {
		// значения для автозаполнителя формы. если не определено, то заполняется названием

		year        : 2011,
		poster      : 'http://torrentpier2.googlecode.com/svn/trunk/upload/images/logo/logo.png',
		screenshots : 'http://img462.imageshack.us/img462/8360/snapshot20070911141251ee8.png\n[img]http://img513.imageshack.us/img513/7226/snapshot20070911141324ey9.png[/img]\nhttp://img513.imageshack.us/img513/2809/snapshot20070911141335tt2.png\n[img]http://img211.imageshack.us/img211/2936/snapshot20070911141603ew2.png[/img]',

		// dummy
		dummy : ''
	},
	<!-- ENDIF -->

	match_rows: function(s) {
		return $.trim(s).split(/\->/g) || [];
	},
	match_cols: function(s) {
		return $.trim(s).match(/<\-\s*(\w+|\{.+?\})([\s\S]+)/) || [];
	},
	match_els: function(s) {
		var re = /`(\S[^`]+)\[/g;
		while (s.match(re))
		{
			s = s.replace(re, '`$1&#91;');
		}
		s = s.replace(/(\w+\[.+?\])/g, '`$1`');
		return $.trim(s).match(/`([\s\S]+?)`/g) || [];
	},
	match_el_attrs: function(s) {
		return s.match(/(\w+|\{.+\})\[(.+)\]/) || [];
	},
	trim_brackets: function(s) {
		return $.trim( s.substring(1, s.length-1) ) || '';
	},

	rows: {},
	el_titles: {},

	build_tpl_form: function(str, res_id)
	{
		$('#'+res_id+' tr').remove();
		TPL.rows = {};
		TPL.el_titles = {};

		$.each(TPL.match_rows(str), function(i,row){
			if (row == null || row == '') return true; // continue
			TPL.rows[i] = $.trim(row);
		});
		$.each(TPL.rows, function(i,row){
			var mr = TPL.match_cols(row);
			if (mr[2] == null) return true; // continue
			var title_id = mr[1];    // id элемента для подстановки его названия или {произвольное название}
			var input_els = mr[2];
			var row_title = (TPL.el_attr[title_id] != null) ? TPL.el_attr[title_id][1] : TPL.trim_brackets(title_id);
			var $tr = $('<tr><td class="rel-title">'+ row_title +':</td><td class="rel-inputs"></td></tr>');
			var $td = $('td.rel-inputs', $tr);

			$.each(TPL.match_els(input_els), function(j,el){
				if (!(el = TPL.trim_brackets(el))) return true; // continue
				var el_html = '';
				var me = TPL.match_el_attrs(el);
				// вставка шаблонного элемента типа TYPE[attr]
				if (me[2] != null) {
					var at = me[2].split(',');
					var nm = at[0];

					switch (me[1])
					{
					case 'E':
						if ( $('#'+ nm +'-hid').length ) {
							if (res_id == 'tpl-row-preview') {
								el_html = '<span class="rel-el hid-el">'+ $('#'+ nm +'-hid').html() +'</span>'; // скрытый элемент
							}
						}
						else {
							el_html = '<span class="rel-el">'+ $('#'+ nm).html() +'</span>';
						}
						break;
					case 'T':
						el_html = '<span class="rel-el rel-title">'+ TPL.el_attr[nm][1] +'</span>';
						break;
					case 'INP':
						var id = TPL.build_el_id_title(nm);
						var def = (TPL.el_attr[id] != null) ? TPL.el_attr[id][2].split(',') : [200,80];
						var mlem = at[1] || def[0];
						var size = at[2] || def[1];
						el_html = '<input class="rel-el rel-input" type="text" id="'+ id +'" maxlength="'+ mlem +'" size="'+ size +'" />';
						break;
					case 'TXT':
						var id = TPL.build_el_id_title(nm);
						var def = (TPL.el_attr[id] != null) ? TPL.el_attr[id][2].split(',') : [3];
						var rows = at[1] || def[0];
						var cols = 100;
						el_html = '<textarea class="rel-el rel-input" id="'+ id +'" rows="'+ rows +'" cols="'+ cols +'" />';
						break;
					case 'SEL':
						var id = TPL.build_el_id_title(nm);
						el_html = TPL.build_select_el(nm);
						break;
					}
				}
				// вставка нешаблонного элемента
				else {
					if (el == 'BR') {
						el_html = '<br />';
					}
					else {
						el_html = '<span class="rel-el rel-free-el">'+ escHTML(el) +'</span>';
					}
				}
				// добавление элемента в td.rel-inputs
				if (el_html != '') {
					$td.append(el_html);
				}
			});
			// добавление tr в форму
			$('#'+res_id).append($tr);
		});

		TPL.build_msg_src();
		$('#rel-form').show();
	},

	build_el_id_title: function(nm) {
		if (TPL.el_attr[nm] != null) {
			var id = nm;
			TPL.el_titles[id] = TPL.el_attr[id][1];
		}
		else {
			var id = $P.md5(nm);
			TPL.el_titles[id] = TPL.trim_brackets(nm);
		}
		return id;
	},
	get_el_id: function(el) {
		return (TPL.el_attr[el] != null || TPL.el_id[el] != null) ? el : $P.md5(el);
	},

	build_select_el: function(name) {
		if (TPL.selects[name] == null) return '';
		var s = '<select class="rel-el rel-input" id="'+name+'">';
		var q = /"/g;  //"
		$.each(TPL.selects[name], function(i,v){
			s += '<option value="'+(i==0 ? '' : v.replace(q, '&quot;'))+'">'+(v=='' ? '&raquo; Выбрать' : v)+'</option>';
		});
		s += '</select>';
		return s;
	},

	// возвращает все элементы формата el[atr1,atr2] в виде
	// { el: 'el[attr]' }  для parse_attr == false
	// { el: [atr1,atr2] } для parse_attr == true
	get_msg_els: function(str, parse_attr) {
		var res = {};
		$.each(str.split('\n'), function(i,v){
			if (!(v = $.trim(v))) return true; // continue
			var m = v.match(/^(\w+|\{.+\})\[(.*)\]$/);
			if (m == null) return true; // continue
			if (parse_attr) {
				res[ m[1] ] = m[2].split(',');
			}
			else {
				res[ m[1] ] = m[0];
			}
		});
		return res;
	},

	// создает-обновляет скрипт создания сообщения (#tpl-src-msg)
	build_msg_src: function() {
		var r_old = TPL.get_msg_els( $('#tpl-src-msg').val(), false );   // старые правила для создания сообщения
		var r_gen = [];                                                  // новые, сгенерированные из всех доступных в форме
		var r_new = [];                                                  // новые, с учетом изменений в форме
		// получение всех инпутов из формы
		var m;
		var t = $('#tpl-src-form').val();
		var r = /(?:INP|TXT|SEL)(?:\[)(\w+|\{.+?\})/g;
		while((m = r.exec(t)) != null) {
			r_gen.push(m[1]);
		}
		// создание нового (старые значения сохраняются без именений, новые добавляются, отсутствующие в форме удаляются)
		$.each(r_gen, function(i,v){
			if (r_old[v] != null) {
				r_new.push( r_old[v] );
			}
			else {
				var def_attr = (TPL.el_attr[v] != null) ? TPL.el_attr[v][3] : '';
				r_new.push( v +'['+ def_attr +']' );
			}
		});
		var new_txt = r_new.join('\n');
		$('#tpl-src-msg').val(new_txt);
	},

	// количество найденных ошибок при заполнении формы
	f_errors_cnt: 0,
	// удаление подсветки ошибок, сброс счетчика
	reset_f_errors: function() {
		TPL.f_errors_cnt = 0;
		$('tr.tpl-err-hl-tr').removeClass('tpl-err-hl-tr');
		$('.tpl-err-hl-input').removeClass('tpl-err-hl-input');
		$('div.tpl-err-hint').remove();
	},
	// подсветка ошибок
	hl_form_err: function(el, hint_id) {
		if (TPL.el_attr[el] != null) {
			var el_id = el;
			var el_title = TPL.el_attr[el][1];
		}
		else {
			var el_id = $P.md5(el);
			var el_title = TPL.el_titles[el_id];
		}
		var hint = TPL.err_msg[hint_id].replace(/%s/, el_title);
		$('#'+el_id)
			.addClass('tpl-err-hl-input')
			.parent('td').append('<div class="tpl-err-hint">'+hint+'</asd>')
			.parent('tr').addClass('tpl-err-hl-tr')
		;
		if (TPL.f_errors_cnt == 0) $('#'+el_id).focus();
		TPL.f_errors_cnt++;
	},
	// сообщения об ошибках при валидации заполнения формы
	err_msg: {
		empty_INP : 'Вы должны заполнить поле <b>%s</b>',
		empty_TXT : 'Вы должны заполнить поле <b>%s</b>',
		empty_SEL : 'Вы должны выбрать <b>%s</b>',
		not_num   : '<b>%s</b> - должно быть число',
		not_url   : '<b>%s</b> - должна быть http:// ссылка',
		not_img   : '<b>%s</b> - должна быть http:// ссылка на картинку'
	},

	msg_attr: {
		HEAD    : 'поместить в заголовок',
		POSTER  : 'постер',
		req     : 'требует заполнения',
		spoiler : 'спойлер',
		BR      : 'новая строка',
		num     : 'число',
		URL     : 'ссылка',
		img     : 'картинка'
	},
	reg: {
		num     : /^\d+$/,
		URL     : /^https?:\/\/[\w\#$%&~/.\-;:=?@\[\]+]+$/i,
		img     : /^https?:\/\/[^\s\?&;:=\#\"<>]+\.(jpg|jpeg|gif|png)$/i,
		img_tag : /(https?:\/\/[^\s\?&;:=\#\"<>]+\.(jpg|jpeg|gif|png)(?!\[|\.))/ig
	},

	// построение сообщения на основе данных из формы
	build_msg_all: function(msg_res_id, title_res_id) {
		$.each(TPL.submit_fn, function(el,fn){
			fn();
		});
		$('#tpl-row-preview tr').remove();

		TPL.reset_f_errors();
		var msg_header = [];
		var msg_poster = '';
		var msg_body = []
		var msg_els = TPL.get_msg_els( $('#tpl-src-msg').val(), true );

		$.each(msg_els, function(el,at){
			var el_id = TPL.get_el_id(el);
			var el_val = $('#'+el_id).val() || '';

			// требуемые поля
			if (el_val == '') {
				if ($.inArray('req', at) != -1) {
					var el_type = (TPL.el_attr[el] != null) ? TPL.el_attr[el][0] : 'INP';
					TPL.hl_form_err(el, 'empty_'+ el_type);
				}
				return true; // continue
			}

			// валидация значений
			if ($.inArray('num', at) != -1 && !TPL.reg['num'].test(el_val)) {
				TPL.hl_form_err(el, 'not_num');
				return true; // continue
			}
			if ($.inArray('URL', at) != -1 && !TPL.reg['URL'].test(el_val)) {
				TPL.hl_form_err(el, 'not_url');
				return true; // continue
			}
			if ($.inArray('img', at) != -1 && !TPL.reg['img'].test(el_val)) {
				TPL.hl_form_err(el, 'not_img');
				return true; // continue
			}

			// post-submit обработка значений
			el_val = TPL.normalize_val(el, el_val);

			// заголовок
			if ($.inArray('HEAD', at) != -1) {
				msg_header.push( el_val );
				return true; // continue
			}
			// постер
			if ($.inArray('POSTER', at) != -1) {
				msg_poster = el_val;
				return true; // continue
			}

			// спойлер
			if ($.inArray('spoiler', at) != -1) {
				msg_body.push( TPL.build_spoiler(el_id, el_val) );
				return true; // continue
			}
			// обычный элемент
			msg_body.push( TPL.build_msg_el(el_id, el_val) );

			// новая строка после элемента
			if ($.inArray('BR', at) != -1) {
				msg_body.push('\n');
			}
		});
		if (TPL.f_errors_cnt) {
			return false;
		}
		msg_header = TPL.build_msg_header(msg_header);
		msg_poster = TPL.build_msg_poster(msg_poster);
		msg_body = msg_body.join('');
		// теги для картинок
		msg_body = msg_body.replace(TPL.reg['img_tag'], '[img]$1[/img]');
		$('#'+msg_res_id).val( msg_header + msg_poster + msg_body );

		TPL.build_title(title_res_id);

		return true;
	},

	normalize_val: function(el, val) {
		switch (el) {
			// 2000 г.
			case 'year':
				val += ' г.';
				break;

			// "Имя / Name /" -> "Имя / Name"
			case 'director':
			case 'studio':
				val = val.replace(/[\s\/]+$/, '');
				break;
		}
		return val;
	},

	build_msg_header: function(a) {
		return '[size=24]'+ a.join(' / ') +'[/size]\n\n';
	},
	build_msg_poster: function(s) {
		return TPL.reg['img'].test(s) ? '[img=right]'+ s +'[/img]\n\n' : s;
	},
	build_spoiler: function(el_id, el_val) {
		return '\n[spoiler="'+ TPL.el_titles[el_id] +'"]\n'+ el_val +'\n[/spoiler]\n';
	},
	build_msg_el: function(el_id, el_val) {
		return '[b]'+ TPL.el_titles[el_id] +'[/b]: '+ el_val +'\n';
	},

	build_title: function(res_id) {
		var title = [];
		var trim_after_chars = {};
		var trim_before_chars = {};
		var g;                                                   // группа элементов <-el1 el2->[,]
		var t = $('#tpl-src-title').val().replace(/\n/g, ' ');   // формат
		var r = /<-([^>]+)->(\S*)/g;
		while((g = r.exec(t)) != null) {
			var g_els = g[1].match(/(\w+|\{.+?\})/g);
			if (g_els == null) return true; // continue

			var g_start_char = ' ';
			var g_delim_char = ' ';
			var g_end_char   = ' ';

			if (g[2].length == 1) {
				g_delim_char = ' '+ g[2];
			}
			else if (g[2].length == 3) {
				g_start_char = g[2].charAt(0);
				trim_after_chars[ g_start_char ] = true;

				g_delim_char = g[2].charAt(1);

				g_end_char = g[2].charAt(2);
				trim_before_chars[ g_end_char ] = true;
			}

			var g_vals = [];
			$.each(g_els, function(i,el){
				var el_id = TPL.get_el_id(el);
				var v = $('#'+el_id).val();
				if (v == undefined || $.trim(v) == '') return true; // continue
				v = TPL.normalize_val(el_id, v);
				g_vals.push(' '+ v +' ');
			});
			if (g_vals.length != 0) {
				title.push(' '+ g_start_char +' ');
				title.push( g_vals.join(' '+g_delim_char+' ') );
				title.push(' '+ g_end_char);
			}
		}
		var t = $.trim( title.join('').replace(/\s+,/g, ',').replace(/\s+/g, ' ') );
		$.each(trim_before_chars, function(ch,v){
			var r = new RegExp( '\\s*'+ preg_quote(ch), 'g' );
			t = t.replace(r, ch);
		});
		$.each(trim_after_chars, function(ch,v){
			var r = new RegExp( preg_quote(ch) +'\\s*', 'g' );
			t = t.replace(r, ch);
		});

		$('#'+res_id).val( t );
	},

	submit_fn : {}
};

<?php echo file_get_contents(BB_ROOT .'misc/tpl/posting_tpl_el_attr.js') ?>

function preg_quote (str) {
	return (str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");  // http://kevin.vanzonneveld.net
}

<!-- IF EDIT_TPL -->
TPL.build_el_attr_select = function(){
	var s = '<select><option value="">&raquo;&raquo; Элементы формы/Названия &nbsp;</option>';
	var q = /"/g;  //"
	$.each(TPL.el_attr, function(name,at){
		var v = at[1].replace(q, '&quot;');
		if (v == '') return true; // continue
		s += '<option value="'+ name +'">'+ v +'</option>';
	});
	s += '</select>';
	return s;
};

TPL.build_el_id_select = function(){
	var s = '<select><option value="">&raquo;&raquo; Другие элементы &nbsp;</option>';
	s += '<option value="`текст...`">Текст...</option>';
	s += '<option value="`BR`">Новая строка</option>';
	var q = /"/g;  //"
	$.each(TPL.el_id, function(id,desc){
		var v = desc.replace(q, '&quot;');
		if (v == '') return true; // continue
		s += '<option value="E['+ id +']">'+ desc +'</option>';
	});
	s += '</select>';
	return s;
};

// preview post html
ajax.posts = function(message, res_id) {
	$('#'+res_id).html('<i class="loading-1">загружается...</i>');
	ajax.exec({
		action  : 'posts',
		type    : 'view_message',
		message : message,
		res_id  : res_id
	});
}
ajax.callback.posts = function(data) {
	$('#'+data.res_id).html(data.message_html).append('<div class="clear"></div>');
	initPostBBCode('#'+data.res_id);
}

// topic_tpl
ajax.topic_tpl = function(mode, params) {
	switch (mode) {
		case 'toggle_info':
			$('#tpl-info-block').toggle();
			$('#tpl-new-block').hide();
			break;

		case 'toggle_new':
			$('#tpl-info-block').hide();
			$('#tpl-new-block').toggle();
			break;

		case 'load':
			ajax.exec({
				action : 'topic_tpl',
				mode   : 'load',
				tpl_id : $('#forum_tpl_select').val()
			});
			break;

		case 'assign':
			if (params.tpl_id == -1) {
				if (!window.confirm('Отключить шаблоны в этом форуме?')) {
					return false;
				}
			}
			ajax.exec({
				action   : 'topic_tpl',
				mode     : 'assign',
				forum_id : {FORUM_ID},
				tpl_id   : params.tpl_id
			});
			break;

		case 'save':
			if (!window.confirm('Сохранить изменения для шаблона "'+ $('#tpl-name-old-save').text() +'"?')) {
				return false;
			}
			$('#tpl-load-resp').html('<i class="loading-1">сохраняется...</i>');
			ajax.exec({
				action        : 'topic_tpl',
				mode          : 'save',
				tpl_id        : $('#tpl-id-save').val(),
				tpl_name      : $('#tpl-name-save').val(),
				tpl_src_form  : $('#tpl-src-form').val(),
				tpl_src_title : $('#tpl-src-title').val(),
				tpl_src_msg   : $('#tpl-src-msg').val(),
				tpl_comment   : $('#tpl-comment-save').val(),
				tpl_rules     : $('#tpl-rules-save').val(),
				tpl_l_ed_tst  : $('#tpl-last-edit-tst').val()
			});
			break;

		case 'new':
			$('#tpl-new-resp').html('<i class="loading-1">сохраняется...</i>');
			ajax.exec({
				action        : 'topic_tpl',
				mode          : 'new',
				tpl_name      : $('#tpl-name-new').val(),
				tpl_src_form  : $('#tpl-src-form').val(),
				tpl_src_title : $('#tpl-src-title').val(),
				tpl_src_msg   : $('#tpl-src-msg').val(),
				tpl_comment   : $('#tpl-comment-new').val(),
				tpl_rules     : $('#tpl-rules-new').val()
			});
			break;

		default:
			alert('invalid mode: '+ mode);
	}
}
ajax.callback.topic_tpl = function(data) {
	switch (data.mode) {
		case 'load':
			$('#tpl-save-block').show();
			$('#tpl-load-resp').html('');
			$.each(data.val, function(id,v){
				$('#'+id).val(v);
			});
			$.each(data.html, function(id,v){
				$('#'+id).html(v);
			});
			$('#tpl-rules-link').attr({href: data.tpl_rules_href});
			break;

		case 'assign':
			alert(data.msg);
			window.location.reload();
			break;

		case 'save':
			$.each(data.html, function(id,v){
				$('#'+id).html(v);
			});
			$('#forum_tpl_select option[value='+ data.tpl_id +']').html('&nbsp;'+ data.tpl_name);
			$('#tpl-name-old-save').html(data.tpl_name);
			$('#tpl-last-edit-tst').val(data.timestamp);
			$('#tpl-load-resp').html('сохранено');
			break;

		case 'new':
			$('#tpl-new-resp').html('новый шаблон создан (в списке выбора он появится после перезагрузки страницы)');
			break;
	}
}

function tpl_build_form ()
{
	$('#preview-block').hide();
	$('#tpl-build-msg-btn').show();
	TPL.build_tpl_form( $('#tpl-src-form').val(), 'rel-tpl' );
}

function tpl_fill_form ()
{
	$.each($('.rel-input'), function(i,el){
		var $el = $(el);
		var id = $el.attr('id');
		if ($el.val() != '') return true; // continue
		if (TPL.el_attr[id] != null) {
			if (TPL.el_attr[id][0] == 'SEL') {
				$el[0].selectedIndex = 1;
			}
			else {
				var v = TPL.el_def_val[id] || TPL.el_attr[id][1];
				$el.val(v);
			}
		}
		else {
			var v = TPL.el_titles[id] || 'значение не найдено';
			$el.val(v);
		}
	});
}

function tpl_preview_msg ()
{
	TPL.build_msg_src();
	$('#preview-msg, #preview-title').val('');
	$('#preview-html-body').html('');
	return TPL.build_msg_all('preview-msg', 'preview-title');
}

function tpl_build_msg (scroll)
{
	if ( tpl_preview_msg() ) {
		$('#preview-block').show();
		if (scroll) {
			$.scrollTo('#preview-block');
		}
	}
}

function str_pad ( input, pad_length, pad_string, pad_type ) {
	// http://kevin.vanzonneveld.net

	if (pad_string == null) {
		pad_string = ' ';
	}
	if (pad_type == null) {
		pad_type = 'STR_PAD_RIGHT';
	}
	var half = '', pad_to_go;

	var str_pad_repeater = function(s, len) {
		var collect = '', i;

		while(collect.length < len) collect += s;
		collect = collect.substr(0,len);

		return collect;
	};

	input += '';

	if (pad_type != 'STR_PAD_LEFT' && pad_type != 'STR_PAD_RIGHT' && pad_type != 'STR_PAD_BOTH') { pad_type = 'STR_PAD_RIGHT'; }
	if ((pad_to_go = pad_length - input.length) > 0) {
		if (pad_type == 'STR_PAD_LEFT') { input = str_pad_repeater(pad_string, pad_to_go) + input; }
		else if (pad_type == 'STR_PAD_RIGHT') { input = input + str_pad_repeater(pad_string, pad_to_go); }
		else if (pad_type == 'STR_PAD_BOTH') {
			half = str_pad_repeater(pad_string, Math.ceil(pad_to_go/2));
			input = half + input + half;
			input = input.substr(0, pad_length);
		}
	}

	return input;
}
<!-- ENDIF / EDIT_TPL -->

function tpl_submit ()
{
	if ( TPL.build_msg_all('tpl-post-message', 'tpl-post-subject') ) {
	 $('#tpl-post-form').submit();
	}
}

$(document).ready(function(){
});
</script>

<h1 class="maintitle"><a href="{FORUM_URL}{FORUM_ID}">{FORUM_NAME}</a></h1>

<div class="nav">
	<p class="floatL"><a href="{U_INDEX}">{T_INDEX}</a></p>
	<p class="floatR">
		<!-- IF CAN_EDIT_TPL and not EDIT_TPL --><a href="{EDIT_TPL_URL}" class="adm">Редактировать шаблон</a> &nbsp;&middot;&nbsp;<!-- ENDIF -->
		<a href="{REGULAR_TOPIC_HREF}">Создать обычную тему</a>
	</p>
	<div class="clear"></div>
</div>

<!-- IF not EDIT_TPL -->
<div style="display: none;">
	<textarea id="tpl-src-form" rows="10" cols="10"></textarea>
	<textarea id="tpl-src-title" rows="10" cols="10"></textarea>
	<textarea id="tpl-src-msg" rows="10" cols="10"></textarea>
</div>
<script type="text/javascript">
$(document).ready(function(){
	TPL.build_tpl_form( $('#tpl-src-form').val(), 'rel-tpl' );
	initPostBBCode('#tpl-rules-html');
});
</script>
<?php require(BB_ROOT .'misc/tpl/posting_tpl_common_header.html') ?>
<!-- IF TPL_RULES_HTML -->
<table class="forumline">
<tr>
	<th>Правила оформления</th>
</tr>
<tr>
	<td class="row1">
	<div class="w95 bCenter" style="padding: 12px;" id="tpl-rules-html">{TPL_RULES_HTML}</div>
	<div class="clear"></div>
	</td>
</tr>
</table>
<div class="spacer_12"></div>
<!-- ENDIF / TPL_RULES_HTML -->
<!-- ENDIF / !EDIT_TPL -->

<!-- IF EDIT_TPL -->
<table class="forumline">
<col class="row2" width="75%">
<col class="row2" width="25%">
<thead>
<tr>
	<th colspan="2">Создание шаблона для релиза</th>
</tr>
</thead>

<tbody id="rel-preview" style="display: none;">
<tr>
	<td colspan="2" style="padding: 6px; background: #FCFCFC;">
	<div id="row-preview-win">
		<table class="forumline">
		<col class="row2" width="20%">
		<col class="row3" width="80%">
		<tbody id="tpl-row-preview">
		</tbody>
		</table>
	</div>
	</td>
</tr>
</tbody>

<tbody id="rel-construct" style="display: none;">
<tr>
	<td colspan="2" class="row2" style="padding: 4px 12px;">

	<table class="borderless w100">
	<tr>
		<td>
			<span id="tpl-el_attr-sel"></span>
			<span id="tpl-el_id-sel"></span>
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" id="tpl-row-src" value="" style="width: 100%;" /><br />
			<a class="med" href="#" onclick="$('#tpl-row-src').val(''); return false;">очистить</a> &nbsp;&middot;&nbsp;
			<a class="med" href="#" onclick="$('#tpl-src-form').val( $('#tpl-src-form').val() +'\n<-'+ $('#tpl-row-src').val() +' ->' ).focus(); return false;">добавить в форму</a> &nbsp;&middot;&nbsp;
			<a class="med" href="#" onclick="$('#tpl-row-src').trigger('keypress', [13]).focus(); return false;" title="Нажать Enter">обновить результат (enter)</a>
		</td>
	</tr>
	</table>

	</td>
</tr>
</tbody>

<tbody id="rel-create">
<tr>
	<td valign="top">
	<div style="width: 99%">
		<div>
			<div class="med">
				<div class="floatL">форма: <a href="#tpl-help-form" class="menu-root menu-alt1">[?]</a></div>
				<div class="floatR"><a class="adm bold" href="#" onclick="$('#rel-preview, #rel-construct').toggle(); $('#tpl-src-form').focus(); return false;">[ Конструктор/Элементы ]</a> <a href="#tpl-help-preview" class="menu-root menu-alt1">[?]</a></div>
				<div class="clear"></div>
			</div>
			<textarea id="tpl-src-form" rows="16" cols="10" wrap="off" style="width: 100%"></textarea>
			<div class="med">название: <a href="#tpl-help-title" class="menu-root menu-alt1">[?]</a></div>
			<textarea id="tpl-src-title" rows="2" cols="10" style="width: 100%"></textarea>
		</div>
		<div style="padding-top: 2px;">
			<div class="floatL" style="padding-top: 2px;">
				<a id="toggle-info-a" class="adm bold" href="#" onclick="ajax.topic_tpl('toggle_info'); return false;">[ Инфо/Изменить ]</a> &nbsp;
				<a id="toggle-new-a" class="adm bold" href="#" onclick="ajax.topic_tpl('toggle_new'); return false;">[ Создать новый ]</a> &nbsp;
				<a class="adm bold" href="#" onclick="ajax.topic_tpl('assign', {tpl_id: -1}); return false;">[ Отключить ]</a> &nbsp;
				<a href="#tpl-help-ctl" class="menu-root menu-alt1">[?]</a>
			</div>
			<div class="floatR">
				<input id="tpl-build-msg-btn" type="button" value="Создать сообщение" onclick="tpl_build_msg(false);" style="display: none;" />&nbsp;
				<input id="tpl-build-form-btn" type="button" value="Создать форму" onclick="tpl_build_form();" />&nbsp;
			</div>
		</div>
		<div class="clear"></div>

		<div id="tpl-info-block" style="display: none;" class="tpl-adm-block med row3">
			<br />
			<fieldset>
			<legend>Включить/Загрузить</legend>
			<div style="padding: 2px 12px 6px;">
			Шаблоны: &nbsp;
			<!-- IF TPL_SELECT -->{TPL_SELECT} &nbsp;
			<input type="button" value="Включить в этом форуме" class="bold" onclick="ajax.topic_tpl('assign', {tpl_id: $('#forum_tpl_select').val()})" /> &nbsp;
			<input type="button" value="Загрузить" onclick="ajax.topic_tpl('load')" /> &nbsp;
			<!-- ELSE -->Нет щаблонов для релизов<!-- ENDIF -->
			<br /><br />
			<span class="gen">
			<!-- IF NO_TPL_ASSIGNED -->
			В этом форуме шаблоны <b>не включены</b><br />
			<!-- ELSE -->
			Сейчас в этом форуме включен шаблон <b>{TPL_NAME}</b><br />
			<!-- ENDIF -->
			</span>
			</div>
			</fieldset>
			<br />

			<div <!-- IF NO_TPL_ASSIGNED -->style="display: none;"<!-- ENDIF --> id="tpl-save-block">
			<fieldset>
			<legend>Сохранить изменения для шаблона <b id="tpl-name-old-save">{TPL_NAME}</b></legend>
			<div style="padding: 2px 12px 6px;">
			<div class="label">Новое название шаблона:</div>
			<input type="text" id="tpl-name-save" size="60" value="{TPL_NAME}" maxlength="60" class="bold" style="width: 75%" /><br />

			<div class="label"><a href="{POST_URL}{TPL_RULES_POST_ID}#{TPL_RULES_POST_ID}" id="tpl-rules-link" target="_blank">Правила</a> (ссылка на сообщение с правилами или номер сообщения):</div>
			<input type="text" id="tpl-rules-save" size="60" value="{TPL_RULES_POST_ID}" style="width: 75%" /><br />

			<div class="label">Комментарий:</div>
			<textarea id="tpl-comment-save" rows="2" cols="80" class="editor" style="width: 90%">{TPL_COMMENT}</textarea>

			<div class="label">Последний раз редактировалось: <i id="tpl-last-edit-time">{TPL_LAST_EDIT_TIME}</i> by <b id="tpl-last-edit-by">{TPL_LAST_EDIT_USER}</b></div>
			<br />

			<input type="hidden" id="tpl-id-save" value="{TPL_ID}">
			<input type="hidden" id="tpl-last-edit-tst" value="{TPL_LAST_EDIT_TIMESTAMP}">
			<input type="button" class="bold" value="Сохранить изменения" onclick="ajax.topic_tpl('save')" />
			<br />
			</div>
			</fieldset>
			<br />
			</div>
			<div id="tpl-load-resp"></div>
		</div>

		<div id="tpl-new-block" style="display: none;" class="tpl-adm-block med row3">
			<div class="label">Название шаблона: *</div>
			<input type="text" id="tpl-name-new" size="60" value="" maxlength="60" class="bold" style="width: 75%" /><br />

			<div class="label">Правила (ссылка на сообщение с правилами или номер сообщения):</div>
			<input type="text" id="tpl-rules-new" size="60" value="" style="width: 75%" /><br />

			<div class="label">Комментарий:</div>
			<textarea id="tpl-comment-new" rows="2" cols="10" class="editor" style="width: 100%"></textarea><br />

			<input type="button" class="bold" value="Создать новый шаблон" onclick="ajax.topic_tpl('new');" /><br /><br />
			<div id="tpl-new-resp"></div>
		</div>
	</div>
	</td>
	<td valign="top">
	<div style="width: 98%">
		<div>
			<p class="med">сообщение: <a href="#tpl-help-msg" class="menu-root menu-alt1">[?]</a></p>
			<textarea id="tpl-src-msg" rows="20" cols="10" wrap="off" class="editor" style="width: 100%"></textarea>
			<div id="msg-attr-list" class="pad_4 med"></div>
		</div>
	</div>
	</td>
</tr>
</tbody>

<tbody id="preview-block" style="display: none;">
<tr><td colspan="2" class="row3">результат [ <a class="med" href="#" onclick="$('#preview-block').hide(); return false;">скрыть</a> ]</td></tr>
<tr>
	<td colspan="2">
	<div style="width: 99%">
		<div><input type="text" id="preview-title" size="60" value="" class="bold" style="width: 100%" /></div>
		<div><textarea id="preview-msg" rows="15" cols="10" wrap="off" class="editor" style="width: 100%"></textarea></div>
		<div class="tCenter">
			<input type="button" value="Создать HTML" onclick="ajax.posts( $('#preview-msg').val(), 'preview-html-body' );" class="bold" />
		</div>
	</div>
	</td>
</tr>
<tr>
	<td colspan="2" class="row1">
		<div class="post_wrap"><div id="preview-html-body" class="post_body">&nbsp;</div></div>
	</td>
</tr>
</tbody>

<tfoot>
<tr>
	<td colspan="2">
	<div class="tRight med">[ <u class="clickable" onclick="$('#tpl-howto').toggle();">Инструкция</u> ]</div>
	<div id="tpl-howto" class="med pad_12" style="display: none;">
	После заполнения поля <i>форма</i> нажмите кнопку <i>Создать форму</i><br /><br />
	В поле <i>сообщение</i> добавьте элементам необходимые атрибуты (req, spoiler и т.д.)<br /><br />
	Заполните созданную форму (вручную либо автозаполнителем)<br /><br />
	Кнопки <i>Продолжить</i> и <i>Создать сообщение</i> создают ббкод сообщения<br /><br />
	Кнопка <i>Создать HTML</i> - создает HTML сообщения<br /><br />
	Заполните поле <i>название</i>
	</div>
	</td>
</tr>
</tfoot>
</table>
<div class="spacer_12"></div>
<!-- ENDIF / EDIT_TPL -->

<div style="display: none;">
<form id="tpl-post-form" method="post" action="{TPL_FORM_ACTION}" name="post" class="tokenized">
	<input type="hidden" name="tor_required" value="{TOR_REQUIRED}">
	<input type="hidden" name="preview" value="1">
	<input id="tpl-post-subject" type="text" name="subject" size="90" value="" />
	<textarea id="tpl-post-message" name="message" rows="1" cols="1"></textarea>
</form>
</div>

<div id="rel-form" style="display: none;">
<table class="forumline">
<col class="row1" width="20%">
<col class="row2" width="80%">
<thead>
<tr>
	<th colspan="2">Заполните форму для релиза<!-- IF EDIT_TPL --> &nbsp; [ <u class="clickable" onclick="tpl_fill_form();">Заполнить</u> ]<!-- ENDIF --></th>
</tr>
</thead>
<tbody id="rel-tpl">
</tbody>
<tfoot>
<tr>
	<td colspan="2" class="pad_8 tCenter bold">На следующей странице проверьте оформление и загрузите torrent файл</td>
</tr>
<tr>
	<td class="catBottom" colspan="2">
		<!-- IF EDIT_TPL -->
		<input type="button" value="Заполнить" style="width: 120px;" onclick="tpl_fill_form();" />&nbsp;&nbsp;
		<input type="button" value="Продолжить" class="bold" style="width: 150px;" onclick="tpl_build_msg(true);" />
		<!-- ELSE -->
		<input type="button" value="Продолжить" class="bold" style="width: 150px;" onclick="tpl_submit(true);" />
		<!-- ENDIF -->
	</td>
</tr>
</tfoot>
</table>
</div>

<!-- IF EDIT_TPL -->
<div id="tpl-help-form" class="menu-sub tpl-help-msg" style="width: 800px;">
	<h4>Скрипт для построения формы</h4>
	<br />
	Формат: <b class="hlp-1">&lt;-</b><b>название_строки_формы</b> &nbsp; <b>элементы</b><b class="hlp-1">-&gt;</b>
	<br /><br />
	Каждый элемент задается в виде: <b class="hlp-1">ТИП</b>[<b>имя_элемента</b>,<b class="hlp-2">опциональные_атрибуты</b>]
	<br /><br />
	<b>INP</b> - однострочное поле для ввода текста, опционально можно указать количество вводимых символов и ширину поля<br />
	<b>INP[genre,200,70]</b> - можно ввести максимум 200 символов, ширина поля 70 символов (ширину больше 80-ти делать не нужно)
	<br /><br />
	<b>TXT</b> - многострочное поле для ввода текста, опционально можно указать высоту<br />
	<b>TXT[casting,10]</b> - высота поля будет 10 строк
	<br /><br />
	<b>SEL</b> - раскрывающийся список с выбором<br />
	<b>SEL[video_quality]</b>
	<br /><br />
	<b>E</b> - статичный либо скрытый элемент (обычно не имеющий названия и не являющийся полем ввода текста)<br />
	<b>E[load_pic_btn]</b> - кнопка загрузки картинки
	<br /><br />
	<b>T</b> - вставляет только название элемента<br />
	<b>T[rus_sub]</b> - добавляет в форму название элемента <b>Русские субтитры</b>
	<br /><br />
	<b class="hlp-2">`</b><b class="hlp-1">текст...</b><b class="hlp-2">`</b> - любой текст и спец. элементы типа `BR` (добавить перевод строки)<br />
	<b class="hlp-2">`</b>на русском<b class="hlp-2">`</b> - добавляет в форму текст <i>на русском</i>
</div>

<div id="tpl-help-title" class="menu-sub tpl-help-msg" style="width: 800px;">
	<h4>Скрипт для построения названия топика</h4>
	<br />
	Формат: <b class="hlp-1">&lt;-</b><b>группа элементов</b><b class="hlp-1">-&gt;</b><b class="hlp-2">объединитель для этой группы</b>
	<br /><br />
	пример:<br />
	<p class="gen bold pad_8">
		<b class="hlp-1">&lt;-</b>title_rus title_eng<b class="hlp-1">-&gt;</b><b class="hlp-2">/</b>
		<b class="hlp-1">&lt;-</b>director year<b class="hlp-1">-&gt;</b><b class="hlp-2">(,)</b>
		<b class="hlp-1">&lt;-</b>genre video_quality<b class="hlp-1">-&gt;</b><b class="hlp-2">[,]</b>
	</p>
	создаст:<br />
	<p class="gen bold pad_8">
		Название <b class="hlp-2">/</b> Оригинальное название
		<b class="hlp-2">(</b>Режиссер<b class="hlp-2">,</b> 2000 г.<b class="hlp-2">)</b>
		<b class="hlp-2">[</b>Жанр<b class="hlp-2">,</b> DVDRip<b class="hlp-2">]</b>
	</p>
</div>

<div id="tpl-help-msg" class="menu-sub tpl-help-msg" style="width: 600px;">
	<h4>Скрипт для построения сообщения</h4>
	<br />
	Формат: <b>имя_элемента</b>[<i>атрибут1,атрибут2</i>]
	<br /><br />
	При создании формы (кнопка <i>Создать форму</i> и при построении того что видит юзер)
	этот скрипт каждый раз проверяется на соответстие элементам формы. При этом отсутствующие в форме элементы из него удаляются,
	а прописанные в форме, но в нем не найденные, добавляются.
	<br /><br />
	Порядок элементов зависит от того как они прописаны в форме
	<br /><br />
	Описание атрибутов - во всплывающей подсказке (наведите мышку на любой атрибут в списке снизу)
</div>

<div id="tpl-help-preview" class="menu-sub tpl-help-msg" style="width: 400px;">
	<h4>Конструктор и предпросмотр элементов</h4>
	<br />
	В IE часть функций не работает!
	<br /><br />
	Подставляет в строку конструктора текущую строку из формы<br />
	В конструкторе для обновления предпросмотра нужно нажать enter<br />
	Скрытые элементы выделены красным цветом<br />
</div>

<div id="tpl-help-ctl" class="menu-sub tpl-help-msg" style="width: 400px;">
	Клик по <b class="adm">[ Инфо/Изменить ]</b> открывает/закрывает окно опций (так же работают другие кнопки)
</div>
<!-- ENDIF -->

<div style="display: none;">
	<!-- TPL.el_id элементы, для E[el] в форму подставляется $(el).html() -->
	<?php require(BB_ROOT .'misc/tpl/posting_tpl_el_id.html') ?>
</div>
<div style="display: none;">
	<!-- исходные значения всех #tpl-src -->
	<textarea id="tpl-src-form-val" rows="10" cols="10">{TPL_SRC_FORM_VAL}</textarea>
	<textarea id="tpl-src-title-val" rows="10" cols="10">{TPL_SRC_TITLE_VAL}</textarea>
	<textarea id="tpl-src-msg-val" rows="10" cols="10">{TPL_SRC_MSG_VAL}</textarea>
</div>

<noscript><div class="warningBox2 bold tCenter">Для показа необходимo включить JavaScript</div></noscript>
