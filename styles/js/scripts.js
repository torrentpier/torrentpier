/*
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
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
        this.addTag('_quoter', function () {
            return '[quote="' + th.quoter + '"][qpost=' + th.qouted_pid + ']'
        }, '[/quote]\n', null, null, function () {
            th.collapseAfterInsert = true;
            return th._prepareMultiline(th.quoterText)
        });

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
        } else {
            alert('Вы не выбрали текст');
        }
        return false;
    },

    // Quote selected text
    emoticon: function (em) {
        if (em) {
            this.insertAtCursor(' ' + em + ' ');
        } else {
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
        if (e.keyCode === this.VK_PAGE_UP && e.shiftKey && !e.ctrlKey && e.altKey) submitter = form.add_attachment_box;
        if (e.keyCode === this.VK_ENTER && !e.shiftKey && !e.ctrlKey && e.altKey) submitter = form.preview;
        if (e.keyCode === this.VK_ENTER && !e.shiftKey && e.ctrlKey && !e.altKey) submitter = form.post;
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
        } else {
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
        if (typeof (tag.open) === "function") op = tag.open(tag.elt);
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
    if (document.post.message.caretPos) document.post.message.caretPos.text = BBOpen + document.post.message.caretPos.text + BBClose; else document.post.message.value += BBOpen + BBClose;
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
        } else {
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
            } else {
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
            src: "" + bb_url + "styles/images/smiles/tr_oops.gif", title: "" + bbl['scr_rules'] + ""
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

/*
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// prototype $
function $p() {
    var elements = [];
    for (var i = 0; i < arguments.length; i++) {
        var element = arguments[i];
        if (typeof element === 'string') element = document.getElementById(element);
        if (arguments.length === 1) return element;
        elements.push(element);
    }
    return elements;
}

function addEvent(obj, type, fn) {
    if (obj.addEventListener) {
        obj.addEventListener(type, fn, false);
        EventCache.add(obj, type, fn);
    } else if (obj.attachEvent) {
        obj["e" + type + fn] = fn;
        obj[type + fn] = function () {
            obj["e" + type + fn](window.event);
        };
        obj.attachEvent("on" + type, obj[type + fn]);
        EventCache.add(obj, type, fn);
    } else {
        obj["on" + type] = obj["e" + type + fn];
    }
}

var EventCache = function () {
    var listEvents = [];
    return {
        listEvents: listEvents, add: function (node, sEventName, fHandler) {
            listEvents.push(arguments);
        }, flush: function () {
            var i, item;
            for (i = listEvents.length - 1; i >= 0; i = i - 1) {
                item = listEvents[i];
                if (item[0].removeEventListener) {
                    item[0].removeEventListener(item[1], item[2], item[3]);
                }
                if (item[1].substring(0, 2) !== "on") {
                    item[1] = "on" + item[1];
                }
                if (item[0].detachEvent) {
                    item[0].detachEvent(item[1], item[2]);
                }
                item[0][item[1]] = null;
            }
        }
    };
}();
if (document.all) {
    addEvent(window, 'unload', EventCache.flush);
}

function imgFit(img, maxW) {
    img.title = 'Размеры изображения: ' + img.width + ' x ' + img.height;
    if (typeof (img.naturalHeight) === 'undefined') {
        img.naturalHeight = img.height;
        img.naturalWidth = img.width;
    }
    if (img.width > maxW) {
        img.height = Math.round((maxW / img.width) * img.height);
        img.width = maxW;
        img.title = 'Нажмите на изображение, чтобы посмотреть его в полный размер';
        img.style.cursor = 'move';
        return false;
    } else if (img.width === maxW && img.width < img.naturalWidth) {
        img.height = img.naturalHeight;
        img.width = img.naturalWidth;
        img.title = 'Размеры изображения: ' + img.naturalWidth + ' x ' + img.naturalHeight;
        return false;
    } else {
        return true;
    }
}

function toggle_block(id) {
    var el = document.getElementById(id);
    el.style.display = (el.style.display === 'none') ? '' : 'none';
}

function toggle_disabled(id, val) {
    document.getElementById(id).disabled = (val) ? 0 : 1;
}

function rand(min, max) {
    return min + Math.floor((max - min + 1) * Math.random());
}

// Cookie functions
/**
 * name       Name of the cookie
 * value      Value of the cookie
 * [days]     Number of days to remain active (default: end of current session)
 * [path]     Path where the cookie is valid (default: path of calling document)
 * [domain]   Domain where the cookie is valid
 *            (default: domain of calling document)
 * [secure]   Boolean value indicating if the cookie transmission requires a
 *            secure transmission
 */
