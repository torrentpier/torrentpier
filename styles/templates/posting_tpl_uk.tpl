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

	// конструктор елементов
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

	// подстановка текущей строки в #tpl-row-src и обновление предпросмора елемента
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

	// select для выбора TPL.attr_el елементов в конструкторе
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

	// select для выбора TPL.el_id елементов в конструкторе и других (BR и т.д.)
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
		poster      : 'https://demo.torrentpier.com/styles/images/logo/logo.png',
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
			var title_id = mr[1];    // id елемента для подстановки его названия или {произвольное название}
			var input_els = mr[2];
			var row_title = (TPL.el_attr[title_id] !== null) ? TPL.el_attr[title_id][1] : TPL.trim_brackets(title_id);
			var $tr = $('<tr><td class="rel-title">'+ row_title +':</td><td class="rel-inputs"></td></tr>');
			var $td = $('td.rel-inputs', $tr);

			$.each(TPL.match_els(input_els), function(j,el){
				if (!(el = TPL.trim_brackets(el))) return true; // continue
				var el_html = '';
				var me = TPL.match_el_attrs(el);
				// вставка шаблонного елемента типа TYPE[attr]
				if (me[2] !== null) {
					var at = me[2].split(',');
					var nm = at[0];

					switch (me[1])
					{
					case 'E':
						if ( $('#'+ nm +'-hid').length ) {
							if (res_id === 'tpl-row-preview') {
								el_html = '<span class="rel-el hid-el">'+ $('#'+ nm +'-hid').html() +'</span>'; // скрытый елемент
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
				// вставка нешаблонного елемента
				else {
					if (el === 'BR') {
						el_html = '<br />';
					}
					else {
						el_html = '<span class="rel-el rel-free-el">'+ escHTML(el) +'</span>';
					}
				}
				// добавление елемента в td.rel-inputs
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
			if ( $sel.val().toLowerCase().match(/^друг(ой|ая|ое|ие)|other$/) ) {
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
			s += '<option value="'+(i===0 ? '' : v.replace(q, '&quot;'))+'">'+(v==='' ? '&raquo; Обрати' : v)+'</option>';
		});
		s += '</select>';
		return s;
	},

	// возвращает все елементи формата el[atr1,atr2] в виде
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

	// количество найденных помилок при заполнении формы
	f_errors_cnt: 0,
	// удаление подсветки помилок, сброс счетчика
	reset_f_errors: function() {
		TPL.f_errors_cnt = 0;
		$('tr.tpl-err-hl-tr').removeClass('tpl-err-hl-tr');
		$('.tpl-err-hl-input').removeClass('tpl-err-hl-input');
		$('div.tpl-err-hint').remove();
	},
	// подсветка помилок
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
			// лише в заголовке
			if ($.inArray('headonly', at) !== -1) {
				return true; // continue
			}
			// обычный елемент
			msg_body.push( TPL.build_msg_el(el_id, el_val) );

			// новая строка после елемента
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
			// 2000 р.
			case 'year':
				val += ' р.';
				break;

			// "Ім'я / Name /" -> "Ім'я / Name"
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
		var g;                                                   // группа елементов <-el1 el2->[,]
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
    код_елемента = ID елемента в форме
    все елементи имеют class "rel-input"
    формат el_attr
      код_елемента: [
        [0] - тип
        [1] - название
        [2] - атрибуты елемента типа size,rows.. по умолчанию (в том же порядке как и опциональные для елемента)
        [3] - атрибуты типа HEAD,req.. по умолчанию для формата сообщения
      ]
    формат елементов в #tpl-src-form (включая все опциональные атрибуты типа maxlength..)
      INP - input[name,maxlength,size]
      TXT - textarea[name,rows]
      SEL - select[name]               -- значения для селектов находятся в TPL.selects
  */
	poster_size: ['INP', 'максимум 500х500 пікселів', '200,80', ''],
	audio_codec: ['SEL', 'Аудіокодек', '', ''],
	audio: ['INP', 'Аудіо', '200,80', ''],
	audio_bitrate: ['SEL', 'Бітрейт аудіо', '', ''],
	casting: ['TXT', 'У ролях', '3', 'BR'],
	game_version: ['SEL', 'Версія гри', '', ''],
	video_codec: ['SEL', 'Відеокодек', '', ''],
	video: ['INP', 'Відео', '200,80', ''],
	game_age: ['SEL', 'Вік', '', ''],
	year: ['INP', 'Рік випуску', '4,5', 'num'],
	moreinfo: ['TXT', 'Дод. інформація', '3', 'BR'],
	genre: ['INP', 'Жанр', '200,40', ''],
	video_quality: ['SEL', 'Якість відео', '', ''],
	video_quality_new: ['SEL', 'Якість відео', '', ''],
	book_quality: ['SEL', 'Якість', '', ''],
	game_multiplay: ['SEL', 'Мультиплеєр гри', '', ''],
	title_ukr: ['INP', 'Назва', '90,80', 'HEAD,req'],
	description: ['TXT', 'Опис', '6', 'BR'],
	title_eng: ['INP', 'Оригінальна назва', '90,80', 'HEAD'],
	translation: ['SEL', 'Переклад', '', ''],
	translation2: ['SEL', 'Переклад 2', '', ''],
	translation3: ['SEL', 'Переклад 3', '', ''],
	translation4: ['SEL', 'Переклад 4', '', ''],
	game_plat_wii: ['SEL', 'Платформа Wii', '', ''],
	game_platform: ['SEL', 'Платформа гри', '', ''],
	poster: ['INP', 'Постер', '200,80', 'img,POSTER'],
	playtime: ['INP', 'Тривалість', '200,30', ''],
	game_firmware: ['SEL', 'Прошивка', '', ''],
	game_region: ['SEL', 'Регіон гри', '', ''],
	game_region_def: ['SEL', 'Регіон гри', '', ''],
	director: ['INP', 'Режисер', '200,50', ''],
	ukr_sub: ['SEL', 'Українські субтитри', '', ''],
	sub_all: ['SEL', 'Cубтитри', '', ''],
	sub_all_new: ['SEL', 'Cубтитри', '', ''],
	screenshots: ['TXT', 'Скріншоти', '3', 'spoiler'],
	screenshots_about: ['TXT', 'Скріншоти вікна About', '3', 'spoiler'],
	vista_compat: ['SEL', 'Сумісність із Vista', '', ''],
	vista_compat_new: ['SEL', 'Сумісність із Vista', '', ''],
	windows7_compat: ['SEL', 'Сумісність із Windows 7', '', ''],
	country: ['INP', 'Країна', '200,50', ''],
	crack_exists: ['SEL', 'Ліки', '', ''],
	//в аудіокнигах, огрызке (apple)
	abook_type: ['SEL', 'Тип аудіокниги', '', ''],
	publishing_type: ['SEL', 'Тип видання', '', ''],
	game_trans_type: ['SEL', 'Тип перекладу гри', '', ''],
	video_format: ['SEL', 'Формат відео', '', ''],
	video_format_new: ['SEL', 'Формат відео', '', ''],
	book_format: ['SEL', 'Формат', '', ''],
	game_lang_psp: ['SEL', 'Мова інтерфейсу гри', '', ''],
	gui_lang: ['SEL', 'Мова інтерфейсу', '', ''],
	book_lang: ['SEL', 'Мова', '', ''],
	maps_lang: ['SEL', 'Мова інтерфейсу', '', ''],
	gui_lang_new: ['SEL', 'Мова інтерфейсу', '', ''],
	tabletka_new: ['SEL', 'Ліки', '', ''],
	cpu_bits: ['SEL', 'Розрядність', '', ''],
	maps_format: ['SEL', 'Формат ', '', ''],
	atlas_type: ['SEL', 'Тип атласу ', '', ''],
	lang_book_avto: ['SEL', 'Мова авто-книги ', '', ''],
	lang_book_med: ['SEL', 'Мова мед-книги ', '', ''],
	product_milestone: ['SEL', 'Стадія розробки ', '', ''],
	apple_ios_sysreq: ['SEL', 'Системні вимоги ', '', ''],
	apple_ios_lang: ['SEL', 'Мова інтерфейсу', '', ''],
	apple_ios_dev: ['SEL', 'Сумісні пристрої', '', ''],
	apple_ios_def: ['SEL', 'Роздільні здатності', '', ''],
	apple_ios_format: ['SEL', 'Формат файлів', '', ''],
	//авто мультимедийки
	avto_mm_type: ['SEL', 'Тип мультимедіа ', '', ''],
	manga_type: ['SEL', 'Тип', '', ''],
	manga_completeness_with_header: ['SEL', 'Завершеність релизу', '', ''],
	sub_format: ['SEL', 'Формат субтитрів', '', ''],
	orig_audio: ['SEL', 'Оригінальна аудіодоріжка', '', ''],
	orig_audio_serial: ['SEL', 'Оригінальна аудіодоріжка', '', ''],
	flang_lang: ['SEL', 'Мова курсу', '', ''],
	anime_release_type: ['SEL', 'Тип релізу', '', ''],
	anime_hwp: ['SEL', 'Сумісніть із побутовими програвачами', '', ''],
	//для dorama и Live-action
	transl_dorama: ['SEL', 'Переклад', '', ''],
	sub_dorama: ['SEL', 'Жорсткі субтитри', '', ''],
	lang_dorama: ['SEL', 'Мова', '', ''],
	lang_dorama_2: ['SEL', 'Мова', '', ''],
	video_codec_2: ['SEL', 'Відеокодек', '', ''],
	audio_codec_2: ['SEL', 'Аудіокодек', '', ''],
	video_format_dorama: ['SEL', 'Формат', '', ''],
	game_type_edition: ['SEL', 'Тип видання', '', ''],
	game_lang: ['SEL', 'Мова інтерфейсу', '', ''],
	game_lang_sound: ['SEL', 'Мова озвучки', '', ''],
	game_tabletka: ['SEL', 'Ліки', '', ''],
	lang_psp: ['SEL', 'Мова інтерфейсу', '', ''],
	lang_sound_psp: ['SEL', 'Мова озвучення', '', ''],
	sub_psp: ['SEL', 'Cубтитри', '', ''],
	funct_psp: ['SEL', 'Дієздатність перевірено', '', ''],
	multiplayer_psp: ['SEL', 'Мультиплеєр', '', ''],
	popsloader_psp: ['SEL', 'Рекомендований POP"s', '', ''],
	lang_mob: ['SEL', 'Мова інтерфейсу', '', ''],
	format_clipart: ['SEL', 'Формат зображень', '', ''],
	format_photostocks: ['SEL', 'Формат зображень', '', ''],
	format_photo: ['SEL', 'Формат зображень', '', ''],
	suit_type: ['SEL', 'Тип костюмів', '', ''],
	format_vector_clipart: ['SEL', 'Формат зображень', '', ''],
	format_3d_model: ['SEL', 'Формат моделей', '', ''],
	format_3d: ['SEL', 'Формат файлів', '', ''],
	material: ['SEL', 'Матеріали', '', ''],
	texture: ['SEL', 'Текстури', '', ''],
	light_source: ['SEL', 'Джерела світла', '', ''],
	folder_pdf: ['SEL', 'Каталог PDF', '', ''],
	video_footage: ['SEL', 'Відеоформат', '', ''],
	frame_rate: ['SEL', 'Частота кадрів FPS', '', ''],
	def_footage: ['SEL', 'Роздільна здатність', '', ''],
	video_format_footage: ['SEL', 'Формат відеофайлу', '', ''],
	lang_anime: ['SEL', 'Мова', '', ''],
	lang_anime_2: ['SEL', 'Мова', '', ''],
	lang_anime_3: ['SEL', 'Мова', '', ''],
	disk_number_psn: ['SEL', 'Кількість дисків', '', ''],
	change_disk_psn: ['SEL', 'Перехід із диска на диск під час гри', '', ''],
	genre_game_dvd: ['SEL', 'Жанр', '', ''],
	platform_game_dvd: ['SEL', 'Платформа', '', ''],
	tabletka_game_dvd: ['SEL', 'Ліки', '', ''],
	format_disk_game_dvd: ['SEL', 'Формат ігрового диска', '', ''],
	format_video_game_dvd: ['SEL', 'Формат відео', '', ''],
	sub_game_video: ['SEL', 'Субтитри', '', ''],
	lang_game_video: ['SEL', 'Мова озвучення ', '', ''],
	format_game_video: ['SEL', 'Формат', '', ''],
	//трейлеры
	material_trailer: ['SEL', 'Тип матеріалу', '', ''],
	transl_trailer: ['SEL', 'Переклад', '', ''],
	video_quality_trailer: ['SEL', 'Якість', '', ''],
	video_format_trailer: ['SEL', 'Формат відео', '', ''],
	video_codec_trailer: ['SEL', 'Відеокодек', '', ''],
	audio_codec_trailer: ['SEL', 'Аудіокодек', '', ''],
	lang_old_game: ['SEL', 'Мова', '', ''],
	//Apple: iPhone, iOS, Mac и проч.
	audio_bitrate_iphone_los: ['SEL', 'Бітрейт', '', ''],
	rip_prog_iphone: ['SEL', 'Програма для створення ріпу ', '', ''],
	words_iphone: ['SEL', 'Тексти', '', ''],
	edition_type_iphone: ['SEL', 'Тип видання', '', ''],
	transl_iphone: ['SEL', 'Переклад ', '', ''],
	video_format_iphone: ['SEL', 'Формат', '', ''],
	video_codec_iphone: ['SEL', 'Відеокодек', '', ''],
	cover_iphone: ['SEL', 'Вшита обкладинка', '', ''],
	tag_iphone: ['SEL', 'Дод. теги (режисер, акторы тощо)', '', ''],
	show_iphone: ['SEL', 'Телешоу/відеокліп ', '', ''],
	chapter_iphone: ['SEL', 'Розділи', '', ''],
	series_iphone: ['SEL', 'Серія/сезон', '', ''],
	audio_codec_iphone: ['SEL', 'Аудіокодек', '', ''],
	audio_bitrate_iphone: ['SEL', 'Бітрейт', '', ''],
	audio_chapters_iphone: ['SEL', 'Розбиття на розділи', '', ''],
	platform_mob: ['SEL', 'Платформа', '', ''],
	mus_loss_performer: ['SEL', 'Виконавець', '', ''],
	audiobook_label: ['SEL', 'Видавництво', '', ''],
	platform_mac_prog: ['SEL', 'Платформа', '', ''],
	lang_mac_prog: ['SEL', 'Мова інтерфейсу', '', ''],
	tablet_mac_prog: ['SEL', 'Ліки', '', ''],
	//спорт
	video_quality_sport: ['SEL', 'Якість', '', ''],
	//Музыкальные библиотеки и Звуковые эффекты
	audio_codec_music_lib: ['SEL', 'Аудіокодек', '', ''],
	bit_music_lib: ['SEL', 'Якість', '', ''],
	bitrate_music_lib: ['SEL', 'Бітрейт', '', ''],
	rate_music_lib: ['SEL', 'Частота', '', ''],
	canales_music_lib: ['SEL', 'Канали', '', ''],
	//Ноты и т.п.
	mus_edit: ['SEL', 'Редакция', '', ''],
	mus_lang: ['SEL', 'Мова', '', ''],
	//мульты и сериалы?
	transl_cartoons_0: ['SEL', 'Переклад', '', ''],
	transl_cartoons_1: ['SEL', 'Переклад 2', '', ''],
	transl_cartoons_2: ['SEL', 'Переклад 3', '', ''],
	format_cartoons_dvd: ['SEL', 'Формат', '', ''],
	type_cartoons: ['SEL', 'Якість', '', ''],
	screen_cartoons: ['SEL', 'Формат екрану', '', ''],
	def_cartoons: ['SEL', 'Система / Роздільна здатність', '', ''],
	video_quality_cartoons: ['SEL', 'Якість', '', ''],
	format_cartoons: ['SEL', 'Формат', '', ''],
	video_quality_cartoons_hd: ['SEL', 'Якість', '', ''],
	format_cartoons_hd: ['SEL', 'Формат', '', ''],
	video_quality_cart_serial: ['SEL', 'Якість', '', ''],
	format_cart_serial: ['SEL', 'Формат', '', ''],
	video_codec_serials: ['SEL', 'Відеокодек', '', ''],
	audio_codec_serials: ['SEL', 'Аудіокодек', '', ''],
	//разное - аватарки
	type_avatar: ['SEL', 'Тип матеріалу', '', ''],
	//темы кпк
	type_theme_kpk: ['SEL', 'Тип матеріалу', '', ''],
	type_3d_model: ['SEL', 'Кількість', '', ''],
	video_quality_vlesson: ['SEL', 'Якість', '', ''],
	format_vlesson: ['SEL', 'Формат', '', ''],
	video_codec_vlesson: ['SEL', 'Відеокодек', '', ''],
	audio_codec_vlesson: ['SEL', 'Аудіокодек', '', ''],
	transl_doc_film: ['SEL', 'Переклад', '', ''],
	chapters_music_dvd: ['SEL', 'Розбиття на розділи за треками', '', ''],
	video_quality_music_dvd: ['SEL', 'Якість ', '', ''],
	format_music_dvd: ['SEL', 'Формат', '', ''],
	video_codec_music_dvd: ['SEL', 'Відеокодек', '', ''],
	audio_codec_music_dvd: ['SEL', 'Аудіокодек', '', ''],
	audio_codec_mus_loss: ['SEL', 'Аудіокодек', '', ''],
	rip_type_mus_loss: ['SEL', 'Тип ріпу', '', ''],
	scan_mus_loss: ['SEL', 'Наявність сканів у роздачі', '', ''],
	scan_mus_loss_apple: ['SEL', 'Наявність сканів у роздачі', '', ''],
	source_mus_loss: ['SEL', 'Джерело', '', ''],
	genre_soundtrack_mus: ['SEL', 'Жанр', '', ''],
	audio_codec_digit_mus: ['SEL', 'Аудіокодек', '', ''],
	source_digit_mus: ['SEL', 'Джерело оцифрування', '', ''],
	vinyl_digit_mus: ['SEL', 'Код класу стану вінілу', '', ''],
	perfotmer_mus_lossy: ['SEL', 'Виконавець', '', ''],
	audio_codec_mus_lossy: ['SEL', 'Аудіокодек', '', ''],
	rip_type_mus_lossy: ['SEL', 'Тип ріпу', '', ''],
	bitrate_mus_lossy: ['SEL', 'Бітрейт аудіо', '', ''],
	tag_mus_lossy: ['SEL', 'ID3-теги', '', ''],
	//тестовые диски
	rip_type_test: ['SEL', 'Тип ріпу', '', ''],
	audio_codec_test: ['SEL', 'Аудіокодек', '', ''],
	video_codec_test: ['SEL', 'Відеокодек', '', ''],
	//linux - ось и программы.
	arch_linux: ['SEL', 'Архітектура', '', ''],
	channel_sound: ['SEL', 'Канали', '', ''],
	lang_game_dvd_pleer: ['SEL', 'Мова інтерфейсу', '', ''],
	audio_codec_film: ['SEL', 'Аудіокодек', '', ''],
	video_quality_serials: ['SEL', 'Якість', '', ''],
	video_quality_serial: ['SEL', 'Якість', '', ''],
	loss_bit: ['SEL', 'Бітрейт аудіо', '', ''],
	type_homebrewe: ['SEL', 'Тип', '', ''],
	console_type: ['SEL', 'Консоль', '', ''],
	anime_type: ['SEL', 'Тип', '', ''],
	sub_all_anime: ['SEL', 'Мова субтитрів', '', ''],
	sub_all_anime_2: ['SEL', 'Мова субтитрів', '', ''],
	sub_all_anime_3: ['SEL', 'Мова субтитрів', '', ''],
	transl_lat_setial: ['SEL', 'Переклад', '', ''],
	transl_lat_setial_1: ['SEL', 'Переклад', '', ''],
	transl_lat_setial_2: ['SEL', 'Переклад', '', ''],
	format_lat_serial: ['SEL', 'Формат', '', ''],
	game_lang_nds: ['SEL', 'Мова:', '', ''],
	lang_comp_vlesson: ['SEL', 'Мова', '', ''],
	type_comp_vlesson: ['SEL', 'Тип матеріалу', '', ''],
	source_mus_lossy: ['SEL', 'Джерело', '', ''],
	lang_notes: ['SEL', 'Мова', '', ''],
	licence_old_game: ['SEL', 'Ліцензія?', '', ''],
	lang_video_les: ['SEL', 'Мова', '', ''],
	type_vlesson: ['SEL', 'Тип матеріалу', '', ''],
	type_game: ['SEL', 'Тип роздачі', '', ''],
	lang_other_game: ['SEL', 'Необхідна мова гри', '', ''],
	//кпк
	format_smart: ['SEL', 'Формат', '', ''],
	def_smart: ['SEL', 'Роздільна здатність', '', ''],
	video_quality_smart: ['SEL', 'Якість', '', ''],
	video_codec_smart: ['SEL', 'Відеокодек', '', ''],
	audio_codec_smart: ['SEL', 'Аудіокодек', '', ''],
	prefix_kpk: ['SEL', 'Префікс', '', ''],
	format_mob: ['SEL', 'Формат', '', ''],
	def_mob: ['SEL', 'Роздільна здатність', '', ''],
	publishing_type_mob: ['SEL', 'Тип видання', '', ''],
	platform_symb: ['SEL', 'Платформа', '', ''],
	launch_xbox: ['SEL', 'Можливість запуску на Xbox 360', '', ''],
	launch_pc: ['SEL', 'Можливість запуску на PC', '', ''],
	video_codec_3d: ['SEL', 'Відеокодек', '', ''],
	audio_codec_3d: ['SEL', 'Аудіокодек', '', ''],
	video_quality_3d_1: ['SEL', 'Якість', '', ''],
	video_quality_3d_2: ['SEL', 'Якість', '', ''],
	container_3d: ['SEL', 'Контейнер', '', ''],
	format_3d: ['SEL', 'Формат 3D', '', ''],
	angle_3d: ['SEL', 'Порядок ракурсів', '', ''],
	update_game: ['SEL', 'Оновлення роздачі', '', ''],
	audio_codec_anime_loss: ['SEL', 'Аудіокодек', '', ''],
	lang_anime_transl: ['SEL', 'Мова', '', ''],
	lang_anime_transl_2: ['SEL', 'Мова', '', ''],
	lang_anime_transl_3: ['SEL', 'Мова', '', ''],
	country_anime: ['SEL', 'Країна', '', ''],
	//відео для PSP
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
	// ID контейнеров содержащих html елементов
	load_pic_btn: 'Кнопка "Додати зображення"',
	load_pic_faq_url: 'Посилання на FAQ "Як залити зображення на безкоштовний хостинг"',
	manga_type_faq_url: 'Посилання на FAQ "Детальніше про типи манги"',
	test_dash: 'Статичний елемент "-" для заголовку',
	make_screenlist_faq_url: 'Як зробити скріншот / скрінліст',
	translation_rules_faq_url: 'Правила позначення перекладів',
	make_sample_faq_url: 'Як зробити семпл відео',
	dvd_reqs_faq_url: 'Вимоги та приклади для DVD',
	hd_reqs_faq_url: 'Вимоги та приклади для HD',
	videofile_info_faq_url: 'Як отримати інформацію про відеофайл',
	bdinfo_faq_url: 'BDInfo',
	dvdinfo_faq_url: 'DVDInfo',
	make_poster_faq_url: 'Інструкція з виготовлення постера',
	pred_alt1_faq_url: 'Про посилання на попередні та альтернативні роздачі',
	quality_decl_faq_url: 'Про позначення якості',
	pred_alt2_faq_url: 'Про посилання на попередні та альтернативні роздачі',
	pred_alt3_faq_url: 'Про посилання на попередні та альтернативні роздачі',
	pred_alt4_faq_url: 'Про посилання на попередні та альтернативні роздачі',
	dvdinfo_faq_url: 'Як отримати інформацію про DVD-Video',
	tyt_faq_url: 'тут',
	wtf_faq_url: 'Що це означає?',
	DVD_PG: 'DVD-PG',
	faq_catalog: 'інструкція',
	psp_psx: 'PSP-PSX',
	faq_pops: 'Що таке Popsloader?',
	faq_code: 'Як дізнатися код диcка?',
	faq_code_PS: 'Як дізнатися код диcка?',
	faq_pegi: 'PEGI',
	faq_screen_psp: 'Як зробити скріншоти з PSP',
	series: 'Серії:',
	season: 'Сезон:',
	series_of: 'з',
	point: ',',
	d_ukr: 'у 3Д /',
	d_eng: '3D',
	genre_faq_url: 'Як визначити жанр?',
	quality_faq: 'Позначення якості відео',
	file_list: 'Як створити список файлів?',
	comparison_anime: 'Порівняння з іншими роздачами.',
	faq_game: 'Прев\'ю (гри)',
	nds: '[NDS]',
	Dreamcast: '[DC]',
	faq_traclist: 'Як швидко створити трекліст із зазначенням бітрейту',
	number: '№',
	faq_isbn: 'Що таке ISBN/ISSN?',
	faq_scrn_books: 'Як зробити приклади сторінок (скріншоти) для роздачі?',
	faq_ps_image: 'FAQ зі зняття образу для Ps1',
	faq_mac_scrn: 'Створення скріншотів у Mac OS',
	// ID елементів, для яких треба створити приховані елементи, що містять абревіатури для підстановки у назву
	// Кожен елемент el_abr повинен точно відповідати el (translation_abr -> translation)
	translation_abr: '[ABR] Переклад',
	translation2_abr: '[ABR] Переклад',
	translation3_abr: '[ABR] Переклад',
	translation4_abr: '[ABR] Переклад',
	maps_lang_abr: '[ABR] Мова інтерфейсу (карты)',
	gui_lang_new_abr: '[ABR] Мова інтерфейсу (новый список)',
	transl_cartoons_0_abr: '[ABR] Переклад',
	transl_cartoons_1_abr: '[ABR] Переклад',
	transl_cartoons_2_abr: '[ABR] Переклад',
	cpu_bits_abr: '[ABR]Розрядність',
	maps_format_abr: '[ABR]Формат',
	lang_book_avto_abr: '[ABR]Мова авто-книги',
	lang_book_med_abr: '[ABR]Мова мед-книги',
	book_lang_abr: '[ABR]Мова книги',
	orig_audio_abr: '[ABR]Мова доріжки',
	orig_audio_serial_abr: '[ABR]Оригінальна аудіодорожка',
	translation_abr: '[ABR]Переклад',
	flang_lang_abr: '[ABR]Мова книги',
	sub_all_new_abr: '[ABR]Субтитри',
	lang_dorama_abr: '[ABR]Мова',
	game_lang_abr: '[ABR] Мова інтерфейсу',
	game_lang_sound_abr: '[ABR] Мова озвучки',
	lang_psp_abr: '[ABR] Мова інтерфейсу',
	lang_mob_abr: '[ABR] Мова інтерфейсу',
	game_type_edition_abr: '[ABR] Тип видання',
	lang_anime_abr: '[ABR] Мова',
	lang_anime_2_abr: '[ABR] Мова',
	lang_anime_3_abr: '[ABR] Мова',
	anime_hwp_abr: '[ABR] Сумісність із побутовими програвачами',
	publishing_type_abr: '[ABR] Тип видання',
	lang_old_game_abr: '[ABR] Мова',
	mus_lang_abr: '[ABR] Мова',
	perfotmer_mus_lossy_abr: '[ABR] Виконавець',
	lang_game_dvd_pleer_abr: '[ABR] Мова',
	video_format_new_abr: '[ABR] Формат відео',
	audio_codec_mus_loss_abr: '[ABR] Аудіокодек',
	lang_dorama_2_abr: '[ABR] Мова',
	type_homebrewe_abr: '[ABR] Тип',
	console_type_abr: '[ABR] Консоль',
	sub_all_anime_abr: '[ABR] Мова',
	sub_all_anime_2_abr: '[ABR] Мова',
	sub_all_anime_3_abr: '[ABR] Мова',
	lang_game_video_abr: '[ABR] Мова',
	sub_game_video_abr: '[ABR] Мова',
	publishing_type_old_abr: '[ABR] Тип видання',
	lang_comp_vlesson_abr: '[ABR] Мова',
	type_comp_vlesson_abr: '[ABR] Тип матеріалу',
	lang_notes_abr: '[ABR] Мова',
	ukr_sub_abr: '[ABR] Українські субтитри',
	lang_video_les_abr: '[ABR] Мова',
	type_vlesson_abr: '[ABR] Тип матеріалу',
	publishing_type_mob_abr: '[ABR] Тип видання',
	format_3d_abr: '[ABR] Формат 3D',
	audio_codec_anime_loss_abr: '[ABR] Аудіокодек',
	lang_anime_transl_abr: '[ABR] Мова',
	lang_anime_transl_2_abr: '[ABR] Мова',
	lang_anime_transl_3_abr: '[ABR] Мова',
	psp_video_type_abr: '[ABR] Тип',
	apple_ios_sysreq_abr: '[ABR] Системні вимоги',
	apple_ios_lang_abr: '[ABR] Мова інтерфейсу',
	apple_ios_format_abr: '[ABR] Формат файлів',
	apple_ios_def_abr: '[ABR] Підтримувана роздільна здатність',
	mus_loss_performer_abr: '[ABR] Виконавець',
	platform_mac_prog_abr: '[ABR] Платформа',
	lang_mac_prog_abr: '[ABR] Мова інтерфейсу',
	tablet_mac_prog_abr: '[ABR] Ліки',
	// dummy
	dummy_abr: '[ABR] '
};

/*
  -------------------------------------------------------------------------------------------------
  -- selects --------------------------------------------------------------------------------------
  -------------------------------------------------------------------------------------------------
*/
TPL.selects = {
	// [0] всегда имеет value='' и если задан как '' (пустая строка) заменяется на "&raquo; Обрати"
	//фильмы зарубежка, наше и т.д. авто, медицина.
	translation: [
		'',
		'Професійний (дубльований)',
		'Професійний (багатоголосий закадровий)',
		'Професійний (двоголосий закадровий)',
		'Любительский (дубльований)',
		'Любительский (багатоголосий закадровий)',
		'Любительский (двоголосий закадровий)',
		'Студійний (одноголосий закадровий)',
		'Авторський (одноголосий закадровий)',
		'Одноголосий закадровий',
		'Субтитри',
		'Не потрібен',
		'Відсутній'
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
		'Професійний (дубльований)',
		'Професійний (багатоголосий закадровий)',
		'Професійний (двоголосий закадровий)',
		'Любительский (дубльований)',
		'Любительский (багатоголосий закадровий)',
		'Любительский (двоголосий закадровий)',
		'Студійний (одноголосий закадровий)',
		'Авторський (одноголосий закадровий)',
		'Одноголосий закадровий',
		'Субтитри',
		'Не потрібен',
		'Відсутній'
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
		'Професійний (дубльований)',
		'Професійний (багатоголосий закадровий)',
		'Професійний (двоголосий закадровий)',
		'Любительский (дубльований)',
		'Любительский (багатоголосий закадровий)',
		'Любительский (двоголосий закадровий)',
		'Студійний (одноголосий закадровий)',
		'Авторський (одноголосий закадровий)',
		'Одноголосий закадровий',
		'Субтитри',
		'Не потрібен',
		'Відсутній'
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
		'Професійний (дубльований)',
		'Професійний (багатоголосий закадровий)',
		'Професійний (двоголосий закадровий)',
		'Любительский (дубльований)',
		'Любительский (багатоголосий закадровий)',
		'Любительский (двоголосий закадровий)',
		'Студійний (одноголосий закадровий)',
		'Авторський (одноголосий закадровий)',
		'Одноголосий закадровий',
		'Субтитри',
		'Не потрібен',
		'Відсутній'
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
		'Українська',
		'Російська',
		'Російська (дореволюційна)',
		'Білоруська',
		'Польська',
		'Англійська',
		'Німецька',
		'Французька',
		'Італійська',
		'Іспанська',
		'Португальська',
		'Китайська',
		'Японська',
		'Болгарська',
		'Інша'
	],

	book_lang_abr: [
		'',
		'UKR',
		'RUS',
		'RUS',
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
		'Українська',
		'Російська',
		'Білоруська',
		'Польська',
		'Англійська',
		'Німецька',
		'Французька',
		'Італійська',
		'Іспанська',
		'Португальська',
		'Китайська',
		'Японська',
		'Арабська',
		'Інша'
	],

	flang_lang_abr: [
		'',
		'UKR',
		'RUS',
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
		'hardsub (вшиті)'
	],

	//відео iphone
	ukr_sub: [
		'',
		'є',
		'немає'
	],

	ukr_sub_abr: [
		'',
		'ukr sub',
		''
	],

	sub_all: [
		'',
		'українські',
		'англійські'
	],

	//відео для PSP
	psp_video_type: [
		'',
		'Фільм',
		'Аніме',
		'Музичний кліп',
		'Мультфільм',
		'Мультсеріал',
		'Серіал',
		'UMD Відео',
		'Спорт',
		'Різне',
		'Телепередача',
		'Документальний фільм',
		'Дорама',
		'Онгоінг (аніме)'
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
		'є',
		'немає'
	],

	orig_audio_serial_abr: [
		'',
		'Original',
		''
	],

	//фильмы зарубежка, наше и т.д.
	orig_audio: [
		'немає',
		'українська',
		'англійська',
		'російська',
		'німецька',
		'французька',
		'іспанська',
		'італійська',
		'польська',
		'чеська',
		'словацька',
		'білоруська',
		'литовська',
		'латвійська',
		'дацька',
		'норвезька',
		'шведська',
		'голландська',
		'фінська',
		'іврит',
		'румунська',
		'молдавська',
		'португальська',
		'інша'
	],

	orig_audio_abr: [
		'',
		'Original Ukr',
		'Original Eng',
		'Original Rus',
		'Original Ger',
		'Original Fre',
		'Original Spa',
		'Original Ita',
		'Original Pol',
		'Original Cze',
		'Original Slo',
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
		'&raquo; Якість відео',
		'DVDRip',
		'DVD5',
		'DVD5 (стиснутий)',
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
		'» Вкажіть якість',
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
		'&raquo; Формат відео',
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
		'&raquo; Відеокодек',
		'DivX',
		'XviD',
		"Інший MPEG4",
		'VPx',
		'MPEG1',
		'MPEG2',
		'Windows Media',
		'QuickTime',
		'H.264',
		'Flash'
	],

	video_codec_2: [
		'&raquo; Відеокодек',
		'DivX',
		'XviD',
		"Інший MPEG4",
		'VPx',
		'MPEG1',
		'MPEG2',
		'Windows Media',
		'QuickTime',
		'H.264',
		'Flash'
	],

	audio_codec: [
		'&raquo; Аудіокодек',
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
		'&raquo; Аудіокодек',
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
		'&raquo; Бітрейт аудіо',
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
		'аудіокнига',
		'аудіоспектакль',
		'модель для збірки'
	],

	publishing_type: [
		'&raquo; Тип видання',
		'ліцензія',
		'піратка'
	],

	publishing_type_abr: [
		'',
		'L',
		'P'
	],

	crack_exists: [
		'&raquo; Ліки',
		'не потрібні',
		'присутні',
		'відсутні'
	],

	gui_lang: [
		'',
		'українська + англійська',
		'лише українська',
		'лише англійська',
		'російська',
		'інша'
	],

	game_platform: [
		'',
		'PS',
		'PS2'
	],

	game_region_def: [
		'&raquo; Регіон',
		'PAL',
		'NTSC',
		'Інший'
	],

	game_region: [
		'&raquo; Регіон',
		'Europe',
		'US',
		'Japan'
	],

	//psp-psx
	game_version: [
		'',
		'FULL',
		'RIP',
		'Інша'
	],

	game_firmware: [
		'',
		'iXtreme Compatible',
		'Xtreme'
	],

	game_age: [
		'&raquo; Вік',
		'EC - Для дітей молодшого віку',
		'E - Для всіх',
		'E10+ - Для дітей, старших 10 років',
		'T - Для підлітків 13-19 років',
		'M - Від 17 років',
		'AO - Лише для дорослих',
		'RP - Рейтинг очікується',
		'Інший'
	],

	game_multiplay: [
		'',
		'немає',
		'2 гравці',
		'4 гравці',
		'більше 4 гравців'
	],

	game_lang_psp: [
		'&raquo; Мова інтерфейсу',
		'JAP',
		'ENG',
		'RUS',
		'Multi5'
	],

	game_trans_type: [
		'&raquo; Тип перекладу',
		'текст',
		'текст + звук',
		'немає'
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
		'Інший'
	],

	book_quality: [
		'',
		'Відскановані сторінки',
		'Відскановані сторінки + шар розпізнаного тексту',
		'Розпізнаний текст із помилками (OCR)',
		'Розпізнаний текст без помилок (OCR)',
		'Електронна книга (eBook)',
		'Сфотографовані сторінки'
	],

	vista_compat: [
		'&raquo; Сумісність із Vista',
		'повна',
		'є',
		'немає',
		'невідомо'
	],

	game_plat_wii: [
		'',
		'Nintendo Wii',
		'GameCube'
	],

	maps_lang: [
		'',
		'Українська + англійська',
		'Українська',
		'Англійська',
		'Російська',
		'Німецька',
		'Багатомовна (українська присутня)',
		'Багатомовна (українська відсутня)',
		'Інша'
	],

	maps_lang_abr: [
		'',
		'UKR + ENG', // Українська + англійська
		'UKR', // Українська
		'ENG', // Англійська
		'RUS', // Російська
		'GER', // Німецька
		'MULTILANG +UKR', // Багатомовна (українська присутня)
		'MULTILANG -UKR', // Багатомовна (українська відсутня)
		''
	],

	gui_lang_new: [
		'',
		'Українська + англійська',
		'Українська',
		'Англійська',
		'Російська',
		'Багатомовний (українська присутня)',
		'Багатомовний (українська відсутня)',
		'Німецька',
		'Японська',
		'Інша'
	],

	gui_lang_new_abr: [
		'',
		'UKR + ENG',
		'UKR',
		'ENG',
		'RUS',
		'Multi + UKR',
		'Multi, NO UKR',
		'DEU',
		'JAP',
		''
	],

	tabletka_new: [
		'',
		'Присутні',
		'Відсутні',
		'Вилікувано',
		'Не потрібні'
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
		'&raquo; Сумісність із Vista',
		'повна',
		'лише з х86 (32-біт)',
		'лише з х64 (64-біт)',
		'немає',
		'невідомо'
	],

	windows7_compat: [
		'&raquo; Сумісність із Windows 7',
		'повна',
		'лише з х86 (32-біт)',
		'лише з х64 (64-біт)',
		'немає',
		'невідомо'
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
		'Топографічні карти',
		'Карти'
	],

	lang_book_avto: [
		'',
		'Українська',
		'Англійська',
		'Російська',
		'Німецька',
		'Японська',
		'Інша'
	],

	lang_book_avto_abr: [
		'',
		'UKR',
		'ENG',
		'RUS',
		'DEU',
		'JAP',
		''
	],

	lang_book_med: [
		'',
		'Українська',
		'Англійська',
		'Російська',
		'Німецька',
		'Французька',
		'Інша'
	],

	lang_book_med_abr: [
		'',
		'UKR',
		'ENG',
		'RUS',
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
		'&raquo; Завершеність',
		'complete',
		'incomplete'
	],

	video_format_new: [
		'» Вкажіть формат відео',
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
		'немає',
		'українські',
		'англійські',
		'російські',
		'німецькі',
		'французькі',
		'іспанські',
		'італійські',
		'польські',
		'чеські',
		'словацькі',
		'білоруські',
		'литовські',
		'латвійські',
		'датські',
		'норвезькі',
		'шведські',
		'голландські',
		'фінські',
		'іврит',
		'румунські',
		'молдавські',
		'португальські',
		'інші'
	],

	sub_all_new_abr: [ // Переклад языков для субтитрів и оригинальной доріжки в тэги для заголовку
		'',
		'',
		'Sub Ukr',
		'Sub Eng',
		'Sub Rus',
		'Sub Ger',
		'Sub Fre',
		'Sub Spa',
		'Sub Ita',
		'Sub Pol',
		'Sub Cze',
		'Sub Slo',
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
		'&raquo; Тип релізу',
		'Хардсаб',
		'Без хардсабу',
		'Напівхардсаб'
	],

	anime_hwp: [
		'&raquo; Сумісність із побутовими програвачами',
		'Є',
		'Немає'
	],

	anime_hwp_abr: [
		'',
		'HWP',
		''
	],

	transl_dorama: [
		'',
		'Українські субтитри',
		'Одноголосе озвучення',
		'Двоголосе озвучення',
		'Багатоголосе озвучення',
		'Дубляж',
		'Відсутній'
	],

	sub_dorama: [
		'',
		'Хардсаб',
		'Напівхардсаб',
		'Без хардсабу'
	],

	lang_dorama: [
		'&raquo; Мова',
		'Українська (окремим файлом)',
		'Українська (у складі контейнера)',
		'Японська',
		'Китайська',
		'Корейська',
		'Тайванська',
		'Англійська',
		'Російська'
	],

	lang_dorama_2: [
		'&raquo; Мова',
		'Українська (окремим файлом)',
		'Українська (у складі контейнера)',
		'Японська',
		'Китайська',
		'Корейська',
		'Тайванська',
		'Англійська',
		'Російська'
	],

	lang_dorama_abr: [
		'',
		'UKR(ext)',
		'UKR(int)',
		'JAP',
		'CHI',
		'KOR',
		'TW',
		'ENG',
		'RUS'
	],

	lang_dorama_2_abr: [
		'',
		'UKR(ext)',
		'UKR(int)',
		'JAP',
		'CHI',
		'KOR',
		'TW',
		'ENG',
		'RUS'
	],

	game_type_edition: [
		'',
		'Ліцензія',
		'Неофіційне',
		'RePack',
		'RiP',
		'Демо-версія',
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
		'українська',
		'українська + англійська',
		'англійська',
		'російська',
		'німецька',
		'многоязычный',
		'відсутня / не требуется',
		'інша'
	],

	game_lang_abr: [
		'',
		'UKR',
		'UKR + ENG',
		'ENG',
		'RUS',
		'DEU',
		'Multi',
		'',
		''
	],

	game_lang_sound: [
		'',
		'українська',
		'українська + англійська',
		'англійська',
		'російська',
		'німецька',
		'відсутня/не требуется',
		'Другая'
	],

	game_lang_sound_abr: [
		'',
		'UKR',
		'UKR + ENG',
		'ENG',
		'RUS',
		'DEU',
		'',
		''
	],

	game_tabletka: [
		'',
		'Присутні',
		'Відсутні',
		'Емуляція образу',
		'Не потрібні'
	],

	lang_psp: [
		'',
		'Українська',
		'Японська',
		'Англійська',
		'Російська',
		'Multi2',
		'Multi3',
		'Multi4',
		'Multi5',
		'Інша'
	],

	lang_psp_abr: [
		'',
		'UKR',
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
		'Відсутня',
		'Українська',
		'Японська',
		'Англійська',
		'Російська',
		'Інша'
	],

	sub_psp: [
		'',
		'Немає',
		'Японські',
		'Англійські',
		'Українські',
		'Інші'
	],

	funct_psp: [
		'',
		'Так',
		'Ні'
	],

	multiplayer_psp: [
		'',
		'2 гравці',
		'4 гравців',
		'Немає'
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
		'Інший'
	],

	//для кпк, мобильных и т.п.
	lang_mob: [
		'&raquo; Мова інтерфейсу',
		'Українська',
		'Українська + англійська',
		'Англійська',
		'Російська',
		'Багатомовний'
	],

	lang_mob_abr: [
		'',
		'UKR',
		'UKR + ENG',
		'ENG',
		'RUS',
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
		'Жіночі костюми',
		'Чоловічі костюми',
		'Дитячі костюми',
		'Групові костюми'
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
		'Інший'
	],

	format_3d: [
		'',
		'max',
		'3ds',
		'c4d',
		'pos',
		'obj',
		'Інший'
	],

	material: [
		'',
		'Так',
		'Ні'
	],

	texture: [
		'',
		'Так',
		'Ні'
	],

	light_source: [
		'',
		'Так',
		'Ні'
	],

	folder_pdf: [
		'',
		'Так',
		'Ні'
	],

	//футажи
	video_footage: [
		'',
		'PAL',
		'NTSC',
		'HD',
		'Інший'
	],

	def_footage: [
		'',
		'720x480',
		'720x576',
		'1280x720',
		'Інша'
	],

	frame_rate: [
		'',
		'25',
		'30',
		'60',
		'Інша'
	],

	video_format_footage: [
		'',
		'MOV',
		'AVI',
		'Інший'
	],

	lang_anime: [
		'&raquo; Мова',
		'Українська (окремим файлом)',
		'Українська (у складі контейнера)',
		'Японська',
		'Англійська',
		'Корейська',
		'Китайська',
		'Іспанська',
		'Італійська',
		'Німецька',
		'Російська',
		'Інша'
	],

	lang_anime_abr: [
		'',
		'UKR(ext)',
		'UKR(int)',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		'RUS',
		''
	],

	lang_anime_2: [
		'&raquo; Мова',
		'Українська (окремим файлом)',
		'Українська (у складі контейнера)',
		'Японська',
		'Англійська',
		'Корейська',
		'Китайська',
		'Іспанська',
		'Італійська',
		'Німецька',
		'Російська',
		'Інша'
	],

	lang_anime_2_abr: [
		'&raquo; Мова',
		'UKR(ext)',
		'UKR(int)',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		'RUS',
		''
	],

	lang_anime_3: [
		'&raquo; Мова',
		'Українська (окремим файлом)',
		'Українська (у складі контейнера)',
		'Японська',
		'Англійська',
		'Корейська',
		'Китайська',
		'Іспанська',
		'Італійська',
		'Німецька',
		'Російська',
		'Інша'
	],

	lang_anime_3_abr: [
		'',
		'UKR(ext)',
		'UKR(int)',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		'RUS',
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
		'Диск лише один',
		'Є',
		'Немає'
	],

	genre_game_dvd: [
		'',
		'Interactive Movie',
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
		'Інша'
	],

	tabletka_game_dvd: [
		'Не потрібні'
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
		'немає',
		'українські',
		'англійські',
		'російські',
		'німецькі',
		'інші'
	],

	sub_game_video_abr: [
		'',
		'Sub-UKR',
		'Sub-ENG',
		'Sub-RUS',
		'Sub-DEU',
		''
	],

	lang_game_video: [
		'',
		'українська',
		'англійська',
		'російська',
		'німецька',
		'інша',
		'немає'
	],

	lang_game_video_abr: [
		'',
		'UKR',
		'ENG',
		'RUS',
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
		'Інший'
	],

	material_trailer: [
		'',
		'трейлер',
		'тизер',
		'фільм про фільм',
		'додаткові матеріали',
		'інтерв\'ю з акторами',
		'сюжет із фільму',
		'вирізані сцени'
	],

	//для трейлеров, відео (разное) и спорта!!!
	transl_trailer: [
		'',
		'Професійний (одноголосий закадровий)',
		'Любительский (одноголосий закадровий)',
		'Двоголосий закадровий',
		'Багатоголосий закадровий',
		'Повний дубляж',
		'Субтитри',
		'Не потрібно'
	],

	//для трейлеров, відео (разное) и спорта!!!
	video_quality_trailer: [
		'&raquo; Якість відео',
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

	//для трейлеров, відео (разное) и спорта!!!
	video_format_trailer: [
		'',
		'AVI',
		'FLV',
		'MOV',
		'MKV',
		'MP4',
		'DVD Video'
	],

	//для трейлеров, відео (разное) и спорта!!!
	video_codec_trailer: [
		'',
		'DivX',
		'XviD',
		'H264',
		'MPEG2'
	],

	//для трейлеров, відео (разное) и спорта!!!
	audio_codec_trailer: [
		'',
		'MP3',
		'AC3',
		'AAC',
		'WMA',
		'DTS'
	],

	lang_old_game: [
		'&raquo; Мова інтерфейсу',
		'українська',
		'українська + англійська',
		'англійська',
		'російська',
		'багатомовна',
		'інша'
	],

	lang_old_game_abr: [
		'',
		'[UKR]',
		'[UKR + ENG]',
		'[ENG]',
		'[RUS]',
		'[Multi]',
		''
	],

	//apple
	edition_type_iphone: [
		'',
		'оригінал',
		'перевидання',
		'ремайстер',
		'ремікс',
		'збірник',
		'синґл'
	],

	words_iphone: [
		'',
		'вбудовані',
		'вбудовані частково',
		'відсутні',
		'не потрібні'
	],

	rip_prog_iphone: [
		'',
		'iTunes (диск)',
		'EAC (диск)',
		'foobar2000 + iTunes (lossless)',
		'Стороння програма (lossless)',
		'XLD (lossless)'
	],

	audio_bitrate_iphone_los: [
		'&raquo; Бітрейт аудіо',
		'lossless',
		'lossless CBR (1411)'
	],

	transl_iphone: [
		'',
		'Любительский одноголосий',
		'Любительский багатоголосий',
		'Любительский Гобліна',
		'Професійний (одноголосий)',
		'Професійний (двоголосий)',
		'Професійний (дубльований)',
		'Професійний (багатоголосий закадровий)',
		'Професійний (дубляж)',
		'Субтитри',
		'Відсутній',
		'Не потрібен'
	],

	video_format_iphone: [
		'',
		'*.mp4',
		'*.m4v',
		'*.mov'
	],

	video_codec_iphone: [
		'&raquo; Відеокодек',
		'H.264',
		'XviD',
		'Інший MPEG4'
	],

	audio_codec_iphone: [
		'&raquo; Аудіокодек',
		'ААС',
		'ALAC',
		'AAC + AC3'
	],

	cover_iphone: [
		'',
		'є',
		'немає'
	],

	tag_iphone: [
		'',
		'прописані',
		'немає',
		'частково'
	],

	show_iphone: [
		'',
		'прописані',
		'немає',
		'не потрібні'
	],

	chapter_iphone: [
		'',
		'прописані',
		'немає'
	],

	series_iphone: [
		'',
		'прописані',
		'немає',
		'не потрібні'
	],

	audio_bitrate_iphone: [
		'&raquo; Бітрейт',
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
		'Інший'
	],

	audio_chapters_iphone: [
		'',
		'є',
		'немає'
	],

	audiobook_label: [
		'Офіційне видання (заповнити сусіднє поле)',
		'Аудіокнига своїми руками',
		'Ніде не купиш'
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
		'українська + англійська',
		'англійська',
		'німецька',
		'російська'
	],

	lang_mac_prog_abr: [
		'',
		'UKR + ENG',
		'ENG',
		'DEU',
		'RUS'
	],

	tablet_mac_prog: [
		'',
		'Серійний номер',
		'Програма вилікувана (не потребує введення даних/вводимо будь-які дані)',
		'Файл ліцензії',
		'Кейґен',
		'Кейґен (необхідний емулятор Windows)',
		'Немає'
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
		'&raquo; Формат відео',
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
		'&raquo; Якість відео',
		'DVDRip',
		'DVD5',
		'DVD5 (стиснутий)',
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
		'&raquo; Аудіокодек',
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
		'Виконавець/гурт',
		'Збірник композицій різних виконавців',
		'Саундтрек'
	],

	mus_loss_performer_abr: [
		'',
		'',
		'VA',
		''
	],

	//Библиотеки семплов
	bit_music_lib: [
		'&raquo; Бітність',
		'8 bit',
		'16 bit',
		'24 bit',
		'Інша'
	],

	bitrate_music_lib: [
		'&raquo; Бітрейт',
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
		'Інший'
	],

	rate_music_lib: [
		'&raquo; Частота',
		'22 kHz',
		'44.1 kHz',
		'48 kHz',
		'96 kHz'
	],

	canales_music_lib: [
		'&raquo; Канали',
		'mono',
		'stereo',
		'5.1'
	],

	channel_sound: [
		'&raquo; Канали',
		'mono',
		'stereo'
	],

	//Ноты
	mus_edit: [
		'Прізвище редактора (заповнити сусіднє поле)',
		'Уртекст',
		'Невідомо/не вказано'
	],

	mus_lang: [
		'',
		'Українська',
		'Англійська',
		'Російська',
		'Німецька',
		'Французька',
		'Італійська',
		'Іспанська',
		'Китайська',
		'Японська',
		'Інша'
	],

	mus_lang_abr: [
		'',
		'UKR',
		'ENG',
		'RUS',
		'DEU',
		'FRA',
		'ITA',
		'ESP',
		'CHI',
		'JPN',
		''
	],

	transl_cartoons_0: [
		'&raquo; Переклад',
		'Повний дубляж',
		'Професійний багатоголосий закадровий',
		'Професійний двоголосий закадровий',
		'Професійний одноголосий закадровий',
		'Любительский багатоголосий закадровий',
		'Любительский двоголосий закадровий',
		'Любительский одноголосий закадровий (автор)',
		'Авторський одноголосий закадровий (автор)',
		'Не потрібен',
		'Відсутній'
	],

	transl_cartoons_1: [
		'&raquo; Переклад 2',
		'Повний дубляж',
		'Професійний багатоголосий закадровий',
		'Професійний двоголосий закадровий',
		'Професійний одноголосий закадровий',
		'Любительский багатоголосий закадровий',
		'Любительский двоголосий закадровий',
		'Любительский одноголосий закадровий (автор)',
		'Авторський одноголосий закадровий (автор)',
		'Не потрібен'
	],

	transl_cartoons_2: [
		'&raquo; Переклад 3',
		'Повний дубляж',
		'Професійний багатоголосий закадровий',
		'Професійний двоголосий закадровий',
		'Професійний одноголосий закадровий',
		'Любительский багатоголосий закадровий',
		'Любительский двоголосий закадровий',
		'Любительский одноголосий закадровий (автор)',
		'Авторський одноголосий закадровий (автор)',
		'Не потрібен'
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
		'&raquo; Якість',
		'DVD5',
		'DVD9',
		'DVD5 (Custom)',
		'DVD5 (стиснутий)',
		'DVD9 (Custom)',
		'DVD9 (стиснутий)'
	],

	screen_cartoons: [
		'&raquo; Формат екрану',
		'16:9',
		'4:3'
	],

	def_cartoons: [
		'&raquo; Система / Роздільна здатність',
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
		'&raquo; Якість',
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
		'DVD5 (стиснутий)',
		'DVD9 (Custom)',
		'DVD9 (стиснутий)'
	],

	format_cartoons: [
		'&raquo; Формат',
		'AVI',
		'MKV',
		'MP4',
		'DVD-Video'
	],

	video_quality_cartoons_hd: [
		'&raquo; Якість',
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
		'&raquo; Якість',
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
		'DVD5 (стиснутий)',
		'DVD9 (Custom)',
		'DVD9 (стиснутий)',
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
		'&raquo; Якість',
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
		'Інша'
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
		'iOS 3.0 і вище',
		'iOS 3.1 і вище',
		'iOS 3.1.2 і вище',
		'iOS 3.1.3 і вище',
		'iOS 3.2 і вище',
		'iOS 4.0 і вище',
		'iOS 4.1 і вище',
		'iOS 4.2 і вище',
		'iOS 4.3 і вище',
		'iOS 5.0 і вище',
		'iOS 5.1 і вище',
		'Інші'
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
		'українська',
		'англійська',
		'японский',
		'китайский',
		'німецька',
		'французька',
		'іспанська',
		'російська',
		'інша'
	],

	apple_ios_lang_abr: [
		'',
		'UKR',
		'ENG',
		'JAP',
		'CHI',
		'GER',
		'FRA',
		'ESP',
		'RUS',
		''
	],

	apple_ios_dev: [
		'',
		'iPhone, iPod Touch, iPad (усі покоління)',
		'iPad (усі покоління)',
		'SD версія для iPhone, iPod Touch + HD версія для iPad',
		'iPhone 3Gs, 4, 4s; iPod Touch 3-го та 4-го покоління; iPad (усі покоління)',
		'iPhone 4s, iPad 2, iPad new',
		'Інші'
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
		'різні (теми)',
		'інший'
	],

	apple_ios_format_abr: [
		'',
		'',
		'',
		'',
		'Рінгтони',
		'',
		'',
		'Шпалери',
		'Шпалери',
		'Шпалери',
		'Теми',
		''
	],

	//фильмы, сериалы.
	video_codec_serials: [
		'&raquo; Відеокодек',
		'DivX',
		'XviD',
		'H.264',
		'MPEG2',
		'Інший'
	],

	audio_codec_serials: [
		'&raquo; Аудіокодек',
		'MP3',
		'AC3',
		'Інший'
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
		'&raquo; Кількість',
		'моделей',
		'сцен',
		'текстур',
		'HDRI',
		'матеріалів',
		'Інше'
	],

	video_quality_vlesson: [
		'&raquo; Якість',
		'DVDRip',
		'DVD5',
		'DVD9',
		'DVD5 (стиснутий)',
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
		'&raquo; Відеокодек',
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
		'&raquo; Аудіокодек',
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
		'Любительcкий (одноголосий)',
		'Любительский (двоголосий)',
		'Авторський (одноголосий)',
		'Професійний (одноголосий)',
		'Професійний (двоголосий)',
		'Професійний (багатоголосий, закадровий)',
		'Професійний (дубляж)',
		'Субтитри',
		'Не потрібен',
		'Відсутній'
	],

	chapters_music_dvd: [
		'&raquo; Розділи (розбиття за треками)',
		'є',
		'немає'
	],

	video_quality_music_dvd: [
		'&raquo; Якість',
		'DVD5',
		'DVD9',
		'DVD5 (стиснутий)',
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
		'&raquo; Відеокодек',
		'MPEG2'
	],

	audio_codec_music_dvd: [
		'&raquo; Аудіокодек',
		'AC3',
		'PCM',
		'DTS',
		'MP2'
	],

	audio_codec_mus_loss: [
		'&raquo; Аудіокодек',
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
		'є',
		'немає',
		'Digital Booklet'
	],

	source_mus_loss: [
		'',
		'власний ріп',
		'завантажено з',
		'релізер',
		'інше'
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
		'інше'
	],

	source_digit_mus: [
		'',
		'автором роздачі',
		'третьою особою',
		'інше'
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
		'Виконавець (группа)',
		'Збірка композицій різних виконавців'
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
		'&raquo; Тип ріпу',
		'tracks',
		'image+.cue',
		'image'
	],

	bitrate_mus_lossy: [
		'&raquo; Бітрейт аудіо',
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
		'так',
		'ні'
	],

	tag_mus_lossy: [
		'',
		'так',
		'ні'
	],

	//программы - тестовые диски
	rip_type_test: [
		'&raquo; Тип ріпу',
		'tracks',
		'image + .cue',
		'tracks + .cue',
		'DVD-Rip',
		'DVD',
		'BluRay-Rip',
		'BluRay'
	],

	audio_codec_test: [
		'&raquo; Аудіокодек',
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
		'&raquo; Відеокодек',
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
		'Інший'
	],

	arch_linux: [
		'',
		'x86',
		'amd64',
		'x86, amd64',
		'Інша'
	],

	lang_game_dvd_pleer: [
		'',
		'українська',
		'англійська',
		'японська',
		'російська',
		'multi'
	],

	lang_game_dvd_pleer_abr: [
		'',
		'UKR',
		'ENG',
		'JAP',
		'RUS',
		'multi'
	],

	audio_codec_film: [
		'» Вкажіть кодек аудіо',
		'MP3',
		'AC3',
		'DTS',
		'AAC',
		'FLAC',
		'OGG'
	],

	video_quality_serials: [
		'» Якість',
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
		'Інше'
	],

	loss_bit: [
		'',
		'lossless'
	],

	type_homebrewe: [
		'',
		'Прошивка',
		'Homebrew-програма',
		'PC-програма',
		'CTF-тема',
		'PTF-тема',
		'Емулятор',
		'Hombrew-гра',
		'Доповнення або посібник до гри',
		'Flash-програма або гра',
		'Карта місцевості',
		'Збереження гри',
		'Шпалери',
		'Інше'
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
		'Ігровий автомат',
		'MAME',
		'Atari 2600',
		'Atari 5200',
		'PC Engine',
		'Neo-Geo',
		'Інша'
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
		'» Мова',
		'українська',
		'англійська',
		'німецька',
		'французька',
		'китайська',
		'російська',
		'інша'
	],

	sub_all_anime_abr: [
		'',
		'SUB',
		'',
		'',
		'',
		'',
		'',
		''
	],

	sub_all_anime_2: [
		'» Мова',
		'українська',
		'англійська',
		'німецька',
		'французька',
		'китайська',
		'російська',
		'інша'
	],

	sub_all_anime_2_abr: [
		'',
		'SUB',
		'',
		'',
		'',
		'',
		'',
		''
	],

	sub_all_anime_3: [
		'» Мова',
		'українська',
		'англійська',
		'німецька',
		'французька',
		'китайська',
		'російська',
		'інша'
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
		'&raquo; Переклад 1',
		'дубляж',
		'професійний багатоголосий закадровий',
		'професійний двоголосий закадровий',
		'професійний одноголосий закадровий',
		'любительский двоголосий закадровий',
		'любительский одноголосий закадровий (автор)',
		'авторський одноголосий закадровий (автор)',
		'не потрібен',
		'відсутній',
		'інший'
	],

	transl_lat_setial_1: [
		'&raquo; Переклад 2',
		'дубляж',
		'професійний багатоголосий закадровий',
		'професійний двоголосий закадровий',
		'професійний одноголосий закадровий',
		'любительский двоголосий закадровий',
		'любительский одноголосий закадровий (автор)',
		'авторський одноголосий закадровий (автор)',
		'не потрібен',
		'інший'
	],

	transl_lat_setial_2: [
		'&raquo; Переклад 3',
		'дубляж',
		'професійний багатоголосий закадровий',
		'професійний двоголосий закадровий',
		'професійний одноголосий закадровий',
		'любительский двоголосий закадровий',
		'любительский одноголосий закадровий (автор)',
		'авторський одноголосий закадровий (автор)',
		'не потрібен',
		'інший'
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
		'Інший'
	],

	game_lang_nds: [
		'&raquo; Мова',
		'UKR',
		'JAP',
		'ENG',
		'RUS',
		'Multi5'
	],

	lang_comp_vlesson: [
		'',
		'Українська',
		'Англійська',
		'Німецька',
		'Російська',
		'Інша'
	],

	lang_comp_vlesson_abr: [
		'',
		'UKR',
		'ENG',
		'DEU',
		'RUS',
		''
	],

	type_comp_vlesson: [
		'',
		'Відеоурок',
		'Мультимедійний диск',
		'Інтерактивний диск',
		'Відеокліпи'
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
		'Українська',
		'Англійська',
		'Німецька',
		'Іспанська',
		'Китайська',
		'Російська',
		'Інша'
	],

	lang_notes_abr: [
		'',
		'UKR',
		'ENG',
		'DEU',
		'ITA',
		'CHN',
		'RUS',
		''
	],

	licence_old_game: [
		'',
		'так',
		'ні'
	],

	lang_video_les: [
		'',
		'Українська',
		'Українська + англійська',
		'Англійська',
		'Німецька',
		'Російська',
		'Інша'
	],

	lang_video_les_abr: [
		'',
		'UKR',
		'UKR/ENG',
		'ENG',
		'DEU',
		'RUS',
		''
	],

	type_vlesson: [
		'',
		'Відеоурок',
		'Мультимедійний диск',
		'Інший'
	],

	type_vlesson_abr: [
		'',
		'Відеоурок',
		'ММ',
		''
	],

	type_game: [
		'',
		'Patch',
		'Maps',
		'Mods',
		'UKR',
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
		'українська',
		'українська + англійська',
		'англійська',
		'німецька',
		'російська',
		'інша'
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
		'Інший MPEG4',
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
		'Піратка',
		'Ліцензія',
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
		'&raquo; Можливість запуску на Xbox 360',
		'Є',
		'Немає',
		'Не знаю, перевірте, будь ласка, самі й напишіть у темі'
	],

	launch_pc: [
		'&raquo; Можливість запуску на PC',
		'Немає',
		'Шукайте порт цієї гри в розділі *Ігри для PC*'
	],

	video_codec_3d: [
		'&raquo; Відеокодек',
		'Divx',
		'xVid',
		'Mpeg2',
		'x264',
		'h.264',
		'MVC'
	],

	audio_codec_3d: [
		'&raquo; Аудіокодек',
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
		'&raquo; Якість',
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
		'&raquo; Якість',
		'720p',
		'1080p',
		'1080i',
		'(Custom)',
		'(стиснутий)'
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
		'Анагліф red-cyan',
		'Анагліф green-magenta',
		'Анагліф yellow-blue',
		'Черезрядковий / Interlace',
		'OverUnder / Вертикальна стереопара',
		'Half OverUnder / Вертикальна анаморфна стереопара',
		'SideBySide / Горизонтальна стереопара',
		'Half SideBySide / Горизонтальна анаморфна стереопара',
		'SeparateFiles / Розділена стереопара'
	],

	format_3d_abr: [
		'',
		'BD3D',
		'Anaglyph / Анагліф',
		'Anaglyph / Анагліф',
		'Anaglyph / Анагліф',
		'Interlaced / Інтерлейс',
		'OverUnder / Вертикальна стереопара',
		'Half OverUnder / Вертикальна анаморфна стереопара',
		'SideBySide / Горизонтальна стереопара',
		'Half SideBySide / Горизонтальна анаморфна стереопара',
		'SeparateFiles / Розділена стереопара'
	],

	angle_3d: [
		'&raquo;  Порядок ракурсів',
		'лівий ракурс перший',
		'правий ракурс перший'
	],

	update_game: [
		'',
		'Так',
		'Ні'
	],

	audio_codec_anime_loss: [
		'&raquo; Аудіокодек',
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
		'&raquo; Мова',
		'Українська',
		'Японська',
		'Англійська',
		'Корейська',
		'Китайська',
		'Іспанська',
		'Італійська',
		'Німецька',
		'Російська',
		'Інша'
	],

	lang_anime_transl_abr: [
		'',
		'UKR',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		'RUS',
		''
	],

	lang_anime_transl_2: [
		'&raquo; Мова',
		'Українська',
		'Японська',
		'Англійська',
		'Корейська',
		'Китайська',
		'Іспанська',
		'Італійська',
		'Німецька',
		'Російська',
		'Інша'
	],

	lang_anime_transl_2_abr: [
		'',
		'UKR',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		'RUS',
		''
	],

	lang_anime_transl_3: [
		'&raquo; Мова',
		'Українська',
		'Японська',
		'Англійська',
		'Корейська',
		'Китайська',
		'Іспанська',
		'Італійська',
		'Німецька',
		'Російська',
		'Інша'
	],

	lang_anime_transl_3_abr: [
		'',
		'UKR',
		'JAP',
		'ENG',
		'KOR',
		'CHI',
		'ESP',
		'ITA',
		'GER',
		'RUS',
		''
	],

	country_anime: [
		'',
		'Японія',
		'Японія/США',
		'Корея',
		'Китай',
		'Інша'
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
	'ukr_sub_abr',
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
	var s = '<select><option value="">&raquo;&raquo; Елементи форми/Назви &nbsp;</option>';
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
	var s = '<select><option value="">&raquo;&raquo; Інші елементи &nbsp;</option>';
	s += '<option value="`текст...`">Текст...</option>';
	s += '<option value="`BR`">Новий рядок</option>';
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
	$('#'+res_id).html('<i class="loading-1">завантаження...</i>');
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
				if (!window.confirm('Відключити шаблони у цьому форумі?')) {
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
			if (!window.confirm('Зберегти зміни для шаблону "'+ $('#tpl-name-old-save').text() +'"?')) {
				return false;
			}
			$('#tpl-load-resp').html('<i class="loading-1">збереження...</i>');
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
			$('#tpl-new-resp').html('<i class="loading-1">збереження...</i>');
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
			$('#tpl-load-resp').html('Зміни успішно збережено');
			break;

		case 'new':
			$('#tpl-new-resp').html('Шаблон успішно створено (у списку він з\'явиться після перезавантаження сторінки).');
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
			var v = TPL.el_titles[id] || 'значення не знайдено';
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
		<!-- IF CAN_EDIT_TPL and not EDIT_TPL --><a href="{EDIT_TPL_URL}" class="adm">Редагувати шаблон</a> &nbsp;&middot;&nbsp;<!-- ENDIF -->
		<a href="{REGULAR_TOPIC_HREF}">Створити звичайну тему</a>
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
	<th>Правила оформлення</th>
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
	<th colspan="2">Створення шаблону для релізу</th>
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
			<a class="med" href="#" onclick="$('#tpl-row-src').val(''); return false;">очистити</a> &nbsp;&middot;&nbsp;
			<a class="med" href="#" onclick="$('#tpl-src-form').val( $('#tpl-src-form').val() +'\n<-'+ $('#tpl-row-src').val() +' ->' ).focus(); return false;">додати до форми</a> &nbsp;&middot;&nbsp;
			<a class="med" href="#" onclick="$('#tpl-row-src').trigger('keypress', [13]).focus(); return false;" title="Нажать Enter">оновити результат (enter)</a>
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
				<div class="floatR"><a class="adm bold" href="#" onclick="$('#rel-preview, #rel-construct').toggle(); $('#tpl-src-form').focus(); return false;">[ Конструктор/Елементи ]</a> <a href="#tpl-help-preview" class="menu-root menu-alt1">[?]</a></div>
				<div class="clear"></div>
			</div>
			<textarea id="tpl-src-form" rows="16" cols="10" wrap="off" style="width: 100%"></textarea>
			<div class="med">назва: <a href="#tpl-help-title" class="menu-root menu-alt1">[?]</a></div>
			<textarea id="tpl-src-title" rows="2" cols="10" style="width: 100%"></textarea>
		</div>
		<div style="padding-top: 2px;">
			<div class="floatL" style="padding-top: 2px;">
				<a id="toggle-info-a" class="adm bold" href="#" onclick="ajax.topic_tpl('toggle_info'); return false;">[ Інфо/Змінити ]</a> &nbsp;
				<a id="toggle-new-a" class="adm bold" href="#" onclick="ajax.topic_tpl('toggle_new'); return false;">[ Створити новий ]</a> &nbsp;
				<a class="adm bold" href="#" onclick="ajax.topic_tpl('assign', {tpl_id: -1}); return false;">[ Відключити ]</a> &nbsp;
				<a href="#tpl-help-ctl" class="menu-root menu-alt1">[?]</a>
			</div>
			<div class="floatR">
				<input id="tpl-build-msg-btn" type="button" value="Створити повідомлення" onclick="tpl_build_msg(false);" style="display: none;" />&nbsp;
				<input id="tpl-build-form-btn" type="button" value="Створити форму" onclick="tpl_build_form();" />&nbsp;
			</div>
		</div>
		<div class="clear"></div>

		<div id="tpl-info-block" style="display: none;" class="tpl-adm-block med row3">
			<br />
			<fieldset>
			<legend>Увімкнути/Завантажити</legend>
			<div style="padding: 2px 12px 6px;">
			Шаблони: &nbsp;
			<!-- IF TPL_SELECT -->{TPL_SELECT} &nbsp;
			<input type="button" value="Увімкнути у цьому форумі" class="bold" onclick="ajax.topic_tpl('assign', {tpl_id: $('#forum_tpl_select').val()})" /> &nbsp;
			<input type="button" value="Завантажити" onclick="ajax.topic_tpl('load')" /> &nbsp;
			<!-- ELSE -->Немає шаблонів для релізів<!-- ENDIF -->
			<br /><br />
			<span class="gen">
			<!-- IF NO_TPL_ASSIGNED -->
			У цьому форумі шаблони <b>вимкнено</b><br />
			<!-- ELSE -->
			Зараз у цьому форумі увімкнено шаблон <b>{TPL_NAME}</b><br />
			<!-- ENDIF -->
			</span>
			</div>
			</fieldset>
			<br />

			<div <!-- IF NO_TPL_ASSIGNED -->style="display: none;"<!-- ENDIF --> id="tpl-save-block">
			<fieldset>
			<legend>Зберегти зміни для шаблону <b id="tpl-name-old-save">{TPL_NAME}</b></legend>
			<div style="padding: 2px 12px 6px;">
			<div class="label">Нова назва шаблону:</div>
			<input type="text" id="tpl-name-save" size="60" value="{TPL_NAME}" maxlength="60" class="bold" style="width: 75%" /><br />

			<div class="label"><a href="{POST_URL}{TPL_RULES_POST_ID}#{TPL_RULES_POST_ID}" id="tpl-rules-link" target="_blank">Правила</a> (посилання на повідомлення із правилами або номер повідомлення):</div>
			<input type="text" id="tpl-rules-save" size="60" value="{TPL_RULES_POST_ID}" style="width: 75%" /><br />

			<div class="label">Коментар:</div>
			<textarea id="tpl-comment-save" rows="2" cols="80" class="editor" style="width: 90%">{TPL_COMMENT}</textarea>

			<div class="label">Востаннє редаговано: <i id="tpl-last-edit-time">{TPL_LAST_EDIT_TIME}</i> користувачем <b id="tpl-last-edit-by">{TPL_LAST_EDIT_USER}</b></div>
			<br />

			<input type="hidden" id="tpl-id-save" value="{TPL_ID}">
			<input type="hidden" id="tpl-last-edit-tst" value="{TPL_LAST_EDIT_TIMESTAMP}">
			<input type="button" class="bold" value="Зберегти зміни" onclick="ajax.topic_tpl('save')" />
			<br />
			</div>
			</fieldset>
			<br />
			</div>
			<div id="tpl-load-resp"></div>
		</div>

		<div id="tpl-new-block" style="display: none;" class="tpl-adm-block med row3">
			<div class="label">Назва шаблону: *</div>
			<input type="text" id="tpl-name-new" size="60" value="" maxlength="60" class="bold" style="width: 75%" /><br />

			<div class="label">Правила (посилання на повідомлення з правилами чи номер повідомлення):</div>
			<input type="text" id="tpl-rules-new" size="60" value="" style="width: 75%" /><br />

			<div class="label">Коментар:</div>
			<textarea id="tpl-comment-new" rows="2" cols="10" class="editor" style="width: 100%"></textarea><br />

			<input type="button" class="bold" value="Створити новий шаблон" onclick="ajax.topic_tpl('new');" /><br /><br />
			<div id="tpl-new-resp"></div>
		</div>
	</div>
	</td>
	<td valign="top">
	<div style="width: 98%">
		<div>
			<p class="med">повідомлення: <a href="#tpl-help-msg" class="menu-root menu-alt1">[?]</a></p>
			<textarea id="tpl-src-msg" rows="20" cols="10" wrap="off" class="editor" style="width: 100%"></textarea>
			<div id="msg-attr-list" class="pad_4 med"></div>
		</div>
	</div>
	</td>
</tr>
</tbody>

<tbody id="preview-block" style="display: none;">
<tr><td colspan="2" class="row3">результат [ <a class="med" href="#" onclick="$('#preview-block').hide(); return false;">сховати</a> ]</td></tr>
<tr>
	<td colspan="2">
	<div style="width: 99%">
		<div><input type="text" id="preview-title" size="60" value="" class="bold" style="width: 100%" /></div>
		<div><textarea id="preview-msg" rows="15" cols="10" wrap="off" class="editor" style="width: 100%"></textarea></div>
		<div class="tCenter">
			<input type="button" value="Створити HTML" onclick="ajax.posts( $('#preview-msg').val(), 'preview-html-body' );" class="bold" />
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
	<div class="tRight med">[ <u class="clickable" onclick="$('#tpl-howto').toggle();">Інструкція</u> ]</div>
	<div id="tpl-howto" class="med pad_12" style="display: none;">
	Після заповнення поля <i>Форма</i> натисніть кнопку <i>Створити форму</i><br /><br />
	У полі <i>Повідомлення</i> додайте до елементів необхідні атрибути (req, spoiler тощо)<br /><br />
	Заповніть створену форму (вручну або автозаповненням)<br /><br />
	Кнопки <i>Продовжити</i> та <i>Створити повідомлення</i> створюють BB-код повідомлення<br /><br />
	Кнопка <i>Створити HTML</i> створює HTML повідомлення<br /><br />
	Заповніть поле <i>Назва</i>
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
	<th colspan="2">Заповніть форму для релізу<!-- IF EDIT_TPL --> &nbsp; [ <u class="clickable" onclick="tpl_fill_form();">Заповнити</u> ]<!-- ENDIF --></th>
</tr>
</thead>
<tbody id="rel-tpl">
</tbody>
<tfoot>
<tr>
	<td colspan="2" class="pad_8 tCenter bold">На наступній сторінці перевірте правильність оформлення і додайте torrent-файл</td>
</tr>
<tr>
	<td class="catBottom" colspan="2">
		<!-- IF EDIT_TPL -->
		<input type="button" value="Заповнити" style="width: 120px;" onclick="tpl_fill_form();" />&nbsp;&nbsp;
		<input type="button" value="Продовжити" class="bold" style="width: 150px;" onclick="tpl_build_msg(true);" />
		<!-- ELSE -->
		<input type="button" value="Продовжити" class="bold" style="width: 150px;" onclick="tpl_submit(true);" />
		<!-- ENDIF -->
	</td>
</tr>
</tfoot>
</table>
</div>

<!-- IF EDIT_TPL -->
<div id="tpl-help-form" class="menu-sub tpl-help-msg" style="width: 800px;">
	<h4>Скрипт для побудови форми</h4>
	<br />
	Формат: <b class="hlp-1">&lt;-</b><b>назва_рядка_форми</b> &nbsp; <b>елементи</b><b class="hlp-1">-&gt;</b>
	<br /><br />
	Кожен елемент задається у вигляді: <b class="hlp-1">ТИП</b>[<b>назва_елемента</b>,<b class="hlp-2">необов'язкові_параметри</b>]
	<br /><br />
	<b>INP</b> - однорядкове поле для вводу тексту, додатково можна вказати максимальну кількість символів та ширину поля<br />
	<b>INP[genre,200,70]</b> - можна ввести максимум 200 символів, ширина поля 70 символів (ширину більшою 80-ти робити не потрібно)
	<br /><br />
	<b>TXT</b> - багаторядкове поле для вводу тексту, додатково можна вказати висоту<br />
	<b>TXT[casting,10]</b> - висота поля буде 10 рядків
	<br /><br />
	<b>SEL</b> - випадний список із вибором<br />
	<b>SEL[video_quality]</b>
	<br /><br />
	<b>E</b> - статичний або прихований елемент (зазвичай не має назви і не є полем вводу тексту)<br />
	<b>E[load_pic_btn]</b> - кнопка завантаження зображення
	<br /><br />
	<b>T</b> - вставляє лише назву елемента<br />
	<b>T[ukr_sub]</b> - додає до форми назву елемента <b>Українські субтитри</b>
	<br /><br />
	<b class="hlp-2">`</b><b class="hlp-1">текст...</b><b class="hlp-2">`</b> - будь-який текст та спец. елементи типу `BR` (перехід на новий рядок)<br />
	<b class="hlp-2">`</b>українською<b class="hlp-2">`</b> - додає до форми текст <i>українською</i>
</div>

<div id="tpl-help-title" class="menu-sub tpl-help-msg" style="width: 800px;">
	<h4>Скрипт для побудови назви релізу</h4>
	<br />
	Формат: <b class="hlp-1">&lt;-</b><b>група елементів</b><b class="hlp-1">-&gt;</b><b class="hlp-2">розділювач для цієї групи</b>
	<br /><br />
	наприклад:<br />
	<p class="gen bold pad_8">
		<b class="hlp-1">&lt;-</b>title_ukr title_eng<b class="hlp-1">-&gt;</b><b class="hlp-2">/</b>
		<b class="hlp-1">&lt;-</b>director year<b class="hlp-1">-&gt;</b><b class="hlp-2">(,)</b>
		<b class="hlp-1">&lt;-</b>genre video_quality<b class="hlp-1">-&gt;</b><b class="hlp-2">[,]</b>
	</p>
	створить:<br />
	<p class="gen bold pad_8">
		Назва <b class="hlp-2">/</b> Оригінальна назва
		<b class="hlp-2">(</b>Режисер<b class="hlp-2">,</b> 2000 р.<b class="hlp-2">)</b>
		<b class="hlp-2">[</b>Жанр<b class="hlp-2">,</b> DVDRip<b class="hlp-2">]</b>
	</p>
</div>

<div id="tpl-help-msg" class="menu-sub tpl-help-msg" style="width: 600px;">
	<h4>Скрипт для побудови повідомлення</h4>
	<br />
	Формат: <b>назва_елемента</b>[<i>атрибут1,атрибут2</i>]
	<br /><br />
	При створенні форми (кнопка <i>Створити форму</i> і при побудові того, що бачить користувач)
	цей скрипт щоразу перевіряє на відповідність елементи форми. При цьому відсутні у формі елементи з нього видаляються,
	а написані у формі, але в ньому відсутні, додаються.
	<br /><br />
	Порядок елементів залежить від того, як вони прописані у формі.
	<br /><br />
	Опис атрибутів міститься у виринаючій підказці (наведіть мишею на будь-який атрибут у списку знизу)
</div>

<div id="tpl-help-preview" class="menu-sub tpl-help-msg" style="width: 400px;">
	<h4>Конструктор і попередній перегляд елементів</h4>
	<br />
	В IE частина функцій не працює!
	<br /><br />
	Підставляє в рядок конструктора поточний рядок із форми.<br />
	У конструкторі для оновления попереднього перегляду слід натиснути enter.<br />
	Приховані елементи виділені червоним кольором.<br />
</div>

<div id="tpl-help-ctl" class="menu-sub tpl-help-msg" style="width: 400px;">
	Кнопка <b class="adm">[ Інфо/Змінити ]</b> відкриває/закриває вікно опцій (так само працюють інші кнопки)
</div>
<!-- ENDIF -->

<div style="display: none;">
	<!-- TPL.el_id елементи, для E[el] в форму подставляется $(el).html() -->
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
<div id="load_pic_btn"><input type="button" style="width: 140px;" value="Завантажити зображення" onclick="window.open('http://fastpic.ru', '_blank'); return false;" /></div>
<!--/load_pic_btn-->

<!-- knopIKI (urls) -->

<!--load_pic_faq_url-->
<div id="load_pic_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=101116" target="_blank"><b>Як завантажити зображення на безкоштовний хостинг</b></a> </div>
<!--/load_pic_faq_url-->

<!--manga_type_faq_url-->
<div id="manga_type_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=2168864#types" target="_blank"><b>Детальніше про типи</b></a> </div>
<!--/manga_type_faq_url-->

<!--make_screenlist_faq_url-->
<div id="make_screenlist_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=48687" target="_blank"><b>Як зробити скріншот / скрінліст</b></a> </div>
<!--/make_screenlist_faq_url-->

<!--translation_rules_faq_url-->
<div id="translation_rules_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=33482343#33482343" target="_blank"><b>Правила позначення перекладів</b></a> </div>
<!--/translation_rules_faq_url-->

<!--make_sample_faq_url-->
<div id="make_sample_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=8415" target="_blank"><b>Як зробити семпл відео</b></a> </div>
<!--/make_sample_faq_url-->

<!--dvd_reqs_faq_url-->
<div id="dvd_reqs_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=27157356#descr" target="_blank"><b>Вимоги і приклади для DVD</b></a> </div>
<!--/dvd_reqs_faq_url-->

<!--hd_reqs_faq_url-->
<div id="hd_reqs_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=2277258#53" target="_blank"><b>Вимоги і приклади для HD</b></a> </div>
<!--/hd_reqs_faq_url-->

<!--videofile_info_faq_url-->
<div id="videofile_info_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=48686" target="_blank"><b>Як отримати інформацію про відеофайл</b></a> </div>
<!--/videofile_info_faq_url-->

<!--bdinfo_faq_url-->
<div id="bdinfo_faq_url"> <a href="http://www.cinemasquid.com/blu-ray/tools/bdinfo" target="_blank"><b>BDInfo</b></a></div>
<!--/bdinfo_faq_url-->

<!--dvdinfo_faq_url-->
<div id="dvdinfo_faq_url"> <a href="http://www.cinemasquid.com/blu-ray/tools/dvdinfo" target="_blank"><b>DVDInfo</b></a></div>
<!--/dvdinfo_faq_url-->

<!--make_poster_faq_url-->
<div id="make_poster_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=1381634" target="_blank"><b>Інструкція із виготовлення постера</b></a> </div>
<!--/make_poster_faq_url-->

<!--pred_alt1_faq_url-->
<div id="pred_alt1_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=648002#8" target="_blank"><b>Про посилання на попередні та альтернативні роздачі</b></a> </div>
<!--/pred_alt1_faq_url-->

<!--quality_decl_faq_url-->
<div id="quality_decl_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=27840514#27840514" target="_blank"><b>про позначення якості</b></a> </div>
<!--/quality_decl_faq_url-->

<!--pred_alt2_faq_url-->
<div id="pred_alt2_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=4960586#4960586" target="_blank"><b>Про посилання на попередні та альтернативні роздачі</b></a> </div>
<!--/pred_alt2_faq_url-->

<!--pred_alt3_faq_url-->
<div id="pred_alt3_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?p=6734641#6734641" target="_blank"><b>Про посилання на попередні та альтернативні роздачі</b></a> </div>
<!--/pred_alt3_faq_url-->

<!--pred_alt4_faq_url-->
<div id="pred_alt4_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=460883#8" target="_blank"><b>Про посилання на попередні та альтернативні роздачі</b></a> </div>
<!--/pred_alt4_faq_url-->

<!--dvdinfo_faq_url-->
<div id="dvdinfo_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=101263" target="_blank"><b>Як отримати інформацію про DVD-Video</b></a> </div>
<!--/dvdinfo_faq_url-->

<!--tyt_faq_url-->
<div id="tyt_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=2135853" target="_blank"><b>тут</b></a> </div>
<!--/tyt_faq_url-->

<!--wtf_faq_url-->
<div id="wtf_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=488848#other" target="_blank"><b>Що це означає?</b></a> </div>
<!--/wtf_faq_url-->

<!--faq_catalog-->
<div id="faq_catalog"> <a href="http://rutracker.org/forum/viewtopic.php?t=1123827#3" target="_blank"><b>інструкція</b></a> </div>
<!--/faq_catalog-->

<!--faq_pops-->
<div id="faq_pops"> <a href="http://rutracker.org/forum/viewtopic.php?t=1077368" target="_blank"><b>Що таке Popsloader?</b></a> </div>
<!--/faq_pops-->

<!--faq_code-->
<div id="faq_code"> <a href="http://rutracker.org/forum/viewtopic.php?p=24015560#24015560" target="_blank"><b>Як дізнатися код диcка?</b></a> </div>
<!--/faq_code-->

<!--faq_code_PS-->
<div id="faq_code_PS"> <a href="http://rutracker.org/forum/viewtopic.php?p=40704481#40704481" target="_blank"><b>Як дізнатися код диcка?</b></a> </div>
<!--/faq_code_PS-->

<!--faq_pegi-->
<div id="faq_pegi"> <a href="http://www.pegi.info/" target="_blank"><b>PEGI?</b></a> </div>
<!--/faq_pegi-->

<!--faq_screen_psp-->
<div id="faq_screen_psp"> <a href="http://rutracker.org/forum/viewtopic.php?t=457909" target="_blank"><b>Як зробити скріншоти з PSP</b></a> </div>
<!--/faq_screen_psp-->

<!--dvdinfo_faq_ur_2l-->
<div id="dvdinfo_faq_url_2"> <a href="http://www.cinemasquid.com/blu-ray/tools/dvdinfo" target="_blank"><b>Як отримати інформацію про DVD-Video файл</b></a></div>
<!--/dvdinfo_faq_url_2-->

<!--quality_faq-->
<div id="quality_faq"> <a href="http://rutracker.org/forum/viewtopic.php?t=2198792" target="_blank"><b>Позначення якості відео</b></a></div>
<!--/quality_faq-->

<!--comparison_anime-->
<div id="comparison_anime"> <a href="http://rutracker.org/forum/viewtopic.php?t=1907922#4" target="_blank"><b>Порівняння з іншими роздачами.</b></a></div>
<!--/comparison_anime-->

<!--file_list-->
<div id="file_list"> <a href="http://rutracker.org/forum/viewtopic.php?p=21307338#21307338" target="_blank"><b>Як створити список файлів?</b></a></div>
<!--/file_list-->

<!--faq_traclist-->
<div id="faq_traclist"> <a href="http://rutracker.org/forum/viewtopic.php?t=2525182" target="_blank"><b>Як швидко створити трекліст із зазначенням бітрейту?</b></a></div>
<!--/faq_traclist-->

<!--faq_isbn-->
<div id="faq_isbn"> <a href="http://rutracker.org/forum/viewtopic.php?t=2083213" target="_blank"><b>Що таке ISBN/ISSN?</b></a> </div>
<!--/faq_isbn-->

<!--faq_scrn_books-->
<div id="faq_scrn_books"> <a href="http://rutracker.org/forum/viewtopic.php?t=1566885" target="_blank"><b>Як зробити приклади сторінок (скріншоти) для роздачі?</b></a> </div>
<!--/faq_scrn_books-->

<!--faq_ps_image-->
<div id="faq_ps_image"> <a href="http://rutracker.org/forum/viewtopic.php?t=3893250" target="_blank"><b>FAQ зі зняття образу для Ps1</b></a> </div>
<!--/faq_ps_image-->

<!--faq_mac_scrn-->
<div id="faq_mac_scrn"> <a href="http://rutracker.org/forum/viewtopic.php?t=1749166" target="_blank"><b>Створення скріншотів у Mac OS</b></a> </div>
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
<input type="hidden" id="series" value="Серії:">
<!--/series-->

<!--series_of-->
<input type="hidden" id="series_of" value="із">
<!--/series_of-->

<!--season-->
<input type="hidden" id="season" value="Сезон:">
<!--/season-->

<!--point-->
<input type="hidden" id="point" value=",">
<!--/point-->

<!--d_ukr-->
<input type="hidden" id="d_ukr" value="у 3Д /">
<!--/d_ukr-->

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
<div id="genre_faq_url"> <a href="http://rutracker.org/forum/viewtopic.php?t=2090617" target="_blank"><b>Як визначити жанр?</b></a> </div>
<!--/genre_faq_url-->

<!--faq_game-->
<div id="faq_game"> <a href="http://rutracker.org/forum/viewtopic.php?t=2706502" target="_blank"><b>Попередній перегляд</b></a> </div>
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

<noscript><div class="warningBox2 bold tCenter">Для відображення шаблонів слід увімкнути JavaScript</div></noscript>
