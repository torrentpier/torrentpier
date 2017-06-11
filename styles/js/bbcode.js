/*
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

// BBCode control
function BBCode(obj) {
  var textarea = document.getElementById(obj);
  this.construct(textarea);
}

BBCode.prototype = {
  VK_TAB: 9,
  VK_ENTER: 13,
  VK_PAGE_UP: 33,
  BRK_OP: '[',
  BRK_CL: ']',
  textarea: null,
  stext: '',
  quoter: null,
  qouted_pid: null,
  collapseAfterInsert: false,
  replaceOnInsert: false,

  // Create new BBCode control
  construct: function (textarea) {
    this.textarea = textarea;
    this.tags = {};
    // Tag for quoting
    this.addTag(
      '_quoter',
      function () {
        return '[quote="' + th.quoter + '"][qpost=' + th.qouted_pid + ']'
      },
      '[/quote]\n',
      null,
      null,
      function () {
        th.collapseAfterInsert = true;
        return th._prepareMultiline(th.quoterText)
      }
    );

    // Init events
    var th = this;
    addEvent(textarea, 'keydown', function (e) {
      return th.onKeyPress(e, window.HTMLElement ? 'down' : 'press')
    });
    addEvent(textarea, 'keypress', function (e) {
      return th.onKeyPress(e, 'press')
    });
  },

  // Insert poster name or poster quotes to the text
  onclickPoster: function (name, post_id) {
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
  onclickQuoteSel: function () {
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
  emoticon: function (em) {
    if (em) {
      this.insertAtCursor(' ' + em + ' ');
    }
    else {
      return false;
    }
    return false;
  },

  // Return current selection and range (if exists)
  getSelection: function () {
    var w = window;
    var text = '', range;
    if (w.getSelection) {
      text = w.getSelection();
    } else {
      return [null, null];
    }
    if (text === '') text = this.stext;
    text = "" + text;
    text = text.replace("/^\s+|\s+$/g", "");
    return [text, range];
  },

  // Insert string at cursor position of textarea
  insertAtCursor: function (text) {
    // Focus is placed to textarea
    var t = this.textarea;
    t.focus();
    // Insert the string
    if (document.selection && document.selection.createRange) {
      var r = document.selection.createRange();
      if (!this.replaceOnInsert) r.collapse();
      r.text = text;
    } else if (t.setSelectionRange) {
      var start = this.replaceOnInsert ? t.selectionStart : t.selectionEnd;
      var end = t.selectionEnd;
      var sel1 = t.value.substr(0, start);
      var sel2 = t.value.substr(end);
      t.value = sel1 + text + sel2;
      t.setSelectionRange(start + text.length, start + text.length);
    } else {
      t.value += text;
    }
    // For IE
    setTimeout(function () {
      t.focus()
    }, 100);
  },

  // Surround piece of textarea text with tags
  surround: function (open, close, fTrans) {
    var t = this.textarea;
    t.focus();
    if (!fTrans) fTrans = function (t) {
      return t;
    };

    var rt = this.getSelection();
    var text = rt[0];
    var range = rt[1];
    if (text === null) return false;

    var notEmpty = text !== null && text !== '';

    // Surround
    if (range) {
      var newText = open + fTrans(text) + (close ? close : '');
      range.text = newText;
      range.collapse();
      if (text !== '') {
        // Correction for stupid IE: \r for moveStart is 0 character
        var delta = 0;
        for (var i = 0; i < newText.length; i++) if (newText.charAt(i) === '\r') delta++;
        range.moveStart("character", -close.length - text.length - open.length + delta);
        range.moveEnd("character", -0);
      } else {
        range.moveEnd("character", -close.length);
      }
      if (!this.collapseAfterInsert) range.select();
    } else if (t.setSelectionRange) {
      var start = t.selectionStart;
      var end = t.selectionEnd;
      var top = t.scrollTop;
      var sel1 = t.value.substr(0, start);
      var sel2 = t.value.substr(end);
      var sel = fTrans(t.value.substr(start, end - start));
      var inner = open + sel + close;
      t.value = sel1 + inner + sel2;
      if (sel !== '') {
        t.setSelectionRange(start, start + inner.length);
        notEmpty = true;
      } else {
        t.setSelectionRange(start + open.length, start + open.length);
        notEmpty = false;
      }
      t.scrollTop = top;
      if (this.collapseAfterInsert) t.setSelectionRange(start + inner.length, start + inner.length);
    } else {
      t.value += open + text + close;
    }
    this.collapseAfterInsert = false;
    return notEmpty;
  },

  // Internal function for cross-browser event cancellation.
  _cancelEvent: function (e) {
    if (e.preventDefault) e.preventDefault();
    if (e.stopPropagation) e.stopPropagation();
    return e.returnValue = false;
  },

  // Available key combinations and these interpretaions for BB are
  // TAB              - Insert TAB char
  // CTRL-TAB         - Next form field (usual TAB)
  // SHIFT-ALT-PAGEUP - Add an Attachment
  // ALT-ENTER        - Preview
  // CTRL-ENTER       - Submit
  onKeyPress: function (e, type) {
    // Try to match all the hot keys.
    var key = String.fromCharCode(e.keyCode ? e.keyCode : e.charCode);
    for (var id in this.tags) {
      var tag = this.tags[id];
      // Pressed control key?
      if (tag.ctrlKey && !e[tag.ctrlKey + "Key"]) continue;
      // Pressed needed key?
      if (!tag.key || key.toUpperCase() !== tag.key.toUpperCase()) continue;
      // Insert
      if (e.type === "keydown") this.insertTag(id);
      // Reset event
      return this._cancelEvent(e);
    }

    // Tab
    if (type === 'press' && e.keyCode === this.VK_TAB && !e.shiftKey && !e.ctrlKey && !e.altKey) {
      this.insertAtCursor('[tab]');
      return this._cancelEvent(e);
    }

    // Ctrl+Tab
    if (e.keyCode === this.VK_TAB && !e.shiftKey && e.ctrlKey && !e.altKey) {
      this.textarea.form.post.focus();
      return this._cancelEvent(e);
    }

    // Hot keys
    var form = this.textarea.form;
    var submitter = null;
    if (e.keyCode === this.VK_PAGE_UP && e.shiftKey && !e.ctrlKey && e.altKey)
      submitter = form.add_attachment_box;
    if (e.keyCode === this.VK_ENTER && !e.shiftKey && !e.ctrlKey && e.altKey)
      submitter = form.preview;
    if (e.keyCode === this.VK_ENTER && !e.shiftKey && e.ctrlKey && !e.altKey)
      submitter = form.post;
    if (submitter) {
      submitter.click();
      return this._cancelEvent(e);
    }

    return true;
  },

  // Adds a BB tag to the list
  addTag: function (id, open, close, key, ctrlKey, multiline) {
    if (!ctrlKey) ctrlKey = "ctrl";
    var tag = {};
    tag.id = id;
    tag.open = open;
    tag.close = close;
    tag.key = key;
    tag.ctrlKey = ctrlKey;
    tag.multiline = multiline;
    tag.elt = this.textarea.form[id];
    this.tags[id] = tag;
    // Setup events
    var elt = tag.elt;
    if (elt) {
      var th = this;
      if (elt.type && elt.type.toUpperCase() === "BUTTON") {
        addEvent(elt, 'click', function () {
          th.insertTag(id);
          return false;
        });
      }
      if (elt.tagName && elt.tagName.toUpperCase() === "SELECT") {
        addEvent(elt, 'change', function () {
          th.insertTag(id);
          return false;
        });
      }
    }
    else {
      if (id && id.indexOf('_') !== 0) return alert("addTag('" + id + "'): no such element in the form");
    }
  },

  // Inserts the tag with specified ID
  insertTag: function (id) {
    // Find tag
    var tag = this.tags[id];
    if (!tag) return alert("Unknown tag ID: " + id);

    // Open tag is generated by callback?
    var op = tag.open;
    if (typeof(tag.open) === "function") op = tag.open(tag.elt);
    var cl = tag.close !== null ? tag.close : "/" + op;

    // Use "[" if needed
    if (op.charAt(0) !== this.BRK_OP) op = this.BRK_OP + op + this.BRK_CL;
    if (cl && cl.charAt(0) !== this.BRK_OP) cl = this.BRK_OP + cl + this.BRK_CL;

    this.surround(op, cl, !tag.multiline ? null : tag.multiline === true ? this._prepareMultiline : tag.multiline);
  },

  _prepareMultiline: function (text) {
    text = text.replace(/\s+$/, '');
    text = text.replace(/^([ \t]*\r?\n)+/, '');
    if (text.indexOf("\n") >= 0) text = "\n" + text + "\n";
    return text;
  }
};

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

function InsertBBCode(BBcode) {
  AddSelectedText('[' + BBcode + ']', '[/' + BBcode + ']');
}

function storeCaret(textEl) {
  if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}

function initPostBBCode(context) {
  $('span.post-hr', context).html('<hr align="left" />');
  initCodes(context);
  initQuotes(context);
  initExternalLinks(context);
  initPostImages(context);
  initSpoilers(context);
  initMedia(context);
}

function initCodes(context) {
  $('div.c-body', context).each(function () {
    var $c = $(this);
    $c.before('<div class="c-head"><b>' + bbl['code'] + ':</b></div>');
  });
}

function initQuotes(context) {
  $('div.q', context).each(function () {
    var $q = $(this);
    var name = $(this).attr('head');
    var q_title = (name ? '<b>' + name + '</b> ' + bbl['wrote'] + ':' : '<b>' + bbl['quote'] + '</b>');
    if (quoted_pid = $q.children('u.q-post:first').text()) {
      var on_this_page = $('#post_' + quoted_pid).length;
      var href = (on_this_page) ? '#' + quoted_pid : './viewtopic.php?p=' + quoted_pid + '#' + quoted_pid;
      q_title += ' <a href="' + href + '" title="' + bbl['quoted_post'] + '"><img src="' + bb_url + 'styles/templates/default/images/icon_latest_reply.gif" class="icon2" alt="" /></a>';
    }
    $q.before('<div class="q-head">' + q_title + '</div>');
  });
}

function initPostImages(context) {
  if (hidePostImg) return;
  var $in_spoilers = $('div.sp-body var.postImg', context);
  $('var.postImg', context).not($in_spoilers).each(function () {
    var $v = $(this);
    var src = $v.attr('title');
    var $img = $('<img src="' + src + '" class="' + $v.attr('class') + '" alt="pic" />');
    $img = fixPostImage($img);
    var maxW = ($v.hasClass('postImgAligned')) ? postImgAligned_MaxWidth : postImg_MaxWidth;
    $img.bind('click', function () {
      return imgFit(this, maxW);
    });
    if (user.opt_js.i_aft_l) {
      $('#preload').append($img);
      var loading_icon = '<a href="' + src + '" target="_blank"><img src="' + bb_url + 'styles/images/pic_loading.gif" alt="" /></a>';
      $v.html(loading_icon);
      if ($.browser.msie) {
        $v.after('<wbr>');
      }
      $img.one('load', function () {
        imgFit(this, maxW);
        $v.empty().append(this);
      });
    }
    else {
      $img.one('load', function () {
        imgFit(this, maxW)
      });
      $v.empty().append($img);
      if ($.browser.msie) {
        $v.after('<wbr>');
      }
    }
  });
}

function initSpoilers(context) {
  $('div.sp-body', context).each(function () {
    var $sp_body = $(this);
    var name = $.trim(this.title) || '' + bbl['spoiler_head'] + '';
    this.title = '';
    var $sp_head = $('<div class="sp-head folded clickable">' + name + '</div>');
    $sp_head.insertBefore($sp_body).click(function (e) {
      if (!$sp_body.hasClass('inited')) {
        initPostImages($sp_body);
        var $sp_fold_btn = $('<div class="sp-fold clickable">[' + bbl['spoiler_close'] + ']</div>').click(function () {
          $.scrollTo($sp_head, {duration: 200, axis: 'y', offset: -200});
          $sp_head.click().animate({opacity: 0.1}, 500).animate({opacity: 1}, 700);
        });
        $sp_body.prepend('<div class="clear"></div>').append('<div class="clear"></div>').append($sp_fold_btn).addClass('inited');
      }
      if (e.shiftKey) {
        e.stopPropagation();
        e.shiftKey = false;
        var fold = $(this).hasClass('unfolded');
        $('div.sp-head', $($sp_body.parents('td')[0])).filter(function () {
          return $(this).hasClass('unfolded') ? fold : !fold
        }).click();
      }
      else {
        $(this).toggleClass('unfolded');
        $sp_body.slideToggle('fast');
      }
    });
  });
}

function initExternalLinks(context) {
  var context = context || 'body';
  if (ExternalLinks_InNewWindow) {
    $("a.postLink:not([href*='" + window.location.hostname + "/'])", context).attr({target: '_blank'});
  }
}

function fixPostImage($img) {
  var banned_image_hosts = /imagebanana|hidebehind/i;
  var src = $img[0].src;
  if (src.match(banned_image_hosts)) {
    $img.wrap('<a href="' + this.src + '" target="_blank"></a>').attr({
      src: "" + bb_url + "styles/images/smiles/tr_oops.gif",
      title: "" + bbl['scr_rules'] + ""
    });
  }
  return $img;
}

function initMedia(context) {
  var apostLink = $('a.postLink', context);
  for (var i = 0; i < apostLink.length; i++) {
    var link = apostLink[i];
    if (typeof link.href !== 'string') {
      continue;
    }
    if (/^http(?:s|):\/\/www.youtube.com\/watch\?(.*)?(&?v=([a-z0-9\-_]+))(.*)?|http:\/\/youtu.be\/.+/i.test(link.href)) {
      var a = document.createElement('span');
      a.className = 'YTLink';
      a.innerHTML = '<span title="' + bbl['play_on'] + '" class="YTLinkButton">&#9658;</span>';
      window.addEvent(a, 'click', function (e) {
        var vhref = e.target.nextSibling.href.replace(/^http(?:s|):\/\/www.youtube.com\/watch\?(.*)?(&?v=([a-z0-9\-_]+))(.*)?|http:\/\/youtu.be\//ig, "http://www.youtube.com/embed/$3");
        var text = e.target.nextSibling.innerText !== "" ? e.target.nextSibling.innerText : e.target.nextSibling.href;
        $('#Panel_youtube').remove();
        ypanel('youtube', {
          title: '<b>' + text + '</b>',
          resizing: 0,
          width: 862,
          height: 550,
          content: '<iframe width="853" height="493" frameborder="0" allowfullscreen="" src="' + vhref + '?wmode=opaque"></iframe>'
        });
      });
      link.parentNode.insertBefore(a, link);
      a.appendChild(link);
    }
  }
}

$(document).ready(function () {
  $('div.post_wrap, div.signature').each(function () {
    initPostBBCode($(this))
  });
});
