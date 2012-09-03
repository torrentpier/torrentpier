// BBCode control. (based on bbcode.js from http://forum.dklab.ru)
function BBCode(obj)
{
	textarea = document.getElementById(obj);
	this.construct(textarea);
}
BBCode.prototype = {
	VK_TAB:		 9,
	VK_ENTER:	 13,
	VK_PAGE_UP: 33,
	BRK_OP:		 '[',
  BRK_CL:     ']',
  textarea:   null,
  stext:      '',
  quoter:     null,
  qouted_pid: null,
  collapseAfterInsert: false,
  replaceOnInsert: false,

  // Create new BBCode control.
  construct: function(textarea) {
    this.textarea = textarea
    this.tags     = new Object();
    // Tag for quoting.
    this.addTag(
      '_quoter',
      function() { return '[quote="'+th.quoter+'"][qpost='+th.qouted_pid+']' },
      '[/quote]\n',
      null,
      null,
      function() { th.collapseAfterInsert=true; return th._prepareMultiline(th.quoterText) }
    );

    // Init events.
    var th = this;
    addEvent(textarea, 'keydown',   function(e) { return th.onKeyPress(e, window.HTMLElement? 'down' : 'press') });
    addEvent(textarea, 'keypress',  function(e) { return th.onKeyPress(e, 'press') });
  },

  // Insert poster name or poster quotes to the text.
  onclickPoster: function(name, post_id) {
    var sel = this.getSelection()[0];
		if (sel) {
			this.quoter = name;
			this.qouted_pid = post_id;
			this.quoterText = sel;
			this.insertTag('_quoter');
		} else {
			this.insertAtCursor("[b]" + name + '[/b], ');
		}
		return false;
	},

	// Quote selected text
	onclickQuoteSel: function() {
		var sel = this.getSelection()[0];
		if (sel) {
			this.insertAtCursor('[quote]' + sel + '[/quote]\n');
		}
		else {
			alert('Вы не выбрали текст');
		}
		return false;
	},

	// Quote selected text
	emoticon: function(em) {
		if (em) {
			this.insertAtCursor(' ' + em + ' ');
		}
		else {
			return false;
		}
		return false;
	},

	// For stupid Opera - save selection before mouseover the button.
	refreshSelection: function(get) {
    if (get) this.stext = this.getSelection()[0];
    else this.stext = '';
  },

  // Return current selection and range (if exists).
  // In Opera, this function must be called periodically (on mouse over,
  // for example), because on click stupid Opera breaks up the selection.
  getSelection: function() {
    var w = window;
    var text='', range;
    if (w.getSelection) {
      // Opera & Mozilla?
      text = w.getSelection();
    } else if (w.document.getSelection) {
      // the Navigator 4.0x code
      text = w.document.getSelection();
    } else if (w.document.selection && w.document.selection.createRange) {
      // the Internet Explorer 4.0x code
      range = w.document.selection.createRange();
      text = range.text;
    } else {
      return [null, null];
    }
    if (text == '') text = this.stext;
    text = ""+text;
    text = text.replace("/^\s+|\s+$/g", "");
    return [text, range];
  },

  // Insert string at cursor position of textarea.
  insertAtCursor: function(text) {
    // Focus is placed to textarea.
    var t = this.textarea;
    t.focus();
    // Insert the string.
    if (document.selection && document.selection.createRange) {
      var r = document.selection.createRange();
      if (!this.replaceOnInsert) r.collapse();
      r.text = text;
    } else if (t.setSelectionRange) {
      var start = this.replaceOnInsert? t.selectionStart : t.selectionEnd;
      var end   = t.selectionEnd;
      var sel1  = t.value.substr(0, start);
      var sel2  = t.value.substr(end);
      t.value   = sel1 + text + sel2;
      t.setSelectionRange(start+text.length, start+text.length);
    } else{
      t.value += text;
    }
    // For IE.
    setTimeout(function() { t.focus() }, 100);
  },

  // Surround piece of textarea text with tags.
  surround: function(open, close, fTrans) {
    var t = this.textarea;
    t.focus();
    if (!fTrans) fTrans = function(t) { return t; };

    var rt    = this.getSelection();
    var text  = rt[0];
    var range = rt[1];
    if (text == null) return false;

    var notEmpty = text != null && text != '';

    // Surround.
    if (range) {
      var notEmpty = text != null && text != '';
      var newText = open + fTrans(text) + (close? close : '');
      range.text = newText;
      range.collapse();
      if (text != '') {
        // Correction for stupid IE: \r for moveStart is 0 character.
        var delta = 0;
        for (var i=0; i<newText.length; i++) if (newText.charAt(i)=='\r') delta++;
        range.moveStart("character", -close.length-text.length-open.length+delta);
        range.moveEnd("character", -0);
      } else {
        range.moveEnd("character", -close.length);
      }
      if (!this.collapseAfterInsert) range.select();
    } else if (t.setSelectionRange) {
      var start = t.selectionStart;
      var end   = t.selectionEnd;
      var top   = t.scrollTop;
      var sel1  = t.value.substr(0, start);
      var sel2  = t.value.substr(end);
      var sel   = fTrans(t.value.substr(start, end-start));
      var inner = open + sel + close;
      t.value   = sel1 + inner + sel2;
      if (sel != '') {
        t.setSelectionRange(start, start+inner.length);
        notEmpty = true;
      } else {
        t.setSelectionRange(start+open.length, start+open.length);
        notEmpty = false;
      }
      t.scrollTop = top;
      if (this.collapseAfterInsert) t.setSelectionRange(start+inner.length, start+inner.length);
    } else {
      t.value += open + text + close;
    }
    this.collapseAfterInsert = false;
    return notEmpty;
  },

  // Internal function for cross-browser event cancellation.
  _cancelEvent: function(e) {
    if (e.preventDefault) e.preventDefault();
    if (e.stopPropagation) e.stopPropagation();
    return e.returnValue = false;
  },

  // Available key combinations and these interpretaions for phpBB are
  //     TAB              - Insert TAB char
  //     CTRL-TAB         - Next form field (usual TAB)
  //     SHIFT-ALT-PAGEUP - Add an Attachment
  //     ALT-ENTER        - Preview
  //     CTRL-ENTER       - Submit
  // The values of virtual codes of keys passed through event.keyCode are
  // Rumata, http://forum.dklab.ru/about/todo/BistrieKlavishiDlyaOtpravkiForm.html
  onKeyPress: function(e, type) {
    // Try to match all the hot keys.
    var key = String.fromCharCode(e.keyCode? e.keyCode : e.charCode);
    for (var id in this.tags) {
      var tag = this.tags[id];
      // Pressed control key?..
      if (tag.ctrlKey && !e[tag.ctrlKey+"Key"]) continue;
      // Pressed needed key?
      if (!tag.key || key.toUpperCase() != tag.key.toUpperCase()) continue;
      // OK. Insert.
      if (e.type == "keydown") this.insertTag(id);
      // Reset event.
      return this._cancelEvent(e);
    }

    // Tab.
    if (type == 'press' && e.keyCode == this.VK_TAB && !e.shiftKey && !e.ctrlKey && !e.altKey) {
    //this.surround("\t", "");
      this.insertAtCursor('[tab]');
      return this._cancelEvent(e);
    }

    // Ctrl+Tab.
    if (e.keyCode == this.VK_TAB && !e.shiftKey && e.ctrlKey && !e.altKey) {
      this.textarea.form.post.focus();
      return this._cancelEvent(e);
    }

    // Hot keys (PHPbb-specific!!!).
    var form = this.textarea.form;
    var submitter = null;
    if (e.keyCode == this.VK_PAGE_UP &&  e.shiftKey && !e.ctrlKey &&  e.altKey)
      submitter = form.add_attachment_box;
    if (e.keyCode == this.VK_ENTER   &&!e.shiftKey && !e.ctrlKey &&  e.altKey)
      submitter = form.preview;
    if (e.keyCode == this.VK_ENTER   && !e.shiftKey &&  e.ctrlKey && !e.altKey)
      submitter = form.post;
    if (submitter) {
      submitter.click();
      return this._cancelEvent(e);
    }

    return true;
  },

  // Adds a BB tag to the list.
  addTag: function(id, open, close, key, ctrlKey, multiline) {
    if (!ctrlKey) ctrlKey = "ctrl";
    var tag = new Object();
    tag.id        = id;
    tag.open      = open;
    tag.close     = close;
    tag.key       = key;
    tag.ctrlKey   = ctrlKey;
    tag.multiline = multiline;
    tag.elt       = this.textarea.form[id]
    this.tags[id] = tag;
    // Setup events.
    var elt = tag.elt;
    if (elt) {
      var th = this;
      if (elt.type && elt.type.toUpperCase()=="BUTTON") {
        addEvent(elt, 'click', function() { th.insertTag(id); return false; });
      }
      if (elt.tagName && elt.tagName.toUpperCase()=="SELECT") {
        addEvent(elt, 'change', function() { th.insertTag(id); return false; });
      }
    } else {
      if (id && id.indexOf('_') != 0) return alert("addTag('"+id+"'): no such element in the form");
    }
  },

  // Inserts the tag with specified ID.
  insertTag: function(id) {
    // Find tag.
    var tag = this.tags[id];
    if (!tag) return alert("Unknown tag ID: "+id);

    // Open tag is generated by callback?
    var op = tag.open;
    if (typeof(tag.open) == "function") op = tag.open(tag.elt);
    var cl = tag.close!=null? tag.close : "/"+op;

    // Use "[" if needed.
    if (op.charAt(0) != this.BRK_OP) op = this.BRK_OP+op+this.BRK_CL;
    if (cl && cl.charAt(0) != this.BRK_OP) cl = this.BRK_OP+cl+this.BRK_CL;

    this.surround(op, cl, !tag.multiline? null : tag.multiline===true? this._prepareMultiline : tag.multiline);
  },

  _prepareMultiline: function(text) {
    text = text.replace(/\s+$/, '');
    text = text.replace(/^([ \t]*\r?\n)+/, '');
    if (text.indexOf("\n") >= 0) text = "\n" + text + "\n";
    return text;
  }

}