function setCookie(name, value, days, path, domain, secure) {
    if (days !== 'SESSION') {
        var date = new Date();
        days = days || 365;
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
        var expires = date.toGMTString();
    } else {
        var expires = '';
    }

    document.cookie = name + '=' + encodeURI(value) + ((expires) ? '; expires=' + expires : '') + ((path) ? '; path=' + path : ((cookiePath) ? '; path=' + cookiePath : '')) + ((domain) ? '; domain=' + domain : ((cookieDomain) ? '; domain=' + cookieDomain : '')) + ((secure) ? '; secure' : ((cookieSecure) ? '; secure' : ''));
}

/**
 * Returns a string containing value of specified cookie,
 *   or null if cookie does not exist.
 */
function getCookie(name) {
    var c, RE = new RegExp('(^|;)\\s*' + name + '\\s*=\\s*([^\\s;]+)', 'g');
    return (c = RE.exec(document.cookie)) ? c[2] : null;
}

/**
 * name      name of the cookie
 * [path]    path of the cookie (must be same as path used to create cookie)
 * [domain]  domain of the cookie (must be same as domain used to create cookie)
 */
function deleteCookie(name, path, domain) {
    setCookie(name, '', -1, path, domain);
}

// Menus
var Menu = {
    hideSpeed: 'fast', offsetCorrection_X: -4, offsetCorrection_Y: 2,

    activeMenuId: null,  //  currently opened menu (from previous click)
    clickedMenuId: null,  //  menu to show up
    $root: null,  //  root element for menu with "href = '#clickedMenuId'"
    $menu: null,  //  clicked menu
    positioningType: null,  //  reserved
    outsideClickWatch: false, //  prevent multiple $(document).click binding

    clicked: function ($root) {
        $root.blur();
        this.clickedMenuId = this.getMenuId($root);
        this.$menu = $(this.clickedMenuId);
        this.$root = $root;
        this.toggle();
    },

    hovered: function ($root) {
        if (this.activeMenuId && this.activeMenuId !== this.getMenuId($root)) {
            this.clicked($root);
        }
    },

    unhovered: function ($root) {
    },

    getMenuId: function ($el) {
        var href = $el.attr('href');
        return href.substr(href.indexOf('#'));
    },

    setLocation: function () {
        var CSS = this.$root.offset();
        CSS.top += this.$root.height() + this.offsetCorrection_Y;
        var curTop = parseInt(CSS.top);
        var tCorner = $(document).scrollTop() + $(window).height() - 20;
        var maxVisibleTop = Math.min(curTop, Math.max(0, tCorner - this.$menu.height()));
        if (curTop !== maxVisibleTop) {
            CSS.top = maxVisibleTop;
        }
        CSS.left += this.offsetCorrection_X;
        var curLeft = parseInt(CSS.left);
        var rCorner = $(document).scrollLeft() + $(window).width() - 6;
        var maxVisibleLeft = Math.min(curLeft, Math.max(0, rCorner - this.$menu.width()));
        if (curLeft !== maxVisibleLeft) {
            CSS.left = maxVisibleLeft;
        }
        this.$menu.css(CSS);
    },

    fixLocation: function () {
        var $menu = this.$menu;
        var curLeft = parseInt($menu.css('left'));
        var rCorner = $(document).scrollLeft() + $(window).width() - 6;
        var maxVisibleLeft = Math.min(curLeft, Math.max(0, rCorner - $menu.width()));
        if (curLeft !== maxVisibleLeft) {
            $menu.css('left', maxVisibleLeft);
        }
        var curTop = parseInt($menu.css('top'));
        var tCorner = $(document).scrollTop() + $(window).height() - 20;
        var maxVisibleTop = Math.min(curTop, Math.max(0, tCorner - $menu.height()));
        if (curTop !== maxVisibleTop) {
            $menu.css('top', maxVisibleTop);
        }
    },

    toggle: function () {
        if (this.activeMenuId && this.activeMenuId !== this.clickedMenuId) {
            $(this.activeMenuId).hide(this.hideSpeed);
        }
        // toggle clicked menu
        if (this.$menu.is(':visible')) {
            this.$menu.hide(this.hideSpeed);
            this.activeMenuId = null;
        } else {
            this.showClickedMenu();
            if (!this.outsideClickWatch) {
                $(document).one('mousedown', function (e) {
                    Menu.hideClickWatcher(e);
                });
                this.outsideClickWatch = true;
            }
        }
    },

    showClickedMenu: function () {
        this.setLocation();
        this.$menu.css({display: 'block'});
        // this.fixLocation();
        this.activeMenuId = this.clickedMenuId;
    },

    // hide if clicked outside of menu
    hideClickWatcher: function (e) {
        this.outsideClickWatch = false;
        this.hide(e);
    },

    hide: function (e) {
        if (this.$menu) {
            this.$menu.hide(this.hideSpeed);
        }
        this.activeMenuId = this.clickedMenuId = this.$menu = null;
    }
};

