<style type="text/css">
td.rel-inputs { padding-left: 6px; }
.rel-el      { margin: 2px 6px 2px 0; }
.rel-title   { font-weight: bold; }
.rel-input   { }
.rel-free-el { font-size: 11px; line-height: 12px; }

textarea.rel-input { width: 98%; }
#rel-create textarea, #tpl-row-src { font-size: 13px; font-family: "Lucida Console","Courier New",Courier,monospace; }

.tpl-err-hl-input { border: 1px solid #8C0000; background: #FFF9F2; }
.tpl-err-hl-tr    { background: #FFEAD5; }
.tpl-err-hint     { color: #8C0000; margin-right: 6px; }
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

$(function(){

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
		if (k === 13 /* Enter */) {
			TPL.build_tpl_form( '<-'+ v +' ->', 'tpl-row-preview' );
			$('#tpl-row-src').val(v);
		}
	});

	// подстановка текущей строки в #tpl-row-src и обновление предпросмора элемента
	$('#tpl-src-form').bind('mouseup keyup focus', function(e){
		if (!$('#rel-preview:visible')[0]) return;
		if (e.keyCode) {
			if ( !(e.keyCode === 38 /*up*/ || e.keyCode === 40 /*down*/) ) return;
		}
		var ss = this.selectionStart;
		if (ss === null) return;
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

		if (src === '') {
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
		poster      : 'https://torrentpier.me/styles/default/xenforo/logo_me.png',
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
			if (row === null || row === '') return true; // continue
			TPL.rows[i] = $.trim(row);
		});
		$.each(TPL.rows, function(i,row){
			var mr = TPL.match_cols(row);
			if (mr[2] === null) return true; // continue
			var title_id = mr[1];    // id элемента для подстановки его названия или {произвольное название}
			var input_els = mr[2];
			var row_title = (TPL.el_attr[title_id] !== null) ? TPL.el_attr[title_id][1] : TPL.trim_brackets(title_id);
			var $tr = $('<tr><td class="rel-title">'+ row_title +':</td><td class="rel-inputs"></td></tr>');
			var $td = $('td.rel-inputs', $tr);

			$.each(TPL.match_els(input_els), function(j,el){
				if (!(el = TPL.trim_brackets(el))) return true; // continue
				var el_html = '';
				var me = TPL.match_el_attrs(el);
				// вставка шаблонного элемента типа TYPE[attr]
				if (me[2] !== null) {
					var at = me[2].split(',');
					var nm = at[0];

					switch (me[1])
					{
					case 'E':
						if ( $('#'+ nm +'-hid').length ) {
							if (res_id === 'tpl-row-preview') {
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
						var def = (TPL.el_attr[id] !== null) ? TPL.el_attr[id][2].split(',') : [200,80];
						var mlem = at[1] || def[0];
						var size = at[2] || def[1];
						el_html = '<input class="rel-el rel-input" type="text" id="'+ id +'" maxlength="'+ mlem +'" size="'+ size +'" />';
						break;
					case 'TXT':
						var id = TPL.build_el_id_title(nm);
						var def = (TPL.el_attr[id] !== null) ? TPL.el_attr[id][2].split(',') : [3];
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
					if (el === 'BR') {
						el_html = '<br />';
					}
					else {
						el_html = '<span class="rel-el rel-free-el">'+ escHTML(el) +'</span>';
					}
				}
				// добавление элемента в td.rel-inputs
				if (el_html !== '') {
					$td.append(el_html);
				}
			});
			// добавление tr в форму
			$('#'+res_id).append($tr);
		});

		TPL.build_msg_src();
		$('#rel-form').show();

		$('select.rel-input').bind('change', function(){
			var $sel = $(this);
			if ( $sel.val().toLowerCase().match(/^друг(ой|ая|ое|ие)$/) ) {
				var $input = $('<input class="rel-el rel-input" type="text" id="'+ $sel.attr('id') +'" style="width: '+ $sel.width() +'px;" />');
				$sel.after($input);
				$sel.remove();
				$input.focus();
			}
		});
	},

	build_el_id_title: function(nm) {
		if (TPL.el_attr[nm] !== null) {
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
		return (TPL.el_attr[el] !== null || TPL.el_id[el] !== null) ? el : $P.md5(el);
	},

	build_select_el: function(name) {
		var sel_id = TPL.get_el_id(name);
		if (TPL.selects[sel_id] === null) return '';
		var s = '<select class="rel-el rel-input" id="'+sel_id+'">';
		var q = /"/g;  //"
		$.each(TPL.selects[sel_id], function(i,v){
			s += '<option value="'+(i===0 ? '' : v.replace(q, '&quot;'))+'">'+(v==='' ? '&raquo; Выбрать' : v)+'</option>';
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
			if (m === null) return true; // continue
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
		while((m = r.exec(t)) !== null) {
			r_gen.push(m[1]);
		}
		// создание нового (старые значения сохраняются без именений, новые добавляются, отсутствующие в форме удаляются)
		$.each(r_gen, function(i,v){
			if (r_old[v] !== null) {
				r_new.push( r_old[v] );
			}
			else {
				var def_attr = (TPL.el_attr[v] !== null) ? TPL.el_attr[v][3] : '';
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
		if (TPL.el_attr[el] !== null) {
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
		if (TPL.f_errors_cnt === 0) $('#'+el_id).focus();
		TPL.f_errors_cnt++;
	},
	// сообщения об ошибках при валидации заполнения формы
	err_msg: {
		empty_INP : '{L_TPL_EMPTY_FIELD}',
		empty_TXT : '{L_TPL_EMPTY_FIELD}',
		empty_SEL : '{L_TPL_EMPTY_SEL}',
		not_num   : '{L_TPL_NOT_NUM}',
		not_url   : '{L_TPL_NOT_URL}',
		not_img   : '{L_TPL_NOT_IMG_URL}'
	},

	msg_attr: {
		HEAD     : '{L_TPL_PUT_INTO_SUBJECT}',
		POSTER   : '{L_TPL_POSTER}',
		req      : '{L_TPL_REQ_FILLING}',
		spoiler  : '{L_TPL_SPOILER}',
		BR       : '{L_TPL_NEW_LINE}',
		br2      : '{L_TPL_NEW_LINE_AFTER}',
		num      : '{L_TPL_NUM}',
		URL      : '{L_TPL_URL}',
		img      : '{L_TPL_IMG}',
		pre      : '{L_TPL_PRE}',
		inline   : '{L_TPL_IN_LINE}',
		headonly : '{L_TPL_HEADER_ONLY}'
	},
	reg: {
		num     : /^\d+$/,
		URL     : /^https?:\/\/[\w\#$%&~/.\-;:=?@\[\]+]+$/i,
		img     : /^https?:\/\/[^\s\?&;:=\#\"<>]+\.(jpg|jpeg|gif|png)$/i,
		img_tag : /(https?:\/\/[^\s\?&;:=\#\"<>]+\.(jpg|jpeg|gif|png)(?!\[|\]|\.))/ig
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
		var msg_body = [];
		var msg_els = TPL.get_msg_els( $('#tpl-src-msg').val(), true );

		$.each(msg_els, function(el,at){
			var el_id = TPL.get_el_id(el);
			var el_val = $('#'+el_id).val() || '';

			// требуемые поля
			if (el_val === '') {
				if ($.inArray('req', at) !== -1) {
					var el_type = (TPL.el_attr[el] !== null) ? TPL.el_attr[el][0] : 'INP';
					TPL.hl_form_err(el, 'empty_'+ el_type);
				}
				return true; // continue
			}

			// валидация значений
			if ($.inArray('num', at) !== -1 && !TPL.reg['num'].test(el_val)) {
				TPL.hl_form_err(el, 'not_num');
				return true; // continue
			}
			if ($.inArray('URL', at) !== -1 && !TPL.reg['URL'].test(el_val)) {
				TPL.hl_form_err(el, 'not_url');
				return true; // continue
			}
			if ($.inArray('img', at) !== -1 && !TPL.reg['img'].test(el_val)) {
				TPL.hl_form_err(el, 'not_img');
				return true; // continue
			}

			// post-submit обработка значений
			el_val = TPL.normalize_val(el, el_val);

			// заголовок
			if ($.inArray('HEAD', at) !== -1) {
				msg_header.push( el_val );
				return true; // continue
			}
			// постер
			if ($.inArray('POSTER', at) !== -1) {
				msg_poster = el_val;
				return true; // continue
			}

			// новая строка после названия
			if ($.inArray('br2', at) !== -1) {
				el_val = '\n'+ el_val;
			}
			// спойлер
			if ($.inArray('spoiler', at) !== -1) {
				msg_body.push( TPL.build_spoiler(el_id, el_val) );
				return true; // continue
			}
			// pre
			if ($.inArray('pre', at) !== -1) {
				msg_body.push( TPL.build_pre(el_id, el_val) );
				return true; // continue
			}
			// inline
			if ($.inArray('inline', at) !== -1) {
				msg_body.push( TPL.build_inline(el_id, el_val) );
				return true; // continue
			}
			// только в заголовке
			if ($.inArray('headonly', at) !== -1) {
				return true; // continue
			}
			// обычный элемент
			msg_body.push( TPL.build_msg_el(el_id, el_val) );

			// новая строка после элемента
			if ($.inArray('BR', at) !== -1) {
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
		return '[size=24]'+ a.join(' / ') +'[/size]\n';
	},
	build_msg_poster: function(s) {
		return TPL.reg['img'].test(s) ? '\n[img=right]'+ s +'[/img]\n' : s;
	},
	build_spoiler: function(el_id, el_val) {
		return '\n[spoiler="'+ TPL.el_titles[el_id] +'"]\n'+ el_val +'\n[/spoiler]\n';
	},
	build_pre: function(el_id, el_val) {
		return '\n[spoiler="'+ TPL.el_titles[el_id] +'"][pre]\n'+ el_val +'\n[/pre][/spoiler]\n';
	},
	build_inline: function(el_id, el_val) {
		return ' '+ TPL.el_titles[el_id] +' '+ el_val;
	},
	build_msg_el: function(el_id, el_val) {
		return '\n[b]'+ TPL.el_titles[el_id] +'[/b]: '+ el_val;
	},

	build_title: function(res_id) {
		var title = [];
		var trim_after_chars = {};
		var trim_before_chars = {};
		var g;                                                   // группа элементов <-el1 el2->[,]
		var t = $('#tpl-src-title').val().replace(/\n/g, ' ');   // формат
		var r = /<-([^>]+)->(\S*)/g;
		while((g = r.exec(t)) !== null) {
			var g_els = g[1].match(/(\w+|\{.+?\})/g);
			if (g_els === null) return true; // continue

			var g_start_char = ' ';
			var g_delim_char = ' ';
			var g_end_char   = ' ';

			if (g[2].length === 1) {
				g_delim_char = ' '+ g[2];
			}
			else if (g[2].length === 3) {
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
				if (v === undefined || $.trim(v) === '') return true; // continue
				v = TPL.normalize_val(el_id, v);
				g_vals.push(' '+ v +' ');
			});
			if (g_vals.length !== 0) {
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

/*
  -------------------------------------------------------------------------------------------------
  -- el_attr --------------------------------------------------------------------------------------
  -------------------------------------------------------------------------------------------------
*/
TPL.el_attr = {
	/*
    код_элемента = ID элемента в форме
    все элементы имеют class "rel-input"
    формат el_attr
      код_элемента: [
        [0] - тип
        [1] - название
        [2] - атрибуты элемента типа size,rows.. по умолчанию (в том же порядке как и опциональные для элемента)
        [3] - атрибуты типа HEAD,req.. по умолчанию для формата сообщения
      ]
    формат элементов в #tpl-src-form (включая все опциональные атрибуты типа maxlength..)
      INP - input[name,maxlength,size]
      TXT - textarea[name,rows]
      SEL - select[name]               -- значения для селектов находятся в TPL.selects
  */
	poster_size: ['INP', 'максимальный 500х500 пикселей', '200,80', ''],
	audio_codec: ['SEL', 'Аудио кодек', '', ''],
	audio: ['INP', 'Аудио', '200,80', ''],
	audio_bitrate: ['SEL', 'Битрейт аудио', '', ''],
	casting: ['TXT', 'В ролях', '3', 'BR'],
	game_version: ['SEL', 'Версия игры', '', ''],
	video_codec: ['SEL', 'Видео кодек', '', ''],
	video: ['INP', 'Видео', '200,80', ''],
	game_age: ['SEL', 'Возраст', '', ''],
	year: ['INP', 'Год выпуска', '4,5', 'num'],
	moreinfo: ['TXT', 'Доп. информация', '3', 'BR'],
	genre: ['INP', 'Жанр', '200,40', ''],
	video_quality: ['SEL', 'Качество видео', '', ''],
	video_quality_new: ['SEL', 'Качество видео', '', ''],
	book_quality: ['SEL', 'Качество', '', ''],
	game_multiplay: ['SEL', 'Мультиплейер игры', '', ''],
	title_rus: ['INP', 'Название', '90,80', 'HEAD,req'],
	description: ['TXT', 'Описание', '6', 'BR'],
	title_eng: ['INP', 'Оригинальное название', '90,80', 'HEAD'],
	translation: ['SEL', 'Перевод', '', ''],
	translation2: ['SEL', 'Перевод 2', '', ''],
	translation3: ['SEL', 'Перевод 3', '', ''],
	translation4: ['SEL', 'Перевод 4', '', ''],
	game_plat_wii: ['SEL', 'Платформа Wii', '', ''],
	game_platform: ['SEL', 'Платформа игры', '', ''],
	poster: ['INP', 'Постер', '200,80', 'img,POSTER'],
	playtime: ['INP', 'Продолжительность', '200,30', ''],
	game_firmware: ['SEL', 'Прошивка', '', ''],
	game_region: ['SEL', 'Регион игры', '', ''],
	game_region_def: ['SEL', 'Регион игры', '', ''],
	director: ['INP', 'Режиссер', '200,50', ''],
	rus_sub: ['SEL', 'Русские субтитры', '', ''],
	sub_all: ['SEL', 'Cубтитры', '', ''],
	sub_all_new: ['SEL', 'Cубтитры', '', ''],
	screenshots: ['TXT', 'Скриншоты', '3', 'spoiler'],
	screenshots_about: ['TXT', 'Скриншоты окна About', '3', 'spoiler'],
	vista_compat: ['SEL', 'Совместимость с Vista', '', ''],
	vista_compat_new: ['SEL', 'Совместимость с Vista', '', ''],
	windows7_compat: ['SEL', 'Совместимость с Windows 7', '', ''],
	country: ['INP', 'Страна', '200,50', ''],
	crack_exists: ['SEL', 'Таблэтка', '', ''],
	//в аудиокнигах, огрызке (apple)
	abook_type: ['SEL', 'Тип аудиокниги', '', ''],
	publishing_type: ['SEL', 'Тип издания', '', ''],
	game_trans_type: ['SEL', 'Тип перевода игры', '', ''],
	video_format: ['SEL', 'Формат видео', '', ''],
	video_format_new: ['SEL', 'Формат видео', '', ''],
	book_format: ['SEL', 'Формат', '', ''],
	game_lang_psp: ['SEL', 'Язык интерфейса игры', '', ''],
	gui_lang: ['SEL', 'Язык интерфейса', '', ''],
	book_lang: ['SEL', 'Язык', '', ''],
	maps_lang: ['SEL', 'Язык интерфейса', '', ''],
	gui_lang_new: ['SEL', 'Язык интерфейса', '', ''],
	tabletka_new: ['SEL', 'Таблэтка', '', ''],
	cpu_bits: ['SEL', 'Разрядность', '', ''],
	maps_format: ['SEL', 'Формат ', '', ''],
	atlas_type: ['SEL', 'Тип атласа ', '', ''],
	lang_book_avto: ['SEL', 'Язык авто-книги ', '', ''],
	lang_book_med: ['SEL', 'Язык мед-книги ', '', ''],
	product_milestone: ['SEL', 'Стадия разработки ', '', ''],
	apple_ios_sysreq: ['SEL', 'Системные требования ', '', ''],
	apple_ios_lang: ['SEL', 'Язык интерфейса', '', ''],
	apple_ios_dev: ['SEL', 'Совместимые устройства', '', ''],
	apple_ios_def: ['SEL', 'Поддерживаемые разрешения', '', ''],
	apple_ios_format: ['SEL', 'Формат файлов', '', ''],
	//авто мультимедийки
	avto_mm_type: ['SEL', 'Тип мультимедиа ', '', ''],
	manga_type: ['SEL', 'Тип', '', ''],
	manga_completeness_with_header: ['SEL', 'Завершенность релиза', '', ''],
	sub_format: ['SEL', 'Формат субтитров', '', ''],
	orig_audio: ['SEL', 'Оригинальная аудиодорожка', '', ''],
	orig_audio_serial: ['SEL', 'Оригинальная аудиодорожка', '', ''],
	flang_lang: ['SEL', 'Язык курса', '', ''],
	anime_release_type: ['SEL', 'Тип релиза', '', ''],
	anime_hwp: ['SEL', 'Совместимость с бытовыми плеерами', '', ''],
	//для dorama и Live-action
	transl_dorama: ['SEL', 'Перевод', '', ''],
	sub_dorama: ['SEL', 'Неотключаемые субтитры', '', ''],
	lang_dorama: ['SEL', 'Язык', '', ''],
	lang_dorama_2: ['SEL', 'Язык', '', ''],
	video_codec_2: ['SEL', 'Видео кодек', '', ''],
	audio_codec_2: ['SEL', 'Аудио кодек', '', ''],
	video_format_dorama: ['SEL', 'Формат', '', ''],
	game_type_edition: ['SEL', 'Тип издания', '', ''],
	game_lang: ['SEL', 'Язык интерфейса', '', ''],
	game_lang_sound: ['SEL', 'Язык озвучки', '', ''],
	game_tabletka: ['SEL', 'Таблэтка', '', ''],
	lang_psp: ['SEL', 'Язык интерфейса', '', ''],
	lang_sound_psp: ['SEL', 'Язык озвучки', '', ''],
	sub_psp: ['SEL', 'Cубтитры', '', ''],
	funct_psp: ['SEL', 'Работоспособность проверена', '', ''],
	multiplayer_psp: ['SEL', 'Мультиплеер', '', ''],
	popsloader_psp: ['SEL', 'Рекомендуемый POP"s', '', ''],
	lang_mob: ['SEL', 'Язык интерфейса', '', ''],
	format_clipart: ['SEL', 'Формат изображений', '', ''],
	format_photostocks: ['SEL', 'Формат изображений', '', ''],
	format_photo: ['SEL', 'Формат изображений', '', ''],
	suit_type: ['SEL', 'Тип костюмов', '', ''],
	format_vector_clipart: ['SEL', 'Формат изображений', '', ''],
	format_3d_model: ['SEL', 'Формат моделей', '', ''],
	format_3d: ['SEL', 'Формат файлов', '', ''],
	material: ['SEL', 'Материалы', '', ''],
	texture: ['SEL', 'Текстуры', '', ''],
	light_source: ['SEL', 'Источники света', '', ''],
	folder_pdf: ['SEL', 'Каталог PDF', '', ''],
	video_footage: ['SEL', 'Видеоформат', '', ''],
	frame_rate: ['SEL', 'Частота кадров FPS', '', ''],
	def_footage: ['SEL', 'Разрешение', '', ''],
	video_format_footage: ['SEL', 'Формат видеофайла', '', ''],
	lang_anime: ['SEL', 'Язык', '', ''],
	lang_anime_2: ['SEL', 'Язык', '', ''],
	lang_anime_3: ['SEL', 'Язык', '', ''],
	disk_number_psn: ['SEL', 'Количество дисков', '', ''],
	change_disk_psn: ['SEL', 'Переход с диска на диск во время игры', '', ''],
	genre_game_dvd: ['SEL', 'Жанр', '', ''],
	platform_game_dvd: ['SEL', 'Платформа', '', ''],
	tabletka_game_dvd: ['SEL', 'Таблэтка', '', ''],
	format_disk_game_dvd: ['SEL', 'Формат игрового диска', '', ''],
	format_video_game_dvd: ['SEL', 'Формат видео', '', ''],
	sub_game_video: ['SEL', 'Субтитры', '', ''],
	lang_game_video: ['SEL', 'Язык озвучки ', '', ''],
	format_game_video: ['SEL', 'Формат', '', ''],
	//трейлеры
	material_trailer: ['SEL', 'Тип раздаваемого материала', '', ''],
	transl_trailer: ['SEL', 'Перевод', '', ''],
	video_quality_trailer: ['SEL', 'Качество', '', ''],
	video_format_trailer: ['SEL', 'Формат видео', '', ''],
	video_codec_trailer: ['SEL', 'Видео кодек', '', ''],
	audio_codec_trailer: ['SEL', 'Аудио кодек', '', ''],
	lang_old_game: ['SEL', 'Язык', '', ''],
	//Apple: iPhone, iOS, Mac и проч.
	audio_bitrate_iphone_los: ['SEL', 'Битрейт', '', ''],
	rip_prog_iphone: ['SEL', 'Программа-риповщик ', '', ''],
	words_iphone: ['SEL', 'Тексты', '', ''],
	edition_type_iphone: ['SEL', 'Тип издания', '', ''],
	transl_iphone: ['SEL', 'Перевод ', '', ''],
	video_format_iphone: ['SEL', 'Формат', '', ''],
	video_codec_iphone: ['SEL', 'Видео кодек', '', ''],
	cover_iphone: ['SEL', 'Вшитая обложка', '', ''],
	tag_iphone: ['SEL', 'Доп. теги (режиссер, актеры и т.д.)', '', ''],
	show_iphone: ['SEL', 'Телешоу/видеоклип ', '', ''],
	chapter_iphone: ['SEL', 'Главы', '', ''],
	series_iphone: ['SEL', 'Серия/сезон', '', ''],
	audio_codec_iphone: ['SEL', 'аудио кодек', '', ''],
	audio_bitrate_iphone: ['SEL', 'Битрейт', '', ''],
	audio_chapters_iphone: ['SEL', 'Разбитие на главы', '', ''],
	platform_mob: ['SEL', 'Платформа', '', ''],
	mus_loss_performer: ['SEL', 'Исполнитель', '', ''],
	audiobook_label: ['SEL', 'Издательство', '', ''],
	platform_mac_prog: ['SEL', 'Платформа', '', ''],
	lang_mac_prog: ['SEL', 'Язык интерфейса', '', ''],
	tablet_mac_prog: ['SEL', 'Таблетка', '', ''],
	//спорт
	video_quality_sport: ['SEL', 'Качество', '', ''],
	//Музыкальные библиотеки и Звуковые эффекты
	audio_codec_music_lib: ['SEL', 'Аудио кодек', '', ''],
	bit_music_lib: ['SEL', 'Качество', '', ''],
	bitrate_music_lib: ['SEL', 'Битрейт', '', ''],
	rate_music_lib: ['SEL', 'Частота', '', ''],
	canales_music_lib: ['SEL', 'Каналы', '', ''],
	//Ноты и т.п.
	mus_edit: ['SEL', 'Редакция', '', ''],
	mus_lang: ['SEL', 'Язык', '', ''],
	//мульты и сериалы?
	transl_cartoons_0: ['SEL', 'Перевод', '', ''],
	transl_cartoons_1: ['SEL', 'Перевод 2', '', ''],
	transl_cartoons_2: ['SEL', 'Перевод 3', '', ''],
	format_cartoons_dvd: ['SEL', 'Формат', '', ''],
	type_cartoons: ['SEL', 'Качество', '', ''],
	screen_cartoons: ['SEL', 'Формат экрана', '', ''],
	def_cartoons: ['SEL', 'Система / Разрешение', '', ''],
	video_quality_cartoons: ['SEL', 'Качество', '', ''],
	format_cartoons: ['SEL', 'Формат', '', ''],
	video_quality_cartoons_hd: ['SEL', 'Качество', '', ''],
	format_cartoons_hd: ['SEL', 'Формат', '', ''],
	video_quality_cart_serial: ['SEL', 'Качество', '', ''],
	format_cart_serial: ['SEL', 'Формат', '', ''],
	video_codec_serials: ['SEL', 'Видео кодек', '', ''],
	audio_codec_serials: ['SEL', 'Аудио кодек', '', ''],
	//разное - аватарки
	type_avatar: ['SEL', 'Тип раздаваемого материала', '', ''],
	//темы кпк
	type_theme_kpk: ['SEL', 'Тип раздаваемого материала', '', ''],
	type_3d_model: ['SEL', 'Количество', '', ''],
	video_quality_vlesson: ['SEL', 'Качество', '', ''],
	format_vlesson: ['SEL', 'Формат', '', ''],
	video_codec_vlesson: ['SEL', 'Видео кодек', '', ''],
	audio_codec_vlesson: ['SEL', 'Аудио кодек', '', ''],
	transl_doc_film: ['SEL', 'Перевод', '', ''],
	chapters_music_dvd: ['SEL', 'Разбивка на главы по трекам', '', ''],
	video_quality_music_dvd: ['SEL', 'Качество ', '', ''],
	format_music_dvd: ['SEL', 'Формат', '', ''],
	video_codec_music_dvd: ['SEL', 'Видео кодек', '', ''],
	audio_codec_music_dvd: ['SEL', 'Аудио кодек', '', ''],
	audio_codec_mus_loss: ['SEL', 'Аудиокодек', '', ''],
	rip_type_mus_loss: ['SEL', 'Тип рипа', '', ''],
	scan_mus_loss: ['SEL', 'Наличие сканов в содержимом раздачи', '', ''],
	scan_mus_loss_apple: ['SEL', 'Наличие сканов в содержимом раздачи', '', ''],
	source_mus_loss: ['SEL', 'Источник', '', ''],
	genre_soundtrack_mus: ['SEL', 'Жанр', '', ''],
	audio_codec_digit_mus: ['SEL', 'Аудио кодек', '', ''],
	source_digit_mus: ['SEL', 'Источник оцифровки', '', ''],
	vinyl_digit_mus: ['SEL', 'Код класса состояния винила', '', ''],
	perfotmer_mus_lossy: ['SEL', 'Исполнитель', '', ''],
	audio_codec_mus_lossy: ['SEL', 'Аудиокодек', '', ''],
	rip_type_mus_lossy: ['SEL', 'Тип рипа', '', ''],
	bitrate_mus_lossy: ['SEL', 'Битрейт аудио', '', ''],
	tag_mus_lossy: ['SEL', 'ID3-теги', '', ''],
	//тестовые диски
	rip_type_test: ['SEL', 'Тип рипа', '', ''],
	audio_codec_test: ['SEL', 'Аудио кодек', '', ''],
	video_codec_test: ['SEL', 'Видео кодек', '', ''],
	//linux - ось и программы.
	arch_linux: ['SEL', 'Архитектура', '', ''],
	channel_sound: ['SEL', 'Каналы', '', ''],
	lang_game_dvd_pleer: ['SEL', 'Язык интерфейса', '', ''],
	audio_codec_film: ['SEL', 'Аудио кодек', '', ''],
	video_quality_serials: ['SEL', 'Качество', '', ''],
	video_quality_serial: ['SEL', 'Качество', '', ''],
	loss_bit: ['SEL', 'Битрейт аудио', '', ''],
	type_homebrewe: ['SEL', 'Тип', '', ''],
	console_type: ['SEL', 'Консоль', '', ''],
	anime_type: ['SEL', 'Тип', '', ''],
	sub_all_anime: ['SEL', 'Язык субтитров', '', ''],
	sub_all_anime_2: ['SEL', 'Язык субтитров', '', ''],
	sub_all_anime_3: ['SEL', 'Язык субтитров', '', ''],
	transl_lat_setial: ['SEL', 'Перевод', '', ''],
	transl_lat_setial_1: ['SEL', 'Перевод', '', ''],
	transl_lat_setial_2: ['SEL', 'Перевод', '', ''],
	format_lat_serial: ['SEL', 'Формат', '', ''],
	game_lang_nds: ['SEL', 'Язык:', '', ''],
	lang_comp_vlesson: ['SEL', 'Язык', '', ''],
	type_comp_vlesson: ['SEL', 'Тип раздаваемого материала', '', ''],
	source_mus_lossy: ['SEL', 'Источник', '', ''],
	lang_notes: ['SEL', 'Язык', '', ''],
	licence_old_game: ['SEL', 'Лицензия?', '', ''],
	lang_video_les: ['SEL', 'Язык', '', ''],
	type_vlesson: ['SEL', 'Тип раздаваемого материала', '', ''],
	type_game: ['SEL', 'Тип раздачи', '', ''],
	lang_other_game: ['SEL', 'Требуемый язык игры', '', ''],
	//кпк
	format_smart: ['SEL', 'Формат', '', ''],
	def_smart: ['SEL', 'Разрешение', '', ''],
	video_quality_smart: ['SEL', 'Качество', '', ''],
	video_codec_smart: ['SEL', 'Видео кодек', '', ''],
	audio_codec_smart: ['SEL', 'Аудио кодек', '', ''],
	prefix_kpk: ['SEL', 'Префикс', '', ''],
	format_mob: ['SEL', 'Формат', '', ''],
	def_mob: ['SEL', 'Разрешение', '', ''],
	publishing_type_mob: ['SEL', 'Тип издания', '', ''],
	platform_symb: ['SEL', 'Платформа', '', ''],
	launch_xbox: ['SEL', 'Возможность запуска на xbox 360', '', ''],
	launch_pc: ['SEL', 'Возможность запуска на PC', '', ''],
	video_codec_3d: ['SEL', 'Видео кодек', '', ''],
	audio_codec_3d: ['SEL', 'Аудио кодек', '', ''],
	video_quality_3d_1: ['SEL', 'Качество', '', ''],
	video_quality_3d_2: ['SEL', 'Качество', '', ''],
	container_3d: ['SEL', 'Контейнер', '', ''],
	format_3d: ['SEL', 'Формат 3D', '', ''],
	angle_3d: ['SEL', 'Порядок ракурсов', '', ''],
	update_game: ['SEL', 'Обновление раздачи', '', ''],
	audio_codec_anime_loss: ['SEL', 'Аудио кодек', '', ''],
	lang_anime_transl: ['SEL', 'Язык', '', ''],
	lang_anime_transl_2: ['SEL', 'Язык', '', ''],
	lang_anime_transl_3: ['SEL', 'Язык', '', ''],
	country_anime: ['SEL', 'Страна', '', ''],
	//видео для PSP
	psp_video_type: ['SEL', 'Тип', '', ''],
	// dummy
	dummy: ['', '', '', '']
};

/*
  -------------------------------------------------------------------------------------------------
  -- el_id ----------------------------------------------------------------------------------------
  -------------------------------------------------------------------------------------------------
*/
TPL.el_id = {
	// ID контейнеров содержащих html элементов
	load_pic_btn: 'Кнопка "Загрузить картинку"',
	load_pic_faq_url: 'Ссылка на FAQ "Как залить картинку на бесплатный хост"',
	manga_type_faq_url: 'Ссылка на FAQ "Подробнее о типах манги"',
	test_dash: 'Статический элемент "-" для заголовка',
	make_screenlist_faq_url: 'Как сделать скриншот / скринлист',
	translation_rules_faq_url: 'Правила обозначения переводов',
	make_sample_faq_url: 'Как сделать сэмпл видео',
	dvd_reqs_faq_url: 'Требования и примеры для DVD',
	hd_reqs_faq_url: 'Требования и примеры для HD',
	videofile_info_faq_url: 'Как получить информацию о видео файле',
	bdinfo_faq_url: 'BDInfo',
	dvdinfo_faq_url: 'DVDInfo',
	make_poster_faq_url: 'Инструкция по изготовлению постера',
	pred_alt1_faq_url: 'О ссылках на предыдущие и альтернативные раздачи',
	quality_decl_faq_url: 'о обозначениях качества',
	pred_alt2_faq_url: 'О ссылках на предыдущие и альтернативные раздачи',
	pred_alt3_faq_url: 'О ссылках на предыдущие и альтернативные раздачи',
	pred_alt4_faq_url: 'О ссылках на предыдущие и альтернативные раздачи',
	dvdinfo_faq_url: 'Как получить информацию о DVD-Video',
	tyt_faq_url: 'тут',
	wtf_faq_url: 'Что это значит?',
	DVD_PG: 'DVD-PG',
	faq_catalog: 'инструкция',
	psp_psx: 'PSP-PSX',
	faq_pops: 'Что такое Popsloader?',
	faq_code: 'Как узнать код диcка?',
	faq_code_PS: 'Как узнать код диcка?',
	faq_pegi: 'PEGI',
	faq_screen_psp: 'Как сделать скриншоты с PSP',
	series: 'Серии:',
	season: 'Сезон:',
	series_of: 'из',
	point: ',',
	d_rus: 'в 3Д /',
	d_eng: '3D',
	genre_faq_url: 'Как определить жанр?',
	quality_faq: 'Обозначение качества видео',
	file_list: 'Как создать список файлов?',
	comparison_anime: 'Сравнения с другими раздачами.',
	faq_game: 'Превью(игры)',
	nds: '[NDS]',
	Dreamcast: '[DC]',
	faq_traclist: 'Как быстро создать треклист с указанием битрейта',
	number: '№',
	faq_isbn: 'Что такое ISBN/ISSN?',
	faq_scrn_books: 'Как сделать примеры страниц (скриншоты) для раздачи?',
	faq_ps_image: 'FAQ по снятию образа для Ps1',
	faq_mac_scrn: 'Создание скриншотов в Mac OS',
	// ID элементов, для которых нужно создать скрытые элементы, содержащие аббревиатуры для подстановки в название
	// Каждый элемент el_abr должен точно соответствовать el (translation_abr -> translation)
	translation_abr: '[ABR] Перевод',
	translation2_abr: '[ABR] Перевод',
	translation3_abr: '[ABR] Перевод',
	translation4_abr: '[ABR] Перевод',
	maps_lang_abr: '[ABR] Язык интерфейса (карты)',
	gui_lang_new_abr: '[ABR] Язык интерфейса (новый список)',
	transl_cartoons_0_abr: '[ABR] Перевод',
	transl_cartoons_1_abr: '[ABR] Перевод',
	transl_cartoons_2_abr: '[ABR] Перевод',
	cpu_bits_abr: '[ABR]Разрядность',
	maps_format_abr: '[ABR]Формат',
	lang_book_avto_abr: '[ABR]Язык авто-книги',
	lang_book_med_abr: '[ABR]Язык мед-книги',
	book_lang_abr: '[ABR]Язык книги',
	orig_audio_abr: '[ABR]Язык дорожки',
	orig_audio_serial_abr: '[ABR]Оригинальная аудиодорожка',
	translation_abr: '[ABR]Перевод',
	flang_lang_abr: '[ABR]Язык книги',
	sub_all_new_abr: '[ABR]Cубтитры',
	lang_dorama_abr: '[ABR]Язык',
	game_lang_abr: '[ABR] Язык интерфейса',
	game_lang_sound_abr: '[ABR] Язык озвучки',
	lang_psp_abr: '[ABR] Язык интерфейса',
	lang_mob_abr: '[ABR] Язык интерфейса',
	game_type_edition_abr: '[ABR] Тип издания',
	lang_anime_abr: '[ABR] Язык',
	lang_anime_2_abr: '[ABR] Язык',
	lang_anime_3_abr: '[ABR] Язык',
	anime_hwp_abr: '[ABR] Совместимость с бытовыми плеерами',
	publishing_type_abr: '[ABR] Тип издания',
	lang_old_game_abr: '[ABR] Язык',
	mus_lang_abr: '[ABR] Язык',
	perfotmer_mus_lossy_abr: '[ABR] Исполнитель',
	lang_game_dvd_pleer_abr: '[ABR] Язык',
	video_format_new_abr: '[ABR] Формат видео',
	audio_codec_mus_loss_abr: '[ABR] Аудио кодек',
	lang_dorama_2_abr: '[ABR] Язык',
	type_homebrewe_abr: '[ABR] Тип',
	console_type_abr: '[ABR] Консоль',
	sub_all_anime_abr: '[ABR] Язык',
	sub_all_anime_2_abr: '[ABR] Язык',
	sub_all_anime_3_abr: '[ABR] Язык',
	lang_game_video_abr: '[ABR] Язык',
	sub_game_video_abr: '[ABR] Язык',
	publishing_type_old_abr: '[ABR] Тип издания',
	lang_comp_vlesson_abr: '[ABR] Язык',
	type_comp_vlesson_abr: '[ABR] Тип раздаваемого материала',
	lang_notes_abr: '[ABR] Язык',
	rus_sub_abr: '[ABR] Русские субтитры',
	lang_video_les_abr: '[ABR] Язык',
	type_vlesson_abr: '[ABR] Тип раздаваемого материала',
	publishing_type_mob_abr: '[ABR] Тип издания',
	format_3d_abr: '[ABR] Формат 3D',
	audio_codec_anime_loss_abr: '[ABR] Аудио кодек',
	lang_anime_transl_abr: '[ABR] Язык',
	lang_anime_transl_2_abr: '[ABR] Язык',
	lang_anime_transl_3_abr: '[ABR] Язык',
	psp_video_type_abr: '[ABR] Тип',
	apple_ios_sysreq_abr: '[ABR] Системные требования',
	apple_ios_lang_abr: '[ABR] Язык интерфейса',
	apple_ios_format_abr: '[ABR] Формат файлов',
	apple_ios_def_abr: '[ABR] Поддерживаемые разрешения',
	mus_loss_performer_abr: '[ABR] Исполнитель',
	platform_mac_prog_abr: '[ABR] Платформа',
	lang_mac_prog_abr: '[ABR] Язык интерфейса',
	tablet_mac_prog_abr: '[ABR] Таблетка',
	// dummy
	dummy_abr: '[ABR] '
};

/*
  -------------------------------------------------------------------------------------------------
  -- selects --------------------------------------------------------------------------------------
  -------------------------------------------------------------------------------------------------
*/
TPL.selects = {
	// [0] всегда имеет value='' и если задан как '' (пустая строка) заменяется на "&raquo; Выбрать"
	//фильмы зарубежка, наше и т.д. авто, медицина.
	translation: [
		'',
		'Профессиональный (дублированный)',
		'Профессиональный (многоголосый закадровый)',
		'Профессиональный (двухголосый закадровый)',
		'Любительский (дублированный)',
		'Любительский (многоголосый закадровый)',
		'Любительский (двухголосый закадровый)',
		'Студийный (одноголосый закадровый)',
		'Авторский (одноголосый закадровый)',
		'Одноголосый закадровый',
		'Субтитры',
		'Не требуется',
		'Отсутствует'
	],

	translation_abr: [
		'',
		'Dub',
		'MVO',
		'DVO',
		'Dub',
		'MVO',
		'DVO',
		'VO',
		'AVO',
		'VO',
		'',
		'',
		''
	],

	translation2: [
		'',
		'Профессиональный (дублированный)',
		'Профессиональный (многоголосый закадровый)',
		'Профессиональный (двухголосый закадровый)',
		'Любительский (дублированный)',
		'Любительский (многоголосый закадровый)',
		'Любительский (двухголосый закадровый)',
		'Студийный (одноголосый закадровый)',
		'Авторский (одноголосый закадровый)',
		'Одноголосый закадровый',
		'Субтитры',
		'Не требуется',
		'Отсутствует'
	],

	translation2_abr: [
		'',
		'Dub',
		'MVO',
		'DVO',
		'Dub',
		'MVO',
		'DVO',
		'VO',
		'AVO',
		'VO',
		'',
		'',
		''
	],

	translation3: [
		'',
		'Профессиональный (дублированный)',
		'Профессиональный (многоголосый закадровый)',
		'Профессиональный (двухголосый закадровый)',
		'Любительский (дублированный)',
		'Любительский (многоголосый закадровый)',
		'Любительский (двухголосый закадровый)',
		'Студийный (одноголосый закадровый)',
		'Авторский (одноголосый закадровый)',
		'Одноголосый закадровый',
		'Субтитры',
		'Не требуется',
		'Отсутствует'
	],

	translation3_abr: [
		'',
		'Dub',
		'MVO',
		'DVO',
		'Dub',
		'MVO',
		'DVO',
		'VO',
		'AVO',
		'VO',
		'',
		'',
		''
	],

	translation4: [
		'',
		'Профессиональный (дублированный)',
		'Профессиональный (многоголосый закадровый)',
		'Профессиональный (двухголосый закадровый)',
		'Любительский (дублированный)',
		'Любительский (многоголосый закадровый)',
		'Любительский (двухголосый закадровый)',
		'Студийный (одноголосый закадровый)',
		'Авторский (одноголосый закадровый)',
		'Одноголосый закадровый',
		'Субтитры',
		'Не требуется',
		'Отсутствует'
	],

	translation4_abr: [
		'',
		'Dub',
		'MVO',
		'DVO',
		'Dub',
		'MVO',
		'DVO',
		'VO',
		'AVO',
		'VO',
		'',
		'',
		''
	],

	book_lang: [
		'',
		'Русский',
		'Русский (дореформенный)',
		'Украинский',
		'Белорусский',
		'Польский',
		'Английский',
		'Немецкий',
		'Французский',
		'Итальянский',
		'Испанский',
		'Португальский',
		'Китайский',
		'Японский',
		'Болгарский',
		'Другой'
	],

	book_lang_abr: [
		'',
		'RUS',
		'RUS',
		'UKR',
		'BLR',
		'POL',
		'ENG',
		'DEU',
		'FRA',
		'ITA',
		'ESP',
		'PRT',
		'CHN',
		'JPN',
		'BGR',
		''
	],

	flang_lang: [
		'',
		'Русский',
		'Украинский',
		'Белорусский',
		'Польский',
		'Английский',
		'Немецкий',
		'Французский',
		'Итальянский',
		'Испанский',
		'Португальский',
		'Китайский',
		'Японский',
		'Арабский',
		'Другой'
	],

	flang_lang_abr: [
		'',
		'RUS',
		'UKR',
		'BLR',
		'POL',
		'ENG',
		'DEU',
		'FRA',
		'ITA',
		'ESP',
		'PRT',
		'CHN',
		'JPN',
		'ARA',
		''
	],

	sub_format: [
		'',
		'softsub (SRT)',
		'softsub (SSA/ASS)',
		'prerendered (IDX+SUB)',
		'hardsub (неотключаемые)'
	],

	//видео iphone
	rus_sub: [
		'',
		'есть',
		'нет'
	],

	rus_sub_abr: [
		'',
		'rus sub',
		''
	],

	sub_all: [
		'',
		'русские',
		'английские'
	],

	//видео для PSP
	psp_video_type: [
		'',
		'Фильм',
		'Аниме',
		'Музыкальный клип',
		'Мультфильм',
		'Мультсериал',
		'Сериал',
		'UMD Видео',
		'Спорт',
		'Разное',
		'ТВ Передача',
		'Документальный фильм',
		'Дорама',
		'Онгоинг (аниме)'
	],

	psp_video_type_abr: [
		'',
		'[FILM]',
		'[ANIME]',
		'[MUSIC]',
		'[MULT]',
		'[MULTSERIAL]',
		'[SERIAL]',
		'[UMD]',
		'[SPORT]',
		'[VIDEO]',
		'[TV]',
		'[DOC]',
		'[DORAMA]',
		'[ONG][ANIME]'
	],

	orig_audio_serial: [
		'',
		'есть',
		'нет'
	],

	orig_audio_serial_abr: [
		'',
		'Original',
		''
	],

	//фильмы зарубежка, наше и т.д.
	orig_audio: [
		'нет',
		'русский',
		'английский',
		'немецкий',
		'французский',
		'испанский',
		'итальянский',
		'польский',
		'чешский',
		'словацкий',
		'украинский',
		'белорусский',
		'литовский',
		'латышский',
		'датский',
		'норвежский',
		'шведский',
		'нидерландский',
		'финский',
		'иврит',
		'румынский',
		'молдавский',
		'португальский',
		'Другой'
	],

	orig_audio_abr: [
		'',
		'Original Rus',
		'Original Eng',
		'Original Ger',
		'Original Fre',
		'Original Spa',
		'Original Ita',
		'Original Pol',
		'Original Cze',
		'Original Slo',
		'Original Ukr',
		'Original Bel',
		'Original Lit',
		'Original Lav',
		'Original Dan',
		'Original Nor',
		'Original Swe',
		'Original Dut',
		'Original Fin',
		'Original Heb',
		'Original Rum',
		'Original Mol',
		'Original Por',
		''
	],

	video_quality: [
		'&raquo; Качество видео',
		'DVDRip',
		'DVD5',
		'DVD5 (сжатый)',
		'DVD9',
		'HDTV',
		'HDTVRip',
		'TVRip',
		'TeleCine',
		'TeleSynch',
		'CamRip',
		'SATRip',
		'VHSRip',
		'HDDVDRip',
		'BDRip',
		'HDRip',
		'DtheaterRip',
		'DVDScreener'
	],

	//фильмы зарубежка, наше и т.д.
	video_quality_new: [
		'» Выберите качество',
		'DVDRip',
		'HDRip',
		'HDTVRip',
		'BDRip',
		'DVDRip-AVC',
		'HDRip-AVC',
		'HDTVRip-AVC',
		'BDRip-AVC',
		'HDDVDRip',
		'DTheaterRip',
		'SATRip',
		'TVRip',
		'VHSRip',
		'DVDScreener',
		'TeleCine',
		'TeleSynch',
		'CamRip',
		'DVD5',
		'DVD9',
		'DVB'
	],

	video_format: [
		'&raquo; Формат видео',
		'AVI',
		'DVD Video',
		'OGM',
		'MKV',
		'WMV',
		'MPEG',
		'MPEG-2',
		'MP4',
		'TS',
		'M2TS',
		'VOB'
	],

	video_codec: [
		'&raquo; Видео кодек',
		'DivX',
		'XviD',
		"Другой MPEG4",
		'VPx',
		'MPEG1',
		'MPEG2',
		'Windows Media',
		'QuickTime',
		'H.264',
		'Flash'
	],

	video_codec_2: [
		'&raquo; Видео кодек',
		'DivX',
		'XviD',
		"Другой MPEG4",
		'VPx',
		'MPEG1',
		'MPEG2',
		'Windows Media',
		'QuickTime',
		'H.264',
		'Flash'
	],

	audio_codec: [
		'&raquo; Аудио кодек',
		'MP3',
		'APE',
		'FLAC',
		'WAVPack',
		'WMA',
		'OGG Vorbis',
		'DTS',
		'DVD-AUDIO',
		'TTA',
		'AAC',
		'AC3',
		'M4A',
		'M4B'
	],

	audio_codec_2: [
		'&raquo; Аудио кодек',
		'MP3',
		'APE',
		'FLAC',
		'WAVPack',
		'WMA',
		'OGG Vorbis',
		'DTS',
		'DVD-AUDIO',
		'TTA',
		'AAC',
		'AC3',
		'M4A',
		'M4B'
	],

	audio_bitrate: [
		'&raquo; Битрейт аудио',
		'lossless',
		'64 kbps',
		'128 kbps',
		'160 kbps',
		'192 kbps',
		'224 kbps',
		'256 kbps',
		'320 kbps',
		'VBR 128-192 kbps',
		'VBR 192-320 kbps'
	],

	abook_type: [
		'&raquo; Тип',
		'аудиокнига',
		'аудиоспектакль',
		'модель для сборки'
	],

	publishing_type: [
		'&raquo; Тип издания',
		'лицензия',
		'пиратка'
	],

	publishing_type_abr: [
		'',
		'L',
		'P'
	],

	crack_exists: [
		'&raquo; Таблэтка',
		'не требуется',
		'присутствует',
		'отсутствует'
	],

	gui_lang: [
		'',
		'английский + русский',
		'только английский',
		'только русский',
		'немецкий',
		'Другой'
	],

	game_platform: [
		'',
		'PS',
		'PS2'
	],

	game_region_def: [
		'&raquo; Регион',
		'PAL',
		'NTSC',
		'Другой'
	],

	game_region: [
		'&raquo; Регион',
		'Europe',
		'US',
		'Japan'
	],

	//psp-psx
	game_version: [
		'',
		'FULL',
		'RIP',
		'Другой'
	],

	game_firmware: [
		'',
		'iXtreme Compatible',
		'Xtreme'
	],

	game_age: [
		'&raquo; Возраст',
		'EC - Для детей младшего возраста',
		'E - Для всех',
		'E10+ - Для всех старше 10 лет',
		'T - Подросткам 13-19 лет',
		'M - От 17 лет',
		'AO - Только для взрослых',
		'RP - Рейтинг ожидается',
		'Другой'
	],

	game_multiplay: [
		'',
		'нет',
		'2х',
		'4х',
		'более 4x'
	],

	game_lang_psp: [
		'&raquo; Язык интерфейса',
		'JAP',
		'ENG',
		'RUS',
		'Multi5'
	],

	game_trans_type: [
		'&raquo; Тип перевода',
		'текст',
		'текст +  звук',
		'нет'
	],

	book_format: [
		'',
		'PDF',
		'DjVu',
		'DOC',
		'RTF',
		'TXT',
		'FB2',
		'CBR/CBZ',
		'HTML',
		'CHM',
		'EXE',
		'JPEG',
		'TIF',
		'LIT',
		'PDB',
		'RB',
		'PDF/DjVu',
		'FB2/RTF/PDF',
		'Другой'
	],

	book_quality: [
		'',
		'Отсканированные страницы',
		'Отсканированные страницы + слой распознанного текста',
		'Распознанный текст с ошибками (OCR)',
		'Распознанный текст без ошибок (OCR)',
		'Изначально компьютерное (eBook)',
		'Сфотографированные страницы'
	],

	vista_compat: [
		'&raquo; Совместимость с Vista',
		'полная',
		'да',
		'нет',
		'неизвестно'
	],

	game_plat_wii: [
		'',
		'Nintendo Wii',
		'GameCube'
	],

	maps_lang: [
		'',
		'Английский + Русский',
		'Английский',
		'Русский',
		'Немецкий',
		'Мультиязычный (русский присутствует)',
		'Мультиязычный (русский отсутствует)',
		'Другой'
	],

	maps_lang_abr: [
		'',
		'ENG + RUS', // Английский + Русский
		'ENG', // Английский
		'RUS', // Русский
		'GER', // Немецкий
		'MULTILANG +RUS', // Мультиязычный (русский присутствует)
		'MULTILANG -RUS', // Мультиязычный (русский отсутствует)
		''
	],

	gui_lang_new: [
		'',
		'Английский + Русский',
		'Английский',
		'Русский',
		'Многоязычный (русский присутствует)',
		'Многоязычный (русский отсутствует)',
		'Немецкий',
		'Японский',
		'Другой'
	],

	gui_lang_new_abr: [
		'',
		'ENG + RUS',
		'ENG',
		'RUS',
		'Multi + RUS',
		'Multi, NO RUS',
		'DEU',
		'JAP',
		''
	],

	tabletka_new: [
		'',
		'Присутствует',
		'Отсутствует',
		'Вылечено',
		'Не требуется'
	],

	cpu_bits: [
		'',
		'32bit',
		'64bit',
		'32bit+64bit'
	],

	cpu_bits_abr: [
		'',
		'x86',
		'x64',
		'x86+x64'
	],

	vista_compat_new: [
		'&raquo; Совместимость с Vista',
		'полная',
		'только с х86 (32-бит)',
		'только с х64 (64-бит)',
		'нет',
		'неизвестно'
	],

	windows7_compat: [
		'&raquo; Совместимость с Windows 7',
		'полная',
		'только с х86 (32-бит)',
		'только с х64 (64-бит)',
		'нет',
		'неизвестно'
	],

	maps_format: [
		'',
		'jpg, jpeg',
		'png',
		'bmp',
		'tiff',
		'gif',
		'psd',
		'pdf',
		'djvu',
		'eps',
		'ai',
		'map',
		'img'
	],

	maps_format_abr: [
		'',
		'JPEG',
		'PNG',
		'BMP',
		'TIFF',
		'GIF',
		'PSD',
		'PDF',
		'DJVU',
		'EPS',
		'AI',
		'MAP',
		'IMG'
	],

	atlas_type: [
		'',
		'Атлас',
		'Топографические карты',
		'Карты'
	],

	lang_book_avto: [
		'',
		'Русский',
		'Английский',
		'Немецкий',
		'Японский',
		'Другой'
	],

	lang_book_avto_abr: [
		'',
		'RUS',
		'ENG',
		'Deu',
		'Jap',
		''
	],

	lang_book_med: [
		'',
		'Русский',
		'Украинский',
		'Английский',
		'Немецкий',
		'Французский',
		'Другой'
	],

	lang_book_med_abr: [
		'',
		'RUS',
		'UKR',
		'ENG',
		'DEU',
		'FRA'
	],

	product_milestone: [
		'',
		'Release',
		'Pre-Beta',
		'Beta',
		'RC'
	],

	avto_mm_type: [
		'',
		'ММ',
		'EWD'
	],

	manga_type: [
		'',
		'manga',
		'doujinshi',
		'ranobe',
		'one-shot',
		'manhwa',
		'manhua'
	],

	manga_completeness_with_header: [
		'&raquo; Завершенность',
		'complete',
		'incomplete'
	],

	video_format_new: [
		'» Выберите формат видео',
		'AVI',
		'MKV',
		'MP4',
		'DVD Video'
	],

	video_format_new_abr: [
		'',
		'',
		'AVC',
		'AVC',
		''
	],

	sub_all_new: [
		'',
		'нет',
		'русские',
		'английские',
		'немецкие',
		'французские',
		'испанские',
		'итальянские',
		'польские',
		'чешские',
		'словацкие',
		'украинские',
		'белорусские',
		'литовские',
		'латышские',
		'датские',
		'норвежские',
		'шведские',
		'нидерландские',
		'финские',
		'иврит',
		'румынские',
		'молдавские',
		'португальские',
		'Другие'
	],

	sub_all_new_abr: [ // Перевод языков для субтитров и оригинальной дорожки в тэги для заголовка
		'',
		'',
		'Sub Rus',
		'Sub Eng',
		'Sub Ger',
		'Sub Fre',
		'Sub Spa',
		'Sub Ita',
		'Sub Pol',
		'Sub Cze',
		'Sub Slo',
		'Sub Ukr',
		'Sub Bel',
		'Sub Lit',
		'Sub Lav',
		'Sub Dan',
		'Sub Nor',
		'Sub Swe',
		'Sub Dut',
		'Sub Fin',
		'Sub Heb',
		'Sub Rum',
		'Sub Mol',
		'Sub Por',
		''
	],

	anime_release_type: [
		'&raquo; Тип релиза',
		'Хардсаб',
		'Без хардсаба',
		'Полухардсаб'
	],

	anime_hwp: [
		'&raquo; Совместимость с бытовыми плеерами',
		'Да',
		'Нет'
	],

	anime_hwp_abr: [
		'',
		'HWP',
		''
	],

	transl_dorama: [
		'',
		'Русские субтитры',
		'Одноголосая озвучка',
		'Двухголосая озвучка',
		'Многоголосая озвучка',
		'Дубляж',
		'Отсутствует'
	],

	sub_dorama: [
		'',
		'Хардсаб',
		'Полухардсаб',
		'Без хардсаба'
	],

	lang_dorama: [
		'&raquo; Язык',
		'Русский (внешним файлом)',
		'Русский (в составе контейнера)',
		'Японский',
		'Китайский',
		'Корейский',
		'Тайваньский',
		'Английский'
	],

	lang_dorama_2: [
		'&raquo; Язык',
		'Русский (внешним файлом)',
		'Русский (в составе контейнера)',
		'Японский',
		'Китайский',
		'Корейский',
		'Тайваньский',
		'Английский'
	],

	lang_dorama_abr: [
		'',
		'RUS(ext)',
		'RUS(int)',
		'JAP',
		'CHI',
		'KOR',
		'TW',
		'ENG'
	],

	lang_dorama_2_abr: [
		'',
		'RUS(ext)',
		'RUS(int)',
		'JAP',
		'CHI',
		'KOR',
		'TW',
		'ENG'
	],

	game_type_edition: [
		'',
		'Лицензия',
		'Неофициальный',
		'RePack',
		'RiP',
		'Демо-версия',
		'Trial'
	],

	game_type_edition_abr: [
		'',
		'L',
		'P',
		'RePack',
		'RiP',
		'Demo',
		'Trial'
	],

	game_lang: [
		'',
		'русский',
		'английский',
		'русский + английский',
		'немецкий',
		'многоязычный',
		'отсутствует / не требуется',
		'Другой'
	],

	game_lang_abr: [
		'',
		'RUS',
		'ENG',
		'RUS',
		'DEU',
		'Multi',
		'',
		''
	],

	game_lang_sound: [
		'',
		'русский',
		'английский',
		'русский + английский',
		'немецкий',
		'отсутствует/не требуется',
		'Другая'
	],

	game_lang_sound_abr: [
		'',
		'RUS',
		'ENG',
		'RUS',
		'DEU',
		'',
		''
	],

	game_tabletka: [
		'',
		'Присутствует',
		'Отсутствует',
		'Эмуляция образа',
		'Не требуется'
	],

	lang_psp: [
		'',
		'Японский',
		'Английский',
		'Русский',
		'Multi2',
		'Multi3',
		'Multi4',
		'Multi5',
		'Другой'
	],

	lang_psp_abr: [
		'',
		'JAP',
		'ENG',
		'RUS',
		'Multi2',
		'Multi3',
		'Multi4',
		'Multi5',
		''
	],

	lang_sound_psp: [
		'',
		'Отсутствует',
		'Японская',
		'Английская',
		'Русская',
		'Другая'
	],

	sub_psp: [
		'',
		'Отсутствуют',
		'Японские',
		'Английские',
		'Русские',
		'Другие'
	],

	funct_psp: [
		'',
		'Да',
		'Нет'
	],

	multiplayer_psp: [
		'',
		'2x',
		'4x',
		'Нет'
	],

	popsloader_psp: [
		'',
		'3.00',
		'3.01',
		'3.02',
		'3.03',
		'3.10',
		'3.11',
		'3.30',
		'3.40',
		'3.51',
		'3.52',
		'3.71',
		'3.72',
		'3.80',
		'3.90',
		'4.01',
		'5.00 - original from flash',
		'PSN',
		'Другой'
	],

	//для кпк, мобильных и т.п.
	lang_mob: [
		'&raquo; Язык интерфейса',
		'Английский',
		'Русский',
		'Русский + Английский ',
		'Многоязычный'
	],

	lang_mob_abr: [
		'',
		'ENG',
		'RUS',
		'RUS + ENG',
		'Multi'
	],

	//формат файлов для клипартов, футажей и иже с ними.
	format_clipart: [
		'',
		'JPEG',
		'PSD',
		'PNG',
		'TIFF'
	],

	//photostocks
	format_photostocks: [
		'',
		'JPEG',
		'TIFF',
		'PSD',
		'EPS',
		'PCD'
	],

	//Костюмы для фотомонтажа
	format_photo: [
		'',
		'PSD',
		'PNG',
		'TIFF'
	],

	//Костюмы для фотомонтажа
	suit_type: [
		'',
		'Женские костюмы',
		'Мужские костюмы',
		'Детские костюмы',
		'Групповые костюмы'
	],

	//векторные клипарты
	format_vector_clipart: [
		'',
		'AI',
		'EPS',
		'CDR'
	],

	format_3d_model: [
		'',
		'3D Studio Max',
		'Cinema4D',
		'Poser',
		'Другое'
	],

	format_3d: [
		'',
		'max',
		'3ds',
		'c4d',
		'pos',
		'obj',
		'Другое'
	],

	material: [
		'',
		'Да',
		'Нет'
	],

	texture: [
		'',
		'Да',
		'Нет'
	],

	light_source: [
		'',
		'Да',
		'Нет'
	],

	folder_pdf: [
		'',
		'Да',
		'Нет'
	],

	//футажи
	video_footage: [
		'',
		'PAL',
		'NTSC',
		'HD',
		'Другой'
	],

	def_footage: [
		'',
		'720x480',
		'720x576',
		'1280x720',
		'Другой'
	],

	frame_rate: [
		'',
		'25',
		'30',
		'60',
		'Другой'
	],

	video_format_footage: [
		'',
		'MOV',
		'AVI',
		'Другой'
	],

	lang_anime: [
		'&raquo; Язык',
		'Русский (внешним файлом)',
		'Русский (в составе контейнера)',
		'Японский',
		'Английский',
		'Корейский',
		'Китайский',
		'Испанский',
		'Итальянский',
		'Немецкий',
		'Другой'
	],

	lang_anime_abr: [
		'',
		'RUS(ext)',
		'RUS(int)',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		''
	],

	lang_anime_2: [
		'&raquo; Язык',
		'Русский (внешним файлом)',
		'Русский (в составе контейнера)',
		'Японский',
		'Английский',
		'Корейский',
		'Китайский',
		'Испанский',
		'Итальянский',
		'Немецкий',
		'Другой'
	],

	lang_anime_2_abr: [
		'&raquo; Язык',
		'RUS(ext)',
		'RUS(int)',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		''
	],

	lang_anime_3: [
		'&raquo; Язык',
		'Русский (внешним файлом)',
		'Русский (в составе контейнера)',
		'Японский',
		'Английский',
		'Корейский',
		'Китайский',
		'Испанский',
		'Итальянский',
		'Немецкий',
		'Другой'
	],

	lang_anime_3_abr: [
		'',
		'RUS(ext)',
		'RUS(int)',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		''
	],

	//psp-psx
	disk_number_psn: [
		'',
		'1',
		'2',
		'3',
		'4'
	],

	//psp=psx
	change_disk_psn: [
		'&raquo; Выбрать/ Диск всего один',
		'Есть',
		'Нет'
	],

	genre_game_dvd: [
		'',
		'Interactive Movie ',
		'Adventure'
	],

	platform_game_dvd: [
		'',
		'DVD players',
		'PC',
		'Microsoft X-Box',
		'XBOX-360',
		'PS2',
		'PS3',
		'Другой'
	],

	tabletka_game_dvd: [
		'Не требуется'
	],

	format_disk_game_dvd: [
		'',
		'HD DVD',
		'DVD9',
		'DVD5',
		'CD'
	],

	format_video_game_dvd: [
		'',
		'HD Video',
		'DVD Video',
		'MPEG 2'
	],

	sub_game_video: [
		'',
		'русские',
		'английские',
		'немецкие',
		'нет',
		'Другие'
	],

	sub_game_video_abr: [
		'',
		'Sub-RUS',
		'Sub-ENG',
		'Sub-DEU',
		'',
		''
	],

	lang_game_video: [
		'',
		'русский',
		'английский',
		'немецкий',
		'нет',
		'Другой'
	],

	lang_game_video_abr: [
		'',
		'RUS',
		'ENG',
		'DEU',
		'',
		''
	],

	format_game_video: [
		'',
		'AVI',
		'MKV',
		'MP4',
		'FLV',
		'WMV',
		'MOV',
		'MPEG',
		'3GP',
		'TS',
		'Другой'
	],

	material_trailer: [
		'',
		'трейлер',
		'тизер',
		'фильм о фильме',
		'дополнительные материалы',
		'интервью с актерами',
		'сюжет из фильма',
		'удаленные сцены'
	],

	//для трейлеров, видео (разное) и спорта!!!
	transl_trailer: [
		'',
		'Профессиональный (одноголосый закадровый)',
		'Любительский (одноголосый закадровый)',
		'Двухголосый закадровый',
		'Многоголосый закадровый',
		'Полное дублированние',
		'Субтитры',
		'Не требуется'
	],

	//для трейлеров, видео (разное) и спорта!!!
	video_quality_trailer: [
		'&raquo; Качество видео',
		'DVDRip',
		'HDRip',
		'HDTVRip',
		'BDRip',
		'TVRip',
		'DVDScreener',
		'TeleSynch',
		'CamRip',
		'DVD5',
		'DVD9',
		'BluRay'
	],

	//для трейлеров, видео (разное) и спорта!!!
	video_format_trailer: [
		'',
		'AVI',
		'FLV',
		'MOV',
		'MKV',
		'MP4',
		'DVD Video'
	],

	//для трейлеров, видео (разное) и спорта!!!
	video_codec_trailer: [
		'',
		'DivX',
		'XviD',
		'H264',
		'MPEG2'
	],

	//для трейлеров, видео (разное) и спорта!!!
	audio_codec_trailer: [
		'',
		'MP3',
		'AC3',
		'AAC',
		'WMA',
		'DTS'
	],

	lang_old_game: [
		'&raquo; Язык интерфейса',
		'только английский',
		'только русский',
		'английский и русский',
		'английский + русский',
		'многоязычный',
		'Другой'
	],

	lang_old_game_abr: [
		'',
		'[ENG]',
		'[RUS]',
		'[ENG] [RUS]',
		'[ENG + RUS]',
		'[Multi]',
		''
	],

	//apple
	edition_type_iphone: [
		'',
		'оригинал',
		'переиздание',
		'ремастер',
		'ремикс',
		'сборник',
		'сингл'
	],

	words_iphone: [
		'',
		'вшиты',
		'вшиты частично',
		'отсутствуют',
		'не требуются'
	],

	rip_prog_iphone: [
		'',
		'iTunes (диск)',
		'EAC (диск)',
		'foobar2000 + iTunes (lossless)',
		'Сторонняя программа (lossless)',
		'XLD (lossless)'
	],

	audio_bitrate_iphone_los: [
		'&raquo; Битрейт аудио',
		'lossless',
		'lossless CBR (1411)'
	],

	transl_iphone: [
		'',
		'Любительский одноголосый',
		'Любительский многоголосый',
		'Любительский Гоблина',
		'Профессиональный (одноголосый)',
		'Профессиональный (двухголосый)',
		'Профессиональный (дублированный)',
		'Профессиональный (многоголосый закадровый)',
		'Профессиональный (многоголосый, полное дублирование)',
		'Субтитры',
		'Отсутствует',
		'Не требуется'
	],

	video_format_iphone: [
		'',
		'*.mp4',
		'*.m4v',
		'*.mov'
	],

	video_codec_iphone: [
		'&raquo; Видео кодек',
		'H.264',
		'XviD',
		'Другой MPEG4'
	],

	audio_codec_iphone: [
		'&raquo; Аудио кодек',
		'ААС',
		'ALAC',
		'AAC + AC3'
	],

	cover_iphone: [
		'',
		'есть',
		'нет'
	],

	tag_iphone: [
		'',
		'прописаны',
		'нет',
		'частично'
	],

	show_iphone: [
		'',
		'прописан',
		'нет',
		'не требуется'
	],
	chapter_iphone: [
		'',
		'прописаны',
		'нет'
	],

	series_iphone: [
		'',
		'прописан',
		'нет',
		'не требуется'
	],

	audio_bitrate_iphone: [
		'&raquo; Битрейт',
		'16 kbps',
		'32 kbps',
		'64 kbps',
		'96 kbps',
		'128 kbps',
		'160 kbps',
		'192 kbps',
		'224 kbps',
		'256 kbps',
		'320 kbps',
		'VBR 16 kbps',
		'VBR 32 kbps',
		'VBR 64 kbps',
		'VBR 96 kbps',
		'VBR 128 kbps',
		'VBR 160 kbps',
		'VBR 192 kbps',
		'VBR 224 kbps',
		'VBR 256 kbps',
		'VBR 320 kbps',
		'Другой'
	],

	audio_chapters_iphone: [
		'',
		'есть',
		'нет'
	],

	audiobook_label: [
		'Официальное издание (заполнить соседнее поле)',
		'Аудиокнига своими руками',
		'Нигде не купишь'
	],

	// -apple
	platform_mob: [
		'',
		'Symbian 6-8',
		'Symbian 9.x',
		'Symbian 9.4',
		'Symbian 6-9.3',
		'Symbian 6.0-9.4',
		'Symbian UIQ',
		'Symbian all',
		'Java',
		'Symbian UIQ 2',
		'Symbian UIQ 3',
		'N-Gage2'
	],

	platform_mac_prog: [
		'',
		'PPC only',
		'Intel only',
		'PPC/Intel Universal'
	],

	platform_mac_prog_abr: [
		'',
		'PPC',
		'Intel',
		'Universal'
	],

	lang_mac_prog: [
		'',
		'русский + английский',
		'английский',
		'немецкий'
	],

	lang_mac_prog_abr: [
		'',
		'RUS',
		'',
		''
	],

	tablet_mac_prog: [
		'',
		'Серийный номер',
		'Программа пролечена (не требует введения данных/вводим любые данные)',
		'Файл лицензии',
		'Кейген',
		'Кейген (требуется эмулятор Windows)',
		'Нет таблетки'
	],

	tablet_mac_prog_abr: [
		'',
		'SN',
		'K-ed',
		'Lic',
		'K-Gen',
		'WIN K-Gen',
		'Demo'
	],

	video_format_dorama: [
		'&raquo; Формат видео',
		'AVI',
		'DVD Video',
		'OGM',
		'MKV',
		'WMV',
		'MPEG',
		'MPEG-2',
		'MP4',
		'TS',
		'M2TS',
		'VOB',
		'RM/RMVB'
	],

	video_quality_sport: [
		'&raquo; Качество видео',
		'DVDRip',
		'DVD5',
		'DVD5 (сжатый)',
		'DVD9',
		'HDTV',
		'HDTVRip',
		'TVRip',
		'TeleCine',
		'TeleSynch',
		'CamRip',
		'SATRip',
		'VHSRip',
		'HDDVDRip',
		'BDRip',
		'HDRip',
		'DtheaterRip',
		'DVDScreener',
		'Stream'
	],

	audio_codec_music_lib: [
		'&raquo; аудио кодек',
		'WAV',
		'MP3',
		'AIFF',
		'APE',
		'FLAC',
		'WMA',
		'OGG Vorbis'
	],

	mus_loss_performer: [
		'',
		'Исполнитель (группа)',
		'Сборник композиций разных исполнителей',
		'Саундтрек'
	],

	mus_loss_performer_abr: [
		'',
		'',
		'VA',
		''
	],

	//Библиотеки сэмплов
	bit_music_lib: [
		'&raquo; битность',
		'8 bit',
		'16 bit',
		'24 bit',
		'Другой'
	],

	bitrate_music_lib: [
		'&raquo; Битрейт',
		'64 kbps',
		'96 kbps',
		'128 kbps',
		'160 kbps',
		'192 kbps',
		'224 kbps',
		'256 kbps',
		'320 kbps',
		'VBR 128-192 kbps',
		'VBR 192-320 kbps',
		'705 kbps',
		'1411 kbps',
		'Другой'
	],

	rate_music_lib: [
		'&raquo; Частота',
		'22 kHz',
		'44.1 kHz',
		'48 kHz',
		'96 kHz'
	],

	canales_music_lib: [
		'&raquo; Каналы',
		'mono',
		'stereo',
		'5.1'
	],

	channel_sound: [
		'&raquo; Каналы',
		'mono',
		'stereo'
	],

	//Ноты
	mus_edit: [
		'Фамилия редактора (заполнить соседнее поле)',
		'Уртекст',
		'Не известно/не указано.'
	],

	mus_lang: [
		'',
		'Русский',
		'Украинский',
		'Английский',
		'Немецкий',
		'Французский',
		'Итальянский',
		'Испанский',
		'Китайский',
		'Японский',
		'Другой'
	],

	mus_lang_abr: [
		'',
		'RUS',
		'UKR',
		'ENG',
		'DEU',
		'FRA',
		'ITA',
		'ESP',
		'CHI',
		'JPN',
		''
	],

	transl_cartoons_0: [
		'&raquo; Перевод',
		'Полное дублирование',
		'Профессиональный многоголосый закадровый',
		'Профессиональный двухголосый закадровый',
		'Профессиональный одноголосый закадровый',
		'Любительский многоголосый закадровый',
		'Любительский двухголосый закадровый',
		'Любительский одноголосый закадровый (автор)',
		'Авторский одноголосый закадровый (автор)',
		'Не требуется',
		'Отсутствует'
	],

	transl_cartoons_1: [
		'&raquo; Перевод 2',
		'Полное дублирование',
		'Профессиональный многоголосый закадровый',
		'Профессиональный двухголосый закадровый',
		'Профессиональный одноголосый закадровый',
		'Любительский многоголосый закадровый',
		'Любительский двухголосый закадровый',
		'Любительский одноголосый закадровый (автор)',
		'Авторский одноголосый закадровый (автор)',
		'Не требуется'
	],

	transl_cartoons_2: [
		'&raquo; Перевод 3',
		'Полное дублирование',
		'Профессиональный многоголосый закадровый',
		'Профессиональный двухголосый закадровый',
		'Профессиональный одноголосый закадровый',
		'Любительский многоголосый закадровый',
		'Любительский двухголосый закадровый',
		'Любительский одноголосый закадровый (автор)',
		'Авторский одноголосый закадровый (автор)',
		'Не требуется'
	],

	transl_cartoons_0_abr: [
		'',
		'DUB',
		'MVO',
		'DVO',
		'VO',
		'MVO',
		'DVO',
		'VO',
		'AVO',
		'',
		''
	],

	transl_cartoons_1_abr: [
		'',
		'DUB',
		'MVO',
		'DVO',
		'VO',
		'MVO',
		'DVO',
		'VO',
		'AVO',
		''
	],

	transl_cartoons_2_abr: [
		'',
		'DUB',
		'MVO',
		'DVO',
		'VO',
		'MVO',
		'DVO',
		'VO',
		'AVO',
		''
	],

	format_cartoons_dvd: [
		'&raquo; Формат',
		'DVD-video'
	],

	type_cartoons: [
		'&raquo; Качество',
		'DVD5',
		'DVD9',
		'DVD5 (Custom)',
		'DVD5 (сжатый)',
		'DVD9 (Custom)',
		'DVD9 (Сжатый)'
	],

	screen_cartoons: [
		'&raquo; Формат экрана',
		'16:9',
		'4:3'
	],

	def_cartoons: [
		'&raquo; Система / Разрешение',
		'PAL (720х576)',
		'NTSC (720x480)',
		'PAL (704x576)',
		'NTSC (704x480)',
		'PAL (352x576)',
		'NTSC (352x480)',
		'PAL (352x288)',
		'NTSC (352x288)'
	],

	video_quality_cartoons: [
		'&raquo; Качество',
		'DVDRip',
		'HDRip',
		'HDTVRip',
		'BDRip',
		'HDDVDRip',
		'DTheaterRip',
		'WEB-DL',
		'WEB-DLRip',
		'WEBRip',
		'DVB',
		'SATRip',
		'TVRip',
		'VHSRip',
		'DVDScreener',
		'TeleCine',
		'TeleSynch',
		'CamRip',
		'DVD5',
		'DVD9',
		'DVD5 (Custom)',
		'DVD5 (Сжатый)',
		'DVD9 (Custom)',
		'DVD9 (Сжатый)'
	],

	format_cartoons: [
		'&raquo; Формат',
		'AVI',
		'MKV',
		'MP4',
		'DVD-Video'
	],

	video_quality_cartoons_hd: [
		'&raquo; Качество',
		'BDRip 720p',
		'BDRip 1080p',
		'BDRemux',
		'Blu-ray disc',
		'Blu-ray disc (custom)',
		'HDTVRip 720p',
		'HDTVRip 1080p',
		'HDTV 1080i'
	],

	format_cartoons_hd: [
		'&raquo; Формат',
		'MKV',
		'TS',
		'M2TS',
		'BDAV',
		'BDMV'
	],

	video_quality_cart_serial: [
		'&raquo; Качество',
		'DVDRip',
		'HDRip',
		'HDTVRip',
		'BDRip',
		'HDDVDRip',
		'DTheaterRip',
		'WEB-DL',
		'WEB-DLRip',
		'WEB-DLRip',
		'WEBRip',
		'DVB',
		'SATRip',
		'TVRip',
		'VHSRip',
		' ',
		'DVD5',
		'DVD9',
		'DVD5 (Custom)',
		'DVD5 (Сжатый)',
		'DVD9 (Custom)',
		'DVD9 (Сжатый)',
		' ',
		'BDRip 720p',
		'BDRip 1080p',
		'WEB-DL 720p',
		'WEB-DL 1080p',
		'BDRemux',
		'Blu-ray disc',
		'Blu-ray disc (custom)',
		'HDTVRip 720p',
		'HDTVRip 1080p',
		'HDTV 1080i'
	],

	video_quality_serial: [
		'&raquo; Качество',
		'DVDRip',
		'HDRip',
		'HDTVRip',
		'WEB-DLRip',
		'BDRip',
		'HDDVDRip',
		'DTheaterRip',
		'WEBRip',
		'SATRip',
		'TVRip',
		'VHSRip',
		'DVD5',
		'DVD5 (Custom)',
		'DVD9 (Custom)',
		'DVD9',
		'HDTV',
		'WEB-DL 720p',
		'HDDVD',
		'DTheater',
		'BDRemux',
		'Blu-Ray',
		'DVB',
		'Другое'
	],

	format_cart_serial: [
		'&raquo; Формат',
		'AVI',
		'MKV',
		'MP4',
		'MPEG',
		'DVD-Video',
		'TS',
		'MPEG-PS',
		'M2TS',
		'BDMV',
		'BDAV'
	],

	//apple >> iOS
	apple_ios_sysreq: [
		'',
		'iOS 3.0 и выше',
		'iOS 3.1 и выше',
		'iOS 3.1.2 и выше',
		'iOS 3.1.3 и выше',
		'iOS 3.2 и выше',
		'iOS 4.0 и выше',
		'iOS 4.1 и выше',
		'iOS 4.2 и выше',
		'iOS 4.3 и выше',
		'iOS 5.0 и выше',
		'iOS 5.1 и выше',
		'Другое'
	],

	apple_ios_sysreq_abr: [
		'',
		'iOS 3.0',
		'iOS 3.1',
		'iOS 3.1.2',
		'iOS 3.1.3',
		'iOS 3.2',
		'iOS 4.0',
		'iOS 4.1',
		'iOS 4.2',
		'iOS 4.3',
		'iOS 5.0',
		'iOS 5.1',
		''
	],

	apple_ios_lang: [
		'',
		'русский',
		'английский',
		'японский',
		'китайский',
		'немецкий',
		'французский',
		'испанский',
		'другой'
	],

	apple_ios_lang_abr: [
		'',
		'RUS',
		'ENG',
		'JAP',
		'CHI',
		'GER',
		'FR',
		'ESP',
		''
	],

	apple_ios_dev: [
		'',
		'iPhone, iPod Touch, iPad (все поколения)',
		'iPad (все поколения)',
		'SD версия для iPhone, iPod Touch + HD версия для iPad',
		'iPhone 3Gs, 4, 4s; iPod Touch 3-го и 4-го поколения; iPad (все поколения)',
		'iPhone 4s, iPad 2, iPad new',
		'Другое'
	],

	apple_ios_def: [
		'',
		'480x320',
		'480x320, 960x640',
		'480x320, 960x640, 1024x768',
		'480x320, 960x640, 1024x768, 2048х1536',
		'1024x768',
		'1024x768, 2048х1536',
		'480x320, 960x640 (SD) + 1024x768 (HD)',
		'480x320, 960x640 (SD) + 1024x768, 2048х1536 (HD)'
	],

	apple_ios_def_abr: [
		'',
		'',
		'',
		'+iPad',
		'+iPad',
		'HD',
		'HD',
		'HD+SD',
		'HD+SD'
	],

	apple_ios_format: [
		'',
		'.ipa',
		'.app',
		'.deb',
		'.m4r',
		'.ipsw',
		'.bin',
		'.jpg / .png',
		'.jpg',
		'.png',
		'разные (темы)',
		'другой'
	],

	apple_ios_format_abr: [
		'',
		'',
		'',
		'',
		'Рингтоны',
		'',
		'',
		'Обои',
		'Обои',
		'Обои',
		'Темы',
		''
	],

	//фильмы, сериалы.
	video_codec_serials: [
		'&raquo; Видео кодек',
		'DivX',
		'XviD',
		'H.264',
		'MPEG2',
		'Другой'
	],

	audio_codec_serials: [
		'&raquo; Аудио кодек',
		'MP3',
		'AC3',
		'Другой'
	],

	type_avatar: [
		'',
		'Avatars',
		'Icons',
		'Smiles',
		'Userbars'
	],

	type_theme_kpk: [
		'',
		'Wall',
		'Themes'
	],

	//3D модели, сцены и материалы
	type_3d_model: [
		'&raquo; Количество',
		'моделей',
		'сцен',
		'текстур',
		'HDRI',
		'материалов',
		'Другое'
	],

	video_quality_vlesson: [
		'&raquo; Качество',
		'DVDRip',
		'DVD5',
		'DVD9',
		'DVD5 (сжатый)',
		'HDTVRip',
		'HDRip',
		'BDRip',
		'SATRip',
		'IPTVRip',
		'TVRip',
		'VHSRip',
		'CAMRip',
		'WEBRip',
		'VCD',
		'PDTVRip'
	],

	format_vlesson: [
		'&raquo; Формат',
		'AVI',
		'MPG',
		'VOB',
		'DVD video',
		'MKV',
		'MP4',
		'WMV',
		'TS/M2TS',
		'FLV',
		'MOV'
	],

	video_codec_vlesson: [
		'&raquo; Видео кодек',
		'XviD',
		'DivX',
		'MPEG1',
		'MPEG2',
		'WMVx',
		'VC-1',
		'H.264',
		'VPx',
		'FLVx'
	],

	audio_codec_vlesson: [
		'&raquo; Аудио кодек',
		'MP3',
		'AC3',
		'AAC',
		'WMA',
		'PCM',
		'DTS',
		'MP2',
		'FLAC',
		'ogg'
	],

	transl_doc_film: [
		'',
		'Любительcкий (одноголосый)',
		'Любительский (двухголосый)',
		'Авторский (одноголосый)',
		'Профессиональный (одноголосый)',
		'Профессиональный (двухголосый)',
		'Профессиональный (многоголосый, закадровый)',
		'Профессиональный (полное дублирование)',
		'Субтитры',
		'Не требуется',
		'Отсутствует'
	],

	chapters_music_dvd: [
		'&raquo; Главы (разбивка по трекам)',
		'есть',
		'нет'
	],

	video_quality_music_dvd: [
		'&raquo; Качество',
		'DVD5',
		'DVD9',
		'DVD5 (сжатый)',
		'HDTVRip',
		'HDRip',
		'BDRip',
		'SATRip',
		'LDRip',
		'TVRip',
		'DTVRip',
		'VHSRip',
		'CAMRip',
		'Screener'
	],

	format_music_dvd: [
		'&raquo; Формат',
		'DVD video'
	],

	video_codec_music_dvd: [
		'&raquo; Видео кодек',
		'MPEG2'
	],

	audio_codec_music_dvd: [
		'&raquo; Аудио кодек',
		'AC3',
		'PCM',
		'DTS',
		'MP2'
	],

	audio_codec_mus_loss: [
		'&raquo; Аудио кодек',
		'FLAC (*.flac) ',
		'APE (*.ape)',
		'WavPack (*.wv)'
	],

	audio_codec_mus_loss_abr: [
		'',
		'FLAC',
		'APE',
		'WavPack'
	],

	rip_type_mus_loss: [
		'',
		'image+.cue',
		'tracks+.cue',
		'tracks',
		'iso.wv'
	],

	scan_mus_loss_apple: [
		'',
		'есть',
		'нет',
		'Digital Booklet'
	],

	source_mus_loss: [
		'',
		'собственный рип',
		'скачано с',
		'релизер',
		'Другое'
	],

	genre_soundtrack_mus: [
		'',
		'Score',
		'Soundtrack'
	],

	audio_codec_digit_mus: [
		'',
		'APE',
		'FLAC',
		'WavPack ',
		'Другое'
	],

	source_digit_mus: [
		'',
		'автором раздачи ',
		'третьим лицом',
		'Другое'
	],

	vinyl_digit_mus: [
		'',
		'Mint',
		'NM',
		'Ex',
		'VG+',
		'VG',
		'G',
		'F',
		'P'
	],

	perfotmer_mus_lossy: [
		'',
		'Исполнитель (группа)',
		'сборник композиций разных исполнителей'
	],

	perfotmer_mus_lossy_abr: [
		'',
		'',
		'VA'
	],

	audio_codec_mus_lossy: [
		'',
		'MP3',
		'OGG',
		'WMA',
		'MPC',
		'MP+',
		'M4A',
		'AAC'
	],

	rip_type_mus_lossy: [
		'&raquo; Тип рипа',
		'tracks',
		'image+.cue',
		'image'
	],

	bitrate_mus_lossy: [
		'&raquo; Битрейт аудио',
		'64 kbps',
		'128 kbps',
		'192 kbps',
		'224 kbps',
		'256 kbps',
		'320 kbps',
		'V0',
		'V1',
		'V2',
		'V3',
		'V4',
		'128-320 kbps',
		'192-320 kbps'
	],

	source_mus_lossy: [
		'',
		'CD',
		'WEB',
		'Vinyl'
	],

	scan_mus_loss: [
		'',
		'да',
		'нет'
	],

	tag_mus_lossy: [
		'',
		'да',
		'нет'
	],

	//программы - тестовые диски
	rip_type_test: [
		'&raquo; Тип рипа',
		'tracks',
		'image + .cue',
		'tracks + .cue',
		'DVD-Rip',
		'DVD',
		'BluRay-Rip',
		'BluRay'
	],

	audio_codec_test: [
		'&raquo; Аудио кодек',
		'WAV',
		'MP3',
		'APE',
		'FLAC',
		'WAVPack',
		'WMA',
		'OGG Vorbis',
		'DTS',
		'DVD-AUDIO',
		'TTA',
		'AAC',
		'AC3',
		'M4A',
		'M4B'
	],

	video_codec_test: [
		'&raquo; Видео кодек',
		'DivX',
		'XviD',
		'MPEG4',
		'VPx',
		'MPEG1',
		'MPEG2',
		'Windows Media',
		'QuickTime',
		'H.264',
		'Flash',
		'Другой'
	],

	arch_linux: [
		'',
		'x86',
		'amd64',
		'x86, amd64',
		'Другая'
	],

	lang_game_dvd_pleer: [
		'',
		'английский',
		'японский',
		'русский',
		'multi'
	],

	lang_game_dvd_pleer_abr: [
		'',
		'ENG',
		'JAP',
		'RUS',
		'multi'
	],

	audio_codec_film: [
		'» Выберите кодек аудио',
		'MP3',
		'AC3',
		'DTS',
		'AAC',
		'FLAC',
		'OGG'
	],

	video_quality_serials: [
		'» Качество',
		'DVDRip',
		'HDRip',
		'HDTVRip',
		'BDRip',
		'HDDVDRip',
		'DTheaterRip',
		'WEB-DLRip',
		'SATRip',
		'SATRemux',
		'TVRip',
		'VHSRip',
		'DVD5',
		'DVD9',
		'HDTV',
		'HDDVD',
		'DTheater',
		'BDRemux',
		'Blu-Ray',
		'Другое'
	],

	loss_bit: [
		'',
		'lossless'
	],

	type_homebrewe: [
		'',
		'Прошивка',
		'Homebrew программа',
		'PC программа',
		'CTF тема',
		'PTF тема',
		'Эмулятор',
		'Hombrew-игра',
		'Дополнение или руководство для игры',
		'Flash-программа или игр',
		'Карта местности',
		'Сохранение игры',
		'Обои',
		'Прочее'
	],

	type_homebrewe_abr: [
		'',
		'[CFW4PSP]',
		'[SOFT4PSP]',
		'[PC4PSP]',
		'[CTF4PSP]',
		'[PTF4PSP]',
		'[EMU4PSP]',
		'[GAME4PSP]',
		'[ADD4GAME]',
		'[FLASH4PSP]',
		'[MAP4PSP]',
		'[SAVE4PSP]',
		'[WLPR4PSP]',
		'[MIST4PSP]'
	],

	console_type: [
		'',
		'Nintendo/Dendy',
		'Super Nintendo',
		'Nintendo 64',
		'Game Boy',
		'GameBoy Color',
		'GameBoy Advaced',
		'Sega Master System',
		'Sega Mega Drive Genesis',
		'Sega Saturn',
		'Sega GameGear',
		'Игровой автомат',
		'MAME',
		'Atari 2600',
		'Atari 5200',
		'PC Engine',
		'Neo-Geo',
		'Другая'
	],

	console_type_abr: [
		'',
		'[NES]',
		'[SNES]',
		'[N64]',
		'[GB]',
		'[GBC]',
		'[GBA]',
		'[SMS]',
		'[SEGA MDG]',
		'[SATURN]',
		'[SGG]',
		'[Игровой автомат]',
		'[MAME]',
		'[Atari 2600]',
		'[Atari 5200]',
		'[PCE]',
		'[Neo-Geo]',
		''
	],

	anime_type: [
		'',
		'TV',
		'Movie',
		'OVA',
		'ONA',
		'Special'
	],

	sub_all_anime: [
		'» Язык',
		'русский',
		'английский',
		'немецкий',
		'французский',
		'китайский',
		'Другой'
	],

	sub_all_anime_abr: [
		'',
		'SUB',
		'',
		'',
		'',
		'',
		''
	],

	sub_all_anime_2: [
		'» Язык',
		'русский',
		'английский',
		'немецкий',
		'французский',
		'китайский',
		'Другой'
	],

	sub_all_anime_2_abr: [
		'',
		'SUB',
		'',
		'',
		'',
		'',
		''
	],

	sub_all_anime_3: [
		'» Язык',
		'русский',
		'английский',
		'немецкий',
		'французский',
		'китайский',
		'Другой'
	],

	sub_all_anime_3_abr: [
		'',
		'SUB',
		'',
		'',
		'',
		'',
		''
	],

	transl_lat_setial: [
		'&raquo; Перевод 1',
		'полное дублирование',
		'профессиональный многоголосый закадровый',
		'профессиональный двухголосый закадровый',
		'профессиональный одноголосый закадровый',
		'любительский двухголосый закадровый',
		'любительский одноголосый закадровый (автор)',
		'авторский одноголосый закадровый (автор)',
		'не требуется',
		'отсутствует',
		'Другой'
	],

	transl_lat_setial_1: [
		'&raquo; Перевод 2',
		'полное дублирование',
		'профессиональный многоголосый закадровый',
		'профессиональный двухголосый закадровый',
		'профессиональный одноголосый закадровый',
		'любительский двухголосый закадровый',
		'любительский одноголосый закадровый (автор)',
		'авторский одноголосый закадровый (автор)',
		'не требуется',
		'Другой'
	],

	transl_lat_setial_2: [
		'&raquo; Перевод 3',
		'полное дублирование',
		'профессиональный многоголосый закадровый',
		'профессиональный двухголосый закадровый',
		'профессиональный одноголосый закадровый',
		'любительский двухголосый закадровый',
		'любительский одноголосый закадровый (автор)',
		'авторский одноголосый закадровый (автор)',
		'не требуется',
		'Другой'
	],

	format_lat_serial: [
		'&raquo; Формат',
		'AVI',
		'MKV',
		'MP4',
		'MPEG',
		'DVD-Video',
		'TS',
		'MPEG-PS',
		'M2TS',
		'BDMV',
		'BDAV',
		'Другой'
	],

	game_lang_nds: [
		'&raquo; Язык',
		'JAP',
		'ENG',
		'RUS',
		'Multi5'
	],

	lang_comp_vlesson: [
		'',
		'Русский',
		'Английский',
		'Немецкий',
		'Другой'
	],

	lang_comp_vlesson_abr: [
		'',
		'RUS',
		'ENG',
		'DEU',
		''
	],

	type_comp_vlesson: [
		'',
		'Видеоурок',
		'Мультимедийный диск',
		'Интерактивный диск',
		'Видеоклипы'
	],

	type_comp_vlesson_abr: [
		'',
		'',
		'ММ',
		'',
		''
	],

	lang_notes: [
		'',
		'Русский',
		'Английский',
		'Немецкий',
		'Испанский',
		'Китайский',
		'Украинский',
		'Другой'
	],

	lang_notes_abr: [
		'',
		'RUS',
		'ENG',
		'DEU',
		'ITA',
		'CHN',
		'UKR',
		''
	],

	licence_old_game: [
		'',
		'да',
		'нет'
	],

	lang_video_les: [
		'',
		'Русский',
		'Английский',
		'Русский + Английский',
		'Немецкий',
		'Украинский',
		'Другой'
	],

	lang_video_les_abr: [
		'',
		'RUS',
		'ENG',
		'RUS/ENG',
		'DEU',
		'UKR',
		''
	],

	type_vlesson: [
		'',
		'Видеоурок',
		'Мультимедийный диск',
		'Другой'
	],

	type_vlesson_abr: [
		'',
		'Видеоурок',
		'ММ',
		''
	],

	type_game: [
		'',
		'Patch',
		'Maps',
		'Mods',
		'RUS',
		'ENG',
		'Pack',
		'Crack',
		'Guide',
		'Save',
		'Trainer',
		'Other'
	],

	lang_other_game: [
		'',
		'русский',
		'английский',
		'русский + английский',
		'немецкий',
		'не важно',
		'Другой'
	],

	format_smart: [
		'',
		'AVI',
		'MP4',
		'MKV',
		'wmv'
	],

	def_smart: [
		'',
		'320x',
		'352x',
		'400x',
		'480x',
		'640x',
		'800x'
	],

	video_quality_smart: [
		'',
		'DVDRip',
		'HDRip',
		'HDTVRip',
		'BDRip',
		'HDDVDRip',
		'WEB-DLRip',
		'SATRip',
		'TVRip',
		'VHSRip',
		'CamRip',
		'TS',
		'DVDScr'
	],

	video_codec_smart: [
		'',
		'DivX',
		'XviD',
		'Другой MPEG4',
		'Widows Media',
		'H.264',
		'Flash',
		'QuickTime'
	],

	audio_codec_smart: [
		'',
		'MP3',
		'AAC'
	],

	prefix_kpk: [
		'',
		'VIDEO',
		'SERIAL',
		'ANIME',
		'MULT',
		'MC',
		'DOC',
		'SPORT'
	],

	format_mob: [
		'',
		'3GP'
	],

	def_mob: [
		'',
		'176x',
		'320x'
	],

	publishing_type_mob: [
		'',
		'Пиратка',
		'Лицензия',
		'Demo',
		'Trial'
	],

	publishing_type_mob_abr: [
		'',
		'P',
		'L',
		'Demo',
		'Trial'
	],

	platform_symb: [
		'',
		'Symbian 6-8',
		'Symbian 9.x',
		'Symbian 9^3',
		'Symbian 9.4',
		'Symbian 6-9.3',
		'Symbian 6.0-9.4',
		'Symbian UIQ',
		'Symbian all',
		'Symbian UIQ 2',
		'Symbian UIQ 3',
		'N-Gage2'
	],

	launch_xbox: [
		'&raquo; Возможность запуска на xbox 360',
		'Да',
		'Нет',
		'Не знаю, проверьте, пожалуйста, сами и напишите о результате в теме'
	],

	launch_pc: [
		'&raquo; Возможность запуска на PC',
		'Нет, запуск на PC невозможен.',
		'Ищите порт этой игры в разделе *Игры для PC*'
	],

	video_codec_3d: [
		'&raquo; Видео кодек',
		'Divx',
		'xVid',
		'Mpeg2',
		'x264',
		'h.264',
		'MVC'
	],

	audio_codec_3d: [
		'&raquo; Аудио кодек',
		'MP3',
		'AC3',
		'AAC',
		'FLAC',
		'LPCM',
		'DTS',
		'DTS-HD',
		'TRUE-HD'
	],

	video_quality_3d_1: [
		'&raquo; Качество',
		'DVD5',
		'DVD9',
		'DVDrip',
		'',
		'Blu-Ray Disc',
		'BDrip',
		'HDTVrip',
		'HDTV',
		'HDrip',
		'HDDVDrip',
		'',
		'SATrip',
		'TVrip',
		'VHSrip',
		'CAMrip'
	],

	video_quality_3d_2: [
		'&raquo; Качество',
		'720p',
		'1080p',
		'1080i',
		'(Custom)',
		'(Сжатый)'
	],

	container_3d: [
		'&raquo; Контейнер',
		'DVD-Video',
		'',
		'AVI',
		'MKV',
		'TS',
		'M2TS',
		'BDMV',
		'ISO'
	],

	format_3d: [
		'&raquo; Формат 3D',
		'Blu-ray 3D',
		'Анаглиф red-cyan',
		'Анаглиф green-magenta',
		'Анаглиф yellow-blue',
		'Чересстрочный / Interlace',
		'OverUnder / Вертикальная стереопара',
		'Half OverUnder / Вертикальная анаморфная стереопара',
		'SideBySide / Горизонтальная стереопара',
		'Half SideBySide / Горизонтальная анаморфная стереопара',
		'SeparateFiles / Раздельная стереопара'
	],

	format_3d_abr: [
		'',
		'BD3D',
		'Anaglyph / Анаглиф',
		'Anaglyph / Анаглиф',
		'Anaglyph / Анаглиф',
		'Interlaced / интерлейс',
		'OverUnder / Вертикальная стереопара',
		'Half OverUnder / Вертикальная анаморфная стереопара',
		'SideBySide / Горизонтальная стереопара',
		'Half SideBySide / Горизонтальная анаморфная стереопара',
		'SeparateFiles / Раздельная стереопара'
	],

	angle_3d: [
		'&raquo;  Порядок ракурсов',
		'левый ракурс первый',
		'правый ракурс первый'
	],

	update_game: [
		'',
		'Да',
		'Нет'
	],

	audio_codec_anime_loss: [
		'&raquo; Аудио кодек',
		'FLAC (*.flac) ',
		'APE (*.ape)',
		'WavPack (*.wv)',
		'TAK (*.tak)',
		'TTA (*.tta)'
	],

	audio_codec_anime_loss_abr: [
		'',
		'FLAC',
		'APE',
		'WavPack',
		'TAK',
		'TTA'
	],

	lang_anime_transl: [
		'&raquo; Язык',
		'Русский',
		'Японский',
		'Английский',
		'Корейский',
		'Китайский',
		'Испанский',
		'Итальянский',
		'Немецкий',
		'Другой'
	],

	lang_anime_transl_abr: [
		'',
		'RUS',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		''
	],

	lang_anime_transl_2: [
		'&raquo; Язык',
		'Русский',
		'Японский',
		'Английский',
		'Корейский',
		'Китайский',
		'Испанский',
		'Итальянский',
		'Немецкий',
		'Другой'
	],

	lang_anime_transl_2_abr: [
		'',
		'RUS',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		''
	],

	lang_anime_transl_3: [
		'&raquo; Язык',
		'Русский',
		'Японский',
		'Английский',
		'Корейский',
		'Китайский',
		'Испанский',
		'Итальянский',
		'Немецкий',
		'Другой'
	],

	lang_anime_transl_3_abr: [
		'',
		'RUS',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		''
	],

	country_anime: [
		'',
		'Япония',
		'Япония/США',
		'Корея',
		'Китай',
		'Другая'
	],

	// dummy
	dummy: ['']
};

TPL.el_abrs = [
	'translation_abr',
	'maps_lang_abr',
	'gui_lang_new_abr',
	'prog_lic_type_abr',
	'cpu_bits_abr',
	'maps_format_abr',
	'lang_book_avto_abr',
	'lang_book_med_abr',
	'book_lang_abr',
	'orig_audio_abr',
	'orig_audio_serial_abr',
	'translation_abr',
	'flang_lang_abr',
	'sub_all_new_abr',
	'lang_dorama_abr',
	'game_lang_abr',
	'game_type_edition_abr',
	'game_lang_sound_abr',
	'lang_psp_abr',
	'lang_mob_abr',
	'lang_anime_abr',
	'lang_anime_2_abr',
	'lang_anime_3_abr',
	'publishing_type_abr',
	'lang_old_game_abr',
	'lang_vlesson_abr',
	'perfotmer_mus_lossy_abr',
	'lang_game_dvd_pleer_abr',
	'translation4_abr',
	'translation3_abr',
	'translation2_abr',
	'transl_cartoons_0_abr',
	'transl_cartoons_1_abr',
	'transl_cartoons_2_abr',
	'video_format_new_abr',
	'audio_codec_mus_loss_abr',
	'lang_dorama_2_abr',
	'type_homebrewe_abr',
	'console_type',
	'sub_all_anime_3_abr',
	'sub_all_anime_abr',
	'sub_all_anime_abr',
	'lang_game_video_abr',
	'sub_game_video_abr',
	'lang_comp_vlesson_abr',
	'type_comp_vlesson_abr',
	'lang_notes_abr',
	'rus_sub_abr',
	'lang_video_les_abr',
	'type_vlesson_abr',
	'publishing_type_mob_abr',
	'audio_codec_anime_loss_abr',
	'lang_anime_transl_abr',
	'lang_anime_transl_2_abr',
	'lang_anime_transl_3_abr',
	'platform_mac_prog_abr',
	'lang_mac_prog_abr',
	'tablet_mac_prog_abr'
];
function preg_quote (str) {
	return (str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
}

<!-- IF EDIT_TPL -->
TPL.build_el_attr_select = function(){
	var s = '<select><option value="">&raquo;&raquo; Элементы формы/Названия &nbsp;</option>';
	var q = /"/g;  //"
	$.each(TPL.el_attr, function(name,at){
		var v = at[1].replace(q, '&quot;');
		if (v === '') return true; // continue
		s += '<option value="'+ name +'">'+ v +'</option>';
	});
	s += '</select>';
	return s;
};

TPL.build_el_id_select = function(){
	var s = '<select><option value="">&raquo;&raquo; Другие элементы &nbsp;</option>';
	s += '<option value="`текст...`">Текст...</option>';
	s += '<option value="`BR`">Новая строка</option>';
	var q = /"/g;
	$.each(TPL.el_id, function(id,desc){
		var v = desc.replace(q, '&quot;');
		if (v === '') return true; // continue
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
};
ajax.callback.posts = function(data) {
	$('#'+data.res_id).html(data.message_html).append('<div class="clear"></div>');
	initPostBBCode('#'+data.res_id);
};

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
			if (params.tpl_id === -1) {
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
};
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
};

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
		if ($el.val() !== '') return true; // continue
		if (TPL.el_attr[id] !== null) {
			if (TPL.el_attr[id][0] === 'SEL') {
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
	if (pad_string === null) {
		pad_string = ' ';
	}
	if (pad_type === null) {
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

	if (pad_type !== 'STR_PAD_LEFT' && pad_type !== 'STR_PAD_RIGHT' && pad_type !== 'STR_PAD_BOTH') { pad_type = 'STR_PAD_RIGHT'; }
	if ((pad_to_go = pad_length - input.length) > 0) {
		if (pad_type === 'STR_PAD_LEFT') { input = str_pad_repeater(pad_string, pad_to_go) + input; }
		else if (pad_type === 'STR_PAD_RIGHT') { input = input + str_pad_repeater(pad_string, pad_to_go); }
		else if (pad_type === 'STR_PAD_BOTH') {
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
$(function(){
	TPL.build_tpl_form( $('#tpl-src-form').val(), 'rel-tpl' );
	initPostBBCode('#tpl-rules-html');
});
</script>
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
	<div id="tpl-abr-box"></div>
	<script type="text/javascript">
	$(function(){
		$.each(TPL.el_id, function(el,desc){
			var m = el.match(/^(.*)(_abr)$/);
			if (m === null) {
				return true; // continue
			}
			var el_abr = m[0];
			var el_ref = m[1];
			$('#tpl-abr-box').append('<div id="'+el_abr+'-hid">'+ TPL.build_select_el(el_abr) +'</div>');
			TPL.submit_fn[el_abr] = function(){
				if ( $('#'+el_ref).length ) {
					$('#'+el_abr)[0].selectedIndex = $('#'+el_ref)[0].selectedIndex;
				}
			}
		});
	});
	</script>

<!-- pictures (knopKI) -->

<!--load_pic_btn-->
<div id="load_pic_btn"><input type="button" style="width: 140px;" value="Загрузить картинку" onclick="window.open('http://fastpic.ru', '_blank'); return false;" /></div>
<!--/load_pic_btn-->

<!-- knopIKI (urls) -->

<!--load_pic_faq_url-->
<div id="load_pic_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=101116" target="_blank"><b>Как залить картинку на бесплатный хост</b></a> </div>
<!--/load_pic_faq_url-->

<!--manga_type_faq_url-->
<div id="manga_type_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=2168864#types" target="_blank"><b>Подробнее о типах</b></a> </div>
<!--/manga_type_faq_url-->

<!--make_screenlist_faq_url-->
<div id="make_screenlist_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=48687" target="_blank"><b>Как сделать скриншот / скринлист</b></a> </div>
<!--/make_screenlist_faq_url-->

<!--translation_rules_faq_url-->
<div id="translation_rules_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=33482343#33482343" target="_blank"><b>Правила обозначения переводов</b></a> </div>
<!--/translation_rules_faq_url-->

<!--make_sample_faq_url-->
<div id="make_sample_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=8415" target="_blank"><b>Как сделать сэмпл видео</b></a> </div>
<!--/make_sample_faq_url-->

<!--dvd_reqs_faq_url-->
<div id="dvd_reqs_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=27157356#descr" target="_blank"><b>Требования и примеры для DVD</b></a> </div>
<!--/dvd_reqs_faq_url-->

<!--hd_reqs_faq_url-->
<div id="hd_reqs_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=2277258#53" target="_blank"><b>Требования и примеры для HD</b></a> </div>
<!--/hd_reqs_faq_url-->

<!--videofile_info_faq_url-->
<div id="videofile_info_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=48686" target="_blank"><b>Как получить информацию о видео файле</b></a> </div>
<!--/videofile_info_faq_url-->

<!--bdinfo_faq_url-->
<div id="bdinfo_faq_url"> <a href="http://www.cinemasquid.com/blu-ray/tools/bdinfo" target="_blank"><b>BDInfo</b></a></div>
<!--/bdinfo_faq_url-->

<!--dvdinfo_faq_url-->
<div id="dvdinfo_faq_url"> <a href="http://www.cinemasquid.com/blu-ray/tools/dvdinfo" target="_blank"><b>DVDInfo</b></a></div>
<!--/dvdinfo_faq_url-->

<!--make_poster_faq_url-->
<div id="make_poster_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=1381634" target="_blank"><b>Инструкция по изготовлению постера</b></a> </div>
<!--/make_poster_faq_url-->

<!--pred_alt1_faq_url-->
<div id="pred_alt1_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=648002#8" target="_blank"><b>О ссылках на предыдущие и альтернативные раздачи</b></a> </div>
<!--/pred_alt1_faq_url-->

<!--quality_decl_faq_url-->
<div id="quality_decl_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=27840514#27840514" target="_blank"><b>об обозначениях качества</b></a> </div>
<!--/quality_decl_faq_url-->

<!--pred_alt2_faq_url-->
<div id="pred_alt2_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=4960586#4960586" target="_blank"><b>О ссылках на предыдущие и альтернативные раздачи</b></a> </div>
<!--/pred_alt2_faq_url-->

<!--pred_alt3_faq_url-->
<div id="pred_alt3_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=6734641#6734641" target="_blank"><b>О ссылках на предыдущие и альтернативные раздачи</b></a> </div>
<!--/pred_alt3_faq_url-->

<!--pred_alt4_faq_url-->
<div id="pred_alt4_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=460883#8" target="_blank"><b>О ссылках на предыдущие и альтернативные раздачи</b></a> </div>
<!--/pred_alt4_faq_url-->

<!--dvdinfo_faq_url-->
<div id="dvdinfo_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=101263" target="_blank"><b>Как получить информацию о DVD-Video</b></a> </div>
<!--/dvdinfo_faq_url-->

<!--tyt_faq_url-->
<div id="tyt_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=2135853" target="_blank"><b>тут</b></a> </div>
<!--/tyt_faq_url-->

<!--wtf_faq_url-->
<div id="wtf_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=488848#other" target="_blank"><b>Что это значит?</b></a> </div>
<!--/wtf_faq_url-->

<!--faq_catalog-->
<div id="faq_catalog"> <a href="http://rutracker.org/forum/viewtopic.php?t=1123827#3" target="_blank"><b>инструкция.</b></a> </div>
<!--/faq_catalog-->

<!--faq_pops-->
<div id="faq_pops"> <a href="http://rutracker.org/forum/viewtopic.php?t=1077368" target="_blank"><b>Что такое Popsloader?</b></a> </div>
<!--/faq_pops-->

<!--faq_code-->
<div id="faq_code"> <a href="http://rutracker.org/forum/viewtopic.php?p=24015560#24015560" target="_blank"><b>Как узнать код диcка?</b></a> </div>
<!--/faq_code-->

<!--faq_code_PS-->
<div id="faq_code_PS"> <a href="http://rutracker.org/forum/viewtopic.php?p=40704481#40704481" target="_blank"><b>Как узнать код диcка?</b></a> </div>
<!--/faq_code_PS-->

<!--faq_pegi-->
<div id="faq_pegi"> <a href="http://www.pegi.info/" target="_blank"><b>PEGI?</b></a> </div>
<!--/faq_pegi-->

<!--faq_screen_psp-->
<div id="faq_screen_psp"> <a href="http://rutracker.org/forum/viewtopic.php?t=457909" target="_blank"><b>Как сделать скриншоты с PSP</b></a> </div>
<!--/faq_screen_psp-->

<!--dvdinfo_faq_ur_2l-->
<div id="dvdinfo_faq_url_2"> <a href="http://www.cinemasquid.com/blu-ray/tools/dvdinfo" target="_blank"><b>Как получить информацию о DVD Video файле</b></a></div>
<!--/dvdinfo_faq_url_2-->

<!--quality_faq-->
<div id="quality_faq"> <a href="http://rutracker.org/forum/viewtopic.php?t=2198792" target="_blank"><b>Обозначение качества видео</b></a></div>
<!--/quality_faq-->

<!--comparison_anime-->
<div id="comparison_anime"> <a href="http://rutracker.org/forum/viewtopic.php?t=1907922#4" target="_blank"><b>Сравнения с другими раздачами.</b></a></div>
<!--/comparison_anime-->

<!--file_list-->
<div id="file_list"> <a href="http://rutracker.org/forum/viewtopic.php?p=21307338#21307338" target="_blank"><b>Как создать список файлов?</b></a></div>
<!--/file_list-->

<!--faq_traclist-->
<div id="faq_traclist"> <a href="http://rutracker.org/forum/viewtopic.php?t=2525182" target="_blank"><b>Как быстро создать треклист с указанием битрейта</b></a></div>
<!--/faq_traclist-->

<!--faq_isbn-->
<div id="faq_isbn"> <a href="http://rutracker.org/forum/viewtopic.php?t=2083213" target="_blank"><b>Что такое ISBN/ISSN?</b></a> </div>
<!--/faq_isbn-->

<!--faq_scrn_books-->
<div id="faq_scrn_books"> <a href="http://rutracker.org/forum/viewtopic.php?t=1566885" target="_blank"><b>Как сделать примеры страниц (скриншоты) для раздачи?</b></a> </div>
<!--/faq_scrn_books-->

<!--faq_ps_image-->
<div id="faq_ps_image"> <a href="http://rutracker.org/forum/viewtopic.php?t=3893250" target="_blank"><b>FAQ по снятию образа для Ps1</b></a> </div>
<!--/faq_ps_image-->

<!--faq_mac_scrn-->
<div id="faq_mac_scrn"> <a href="http://rutracker.org/forum/viewtopic.php?t=1749166" target="_blank"><b>Создание скриншотов в Mac OS</b></a> </div>
<!--/faq_mac_scrn-->

<!--test_dash-->
<input type="hidden" id="test_dash" value="-">
<!--/test_dash-->

<!--DVD_PG-->
<input type="hidden" id="DVD_PG" value="DVD-PG">
<!--/DVD_PG-->

<!--psp_psx-->
<input type="hidden" id="psp_psx" value="PSP-PSX">
<!--/psp_psx-->

<!--series-->
<input type="hidden" id="series" value="Серии:">
<!--/series-->

<!--series_of-->
<input type="hidden" id="series_of" value="из">
<!--/series_of-->

<!--season-->
<input type="hidden" id="season" value="Сезон:">
<!--/season-->

<!--point-->
<input type="hidden" id="point" value=",">
<!--/point-->

<!--d_rus-->
<input type="hidden" id="d_rus" value="в 3Д /">
<!--/d_rus-->

<!--d_eng-->
<input type="hidden" id="d_eng" value="3D">
<!--/d_eng-->

<!--nds-->
<input type="hidden" id="nds" value="[NDS]">
<!--/nds-->

<!--Dreamcast-->
<input type="hidden" id="Dreamcast" value="[DC]">
<!--/Dreamcast-->

<!--genre_faq_url-->
<div id="genre_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=2090617" target="_blank"><b>Как определить жанр</b></a> </div>
<!--/genre_faq_url-->

<!--faq_game-->
<div id="faq_game"> <a href="http://rutracker.org/forum/viewtopic.php?t=2706502" target="_blank"><b>Превью</b></a> </div>
<!--/faq_game-->

<!--number-->
<input type="hidden" id="number" value="№">
<!--/number-->
</div>

<div style="display: none;">
	<!-- исходные значения всех #tpl-src -->
	<textarea id="tpl-src-form-val" rows="10" cols="10">{TPL_SRC_FORM_VAL}</textarea>
	<textarea id="tpl-src-title-val" rows="10" cols="10">{TPL_SRC_TITLE_VAL}</textarea>
	<textarea id="tpl-src-msg-val" rows="10" cols="10">{TPL_SRC_MSG_VAL}</textarea>
</div>

<noscript><div class="warningBox2 bold tCenter">Для показа необходимo включить JavaScript</div></noscript>