// Emulation of innerText for Mozilla.
if (window.HTMLElement && window.HTMLElement.prototype.__defineSetter__) {
  HTMLElement.prototype.__defineSetter__("innerText", function (sText) {
     this.innerHTML = sText.replace(/\&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
  });
  HTMLElement.prototype.__defineGetter__("innerText", function () {
     var r = this.ownerDocument.createRange();
     r.selectNodeContents(this);
     return r.toString();
  });
}

function AddSelectedText(BBOpen, BBClose) {
 if (document.post.message.caretPos) document.post.message.caretPos.text = BBOpen + document.post.message.caretPos.text + BBClose;
 else document.post.message.value += BBOpen + BBClose;
 document.post.message.focus()
}

function InsertBBCode(BBcode)
{
	AddSelectedText('[' + BBcode + ']','[/' + BBcode + ']');
}

function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}

// Translit START

// One character letters
var t_table1 = "ABVGDEZIJKLMNOPRSTUFXHCYWabvgdezijklmnoprstufxhcyw'#";
var w_table1 = "АБВГДЕЗИЙКЛМНОПРСТУФХХЦЫЩабвгдезийклмнопрстуфххцыщьъ";

// Two character letters
var t_table2 = "EHSZYOJOZHCHSHYUJUYAJAehszyojozhchshyujuyajaEhSzYoJoZhChShYuJuYaJa";
var w_table2 = "ЭЩЁЁЖЧШЮЮЯЯэщёёжчшююяяЭЩЁЁЖЧШЮЮЯЯ";