$(document).ready(function () {
    // Menus
    $('body').append($('div.menu-sub'));
    $('a.menu-root')
        .click(function (e) {
            e.preventDefault();
            Menu.clicked($(this));
            return false;
        })
        .hover(function () {
            Menu.hovered($(this));
            return false;
        }, function () {
            Menu.unhovered($(this));
            return false;
        });
    $('div.menu-sub')
        .mousedown(function (e) {
            e.stopPropagation();
        })
        .find('a')
        .click(function (e) {
            Menu.hide(e);
        });
    // Input hints
    $('input')
        .filter('.hint').one('focus', function () {
        $(this).val('').removeClass('hint');
    })
        .end()
        .filter('.error').one('focus', function () {
        $(this).removeClass('error');
    });
});

//
// Ajax
//
function Ajax(handlerURL, requestType, dataType) {
    this.url = handlerURL;
    this.type = requestType;
    this.dataType = dataType;
    this.errors = {};
}

Ajax.prototype = {
    init: {},  // init functions (run before submit, after triggering ajax event)
    callback: {},  // callback functions (response handlers)
    state: {},  // current action state
    request: {},  // request data
    params: {},  // action params, format: ajax.params[ElementID] = { param: "val" ... }
    form_token: '', hide_loading: null,

    exec: function (request, hide_loading = false) {
        this.request[request.action] = request;
        request['form_token'] = this.form_token;
        this.hide_loading = hide_loading;
        $.ajax({
            url: this.url,
            type: this.type,
            dataType: this.dataType,
            data: request,
            success: ajax.success,
            error: ajax.error
        });
    },

    success: function (response) {
        var action = response.action;
        // raw_output normally might contain only error messages (if php.ini.display_errors == 1)
        if (response.raw_output) {
            $('body').prepend(response.raw_output);
        }
        if (response.sql_log) {
            $('#sqlLog').prepend(response.sql_log + '<hr />');
            fixSqlLog();
        }
        if (response.update_ids) {
            for (id in response.update_ids) {
                $('#' + id).html(response.update_ids[id]);
            }
        }
        if (response.prompt_password) {
            var user_password = prompt('Для доступа к данной функции, пожалуйста, введите свой пароль', '');
            if (user_password) {
                var req = ajax.request[action];
                req.user_password = user_password;
                ajax.exec(req);
            } else {
                ajax.clearActionState(action);
                ajax.showErrorMsg('Введен неверный пароль');
            }
        } else if (response.prompt_confirm) {
            if (window.confirm(response.confirm_msg)) {
                var req = ajax.request[action];
                req.confirmed = 1;
                ajax.exec(req);
            } else {
                ajax.clearActionState(action);
            }
        } else if (response.error_code) {
            ajax.showErrorMsg(response.error_msg);
            console.log(response.console_log);
            $('.loading-1').removeClass('loading-1').html('error');
        } else {
            ajax.callback[action](response);
            ajax.clearActionState(action);
        }
    },

    error: function (xml, desc) {
    },

    clearActionState: function (action) {
        ajax.state[action] = ajax.request[action] = '';
    },

    showErrorMsg: function (msg) {
        alert(msg);
    },

    callInitFn: function (event) {
        event.stopPropagation();
        var params = ajax.params[$(this).attr('id')];
        var action = params.action;
        if (ajax.state[action] === 'readyToSubmit' || ajax.state[action] === 'error') {
            return false;
        } else {
            ajax.state[action] = 'readyToSubmit';
        }
        ajax.init[action](params);
    },

    setStatusBoxPosition: function ($el) {
        var newTop = $(document).scrollTop();
        var rCorner = $(document).scrollLeft() + $(window).width() - 8;
        var newLeft = Math.max(0, rCorner - $el.width());
        $el.css({top: newTop, left: newLeft});
    },

    makeEditable: function (rootElementId, editableType) {
        var $root = $('#' + rootElementId);
        var $editable = $('.editable', $root);
        var inputsHtml = $('#editable-tpl-' + editableType).html();
        $editable.hide().after(inputsHtml);
        var $inputs = $('.editable-inputs', $root);
        if (editableType === 'input' || editableType === 'textarea') {
            $('.editable-value', $inputs).val($.trim($editable.text()));
        }
        $('input.editable-submit', $inputs).click(function () {
            var params = ajax.params[rootElementId];
            var $val = $('.editable-value', '#' + rootElementId);
            params.value = ($val.size() === 1) ? $val.val() : $val.filter(':checked').val();
            params.submit = true;
            ajax.init[params.action](params);
        });
        $('input.editable-cancel', $inputs).click(function () {
            ajax.restoreEditable(rootElementId);
        });
        $inputs.show().find('.editable-value').focus();
        $root.removeClass('editable-container');
    },

    restoreEditable: function (rootElementId, newValue) {
        var $root = $('#' + rootElementId);
        var $editable = $('.editable', $root);
        $('.editable-inputs', $root).remove();
        if (newValue) {
            $editable.text(newValue);
        }
        $editable.show();
        ajax.clearActionState(ajax.params[rootElementId].action);
        ajax.params[rootElementId].submit = false;
        $root.addClass('editable-container');
    }
};

$(document).ready(function () {
    // Setup ajax-loading box
    $("#ajax-loading").ajaxStart(function () {
        if (ajax.hide_loading === false) {
            $("#ajax-error").hide();
            $(this).show();
            ajax.setStatusBoxPosition($(this));
        }
    });
    $("#ajax-loading").ajaxStop(function () {
        if (ajax.hide_loading === false) {
            $(this).hide();
        }
    });

    // Setup ajax-error box
    $("#ajax-error").ajaxError(function (req, xml) {
        var status = xml.status;
        var text = xml.statusText;
        if (status === 200) {
            status = '';
            text = 'неверный формат данных';
        }
        $(this).html("Ошибка в: <i>" + ajax.url + "</i><br /><b>" + status + " " + text + "</b>").show();
        ajax.setStatusBoxPosition($(this));
    });

    // Bind ajax events
    $('var.ajax-params').each(function () {
        var params = $.evalJSON($(this).html());
        params.event = params.event || 'dblclick';
        ajax.params[params.id] = params;
        $("#" + params.id).bind(params.event, ajax.callInitFn);
        if (params.event === 'click' || params.event === 'dblclick') {
            $("#" + params.id).addClass('editable-container');
        }
    });
});

/**
 * Autocomplete password
 **/
var array_for_rand_pass = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
var array_rand = function (array) {
    var array_length = array.length;
    var result = Math.random() * array_length;
    return Math.floor(result);
};

var autocomplete = function (noCenter) {
    var string_result = ""; // Empty string
    for (var i = 1; i <= 8; i++) {
        string_result += array_for_rand_pass[array_rand(array_for_rand_pass)];
    }

    var _popup_left = (Math.ceil(window.screen.availWidth / 2) - 150);
    var _popup_top = (Math.ceil(window.screen.availHeight / 2) - 50);

    if (!noCenter) {
        $("div#autocomplete_popup").css({
            left: _popup_left + "px", top: _popup_top + "px"
        }).show(1000);
    } else {
        $("div#autocomplete_popup").show(1000);
    }

    $("input#pass, input#pass_confirm, div#autocomplete_popup input").each(function () {
        $(this).val(string_result);
    });
};

$(document).ready(function () {
    $("span#autocomplete").click(function () {
        autocomplete();
    });

    // перемещение окна
    var _X, _Y;
    var _bMoveble = false;

    $("div#autocomplete_popup div.title").mousedown(function (event) {
        _bMoveble = true;
        _X = event.clientX;
        _Y = event.clientY;
    });

    $("div#autocomplete_popup div.title").mousemove(function (event) {
        var jFrame = $("div#autocomplete_popup");
        var jFLeft = parseInt(jFrame.css("left"));
        var jFTop = parseInt(jFrame.css("top"));

        if (_bMoveble) {
            if (event.clientX < _X) {
                jFrame.css("left", jFLeft - (_X - event.clientX) + "px");
            } else {
                jFrame.css("left", (jFLeft + (event.clientX - _X)) + "px");
            }

            if (event.clientY < _Y) {
                jFrame.css("top", jFTop - (_Y - event.clientY) + "px");
            } else {
                jFrame.css("top", (jFTop + (event.clientY - _Y)) + "px");
            }

            _X = event.clientX;
            _Y = event.clientY;
        }
    });

    $("div#autocomplete_popup div.title").mouseup(function () {
        _bMoveble = false;
    }).mouseout(function () {
        _bMoveble = false;
    });
});