var tagArray = [
	'code',  '',
	'img',   '',
	'quote', "(=[\"']?[^"+String.fromCharCode(92,93)+"]+)?",
	'email', "(=[\"']?[a-zA-Z0-9_.-]+@?[a-zA-Z0-9_.-]+[\"']?)?",
	'url',   "(=[\"']?[^ \"'"+String.fromCharCode(92,93)+"]*[\"']?)?"
];

function translit2win (str)
{
  var len = str.length;
  var new_str = "";

  for (i = 0; i < len; i++)
  {
  /* non-translatable text must be in ^ */
  if(str.substr(i).indexOf("^")==0){
    end_len=str.substr(i+1).indexOf("^")+2;
    if (end_len>1){
      new_str+=str.substr(i,end_len);
      i += end_len - 1;
      continue;
    }
  }

  /* Skipping emoticons */
  if(str.substr(i).indexOf(":")==0){
    iEnd = str.substr(i+1).indexOf(":")+2;
    if (iEnd > 1 && str.substr(i,iEnd).match("^:[a-zA-Z0-9]+:$")){
      new_str += str.substr(i,iEnd);
      i += iEnd - 1;
      continue;
    }
  }

  /* Skipping http|news|ftp:/.../ links */
  rExp = new RegExp("^((http|https|news|ftp|ed2k):\\/\\/[\\/a-zA-Z0-9%_?.:;&#|\(\)+=@-]+)","i");
  if (newArr = str.substr(i).match(rExp)){
    new_str += newArr[1];
    i += newArr[1].length - 1;
    continue;
  }

  /* Skipping FONT, COLOR, SIZE tags */
  rExp = new RegExp("^(\\[\\/?(b|i|u|s|font(=[a-z0-9]+)?|size(=[0-9]+)?|color(=#?[a-z0-9]+)?)\\])","i");
  if (newArr = str.substr(i).match(rExp)){
    new_str += newArr[1];
    i += newArr[1].length - 1;
    continue;
  }

  /* Skipping [QUOTE]..[/QUOTE], [IMG]..[/IMG], [CODE]..[/CODE], [SQL]..[/SQL], [EMAIL]..[/EMAIL] tags */
  bSkip = false;
  for(j = 0; j < tagArray.length; j += 2){
    rExp = new RegExp("^(\\["+tagArray[j]+tagArray[j+1]+"\\])","i");
    if (newArr = str.substr(i).match(rExp)){
      rExp = new RegExp("\\[\\/" + tagArray[j] + "\\]", "i");
      if (iEnd = str.substr(i + newArr[1].length + 2).search(rExp)){
        end_len = iEnd + newArr[1].length + tagArray[j].length + 4;
        new_str += str.substr(i,end_len);
        i += end_len - 1;
        bSkip = true;
      }
    }
    if(bSkip)break;
  }
  if(bSkip)continue;

  // Check for 2-character letters
  is2char=false;
  if (i < len-1) {
   for(j = 0; j < w_table2.length; j++)
   {
    if(str.substr(i, 2) == t_table2.substr(j*2,2)) {
     new_str+= w_table2.substr(j, 1);
     i++;
     is2char=true;
     break;
    }
   }
  }

  if(!is2char) {
    // Convert one-character letter
    var c = str.substr(i, 1);
    var pos = t_table1.indexOf(c);
    if (pos < 0)
      new_str+= c;
    else
      new_str+= w_table1.substr(pos, 1);
  }
 }

//  document.REPLIER.Post.focus();
  return new_str;
}

function transliterate (msg, e)
{
	if (e) e.disabled = true;
	setTimeout(function() {
	 if (!bbcode.surround('', '', translit2win)) {
			msg.value = translit2win(msg.value);
		}
		if (e) e.disabled = false;
	}, 1);
}

// Translit END
