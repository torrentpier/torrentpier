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

//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImJiY29kZS5qcyIsIm1haW4uanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQ2xkQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJzY3JpcHRzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLypcclxuICogVG9ycmVudFBpZXIg4oCTIEJ1bGwtcG93ZXJlZCBCaXRUb3JyZW50IHRyYWNrZXIgZW5naW5lXHJcbiAqXHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDA1LTIwMjQgVG9ycmVudFBpZXIgKGh0dHBzOi8vdG9ycmVudHBpZXIuY29tKVxyXG4gKiBAbGluayAgICAgIGh0dHBzOi8vZ2l0aHViLmNvbS90b3JyZW50cGllci90b3JyZW50cGllciBmb3IgdGhlIGNhbm9uaWNhbCBzb3VyY2UgcmVwb3NpdG9yeVxyXG4gKiBAbGljZW5zZSAgIGh0dHBzOi8vZ2l0aHViLmNvbS90b3JyZW50cGllci90b3JyZW50cGllci9ibG9iL21hc3Rlci9MSUNFTlNFIE1JVCBMaWNlbnNlXHJcbiAqL1xyXG5cclxuLy8gQkJDb2RlIGNvbnRyb2xcclxuZnVuY3Rpb24gQkJDb2RlKG9iaikge1xyXG4gICAgdmFyIHRleHRhcmVhID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQob2JqKTtcclxuICAgIHRoaXMuY29uc3RydWN0KHRleHRhcmVhKTtcclxufVxyXG5cclxuQkJDb2RlLnByb3RvdHlwZSA9IHtcclxuICAgIFZLX1RBQjogOSxcclxuICAgIFZLX0VOVEVSOiAxMyxcclxuICAgIFZLX1BBR0VfVVA6IDMzLFxyXG4gICAgQlJLX09QOiAnWycsXHJcbiAgICBCUktfQ0w6ICddJyxcclxuICAgIHRleHRhcmVhOiBudWxsLFxyXG4gICAgc3RleHQ6ICcnLFxyXG4gICAgcXVvdGVyOiBudWxsLFxyXG4gICAgcW91dGVkX3BpZDogbnVsbCxcclxuICAgIGNvbGxhcHNlQWZ0ZXJJbnNlcnQ6IGZhbHNlLFxyXG4gICAgcmVwbGFjZU9uSW5zZXJ0OiBmYWxzZSxcclxuXHJcbiAgICAvLyBDcmVhdGUgbmV3IEJCQ29kZSBjb250cm9sXHJcbiAgICBjb25zdHJ1Y3Q6IGZ1bmN0aW9uICh0ZXh0YXJlYSkge1xyXG4gICAgICAgIHRoaXMudGV4dGFyZWEgPSB0ZXh0YXJlYTtcclxuICAgICAgICB0aGlzLnRhZ3MgPSB7fTtcclxuICAgICAgICAvLyBUYWcgZm9yIHF1b3RpbmdcclxuICAgICAgICB0aGlzLmFkZFRhZygnX3F1b3RlcicsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgcmV0dXJuICdbcXVvdGU9XCInICsgdGgucXVvdGVyICsgJ1wiXVtxcG9zdD0nICsgdGgucW91dGVkX3BpZCArICddJ1xyXG4gICAgICAgIH0sICdbL3F1b3RlXVxcbicsIG51bGwsIG51bGwsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgdGguY29sbGFwc2VBZnRlckluc2VydCA9IHRydWU7XHJcbiAgICAgICAgICAgIHJldHVybiB0aC5fcHJlcGFyZU11bHRpbGluZSh0aC5xdW90ZXJUZXh0KVxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAvLyBJbml0IGV2ZW50c1xyXG4gICAgICAgIHZhciB0aCA9IHRoaXM7XHJcbiAgICAgICAgYWRkRXZlbnQodGV4dGFyZWEsICdrZXlkb3duJywgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgcmV0dXJuIHRoLm9uS2V5UHJlc3MoZSwgd2luZG93LkhUTUxFbGVtZW50ID8gJ2Rvd24nIDogJ3ByZXNzJylcclxuICAgICAgICB9KTtcclxuICAgICAgICBhZGRFdmVudCh0ZXh0YXJlYSwgJ2tleXByZXNzJywgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgcmV0dXJuIHRoLm9uS2V5UHJlc3MoZSwgJ3ByZXNzJylcclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG4gICAgLy8gSW5zZXJ0IHBvc3RlciBuYW1lIG9yIHBvc3RlciBxdW90ZXMgdG8gdGhlIHRleHRcclxuICAgIG9uY2xpY2tQb3N0ZXI6IGZ1bmN0aW9uIChuYW1lLCBwb3N0X2lkKSB7XHJcbiAgICAgICAgdmFyIHNlbCA9IHRoaXMuZ2V0U2VsZWN0aW9uKClbMF07XHJcbiAgICAgICAgaWYgKHNlbCkge1xyXG4gICAgICAgICAgICB0aGlzLnF1b3RlciA9IG5hbWU7XHJcbiAgICAgICAgICAgIHRoaXMucW91dGVkX3BpZCA9IHBvc3RfaWQ7XHJcbiAgICAgICAgICAgIHRoaXMucXVvdGVyVGV4dCA9IHNlbDtcclxuICAgICAgICAgICAgdGhpcy5pbnNlcnRUYWcoJ19xdW90ZXInKTtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICB0aGlzLmluc2VydEF0Q3Vyc29yKFwiW2JdXCIgKyBuYW1lICsgJ1svYl0sICcpO1xyXG4gICAgICAgIH1cclxuICAgICAgICByZXR1cm4gZmFsc2U7XHJcbiAgICB9LFxyXG5cclxuICAgIC8vIFF1b3RlIHNlbGVjdGVkIHRleHRcclxuICAgIG9uY2xpY2tRdW90ZVNlbDogZnVuY3Rpb24gKCkge1xyXG4gICAgICAgIHZhciBzZWwgPSB0aGlzLmdldFNlbGVjdGlvbigpWzBdO1xyXG4gICAgICAgIGlmIChzZWwpIHtcclxuICAgICAgICAgICAgdGhpcy5pbnNlcnRBdEN1cnNvcignW3F1b3RlXScgKyBzZWwgKyAnWy9xdW90ZV1cXG4nKTtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICBhbGVydCgn0JLRiyDQvdC1INCy0YvQsdGA0LDQu9C4INGC0LXQutGB0YInKTtcclxuICAgICAgICB9XHJcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgfSxcclxuXHJcbiAgICAvLyBRdW90ZSBzZWxlY3RlZCB0ZXh0XHJcbiAgICBlbW90aWNvbjogZnVuY3Rpb24gKGVtKSB7XHJcbiAgICAgICAgaWYgKGVtKSB7XHJcbiAgICAgICAgICAgIHRoaXMuaW5zZXJ0QXRDdXJzb3IoJyAnICsgZW0gKyAnICcpO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcclxuICAgICAgICB9XHJcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgfSxcclxuXHJcbiAgICAvLyBSZXR1cm4gY3VycmVudCBzZWxlY3Rpb24gYW5kIHJhbmdlIChpZiBleGlzdHMpXHJcbiAgICBnZXRTZWxlY3Rpb246IGZ1bmN0aW9uICgpIHtcclxuICAgICAgICB2YXIgdyA9IHdpbmRvdztcclxuICAgICAgICB2YXIgdGV4dCA9ICcnLCByYW5nZTtcclxuICAgICAgICBpZiAody5nZXRTZWxlY3Rpb24pIHtcclxuICAgICAgICAgICAgdGV4dCA9IHcuZ2V0U2VsZWN0aW9uKCk7XHJcbiAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgcmV0dXJuIFtudWxsLCBudWxsXTtcclxuICAgICAgICB9XHJcbiAgICAgICAgaWYgKHRleHQgPT09ICcnKSB0ZXh0ID0gdGhpcy5zdGV4dDtcclxuICAgICAgICB0ZXh0ID0gXCJcIiArIHRleHQ7XHJcbiAgICAgICAgdGV4dCA9IHRleHQucmVwbGFjZShcIi9eXFxzK3xcXHMrJC9nXCIsIFwiXCIpO1xyXG4gICAgICAgIHJldHVybiBbdGV4dCwgcmFuZ2VdO1xyXG4gICAgfSxcclxuXHJcbiAgICAvLyBJbnNlcnQgc3RyaW5nIGF0IGN1cnNvciBwb3NpdGlvbiBvZiB0ZXh0YXJlYVxyXG4gICAgaW5zZXJ0QXRDdXJzb3I6IGZ1bmN0aW9uICh0ZXh0KSB7XHJcbiAgICAgICAgLy8gRm9jdXMgaXMgcGxhY2VkIHRvIHRleHRhcmVhXHJcbiAgICAgICAgdmFyIHQgPSB0aGlzLnRleHRhcmVhO1xyXG4gICAgICAgIHQuZm9jdXMoKTtcclxuICAgICAgICAvLyBJbnNlcnQgdGhlIHN0cmluZ1xyXG4gICAgICAgIGlmIChkb2N1bWVudC5zZWxlY3Rpb24gJiYgZG9jdW1lbnQuc2VsZWN0aW9uLmNyZWF0ZVJhbmdlKSB7XHJcbiAgICAgICAgICAgIHZhciByID0gZG9jdW1lbnQuc2VsZWN0aW9uLmNyZWF0ZVJhbmdlKCk7XHJcbiAgICAgICAgICAgIGlmICghdGhpcy5yZXBsYWNlT25JbnNlcnQpIHIuY29sbGFwc2UoKTtcclxuICAgICAgICAgICAgci50ZXh0ID0gdGV4dDtcclxuICAgICAgICB9IGVsc2UgaWYgKHQuc2V0U2VsZWN0aW9uUmFuZ2UpIHtcclxuICAgICAgICAgICAgdmFyIHN0YXJ0ID0gdGhpcy5yZXBsYWNlT25JbnNlcnQgPyB0LnNlbGVjdGlvblN0YXJ0IDogdC5zZWxlY3Rpb25FbmQ7XHJcbiAgICAgICAgICAgIHZhciBlbmQgPSB0LnNlbGVjdGlvbkVuZDtcclxuICAgICAgICAgICAgdmFyIHNlbDEgPSB0LnZhbHVlLnN1YnN0cigwLCBzdGFydCk7XHJcbiAgICAgICAgICAgIHZhciBzZWwyID0gdC52YWx1ZS5zdWJzdHIoZW5kKTtcclxuICAgICAgICAgICAgdC52YWx1ZSA9IHNlbDEgKyB0ZXh0ICsgc2VsMjtcclxuICAgICAgICAgICAgdC5zZXRTZWxlY3Rpb25SYW5nZShzdGFydCArIHRleHQubGVuZ3RoLCBzdGFydCArIHRleHQubGVuZ3RoKTtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICB0LnZhbHVlICs9IHRleHQ7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIC8vIEZvciBJRVxyXG4gICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICB0LmZvY3VzKClcclxuICAgICAgICB9LCAxMDApO1xyXG4gICAgfSxcclxuXHJcbiAgICAvLyBTdXJyb3VuZCBwaWVjZSBvZiB0ZXh0YXJlYSB0ZXh0IHdpdGggdGFnc1xyXG4gICAgc3Vycm91bmQ6IGZ1bmN0aW9uIChvcGVuLCBjbG9zZSwgZlRyYW5zKSB7XHJcbiAgICAgICAgdmFyIHQgPSB0aGlzLnRleHRhcmVhO1xyXG4gICAgICAgIHQuZm9jdXMoKTtcclxuICAgICAgICBpZiAoIWZUcmFucykgZlRyYW5zID0gZnVuY3Rpb24gKHQpIHtcclxuICAgICAgICAgICAgcmV0dXJuIHQ7XHJcbiAgICAgICAgfTtcclxuXHJcbiAgICAgICAgdmFyIHJ0ID0gdGhpcy5nZXRTZWxlY3Rpb24oKTtcclxuICAgICAgICB2YXIgdGV4dCA9IHJ0WzBdO1xyXG4gICAgICAgIHZhciByYW5nZSA9IHJ0WzFdO1xyXG4gICAgICAgIGlmICh0ZXh0ID09PSBudWxsKSByZXR1cm4gZmFsc2U7XHJcblxyXG4gICAgICAgIHZhciBub3RFbXB0eSA9IHRleHQgIT09IG51bGwgJiYgdGV4dCAhPT0gJyc7XHJcblxyXG4gICAgICAgIC8vIFN1cnJvdW5kXHJcbiAgICAgICAgaWYgKHJhbmdlKSB7XHJcbiAgICAgICAgICAgIHZhciBuZXdUZXh0ID0gb3BlbiArIGZUcmFucyh0ZXh0KSArIChjbG9zZSA/IGNsb3NlIDogJycpO1xyXG4gICAgICAgICAgICByYW5nZS50ZXh0ID0gbmV3VGV4dDtcclxuICAgICAgICAgICAgcmFuZ2UuY29sbGFwc2UoKTtcclxuICAgICAgICAgICAgaWYgKHRleHQgIT09ICcnKSB7XHJcbiAgICAgICAgICAgICAgICAvLyBDb3JyZWN0aW9uIGZvciBzdHVwaWQgSUU6IFxcciBmb3IgbW92ZVN0YXJ0IGlzIDAgY2hhcmFjdGVyXHJcbiAgICAgICAgICAgICAgICB2YXIgZGVsdGEgPSAwO1xyXG4gICAgICAgICAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCBuZXdUZXh0Lmxlbmd0aDsgaSsrKSBpZiAobmV3VGV4dC5jaGFyQXQoaSkgPT09ICdcXHInKSBkZWx0YSsrO1xyXG4gICAgICAgICAgICAgICAgcmFuZ2UubW92ZVN0YXJ0KFwiY2hhcmFjdGVyXCIsIC1jbG9zZS5sZW5ndGggLSB0ZXh0Lmxlbmd0aCAtIG9wZW4ubGVuZ3RoICsgZGVsdGEpO1xyXG4gICAgICAgICAgICAgICAgcmFuZ2UubW92ZUVuZChcImNoYXJhY3RlclwiLCAtMCk7XHJcbiAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICByYW5nZS5tb3ZlRW5kKFwiY2hhcmFjdGVyXCIsIC1jbG9zZS5sZW5ndGgpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIGlmICghdGhpcy5jb2xsYXBzZUFmdGVySW5zZXJ0KSByYW5nZS5zZWxlY3QoKTtcclxuICAgICAgICB9IGVsc2UgaWYgKHQuc2V0U2VsZWN0aW9uUmFuZ2UpIHtcclxuICAgICAgICAgICAgdmFyIHN0YXJ0ID0gdC5zZWxlY3Rpb25TdGFydDtcclxuICAgICAgICAgICAgdmFyIGVuZCA9IHQuc2VsZWN0aW9uRW5kO1xyXG4gICAgICAgICAgICB2YXIgdG9wID0gdC5zY3JvbGxUb3A7XHJcbiAgICAgICAgICAgIHZhciBzZWwxID0gdC52YWx1ZS5zdWJzdHIoMCwgc3RhcnQpO1xyXG4gICAgICAgICAgICB2YXIgc2VsMiA9IHQudmFsdWUuc3Vic3RyKGVuZCk7XHJcbiAgICAgICAgICAgIHZhciBzZWwgPSBmVHJhbnModC52YWx1ZS5zdWJzdHIoc3RhcnQsIGVuZCAtIHN0YXJ0KSk7XHJcbiAgICAgICAgICAgIHZhciBpbm5lciA9IG9wZW4gKyBzZWwgKyBjbG9zZTtcclxuICAgICAgICAgICAgdC52YWx1ZSA9IHNlbDEgKyBpbm5lciArIHNlbDI7XHJcbiAgICAgICAgICAgIGlmIChzZWwgIT09ICcnKSB7XHJcbiAgICAgICAgICAgICAgICB0LnNldFNlbGVjdGlvblJhbmdlKHN0YXJ0LCBzdGFydCArIGlubmVyLmxlbmd0aCk7XHJcbiAgICAgICAgICAgICAgICBub3RFbXB0eSA9IHRydWU7XHJcbiAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICB0LnNldFNlbGVjdGlvblJhbmdlKHN0YXJ0ICsgb3Blbi5sZW5ndGgsIHN0YXJ0ICsgb3Blbi5sZW5ndGgpO1xyXG4gICAgICAgICAgICAgICAgbm90RW1wdHkgPSBmYWxzZTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB0LnNjcm9sbFRvcCA9IHRvcDtcclxuICAgICAgICAgICAgaWYgKHRoaXMuY29sbGFwc2VBZnRlckluc2VydCkgdC5zZXRTZWxlY3Rpb25SYW5nZShzdGFydCArIGlubmVyLmxlbmd0aCwgc3RhcnQgKyBpbm5lci5sZW5ndGgpO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgIHQudmFsdWUgKz0gb3BlbiArIHRleHQgKyBjbG9zZTtcclxuICAgICAgICB9XHJcbiAgICAgICAgdGhpcy5jb2xsYXBzZUFmdGVySW5zZXJ0ID0gZmFsc2U7XHJcbiAgICAgICAgcmV0dXJuIG5vdEVtcHR5O1xyXG4gICAgfSxcclxuXHJcbiAgICAvLyBJbnRlcm5hbCBmdW5jdGlvbiBmb3IgY3Jvc3MtYnJvd3NlciBldmVudCBjYW5jZWxsYXRpb24uXHJcbiAgICBfY2FuY2VsRXZlbnQ6IGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgaWYgKGUucHJldmVudERlZmF1bHQpIGUucHJldmVudERlZmF1bHQoKTtcclxuICAgICAgICBpZiAoZS5zdG9wUHJvcGFnYXRpb24pIGUuc3RvcFByb3BhZ2F0aW9uKCk7XHJcbiAgICAgICAgcmV0dXJuIGUucmV0dXJuVmFsdWUgPSBmYWxzZTtcclxuICAgIH0sXHJcblxyXG4gICAgLy8gQXZhaWxhYmxlIGtleSBjb21iaW5hdGlvbnMgYW5kIHRoZXNlIGludGVycHJldGFpb25zIGZvciBCQiBhcmVcclxuICAgIC8vIFRBQiAgICAgICAgICAgICAgLSBJbnNlcnQgVEFCIGNoYXJcclxuICAgIC8vIENUUkwtVEFCICAgICAgICAgLSBOZXh0IGZvcm0gZmllbGQgKHVzdWFsIFRBQilcclxuICAgIC8vIFNISUZULUFMVC1QQUdFVVAgLSBBZGQgYW4gQXR0YWNobWVudFxyXG4gICAgLy8gQUxULUVOVEVSICAgICAgICAtIFByZXZpZXdcclxuICAgIC8vIENUUkwtRU5URVIgICAgICAgLSBTdWJtaXRcclxuICAgIG9uS2V5UHJlc3M6IGZ1bmN0aW9uIChlLCB0eXBlKSB7XHJcbiAgICAgICAgLy8gVHJ5IHRvIG1hdGNoIGFsbCB0aGUgaG90IGtleXMuXHJcbiAgICAgICAgdmFyIGtleSA9IFN0cmluZy5mcm9tQ2hhckNvZGUoZS5rZXlDb2RlID8gZS5rZXlDb2RlIDogZS5jaGFyQ29kZSk7XHJcbiAgICAgICAgZm9yICh2YXIgaWQgaW4gdGhpcy50YWdzKSB7XHJcbiAgICAgICAgICAgIHZhciB0YWcgPSB0aGlzLnRhZ3NbaWRdO1xyXG4gICAgICAgICAgICAvLyBQcmVzc2VkIGNvbnRyb2wga2V5P1xyXG4gICAgICAgICAgICBpZiAodGFnLmN0cmxLZXkgJiYgIWVbdGFnLmN0cmxLZXkgKyBcIktleVwiXSkgY29udGludWU7XHJcbiAgICAgICAgICAgIC8vIFByZXNzZWQgbmVlZGVkIGtleT9cclxuICAgICAgICAgICAgaWYgKCF0YWcua2V5IHx8IGtleS50b1VwcGVyQ2FzZSgpICE9PSB0YWcua2V5LnRvVXBwZXJDYXNlKCkpIGNvbnRpbnVlO1xyXG4gICAgICAgICAgICAvLyBJbnNlcnRcclxuICAgICAgICAgICAgaWYgKGUudHlwZSA9PT0gXCJrZXlkb3duXCIpIHRoaXMuaW5zZXJ0VGFnKGlkKTtcclxuICAgICAgICAgICAgLy8gUmVzZXQgZXZlbnRcclxuICAgICAgICAgICAgcmV0dXJuIHRoaXMuX2NhbmNlbEV2ZW50KGUpO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgLy8gVGFiXHJcbiAgICAgICAgaWYgKHR5cGUgPT09ICdwcmVzcycgJiYgZS5rZXlDb2RlID09PSB0aGlzLlZLX1RBQiAmJiAhZS5zaGlmdEtleSAmJiAhZS5jdHJsS2V5ICYmICFlLmFsdEtleSkge1xyXG4gICAgICAgICAgICB0aGlzLmluc2VydEF0Q3Vyc29yKCdbdGFiXScpO1xyXG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fY2FuY2VsRXZlbnQoZSk7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICAvLyBDdHJsK1RhYlxyXG4gICAgICAgIGlmIChlLmtleUNvZGUgPT09IHRoaXMuVktfVEFCICYmICFlLnNoaWZ0S2V5ICYmIGUuY3RybEtleSAmJiAhZS5hbHRLZXkpIHtcclxuICAgICAgICAgICAgdGhpcy50ZXh0YXJlYS5mb3JtLnBvc3QuZm9jdXMoKTtcclxuICAgICAgICAgICAgcmV0dXJuIHRoaXMuX2NhbmNlbEV2ZW50KGUpO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgLy8gSG90IGtleXNcclxuICAgICAgICB2YXIgZm9ybSA9IHRoaXMudGV4dGFyZWEuZm9ybTtcclxuICAgICAgICB2YXIgc3VibWl0dGVyID0gbnVsbDtcclxuICAgICAgICBpZiAoZS5rZXlDb2RlID09PSB0aGlzLlZLX1BBR0VfVVAgJiYgZS5zaGlmdEtleSAmJiAhZS5jdHJsS2V5ICYmIGUuYWx0S2V5KSBzdWJtaXR0ZXIgPSBmb3JtLmFkZF9hdHRhY2htZW50X2JveDtcclxuICAgICAgICBpZiAoZS5rZXlDb2RlID09PSB0aGlzLlZLX0VOVEVSICYmICFlLnNoaWZ0S2V5ICYmICFlLmN0cmxLZXkgJiYgZS5hbHRLZXkpIHN1Ym1pdHRlciA9IGZvcm0ucHJldmlldztcclxuICAgICAgICBpZiAoZS5rZXlDb2RlID09PSB0aGlzLlZLX0VOVEVSICYmICFlLnNoaWZ0S2V5ICYmIGUuY3RybEtleSAmJiAhZS5hbHRLZXkpIHN1Ym1pdHRlciA9IGZvcm0ucG9zdDtcclxuICAgICAgICBpZiAoc3VibWl0dGVyKSB7XHJcbiAgICAgICAgICAgIHN1Ym1pdHRlci5jbGljaygpO1xyXG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fY2FuY2VsRXZlbnQoZSk7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICByZXR1cm4gdHJ1ZTtcclxuICAgIH0sXHJcblxyXG4gICAgLy8gQWRkcyBhIEJCIHRhZyB0byB0aGUgbGlzdFxyXG4gICAgYWRkVGFnOiBmdW5jdGlvbiAoaWQsIG9wZW4sIGNsb3NlLCBrZXksIGN0cmxLZXksIG11bHRpbGluZSkge1xyXG4gICAgICAgIGlmICghY3RybEtleSkgY3RybEtleSA9IFwiY3RybFwiO1xyXG4gICAgICAgIHZhciB0YWcgPSB7fTtcclxuICAgICAgICB0YWcuaWQgPSBpZDtcclxuICAgICAgICB0YWcub3BlbiA9IG9wZW47XHJcbiAgICAgICAgdGFnLmNsb3NlID0gY2xvc2U7XHJcbiAgICAgICAgdGFnLmtleSA9IGtleTtcclxuICAgICAgICB0YWcuY3RybEtleSA9IGN0cmxLZXk7XHJcbiAgICAgICAgdGFnLm11bHRpbGluZSA9IG11bHRpbGluZTtcclxuICAgICAgICB0YWcuZWx0ID0gdGhpcy50ZXh0YXJlYS5mb3JtW2lkXTtcclxuICAgICAgICB0aGlzLnRhZ3NbaWRdID0gdGFnO1xyXG4gICAgICAgIC8vIFNldHVwIGV2ZW50c1xyXG4gICAgICAgIHZhciBlbHQgPSB0YWcuZWx0O1xyXG4gICAgICAgIGlmIChlbHQpIHtcclxuICAgICAgICAgICAgdmFyIHRoID0gdGhpcztcclxuICAgICAgICAgICAgaWYgKGVsdC50eXBlICYmIGVsdC50eXBlLnRvVXBwZXJDYXNlKCkgPT09IFwiQlVUVE9OXCIpIHtcclxuICAgICAgICAgICAgICAgIGFkZEV2ZW50KGVsdCwgJ2NsaWNrJywgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAgICAgICAgIHRoLmluc2VydFRhZyhpZCk7XHJcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgaWYgKGVsdC50YWdOYW1lICYmIGVsdC50YWdOYW1lLnRvVXBwZXJDYXNlKCkgPT09IFwiU0VMRUNUXCIpIHtcclxuICAgICAgICAgICAgICAgIGFkZEV2ZW50KGVsdCwgJ2NoYW5nZScsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgICAgICB0aC5pbnNlcnRUYWcoaWQpO1xyXG4gICAgICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTtcclxuICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgaWYgKGlkICYmIGlkLmluZGV4T2YoJ18nKSAhPT0gMCkgcmV0dXJuIGFsZXJ0KFwiYWRkVGFnKCdcIiArIGlkICsgXCInKTogbm8gc3VjaCBlbGVtZW50IGluIHRoZSBmb3JtXCIpO1xyXG4gICAgICAgIH1cclxuICAgIH0sXHJcblxyXG4gICAgLy8gSW5zZXJ0cyB0aGUgdGFnIHdpdGggc3BlY2lmaWVkIElEXHJcbiAgICBpbnNlcnRUYWc6IGZ1bmN0aW9uIChpZCkge1xyXG4gICAgICAgIC8vIEZpbmQgdGFnXHJcbiAgICAgICAgdmFyIHRhZyA9IHRoaXMudGFnc1tpZF07XHJcbiAgICAgICAgaWYgKCF0YWcpIHJldHVybiBhbGVydChcIlVua25vd24gdGFnIElEOiBcIiArIGlkKTtcclxuXHJcbiAgICAgICAgLy8gT3BlbiB0YWcgaXMgZ2VuZXJhdGVkIGJ5IGNhbGxiYWNrP1xyXG4gICAgICAgIHZhciBvcCA9IHRhZy5vcGVuO1xyXG4gICAgICAgIGlmICh0eXBlb2YgKHRhZy5vcGVuKSA9PT0gXCJmdW5jdGlvblwiKSBvcCA9IHRhZy5vcGVuKHRhZy5lbHQpO1xyXG4gICAgICAgIHZhciBjbCA9IHRhZy5jbG9zZSAhPT0gbnVsbCA/IHRhZy5jbG9zZSA6IFwiL1wiICsgb3A7XHJcblxyXG4gICAgICAgIC8vIFVzZSBcIltcIiBpZiBuZWVkZWRcclxuICAgICAgICBpZiAob3AuY2hhckF0KDApICE9PSB0aGlzLkJSS19PUCkgb3AgPSB0aGlzLkJSS19PUCArIG9wICsgdGhpcy5CUktfQ0w7XHJcbiAgICAgICAgaWYgKGNsICYmIGNsLmNoYXJBdCgwKSAhPT0gdGhpcy5CUktfT1ApIGNsID0gdGhpcy5CUktfT1AgKyBjbCArIHRoaXMuQlJLX0NMO1xyXG5cclxuICAgICAgICB0aGlzLnN1cnJvdW5kKG9wLCBjbCwgIXRhZy5tdWx0aWxpbmUgPyBudWxsIDogdGFnLm11bHRpbGluZSA9PT0gdHJ1ZSA/IHRoaXMuX3ByZXBhcmVNdWx0aWxpbmUgOiB0YWcubXVsdGlsaW5lKTtcclxuICAgIH0sXHJcblxyXG4gICAgX3ByZXBhcmVNdWx0aWxpbmU6IGZ1bmN0aW9uICh0ZXh0KSB7XHJcbiAgICAgICAgdGV4dCA9IHRleHQucmVwbGFjZSgvXFxzKyQvLCAnJyk7XHJcbiAgICAgICAgdGV4dCA9IHRleHQucmVwbGFjZSgvXihbIFxcdF0qXFxyP1xcbikrLywgJycpO1xyXG4gICAgICAgIGlmICh0ZXh0LmluZGV4T2YoXCJcXG5cIikgPj0gMCkgdGV4dCA9IFwiXFxuXCIgKyB0ZXh0ICsgXCJcXG5cIjtcclxuICAgICAgICByZXR1cm4gdGV4dDtcclxuICAgIH1cclxufTtcclxuXHJcbi8vIEVtdWxhdGlvbiBvZiBpbm5lclRleHQgZm9yIE1vemlsbGEuXHJcbmlmICh3aW5kb3cuSFRNTEVsZW1lbnQgJiYgd2luZG93LkhUTUxFbGVtZW50LnByb3RvdHlwZS5fX2RlZmluZVNldHRlcl9fKSB7XHJcbiAgICBIVE1MRWxlbWVudC5wcm90b3R5cGUuX19kZWZpbmVTZXR0ZXJfXyhcImlubmVyVGV4dFwiLCBmdW5jdGlvbiAoc1RleHQpIHtcclxuICAgICAgICB0aGlzLmlubmVySFRNTCA9IHNUZXh0LnJlcGxhY2UoL1xcJi9nLCBcIiZhbXA7XCIpLnJlcGxhY2UoLzwvZywgXCImbHQ7XCIpLnJlcGxhY2UoLz4vZywgXCImZ3Q7XCIpO1xyXG4gICAgfSk7XHJcbiAgICBIVE1MRWxlbWVudC5wcm90b3R5cGUuX19kZWZpbmVHZXR0ZXJfXyhcImlubmVyVGV4dFwiLCBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgdmFyIHIgPSB0aGlzLm93bmVyRG9jdW1lbnQuY3JlYXRlUmFuZ2UoKTtcclxuICAgICAgICByLnNlbGVjdE5vZGVDb250ZW50cyh0aGlzKTtcclxuICAgICAgICByZXR1cm4gci50b1N0cmluZygpO1xyXG4gICAgfSk7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIEFkZFNlbGVjdGVkVGV4dChCQk9wZW4sIEJCQ2xvc2UpIHtcclxuICAgIGlmIChkb2N1bWVudC5wb3N0Lm1lc3NhZ2UuY2FyZXRQb3MpIGRvY3VtZW50LnBvc3QubWVzc2FnZS5jYXJldFBvcy50ZXh0ID0gQkJPcGVuICsgZG9jdW1lbnQucG9zdC5tZXNzYWdlLmNhcmV0UG9zLnRleHQgKyBCQkNsb3NlOyBlbHNlIGRvY3VtZW50LnBvc3QubWVzc2FnZS52YWx1ZSArPSBCQk9wZW4gKyBCQkNsb3NlO1xyXG4gICAgZG9jdW1lbnQucG9zdC5tZXNzYWdlLmZvY3VzKClcclxufVxyXG5cclxuZnVuY3Rpb24gSW5zZXJ0QkJDb2RlKEJCY29kZSkge1xyXG4gICAgQWRkU2VsZWN0ZWRUZXh0KCdbJyArIEJCY29kZSArICddJywgJ1svJyArIEJCY29kZSArICddJyk7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIHN0b3JlQ2FyZXQodGV4dEVsKSB7XHJcbiAgICBpZiAodGV4dEVsLmNyZWF0ZVRleHRSYW5nZSkgdGV4dEVsLmNhcmV0UG9zID0gZG9jdW1lbnQuc2VsZWN0aW9uLmNyZWF0ZVJhbmdlKCkuZHVwbGljYXRlKCk7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIGluaXRQb3N0QkJDb2RlKGNvbnRleHQpIHtcclxuICAgICQoJ3NwYW4ucG9zdC1ocicsIGNvbnRleHQpLmh0bWwoJzxociBhbGlnbj1cImxlZnRcIiAvPicpO1xyXG4gICAgaW5pdENvZGVzKGNvbnRleHQpO1xyXG4gICAgaW5pdFF1b3Rlcyhjb250ZXh0KTtcclxuICAgIGluaXRFeHRlcm5hbExpbmtzKGNvbnRleHQpO1xyXG4gICAgaW5pdFBvc3RJbWFnZXMoY29udGV4dCk7XHJcbiAgICBpbml0U3BvaWxlcnMoY29udGV4dCk7XHJcbiAgICBpbml0TWVkaWEoY29udGV4dCk7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIGluaXRDb2Rlcyhjb250ZXh0KSB7XHJcbiAgICAkKCdkaXYuYy1ib2R5JywgY29udGV4dCkuZWFjaChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgdmFyICRjID0gJCh0aGlzKTtcclxuICAgICAgICAkYy5iZWZvcmUoJzxkaXYgY2xhc3M9XCJjLWhlYWRcIj48Yj4nICsgYmJsWydjb2RlJ10gKyAnOjwvYj48L2Rpdj4nKTtcclxuICAgIH0pO1xyXG59XHJcblxyXG5mdW5jdGlvbiBpbml0UXVvdGVzKGNvbnRleHQpIHtcclxuICAgICQoJ2Rpdi5xJywgY29udGV4dCkuZWFjaChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgdmFyICRxID0gJCh0aGlzKTtcclxuICAgICAgICB2YXIgbmFtZSA9ICQodGhpcykuYXR0cignaGVhZCcpO1xyXG4gICAgICAgIHZhciBxX3RpdGxlID0gKG5hbWUgPyAnPGI+JyArIG5hbWUgKyAnPC9iPiAnICsgYmJsWyd3cm90ZSddICsgJzonIDogJzxiPicgKyBiYmxbJ3F1b3RlJ10gKyAnPC9iPicpO1xyXG4gICAgICAgIGlmIChxdW90ZWRfcGlkID0gJHEuY2hpbGRyZW4oJ3UucS1wb3N0OmZpcnN0JykudGV4dCgpKSB7XHJcbiAgICAgICAgICAgIHZhciBvbl90aGlzX3BhZ2UgPSAkKCcjcG9zdF8nICsgcXVvdGVkX3BpZCkubGVuZ3RoO1xyXG4gICAgICAgICAgICB2YXIgaHJlZiA9IChvbl90aGlzX3BhZ2UpID8gJyMnICsgcXVvdGVkX3BpZCA6ICcuL3ZpZXd0b3BpYy5waHA/cD0nICsgcXVvdGVkX3BpZCArICcjJyArIHF1b3RlZF9waWQ7XHJcbiAgICAgICAgICAgIHFfdGl0bGUgKz0gJyA8YSBocmVmPVwiJyArIGhyZWYgKyAnXCIgdGl0bGU9XCInICsgYmJsWydxdW90ZWRfcG9zdCddICsgJ1wiPjxpbWcgc3JjPVwiJyArIGJiX3VybCArICdzdHlsZXMvdGVtcGxhdGVzL2RlZmF1bHQvaW1hZ2VzL2ljb25fbGF0ZXN0X3JlcGx5LmdpZlwiIGNsYXNzPVwiaWNvbjJcIiBhbHQ9XCJcIiAvPjwvYT4nO1xyXG4gICAgICAgIH1cclxuICAgICAgICAkcS5iZWZvcmUoJzxkaXYgY2xhc3M9XCJxLWhlYWRcIj4nICsgcV90aXRsZSArICc8L2Rpdj4nKTtcclxuICAgIH0pO1xyXG59XHJcblxyXG5mdW5jdGlvbiBpbml0UG9zdEltYWdlcyhjb250ZXh0KSB7XHJcbiAgICBpZiAoaGlkZVBvc3RJbWcpIHJldHVybjtcclxuICAgIHZhciAkaW5fc3BvaWxlcnMgPSAkKCdkaXYuc3AtYm9keSB2YXIucG9zdEltZycsIGNvbnRleHQpO1xyXG4gICAgJCgndmFyLnBvc3RJbWcnLCBjb250ZXh0KS5ub3QoJGluX3Nwb2lsZXJzKS5lYWNoKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICB2YXIgJHYgPSAkKHRoaXMpO1xyXG4gICAgICAgIHZhciBzcmMgPSAkdi5hdHRyKCd0aXRsZScpO1xyXG4gICAgICAgIHZhciAkaW1nID0gJCgnPGltZyBzcmM9XCInICsgc3JjICsgJ1wiIGNsYXNzPVwiJyArICR2LmF0dHIoJ2NsYXNzJykgKyAnXCIgYWx0PVwicGljXCIgLz4nKTtcclxuICAgICAgICAkaW1nID0gZml4UG9zdEltYWdlKCRpbWcpO1xyXG4gICAgICAgIHZhciBtYXhXID0gKCR2Lmhhc0NsYXNzKCdwb3N0SW1nQWxpZ25lZCcpKSA/IHBvc3RJbWdBbGlnbmVkX01heFdpZHRoIDogcG9zdEltZ19NYXhXaWR0aDtcclxuICAgICAgICAkaW1nLmJpbmQoJ2NsaWNrJywgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICByZXR1cm4gaW1nRml0KHRoaXMsIG1heFcpO1xyXG4gICAgICAgIH0pO1xyXG4gICAgICAgIGlmICh1c2VyLm9wdF9qcy5pX2FmdF9sKSB7XHJcbiAgICAgICAgICAgICQoJyNwcmVsb2FkJykuYXBwZW5kKCRpbWcpO1xyXG4gICAgICAgICAgICB2YXIgbG9hZGluZ19pY29uID0gJzxhIGhyZWY9XCInICsgc3JjICsgJ1wiIHRhcmdldD1cIl9ibGFua1wiPjxpbWcgc3JjPVwiJyArIGJiX3VybCArICdzdHlsZXMvaW1hZ2VzL3BpY19sb2FkaW5nLmdpZlwiIGFsdD1cIlwiIC8+PC9hPic7XHJcbiAgICAgICAgICAgICR2Lmh0bWwobG9hZGluZ19pY29uKTtcclxuICAgICAgICAgICAgaWYgKCQuYnJvd3Nlci5tc2llKSB7XHJcbiAgICAgICAgICAgICAgICAkdi5hZnRlcignPHdicj4nKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAkaW1nLm9uZSgnbG9hZCcsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgIGltZ0ZpdCh0aGlzLCBtYXhXKTtcclxuICAgICAgICAgICAgICAgICR2LmVtcHR5KCkuYXBwZW5kKHRoaXMpO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAkaW1nLm9uZSgnbG9hZCcsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgIGltZ0ZpdCh0aGlzLCBtYXhXKVxyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgJHYuZW1wdHkoKS5hcHBlbmQoJGltZyk7XHJcbiAgICAgICAgICAgIGlmICgkLmJyb3dzZXIubXNpZSkge1xyXG4gICAgICAgICAgICAgICAgJHYuYWZ0ZXIoJzx3YnI+Jyk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9XHJcbiAgICB9KTtcclxufVxyXG5cclxuZnVuY3Rpb24gaW5pdFNwb2lsZXJzKGNvbnRleHQpIHtcclxuICAgICQoJ2Rpdi5zcC1ib2R5JywgY29udGV4dCkuZWFjaChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgdmFyICRzcF9ib2R5ID0gJCh0aGlzKTtcclxuICAgICAgICB2YXIgbmFtZSA9ICQudHJpbSh0aGlzLnRpdGxlKSB8fCAnJyArIGJibFsnc3BvaWxlcl9oZWFkJ10gKyAnJztcclxuICAgICAgICB0aGlzLnRpdGxlID0gJyc7XHJcbiAgICAgICAgdmFyICRzcF9oZWFkID0gJCgnPGRpdiBjbGFzcz1cInNwLWhlYWQgZm9sZGVkIGNsaWNrYWJsZVwiPicgKyBuYW1lICsgJzwvZGl2PicpO1xyXG4gICAgICAgICRzcF9oZWFkLmluc2VydEJlZm9yZSgkc3BfYm9keSkuY2xpY2soZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgaWYgKCEkc3BfYm9keS5oYXNDbGFzcygnaW5pdGVkJykpIHtcclxuICAgICAgICAgICAgICAgIGluaXRQb3N0SW1hZ2VzKCRzcF9ib2R5KTtcclxuICAgICAgICAgICAgICAgIHZhciAkc3BfZm9sZF9idG4gPSAkKCc8ZGl2IGNsYXNzPVwic3AtZm9sZCBjbGlja2FibGVcIj5bJyArIGJibFsnc3BvaWxlcl9jbG9zZSddICsgJ108L2Rpdj4nKS5jbGljayhmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgJC5zY3JvbGxUbygkc3BfaGVhZCwge2R1cmF0aW9uOiAyMDAsIGF4aXM6ICd5Jywgb2Zmc2V0OiAtMjAwfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgJHNwX2hlYWQuY2xpY2soKS5hbmltYXRlKHtvcGFjaXR5OiAwLjF9LCA1MDApLmFuaW1hdGUoe29wYWNpdHk6IDF9LCA3MDApO1xyXG4gICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAkc3BfYm9keS5wcmVwZW5kKCc8ZGl2IGNsYXNzPVwiY2xlYXJcIj48L2Rpdj4nKS5hcHBlbmQoJzxkaXYgY2xhc3M9XCJjbGVhclwiPjwvZGl2PicpLmFwcGVuZCgkc3BfZm9sZF9idG4pLmFkZENsYXNzKCdpbml0ZWQnKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICBpZiAoZS5zaGlmdEtleSkge1xyXG4gICAgICAgICAgICAgICAgZS5zdG9wUHJvcGFnYXRpb24oKTtcclxuICAgICAgICAgICAgICAgIGUuc2hpZnRLZXkgPSBmYWxzZTtcclxuICAgICAgICAgICAgICAgIHZhciBmb2xkID0gJCh0aGlzKS5oYXNDbGFzcygndW5mb2xkZWQnKTtcclxuICAgICAgICAgICAgICAgICQoJ2Rpdi5zcC1oZWFkJywgJCgkc3BfYm9keS5wYXJlbnRzKCd0ZCcpWzBdKSkuZmlsdGVyKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgICAgICByZXR1cm4gJCh0aGlzKS5oYXNDbGFzcygndW5mb2xkZWQnKSA/IGZvbGQgOiAhZm9sZFxyXG4gICAgICAgICAgICAgICAgfSkuY2xpY2soKTtcclxuICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgICQodGhpcykudG9nZ2xlQ2xhc3MoJ3VuZm9sZGVkJyk7XHJcbiAgICAgICAgICAgICAgICAkc3BfYm9keS5zbGlkZVRvZ2dsZSgnZmFzdCcpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcbiAgICB9KTtcclxufVxyXG5cclxuZnVuY3Rpb24gaW5pdEV4dGVybmFsTGlua3MoY29udGV4dCkge1xyXG4gICAgdmFyIGNvbnRleHQgPSBjb250ZXh0IHx8ICdib2R5JztcclxuICAgIGlmIChFeHRlcm5hbExpbmtzX0luTmV3V2luZG93KSB7XHJcbiAgICAgICAgJChcImEucG9zdExpbms6bm90KFtocmVmKj0nXCIgKyB3aW5kb3cubG9jYXRpb24uaG9zdG5hbWUgKyBcIi8nXSlcIiwgY29udGV4dCkuYXR0cih7dGFyZ2V0OiAnX2JsYW5rJ30pO1xyXG4gICAgfVxyXG59XHJcblxyXG5mdW5jdGlvbiBmaXhQb3N0SW1hZ2UoJGltZykge1xyXG4gICAgdmFyIGJhbm5lZF9pbWFnZV9ob3N0cyA9IC9pbWFnZWJhbmFuYXxoaWRlYmVoaW5kL2k7XHJcbiAgICB2YXIgc3JjID0gJGltZ1swXS5zcmM7XHJcbiAgICBpZiAoc3JjLm1hdGNoKGJhbm5lZF9pbWFnZV9ob3N0cykpIHtcclxuICAgICAgICAkaW1nLndyYXAoJzxhIGhyZWY9XCInICsgdGhpcy5zcmMgKyAnXCIgdGFyZ2V0PVwiX2JsYW5rXCI+PC9hPicpLmF0dHIoe1xyXG4gICAgICAgICAgICBzcmM6IFwiXCIgKyBiYl91cmwgKyBcInN0eWxlcy9pbWFnZXMvc21pbGVzL3RyX29vcHMuZ2lmXCIsIHRpdGxlOiBcIlwiICsgYmJsWydzY3JfcnVsZXMnXSArIFwiXCJcclxuICAgICAgICB9KTtcclxuICAgIH1cclxuICAgIHJldHVybiAkaW1nO1xyXG59XHJcblxyXG5mdW5jdGlvbiBpbml0TWVkaWEoY29udGV4dCkge1xyXG4gICAgdmFyIGFwb3N0TGluayA9ICQoJ2EucG9zdExpbmsnLCBjb250ZXh0KTtcclxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgYXBvc3RMaW5rLmxlbmd0aDsgaSsrKSB7XHJcbiAgICAgICAgdmFyIGxpbmsgPSBhcG9zdExpbmtbaV07XHJcbiAgICAgICAgaWYgKHR5cGVvZiBsaW5rLmhyZWYgIT09ICdzdHJpbmcnKSB7XHJcbiAgICAgICAgICAgIGNvbnRpbnVlO1xyXG4gICAgICAgIH1cclxuICAgICAgICBpZiAoL15odHRwKD86c3wpOlxcL1xcL3d3dy55b3V0dWJlLmNvbVxcL3dhdGNoXFw/KC4qKT8oJj92PShbYS16MC05XFwtX10rKSkoLiopP3xodHRwOlxcL1xcL3lvdXR1LmJlXFwvLisvaS50ZXN0KGxpbmsuaHJlZikpIHtcclxuICAgICAgICAgICAgdmFyIGEgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdzcGFuJyk7XHJcbiAgICAgICAgICAgIGEuY2xhc3NOYW1lID0gJ1lUTGluayc7XHJcbiAgICAgICAgICAgIGEuaW5uZXJIVE1MID0gJzxzcGFuIHRpdGxlPVwiJyArIGJibFsncGxheV9vbiddICsgJ1wiIGNsYXNzPVwiWVRMaW5rQnV0dG9uXCI+JiM5NjU4Ozwvc3Bhbj4nO1xyXG4gICAgICAgICAgICB3aW5kb3cuYWRkRXZlbnQoYSwgJ2NsaWNrJywgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgICAgIHZhciB2aHJlZiA9IGUudGFyZ2V0Lm5leHRTaWJsaW5nLmhyZWYucmVwbGFjZSgvXmh0dHAoPzpzfCk6XFwvXFwvd3d3LnlvdXR1YmUuY29tXFwvd2F0Y2hcXD8oLiopPygmP3Y9KFthLXowLTlcXC1fXSspKSguKik/fGh0dHA6XFwvXFwveW91dHUuYmVcXC8vaWcsIFwiaHR0cDovL3d3dy55b3V0dWJlLmNvbS9lbWJlZC8kM1wiKTtcclxuICAgICAgICAgICAgICAgIHZhciB0ZXh0ID0gZS50YXJnZXQubmV4dFNpYmxpbmcuaW5uZXJUZXh0ICE9PSBcIlwiID8gZS50YXJnZXQubmV4dFNpYmxpbmcuaW5uZXJUZXh0IDogZS50YXJnZXQubmV4dFNpYmxpbmcuaHJlZjtcclxuICAgICAgICAgICAgICAgICQoJyNQYW5lbF95b3V0dWJlJykucmVtb3ZlKCk7XHJcbiAgICAgICAgICAgICAgICB5cGFuZWwoJ3lvdXR1YmUnLCB7XHJcbiAgICAgICAgICAgICAgICAgICAgdGl0bGU6ICc8Yj4nICsgdGV4dCArICc8L2I+JyxcclxuICAgICAgICAgICAgICAgICAgICByZXNpemluZzogMCxcclxuICAgICAgICAgICAgICAgICAgICB3aWR0aDogODYyLFxyXG4gICAgICAgICAgICAgICAgICAgIGhlaWdodDogNTUwLFxyXG4gICAgICAgICAgICAgICAgICAgIGNvbnRlbnQ6ICc8aWZyYW1lIHdpZHRoPVwiODUzXCIgaGVpZ2h0PVwiNDkzXCIgZnJhbWVib3JkZXI9XCIwXCIgYWxsb3dmdWxsc2NyZWVuPVwiXCIgc3JjPVwiJyArIHZocmVmICsgJz93bW9kZT1vcGFxdWVcIj48L2lmcmFtZT4nXHJcbiAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgIGxpbmsucGFyZW50Tm9kZS5pbnNlcnRCZWZvcmUoYSwgbGluayk7XHJcbiAgICAgICAgICAgIGEuYXBwZW5kQ2hpbGQobGluayk7XHJcbiAgICAgICAgfVxyXG4gICAgfVxyXG59XHJcblxyXG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbiAoKSB7XHJcbiAgICAkKCdkaXYucG9zdF93cmFwLCBkaXYuc2lnbmF0dXJlJykuZWFjaChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgaW5pdFBvc3RCQkNvZGUoJCh0aGlzKSlcclxuICAgIH0pO1xyXG59KTtcclxuIiwiLypcclxuICogVG9ycmVudFBpZXIg4oCTIEJ1bGwtcG93ZXJlZCBCaXRUb3JyZW50IHRyYWNrZXIgZW5naW5lXHJcbiAqXHJcbiAqIEBjb3B5cmlnaHQgQ29weXJpZ2h0IChjKSAyMDA1LTIwMjQgVG9ycmVudFBpZXIgKGh0dHBzOi8vdG9ycmVudHBpZXIuY29tKVxyXG4gKiBAbGluayAgICAgIGh0dHBzOi8vZ2l0aHViLmNvbS90b3JyZW50cGllci90b3JyZW50cGllciBmb3IgdGhlIGNhbm9uaWNhbCBzb3VyY2UgcmVwb3NpdG9yeVxyXG4gKiBAbGljZW5zZSAgIGh0dHBzOi8vZ2l0aHViLmNvbS90b3JyZW50cGllci90b3JyZW50cGllci9ibG9iL21hc3Rlci9MSUNFTlNFIE1JVCBMaWNlbnNlXHJcbiAqL1xyXG5cclxuLy8gcHJvdG90eXBlICRcclxuZnVuY3Rpb24gJHAoKSB7XHJcbiAgICB2YXIgZWxlbWVudHMgPSBbXTtcclxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgYXJndW1lbnRzLmxlbmd0aDsgaSsrKSB7XHJcbiAgICAgICAgdmFyIGVsZW1lbnQgPSBhcmd1bWVudHNbaV07XHJcbiAgICAgICAgaWYgKHR5cGVvZiBlbGVtZW50ID09PSAnc3RyaW5nJykgZWxlbWVudCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGVsZW1lbnQpO1xyXG4gICAgICAgIGlmIChhcmd1bWVudHMubGVuZ3RoID09PSAxKSByZXR1cm4gZWxlbWVudDtcclxuICAgICAgICBlbGVtZW50cy5wdXNoKGVsZW1lbnQpO1xyXG4gICAgfVxyXG4gICAgcmV0dXJuIGVsZW1lbnRzO1xyXG59XHJcblxyXG5mdW5jdGlvbiBhZGRFdmVudChvYmosIHR5cGUsIGZuKSB7XHJcbiAgICBpZiAob2JqLmFkZEV2ZW50TGlzdGVuZXIpIHtcclxuICAgICAgICBvYmouYWRkRXZlbnRMaXN0ZW5lcih0eXBlLCBmbiwgZmFsc2UpO1xyXG4gICAgICAgIEV2ZW50Q2FjaGUuYWRkKG9iaiwgdHlwZSwgZm4pO1xyXG4gICAgfSBlbHNlIGlmIChvYmouYXR0YWNoRXZlbnQpIHtcclxuICAgICAgICBvYmpbXCJlXCIgKyB0eXBlICsgZm5dID0gZm47XHJcbiAgICAgICAgb2JqW3R5cGUgKyBmbl0gPSBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgIG9ialtcImVcIiArIHR5cGUgKyBmbl0od2luZG93LmV2ZW50KTtcclxuICAgICAgICB9O1xyXG4gICAgICAgIG9iai5hdHRhY2hFdmVudChcIm9uXCIgKyB0eXBlLCBvYmpbdHlwZSArIGZuXSk7XHJcbiAgICAgICAgRXZlbnRDYWNoZS5hZGQob2JqLCB0eXBlLCBmbik7XHJcbiAgICB9IGVsc2Uge1xyXG4gICAgICAgIG9ialtcIm9uXCIgKyB0eXBlXSA9IG9ialtcImVcIiArIHR5cGUgKyBmbl07XHJcbiAgICB9XHJcbn1cclxuXHJcbnZhciBFdmVudENhY2hlID0gZnVuY3Rpb24gKCkge1xyXG4gICAgdmFyIGxpc3RFdmVudHMgPSBbXTtcclxuICAgIHJldHVybiB7XHJcbiAgICAgICAgbGlzdEV2ZW50czogbGlzdEV2ZW50cywgYWRkOiBmdW5jdGlvbiAobm9kZSwgc0V2ZW50TmFtZSwgZkhhbmRsZXIpIHtcclxuICAgICAgICAgICAgbGlzdEV2ZW50cy5wdXNoKGFyZ3VtZW50cyk7XHJcbiAgICAgICAgfSwgZmx1c2g6IGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgdmFyIGksIGl0ZW07XHJcbiAgICAgICAgICAgIGZvciAoaSA9IGxpc3RFdmVudHMubGVuZ3RoIC0gMTsgaSA+PSAwOyBpID0gaSAtIDEpIHtcclxuICAgICAgICAgICAgICAgIGl0ZW0gPSBsaXN0RXZlbnRzW2ldO1xyXG4gICAgICAgICAgICAgICAgaWYgKGl0ZW1bMF0ucmVtb3ZlRXZlbnRMaXN0ZW5lcikge1xyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW1bMF0ucmVtb3ZlRXZlbnRMaXN0ZW5lcihpdGVtWzFdLCBpdGVtWzJdLCBpdGVtWzNdKTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIGlmIChpdGVtWzFdLnN1YnN0cmluZygwLCAyKSAhPT0gXCJvblwiKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbVsxXSA9IFwib25cIiArIGl0ZW1bMV07XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICBpZiAoaXRlbVswXS5kZXRhY2hFdmVudCkge1xyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW1bMF0uZGV0YWNoRXZlbnQoaXRlbVsxXSwgaXRlbVsyXSk7XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICBpdGVtWzBdW2l0ZW1bMV1dID0gbnVsbDtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuICAgIH07XHJcbn0oKTtcclxuaWYgKGRvY3VtZW50LmFsbCkge1xyXG4gICAgYWRkRXZlbnQod2luZG93LCAndW5sb2FkJywgRXZlbnRDYWNoZS5mbHVzaCk7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIGltZ0ZpdChpbWcsIG1heFcpIHtcclxuICAgIGltZy50aXRsZSA9ICfQoNCw0LfQvNC10YDRiyDQuNC30L7QsdGA0LDQttC10L3QuNGPOiAnICsgaW1nLndpZHRoICsgJyB4ICcgKyBpbWcuaGVpZ2h0O1xyXG4gICAgaWYgKHR5cGVvZiAoaW1nLm5hdHVyYWxIZWlnaHQpID09PSAndW5kZWZpbmVkJykge1xyXG4gICAgICAgIGltZy5uYXR1cmFsSGVpZ2h0ID0gaW1nLmhlaWdodDtcclxuICAgICAgICBpbWcubmF0dXJhbFdpZHRoID0gaW1nLndpZHRoO1xyXG4gICAgfVxyXG4gICAgaWYgKGltZy53aWR0aCA+IG1heFcpIHtcclxuICAgICAgICBpbWcuaGVpZ2h0ID0gTWF0aC5yb3VuZCgobWF4VyAvIGltZy53aWR0aCkgKiBpbWcuaGVpZ2h0KTtcclxuICAgICAgICBpbWcud2lkdGggPSBtYXhXO1xyXG4gICAgICAgIGltZy50aXRsZSA9ICfQndCw0LbQvNC40YLQtSDQvdCwINC40LfQvtCx0YDQsNC20LXQvdC40LUsINGH0YLQvtCx0Ysg0L/QvtGB0LzQvtGC0YDQtdGC0Ywg0LXQs9C+INCyINC/0L7Qu9C90YvQuSDRgNCw0LfQvNC10YAnO1xyXG4gICAgICAgIGltZy5zdHlsZS5jdXJzb3IgPSAnbW92ZSc7XHJcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgfSBlbHNlIGlmIChpbWcud2lkdGggPT09IG1heFcgJiYgaW1nLndpZHRoIDwgaW1nLm5hdHVyYWxXaWR0aCkge1xyXG4gICAgICAgIGltZy5oZWlnaHQgPSBpbWcubmF0dXJhbEhlaWdodDtcclxuICAgICAgICBpbWcud2lkdGggPSBpbWcubmF0dXJhbFdpZHRoO1xyXG4gICAgICAgIGltZy50aXRsZSA9ICfQoNCw0LfQvNC10YDRiyDQuNC30L7QsdGA0LDQttC10L3QuNGPOiAnICsgaW1nLm5hdHVyYWxXaWR0aCArICcgeCAnICsgaW1nLm5hdHVyYWxIZWlnaHQ7XHJcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgfSBlbHNlIHtcclxuICAgICAgICByZXR1cm4gdHJ1ZTtcclxuICAgIH1cclxufVxyXG5cclxuZnVuY3Rpb24gdG9nZ2xlX2Jsb2NrKGlkKSB7XHJcbiAgICB2YXIgZWwgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCk7XHJcbiAgICBlbC5zdHlsZS5kaXNwbGF5ID0gKGVsLnN0eWxlLmRpc3BsYXkgPT09ICdub25lJykgPyAnJyA6ICdub25lJztcclxufVxyXG5cclxuZnVuY3Rpb24gdG9nZ2xlX2Rpc2FibGVkKGlkLCB2YWwpIHtcclxuICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGlkKS5kaXNhYmxlZCA9ICh2YWwpID8gMCA6IDE7XHJcbn1cclxuXHJcbmZ1bmN0aW9uIHJhbmQobWluLCBtYXgpIHtcclxuICAgIHJldHVybiBtaW4gKyBNYXRoLmZsb29yKChtYXggLSBtaW4gKyAxKSAqIE1hdGgucmFuZG9tKCkpO1xyXG59XHJcblxyXG4vLyBDb29raWUgZnVuY3Rpb25zXHJcbi8qKlxyXG4gKiBuYW1lICAgICAgIE5hbWUgb2YgdGhlIGNvb2tpZVxyXG4gKiB2YWx1ZSAgICAgIFZhbHVlIG9mIHRoZSBjb29raWVcclxuICogW2RheXNdICAgICBOdW1iZXIgb2YgZGF5cyB0byByZW1haW4gYWN0aXZlIChkZWZhdWx0OiBlbmQgb2YgY3VycmVudCBzZXNzaW9uKVxyXG4gKiBbcGF0aF0gICAgIFBhdGggd2hlcmUgdGhlIGNvb2tpZSBpcyB2YWxpZCAoZGVmYXVsdDogcGF0aCBvZiBjYWxsaW5nIGRvY3VtZW50KVxyXG4gKiBbZG9tYWluXSAgIERvbWFpbiB3aGVyZSB0aGUgY29va2llIGlzIHZhbGlkXHJcbiAqICAgICAgICAgICAgKGRlZmF1bHQ6IGRvbWFpbiBvZiBjYWxsaW5nIGRvY3VtZW50KVxyXG4gKiBbc2VjdXJlXSAgIEJvb2xlYW4gdmFsdWUgaW5kaWNhdGluZyBpZiB0aGUgY29va2llIHRyYW5zbWlzc2lvbiByZXF1aXJlcyBhXHJcbiAqICAgICAgICAgICAgc2VjdXJlIHRyYW5zbWlzc2lvblxyXG4gKi9cclxuZnVuY3Rpb24gc2V0Q29va2llKG5hbWUsIHZhbHVlLCBkYXlzLCBwYXRoLCBkb21haW4sIHNlY3VyZSkge1xyXG4gICAgaWYgKGRheXMgIT09ICdTRVNTSU9OJykge1xyXG4gICAgICAgIHZhciBkYXRlID0gbmV3IERhdGUoKTtcclxuICAgICAgICBkYXlzID0gZGF5cyB8fCAzNjU7XHJcbiAgICAgICAgZGF0ZS5zZXRUaW1lKGRhdGUuZ2V0VGltZSgpICsgZGF5cyAqIDI0ICogNjAgKiA2MCAqIDEwMDApO1xyXG4gICAgICAgIHZhciBleHBpcmVzID0gZGF0ZS50b0dNVFN0cmluZygpO1xyXG4gICAgfSBlbHNlIHtcclxuICAgICAgICB2YXIgZXhwaXJlcyA9ICcnO1xyXG4gICAgfVxyXG5cclxuICAgIGRvY3VtZW50LmNvb2tpZSA9IG5hbWUgKyAnPScgKyBlbmNvZGVVUkkodmFsdWUpICsgKChleHBpcmVzKSA/ICc7IGV4cGlyZXM9JyArIGV4cGlyZXMgOiAnJykgKyAoKHBhdGgpID8gJzsgcGF0aD0nICsgcGF0aCA6ICgoY29va2llUGF0aCkgPyAnOyBwYXRoPScgKyBjb29raWVQYXRoIDogJycpKSArICgoZG9tYWluKSA/ICc7IGRvbWFpbj0nICsgZG9tYWluIDogKChjb29raWVEb21haW4pID8gJzsgZG9tYWluPScgKyBjb29raWVEb21haW4gOiAnJykpICsgKChzZWN1cmUpID8gJzsgc2VjdXJlJyA6ICgoY29va2llU2VjdXJlKSA/ICc7IHNlY3VyZScgOiAnJykpO1xyXG59XHJcblxyXG4vKipcclxuICogUmV0dXJucyBhIHN0cmluZyBjb250YWluaW5nIHZhbHVlIG9mIHNwZWNpZmllZCBjb29raWUsXHJcbiAqICAgb3IgbnVsbCBpZiBjb29raWUgZG9lcyBub3QgZXhpc3QuXHJcbiAqL1xyXG5mdW5jdGlvbiBnZXRDb29raWUobmFtZSkge1xyXG4gICAgdmFyIGMsIFJFID0gbmV3IFJlZ0V4cCgnKF58OylcXFxccyonICsgbmFtZSArICdcXFxccyo9XFxcXHMqKFteXFxcXHM7XSspJywgJ2cnKTtcclxuICAgIHJldHVybiAoYyA9IFJFLmV4ZWMoZG9jdW1lbnQuY29va2llKSkgPyBjWzJdIDogbnVsbDtcclxufVxyXG5cclxuLyoqXHJcbiAqIG5hbWUgICAgICBuYW1lIG9mIHRoZSBjb29raWVcclxuICogW3BhdGhdICAgIHBhdGggb2YgdGhlIGNvb2tpZSAobXVzdCBiZSBzYW1lIGFzIHBhdGggdXNlZCB0byBjcmVhdGUgY29va2llKVxyXG4gKiBbZG9tYWluXSAgZG9tYWluIG9mIHRoZSBjb29raWUgKG11c3QgYmUgc2FtZSBhcyBkb21haW4gdXNlZCB0byBjcmVhdGUgY29va2llKVxyXG4gKi9cclxuZnVuY3Rpb24gZGVsZXRlQ29va2llKG5hbWUsIHBhdGgsIGRvbWFpbikge1xyXG4gICAgc2V0Q29va2llKG5hbWUsICcnLCAtMSwgcGF0aCwgZG9tYWluKTtcclxufVxyXG5cclxuLy8gTWVudXNcclxudmFyIE1lbnUgPSB7XHJcbiAgICBoaWRlU3BlZWQ6ICdmYXN0Jywgb2Zmc2V0Q29ycmVjdGlvbl9YOiAtNCwgb2Zmc2V0Q29ycmVjdGlvbl9ZOiAyLFxyXG5cclxuICAgIGFjdGl2ZU1lbnVJZDogbnVsbCwgIC8vICBjdXJyZW50bHkgb3BlbmVkIG1lbnUgKGZyb20gcHJldmlvdXMgY2xpY2spXHJcbiAgICBjbGlja2VkTWVudUlkOiBudWxsLCAgLy8gIG1lbnUgdG8gc2hvdyB1cFxyXG4gICAgJHJvb3Q6IG51bGwsICAvLyAgcm9vdCBlbGVtZW50IGZvciBtZW51IHdpdGggXCJocmVmID0gJyNjbGlja2VkTWVudUlkJ1wiXHJcbiAgICAkbWVudTogbnVsbCwgIC8vICBjbGlja2VkIG1lbnVcclxuICAgIHBvc2l0aW9uaW5nVHlwZTogbnVsbCwgIC8vICByZXNlcnZlZFxyXG4gICAgb3V0c2lkZUNsaWNrV2F0Y2g6IGZhbHNlLCAvLyAgcHJldmVudCBtdWx0aXBsZSAkKGRvY3VtZW50KS5jbGljayBiaW5kaW5nXHJcblxyXG4gICAgY2xpY2tlZDogZnVuY3Rpb24gKCRyb290KSB7XHJcbiAgICAgICAgJHJvb3QuYmx1cigpO1xyXG4gICAgICAgIHRoaXMuY2xpY2tlZE1lbnVJZCA9IHRoaXMuZ2V0TWVudUlkKCRyb290KTtcclxuICAgICAgICB0aGlzLiRtZW51ID0gJCh0aGlzLmNsaWNrZWRNZW51SWQpO1xyXG4gICAgICAgIHRoaXMuJHJvb3QgPSAkcm9vdDtcclxuICAgICAgICB0aGlzLnRvZ2dsZSgpO1xyXG4gICAgfSxcclxuXHJcbiAgICBob3ZlcmVkOiBmdW5jdGlvbiAoJHJvb3QpIHtcclxuICAgICAgICBpZiAodGhpcy5hY3RpdmVNZW51SWQgJiYgdGhpcy5hY3RpdmVNZW51SWQgIT09IHRoaXMuZ2V0TWVudUlkKCRyb290KSkge1xyXG4gICAgICAgICAgICB0aGlzLmNsaWNrZWQoJHJvb3QpO1xyXG4gICAgICAgIH1cclxuICAgIH0sXHJcblxyXG4gICAgdW5ob3ZlcmVkOiBmdW5jdGlvbiAoJHJvb3QpIHtcclxuICAgIH0sXHJcblxyXG4gICAgZ2V0TWVudUlkOiBmdW5jdGlvbiAoJGVsKSB7XHJcbiAgICAgICAgdmFyIGhyZWYgPSAkZWwuYXR0cignaHJlZicpO1xyXG4gICAgICAgIHJldHVybiBocmVmLnN1YnN0cihocmVmLmluZGV4T2YoJyMnKSk7XHJcbiAgICB9LFxyXG5cclxuICAgIHNldExvY2F0aW9uOiBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgdmFyIENTUyA9IHRoaXMuJHJvb3Qub2Zmc2V0KCk7XHJcbiAgICAgICAgQ1NTLnRvcCArPSB0aGlzLiRyb290LmhlaWdodCgpICsgdGhpcy5vZmZzZXRDb3JyZWN0aW9uX1k7XHJcbiAgICAgICAgdmFyIGN1clRvcCA9IHBhcnNlSW50KENTUy50b3ApO1xyXG4gICAgICAgIHZhciB0Q29ybmVyID0gJChkb2N1bWVudCkuc2Nyb2xsVG9wKCkgKyAkKHdpbmRvdykuaGVpZ2h0KCkgLSAyMDtcclxuICAgICAgICB2YXIgbWF4VmlzaWJsZVRvcCA9IE1hdGgubWluKGN1clRvcCwgTWF0aC5tYXgoMCwgdENvcm5lciAtIHRoaXMuJG1lbnUuaGVpZ2h0KCkpKTtcclxuICAgICAgICBpZiAoY3VyVG9wICE9PSBtYXhWaXNpYmxlVG9wKSB7XHJcbiAgICAgICAgICAgIENTUy50b3AgPSBtYXhWaXNpYmxlVG9wO1xyXG4gICAgICAgIH1cclxuICAgICAgICBDU1MubGVmdCArPSB0aGlzLm9mZnNldENvcnJlY3Rpb25fWDtcclxuICAgICAgICB2YXIgY3VyTGVmdCA9IHBhcnNlSW50KENTUy5sZWZ0KTtcclxuICAgICAgICB2YXIgckNvcm5lciA9ICQoZG9jdW1lbnQpLnNjcm9sbExlZnQoKSArICQod2luZG93KS53aWR0aCgpIC0gNjtcclxuICAgICAgICB2YXIgbWF4VmlzaWJsZUxlZnQgPSBNYXRoLm1pbihjdXJMZWZ0LCBNYXRoLm1heCgwLCByQ29ybmVyIC0gdGhpcy4kbWVudS53aWR0aCgpKSk7XHJcbiAgICAgICAgaWYgKGN1ckxlZnQgIT09IG1heFZpc2libGVMZWZ0KSB7XHJcbiAgICAgICAgICAgIENTUy5sZWZ0ID0gbWF4VmlzaWJsZUxlZnQ7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIHRoaXMuJG1lbnUuY3NzKENTUyk7XHJcbiAgICB9LFxyXG5cclxuICAgIGZpeExvY2F0aW9uOiBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgdmFyICRtZW51ID0gdGhpcy4kbWVudTtcclxuICAgICAgICB2YXIgY3VyTGVmdCA9IHBhcnNlSW50KCRtZW51LmNzcygnbGVmdCcpKTtcclxuICAgICAgICB2YXIgckNvcm5lciA9ICQoZG9jdW1lbnQpLnNjcm9sbExlZnQoKSArICQod2luZG93KS53aWR0aCgpIC0gNjtcclxuICAgICAgICB2YXIgbWF4VmlzaWJsZUxlZnQgPSBNYXRoLm1pbihjdXJMZWZ0LCBNYXRoLm1heCgwLCByQ29ybmVyIC0gJG1lbnUud2lkdGgoKSkpO1xyXG4gICAgICAgIGlmIChjdXJMZWZ0ICE9PSBtYXhWaXNpYmxlTGVmdCkge1xyXG4gICAgICAgICAgICAkbWVudS5jc3MoJ2xlZnQnLCBtYXhWaXNpYmxlTGVmdCk7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIHZhciBjdXJUb3AgPSBwYXJzZUludCgkbWVudS5jc3MoJ3RvcCcpKTtcclxuICAgICAgICB2YXIgdENvcm5lciA9ICQoZG9jdW1lbnQpLnNjcm9sbFRvcCgpICsgJCh3aW5kb3cpLmhlaWdodCgpIC0gMjA7XHJcbiAgICAgICAgdmFyIG1heFZpc2libGVUb3AgPSBNYXRoLm1pbihjdXJUb3AsIE1hdGgubWF4KDAsIHRDb3JuZXIgLSAkbWVudS5oZWlnaHQoKSkpO1xyXG4gICAgICAgIGlmIChjdXJUb3AgIT09IG1heFZpc2libGVUb3ApIHtcclxuICAgICAgICAgICAgJG1lbnUuY3NzKCd0b3AnLCBtYXhWaXNpYmxlVG9wKTtcclxuICAgICAgICB9XHJcbiAgICB9LFxyXG5cclxuICAgIHRvZ2dsZTogZnVuY3Rpb24gKCkge1xyXG4gICAgICAgIGlmICh0aGlzLmFjdGl2ZU1lbnVJZCAmJiB0aGlzLmFjdGl2ZU1lbnVJZCAhPT0gdGhpcy5jbGlja2VkTWVudUlkKSB7XHJcbiAgICAgICAgICAgICQodGhpcy5hY3RpdmVNZW51SWQpLmhpZGUodGhpcy5oaWRlU3BlZWQpO1xyXG4gICAgICAgIH1cclxuICAgICAgICAvLyB0b2dnbGUgY2xpY2tlZCBtZW51XHJcbiAgICAgICAgaWYgKHRoaXMuJG1lbnUuaXMoJzp2aXNpYmxlJykpIHtcclxuICAgICAgICAgICAgdGhpcy4kbWVudS5oaWRlKHRoaXMuaGlkZVNwZWVkKTtcclxuICAgICAgICAgICAgdGhpcy5hY3RpdmVNZW51SWQgPSBudWxsO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgIHRoaXMuc2hvd0NsaWNrZWRNZW51KCk7XHJcbiAgICAgICAgICAgIGlmICghdGhpcy5vdXRzaWRlQ2xpY2tXYXRjaCkge1xyXG4gICAgICAgICAgICAgICAgJChkb2N1bWVudCkub25lKCdtb3VzZWRvd24nLCBmdW5jdGlvbiAoZSkge1xyXG4gICAgICAgICAgICAgICAgICAgIE1lbnUuaGlkZUNsaWNrV2F0Y2hlcihlKTtcclxuICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgdGhpcy5vdXRzaWRlQ2xpY2tXYXRjaCA9IHRydWU7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9XHJcbiAgICB9LFxyXG5cclxuICAgIHNob3dDbGlja2VkTWVudTogZnVuY3Rpb24gKCkge1xyXG4gICAgICAgIHRoaXMuc2V0TG9jYXRpb24oKTtcclxuICAgICAgICB0aGlzLiRtZW51LmNzcyh7ZGlzcGxheTogJ2Jsb2NrJ30pO1xyXG4gICAgICAgIC8vIHRoaXMuZml4TG9jYXRpb24oKTtcclxuICAgICAgICB0aGlzLmFjdGl2ZU1lbnVJZCA9IHRoaXMuY2xpY2tlZE1lbnVJZDtcclxuICAgIH0sXHJcblxyXG4gICAgLy8gaGlkZSBpZiBjbGlja2VkIG91dHNpZGUgb2YgbWVudVxyXG4gICAgaGlkZUNsaWNrV2F0Y2hlcjogZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICB0aGlzLm91dHNpZGVDbGlja1dhdGNoID0gZmFsc2U7XHJcbiAgICAgICAgdGhpcy5oaWRlKGUpO1xyXG4gICAgfSxcclxuXHJcbiAgICBoaWRlOiBmdW5jdGlvbiAoZSkge1xyXG4gICAgICAgIGlmICh0aGlzLiRtZW51KSB7XHJcbiAgICAgICAgICAgIHRoaXMuJG1lbnUuaGlkZSh0aGlzLmhpZGVTcGVlZCk7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIHRoaXMuYWN0aXZlTWVudUlkID0gdGhpcy5jbGlja2VkTWVudUlkID0gdGhpcy4kbWVudSA9IG51bGw7XHJcbiAgICB9XHJcbn07XHJcblxyXG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbiAoKSB7XHJcbiAgICAvLyBNZW51c1xyXG4gICAgJCgnYm9keScpLmFwcGVuZCgkKCdkaXYubWVudS1zdWInKSk7XHJcbiAgICAkKCdhLm1lbnUtcm9vdCcpXHJcbiAgICAgICAgLmNsaWNrKGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcclxuICAgICAgICAgICAgTWVudS5jbGlja2VkKCQodGhpcykpO1xyXG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XHJcbiAgICAgICAgfSlcclxuICAgICAgICAuaG92ZXIoZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICBNZW51LmhvdmVyZWQoJCh0aGlzKSk7XHJcbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcclxuICAgICAgICB9LCBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgIE1lbnUudW5ob3ZlcmVkKCQodGhpcykpO1xyXG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XHJcbiAgICAgICAgfSk7XHJcbiAgICAkKCdkaXYubWVudS1zdWInKVxyXG4gICAgICAgIC5tb3VzZWRvd24oZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgZS5zdG9wUHJvcGFnYXRpb24oKTtcclxuICAgICAgICB9KVxyXG4gICAgICAgIC5maW5kKCdhJylcclxuICAgICAgICAuY2xpY2soZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgTWVudS5oaWRlKGUpO1xyXG4gICAgICAgIH0pO1xyXG4gICAgLy8gSW5wdXQgaGludHNcclxuICAgICQoJ2lucHV0JylcclxuICAgICAgICAuZmlsdGVyKCcuaGludCcpLm9uZSgnZm9jdXMnLCBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgJCh0aGlzKS52YWwoJycpLnJlbW92ZUNsYXNzKCdoaW50Jyk7XHJcbiAgICB9KVxyXG4gICAgICAgIC5lbmQoKVxyXG4gICAgICAgIC5maWx0ZXIoJy5lcnJvcicpLm9uZSgnZm9jdXMnLCBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgJCh0aGlzKS5yZW1vdmVDbGFzcygnZXJyb3InKTtcclxuICAgIH0pO1xyXG59KTtcclxuXHJcbi8vXHJcbi8vIEFqYXhcclxuLy9cclxuZnVuY3Rpb24gQWpheChoYW5kbGVyVVJMLCByZXF1ZXN0VHlwZSwgZGF0YVR5cGUpIHtcclxuICAgIHRoaXMudXJsID0gaGFuZGxlclVSTDtcclxuICAgIHRoaXMudHlwZSA9IHJlcXVlc3RUeXBlO1xyXG4gICAgdGhpcy5kYXRhVHlwZSA9IGRhdGFUeXBlO1xyXG4gICAgdGhpcy5lcnJvcnMgPSB7fTtcclxufVxyXG5cclxuQWpheC5wcm90b3R5cGUgPSB7XHJcbiAgICBpbml0OiB7fSwgIC8vIGluaXQgZnVuY3Rpb25zIChydW4gYmVmb3JlIHN1Ym1pdCwgYWZ0ZXIgdHJpZ2dlcmluZyBhamF4IGV2ZW50KVxyXG4gICAgY2FsbGJhY2s6IHt9LCAgLy8gY2FsbGJhY2sgZnVuY3Rpb25zIChyZXNwb25zZSBoYW5kbGVycylcclxuICAgIHN0YXRlOiB7fSwgIC8vIGN1cnJlbnQgYWN0aW9uIHN0YXRlXHJcbiAgICByZXF1ZXN0OiB7fSwgIC8vIHJlcXVlc3QgZGF0YVxyXG4gICAgcGFyYW1zOiB7fSwgIC8vIGFjdGlvbiBwYXJhbXMsIGZvcm1hdDogYWpheC5wYXJhbXNbRWxlbWVudElEXSA9IHsgcGFyYW06IFwidmFsXCIgLi4uIH1cclxuICAgIGZvcm1fdG9rZW46ICcnLCBoaWRlX2xvYWRpbmc6IG51bGwsXHJcblxyXG4gICAgZXhlYzogZnVuY3Rpb24gKHJlcXVlc3QsIGhpZGVfbG9hZGluZyA9IGZhbHNlKSB7XHJcbiAgICAgICAgdGhpcy5yZXF1ZXN0W3JlcXVlc3QuYWN0aW9uXSA9IHJlcXVlc3Q7XHJcbiAgICAgICAgcmVxdWVzdFsnZm9ybV90b2tlbiddID0gdGhpcy5mb3JtX3Rva2VuO1xyXG4gICAgICAgIHRoaXMuaGlkZV9sb2FkaW5nID0gaGlkZV9sb2FkaW5nO1xyXG4gICAgICAgICQuYWpheCh7XHJcbiAgICAgICAgICAgIHVybDogdGhpcy51cmwsXHJcbiAgICAgICAgICAgIHR5cGU6IHRoaXMudHlwZSxcclxuICAgICAgICAgICAgZGF0YVR5cGU6IHRoaXMuZGF0YVR5cGUsXHJcbiAgICAgICAgICAgIGRhdGE6IHJlcXVlc3QsXHJcbiAgICAgICAgICAgIHN1Y2Nlc3M6IGFqYXguc3VjY2VzcyxcclxuICAgICAgICAgICAgZXJyb3I6IGFqYXguZXJyb3JcclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG4gICAgc3VjY2VzczogZnVuY3Rpb24gKHJlc3BvbnNlKSB7XHJcbiAgICAgICAgdmFyIGFjdGlvbiA9IHJlc3BvbnNlLmFjdGlvbjtcclxuICAgICAgICAvLyByYXdfb3V0cHV0IG5vcm1hbGx5IG1pZ2h0IGNvbnRhaW4gb25seSBlcnJvciBtZXNzYWdlcyAoaWYgcGhwLmluaS5kaXNwbGF5X2Vycm9ycyA9PSAxKVxyXG4gICAgICAgIGlmIChyZXNwb25zZS5yYXdfb3V0cHV0KSB7XHJcbiAgICAgICAgICAgICQoJ2JvZHknKS5wcmVwZW5kKHJlc3BvbnNlLnJhd19vdXRwdXQpO1xyXG4gICAgICAgIH1cclxuICAgICAgICBpZiAocmVzcG9uc2Uuc3FsX2xvZykge1xyXG4gICAgICAgICAgICAkKCcjc3FsTG9nJykucHJlcGVuZChyZXNwb25zZS5zcWxfbG9nICsgJzxociAvPicpO1xyXG4gICAgICAgICAgICBmaXhTcWxMb2coKTtcclxuICAgICAgICB9XHJcbiAgICAgICAgaWYgKHJlc3BvbnNlLnVwZGF0ZV9pZHMpIHtcclxuICAgICAgICAgICAgZm9yIChpZCBpbiByZXNwb25zZS51cGRhdGVfaWRzKSB7XHJcbiAgICAgICAgICAgICAgICAkKCcjJyArIGlkKS5odG1sKHJlc3BvbnNlLnVwZGF0ZV9pZHNbaWRdKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuICAgICAgICBpZiAocmVzcG9uc2UucHJvbXB0X3Bhc3N3b3JkKSB7XHJcbiAgICAgICAgICAgIHZhciB1c2VyX3Bhc3N3b3JkID0gcHJvbXB0KCfQlNC70Y8g0LTQvtGB0YLRg9C/0LAg0Log0LTQsNC90L3QvtC5INGE0YPQvdC60YbQuNC4LCDQv9C+0LbQsNC70YPQudGB0YLQsCwg0LLQstC10LTQuNGC0LUg0YHQstC+0Lkg0L/QsNGA0L7Qu9GMJywgJycpO1xyXG4gICAgICAgICAgICBpZiAodXNlcl9wYXNzd29yZCkge1xyXG4gICAgICAgICAgICAgICAgdmFyIHJlcSA9IGFqYXgucmVxdWVzdFthY3Rpb25dO1xyXG4gICAgICAgICAgICAgICAgcmVxLnVzZXJfcGFzc3dvcmQgPSB1c2VyX3Bhc3N3b3JkO1xyXG4gICAgICAgICAgICAgICAgYWpheC5leGVjKHJlcSk7XHJcbiAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICBhamF4LmNsZWFyQWN0aW9uU3RhdGUoYWN0aW9uKTtcclxuICAgICAgICAgICAgICAgIGFqYXguc2hvd0Vycm9yTXNnKCfQktCy0LXQtNC10L0g0L3QtdCy0LXRgNC90YvQuSDQv9Cw0YDQvtC70YwnKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0gZWxzZSBpZiAocmVzcG9uc2UucHJvbXB0X2NvbmZpcm0pIHtcclxuICAgICAgICAgICAgaWYgKHdpbmRvdy5jb25maXJtKHJlc3BvbnNlLmNvbmZpcm1fbXNnKSkge1xyXG4gICAgICAgICAgICAgICAgdmFyIHJlcSA9IGFqYXgucmVxdWVzdFthY3Rpb25dO1xyXG4gICAgICAgICAgICAgICAgcmVxLmNvbmZpcm1lZCA9IDE7XHJcbiAgICAgICAgICAgICAgICBhamF4LmV4ZWMocmVxKTtcclxuICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgIGFqYXguY2xlYXJBY3Rpb25TdGF0ZShhY3Rpb24pO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSBlbHNlIGlmIChyZXNwb25zZS5lcnJvcl9jb2RlKSB7XHJcbiAgICAgICAgICAgIGFqYXguc2hvd0Vycm9yTXNnKHJlc3BvbnNlLmVycm9yX21zZyk7XHJcbiAgICAgICAgICAgIGNvbnNvbGUubG9nKHJlc3BvbnNlLmNvbnNvbGVfbG9nKTtcclxuICAgICAgICAgICAgJCgnLmxvYWRpbmctMScpLnJlbW92ZUNsYXNzKCdsb2FkaW5nLTEnKS5odG1sKCdlcnJvcicpO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgIGFqYXguY2FsbGJhY2tbYWN0aW9uXShyZXNwb25zZSk7XHJcbiAgICAgICAgICAgIGFqYXguY2xlYXJBY3Rpb25TdGF0ZShhY3Rpb24pO1xyXG4gICAgICAgIH1cclxuICAgIH0sXHJcblxyXG4gICAgZXJyb3I6IGZ1bmN0aW9uICh4bWwsIGRlc2MpIHtcclxuICAgIH0sXHJcblxyXG4gICAgY2xlYXJBY3Rpb25TdGF0ZTogZnVuY3Rpb24gKGFjdGlvbikge1xyXG4gICAgICAgIGFqYXguc3RhdGVbYWN0aW9uXSA9IGFqYXgucmVxdWVzdFthY3Rpb25dID0gJyc7XHJcbiAgICB9LFxyXG5cclxuICAgIHNob3dFcnJvck1zZzogZnVuY3Rpb24gKG1zZykge1xyXG4gICAgICAgIGFsZXJ0KG1zZyk7XHJcbiAgICB9LFxyXG5cclxuICAgIGNhbGxJbml0Rm46IGZ1bmN0aW9uIChldmVudCkge1xyXG4gICAgICAgIGV2ZW50LnN0b3BQcm9wYWdhdGlvbigpO1xyXG4gICAgICAgIHZhciBwYXJhbXMgPSBhamF4LnBhcmFtc1skKHRoaXMpLmF0dHIoJ2lkJyldO1xyXG4gICAgICAgIHZhciBhY3Rpb24gPSBwYXJhbXMuYWN0aW9uO1xyXG4gICAgICAgIGlmIChhamF4LnN0YXRlW2FjdGlvbl0gPT09ICdyZWFkeVRvU3VibWl0JyB8fCBhamF4LnN0YXRlW2FjdGlvbl0gPT09ICdlcnJvcicpIHtcclxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgIGFqYXguc3RhdGVbYWN0aW9uXSA9ICdyZWFkeVRvU3VibWl0JztcclxuICAgICAgICB9XHJcbiAgICAgICAgYWpheC5pbml0W2FjdGlvbl0ocGFyYW1zKTtcclxuICAgIH0sXHJcblxyXG4gICAgc2V0U3RhdHVzQm94UG9zaXRpb246IGZ1bmN0aW9uICgkZWwpIHtcclxuICAgICAgICB2YXIgbmV3VG9wID0gJChkb2N1bWVudCkuc2Nyb2xsVG9wKCk7XHJcbiAgICAgICAgdmFyIHJDb3JuZXIgPSAkKGRvY3VtZW50KS5zY3JvbGxMZWZ0KCkgKyAkKHdpbmRvdykud2lkdGgoKSAtIDg7XHJcbiAgICAgICAgdmFyIG5ld0xlZnQgPSBNYXRoLm1heCgwLCByQ29ybmVyIC0gJGVsLndpZHRoKCkpO1xyXG4gICAgICAgICRlbC5jc3Moe3RvcDogbmV3VG9wLCBsZWZ0OiBuZXdMZWZ0fSk7XHJcbiAgICB9LFxyXG5cclxuICAgIG1ha2VFZGl0YWJsZTogZnVuY3Rpb24gKHJvb3RFbGVtZW50SWQsIGVkaXRhYmxlVHlwZSkge1xyXG4gICAgICAgIHZhciAkcm9vdCA9ICQoJyMnICsgcm9vdEVsZW1lbnRJZCk7XHJcbiAgICAgICAgdmFyICRlZGl0YWJsZSA9ICQoJy5lZGl0YWJsZScsICRyb290KTtcclxuICAgICAgICB2YXIgaW5wdXRzSHRtbCA9ICQoJyNlZGl0YWJsZS10cGwtJyArIGVkaXRhYmxlVHlwZSkuaHRtbCgpO1xyXG4gICAgICAgICRlZGl0YWJsZS5oaWRlKCkuYWZ0ZXIoaW5wdXRzSHRtbCk7XHJcbiAgICAgICAgdmFyICRpbnB1dHMgPSAkKCcuZWRpdGFibGUtaW5wdXRzJywgJHJvb3QpO1xyXG4gICAgICAgIGlmIChlZGl0YWJsZVR5cGUgPT09ICdpbnB1dCcgfHwgZWRpdGFibGVUeXBlID09PSAndGV4dGFyZWEnKSB7XHJcbiAgICAgICAgICAgICQoJy5lZGl0YWJsZS12YWx1ZScsICRpbnB1dHMpLnZhbCgkLnRyaW0oJGVkaXRhYmxlLnRleHQoKSkpO1xyXG4gICAgICAgIH1cclxuICAgICAgICAkKCdpbnB1dC5lZGl0YWJsZS1zdWJtaXQnLCAkaW5wdXRzKS5jbGljayhmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgIHZhciBwYXJhbXMgPSBhamF4LnBhcmFtc1tyb290RWxlbWVudElkXTtcclxuICAgICAgICAgICAgdmFyICR2YWwgPSAkKCcuZWRpdGFibGUtdmFsdWUnLCAnIycgKyByb290RWxlbWVudElkKTtcclxuICAgICAgICAgICAgcGFyYW1zLnZhbHVlID0gKCR2YWwuc2l6ZSgpID09PSAxKSA/ICR2YWwudmFsKCkgOiAkdmFsLmZpbHRlcignOmNoZWNrZWQnKS52YWwoKTtcclxuICAgICAgICAgICAgcGFyYW1zLnN1Ym1pdCA9IHRydWU7XHJcbiAgICAgICAgICAgIGFqYXguaW5pdFtwYXJhbXMuYWN0aW9uXShwYXJhbXMpO1xyXG4gICAgICAgIH0pO1xyXG4gICAgICAgICQoJ2lucHV0LmVkaXRhYmxlLWNhbmNlbCcsICRpbnB1dHMpLmNsaWNrKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgYWpheC5yZXN0b3JlRWRpdGFibGUocm9vdEVsZW1lbnRJZCk7XHJcbiAgICAgICAgfSk7XHJcbiAgICAgICAgJGlucHV0cy5zaG93KCkuZmluZCgnLmVkaXRhYmxlLXZhbHVlJykuZm9jdXMoKTtcclxuICAgICAgICAkcm9vdC5yZW1vdmVDbGFzcygnZWRpdGFibGUtY29udGFpbmVyJyk7XHJcbiAgICB9LFxyXG5cclxuICAgIHJlc3RvcmVFZGl0YWJsZTogZnVuY3Rpb24gKHJvb3RFbGVtZW50SWQsIG5ld1ZhbHVlKSB7XHJcbiAgICAgICAgdmFyICRyb290ID0gJCgnIycgKyByb290RWxlbWVudElkKTtcclxuICAgICAgICB2YXIgJGVkaXRhYmxlID0gJCgnLmVkaXRhYmxlJywgJHJvb3QpO1xyXG4gICAgICAgICQoJy5lZGl0YWJsZS1pbnB1dHMnLCAkcm9vdCkucmVtb3ZlKCk7XHJcbiAgICAgICAgaWYgKG5ld1ZhbHVlKSB7XHJcbiAgICAgICAgICAgICRlZGl0YWJsZS50ZXh0KG5ld1ZhbHVlKTtcclxuICAgICAgICB9XHJcbiAgICAgICAgJGVkaXRhYmxlLnNob3coKTtcclxuICAgICAgICBhamF4LmNsZWFyQWN0aW9uU3RhdGUoYWpheC5wYXJhbXNbcm9vdEVsZW1lbnRJZF0uYWN0aW9uKTtcclxuICAgICAgICBhamF4LnBhcmFtc1tyb290RWxlbWVudElkXS5zdWJtaXQgPSBmYWxzZTtcclxuICAgICAgICAkcm9vdC5hZGRDbGFzcygnZWRpdGFibGUtY29udGFpbmVyJyk7XHJcbiAgICB9XHJcbn07XHJcblxyXG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbiAoKSB7XHJcbiAgICAvLyBTZXR1cCBhamF4LWxvYWRpbmcgYm94XHJcbiAgICAkKFwiI2FqYXgtbG9hZGluZ1wiKS5hamF4U3RhcnQoZnVuY3Rpb24gKCkge1xyXG4gICAgICAgIGlmIChhamF4LmhpZGVfbG9hZGluZyA9PT0gZmFsc2UpIHtcclxuICAgICAgICAgICAgJChcIiNhamF4LWVycm9yXCIpLmhpZGUoKTtcclxuICAgICAgICAgICAgJCh0aGlzKS5zaG93KCk7XHJcbiAgICAgICAgICAgIGFqYXguc2V0U3RhdHVzQm94UG9zaXRpb24oJCh0aGlzKSk7XHJcbiAgICAgICAgfVxyXG4gICAgfSk7XHJcbiAgICAkKFwiI2FqYXgtbG9hZGluZ1wiKS5hamF4U3RvcChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgaWYgKGFqYXguaGlkZV9sb2FkaW5nID09PSBmYWxzZSkge1xyXG4gICAgICAgICAgICAkKHRoaXMpLmhpZGUoKTtcclxuICAgICAgICB9XHJcbiAgICB9KTtcclxuXHJcbiAgICAvLyBTZXR1cCBhamF4LWVycm9yIGJveFxyXG4gICAgJChcIiNhamF4LWVycm9yXCIpLmFqYXhFcnJvcihmdW5jdGlvbiAocmVxLCB4bWwpIHtcclxuICAgICAgICB2YXIgc3RhdHVzID0geG1sLnN0YXR1cztcclxuICAgICAgICB2YXIgdGV4dCA9IHhtbC5zdGF0dXNUZXh0O1xyXG4gICAgICAgIGlmIChzdGF0dXMgPT09IDIwMCkge1xyXG4gICAgICAgICAgICBzdGF0dXMgPSAnJztcclxuICAgICAgICAgICAgdGV4dCA9ICfQvdC10LLQtdGA0L3Ri9C5INGE0L7RgNC80LDRgiDQtNCw0L3QvdGL0YUnO1xyXG4gICAgICAgIH1cclxuICAgICAgICAkKHRoaXMpLmh0bWwoXCLQntGI0LjQsdC60LAg0LI6IDxpPlwiICsgYWpheC51cmwgKyBcIjwvaT48YnIgLz48Yj5cIiArIHN0YXR1cyArIFwiIFwiICsgdGV4dCArIFwiPC9iPlwiKS5zaG93KCk7XHJcbiAgICAgICAgYWpheC5zZXRTdGF0dXNCb3hQb3NpdGlvbigkKHRoaXMpKTtcclxuICAgIH0pO1xyXG5cclxuICAgIC8vIEJpbmQgYWpheCBldmVudHNcclxuICAgICQoJ3Zhci5hamF4LXBhcmFtcycpLmVhY2goZnVuY3Rpb24gKCkge1xyXG4gICAgICAgIHZhciBwYXJhbXMgPSAkLmV2YWxKU09OKCQodGhpcykuaHRtbCgpKTtcclxuICAgICAgICBwYXJhbXMuZXZlbnQgPSBwYXJhbXMuZXZlbnQgfHwgJ2RibGNsaWNrJztcclxuICAgICAgICBhamF4LnBhcmFtc1twYXJhbXMuaWRdID0gcGFyYW1zO1xyXG4gICAgICAgICQoXCIjXCIgKyBwYXJhbXMuaWQpLmJpbmQocGFyYW1zLmV2ZW50LCBhamF4LmNhbGxJbml0Rm4pO1xyXG4gICAgICAgIGlmIChwYXJhbXMuZXZlbnQgPT09ICdjbGljaycgfHwgcGFyYW1zLmV2ZW50ID09PSAnZGJsY2xpY2snKSB7XHJcbiAgICAgICAgICAgICQoXCIjXCIgKyBwYXJhbXMuaWQpLmFkZENsYXNzKCdlZGl0YWJsZS1jb250YWluZXInKTtcclxuICAgICAgICB9XHJcbiAgICB9KTtcclxufSk7XHJcblxyXG4vKipcclxuICogQXV0b2NvbXBsZXRlIHBhc3N3b3JkXHJcbiAqKi9cclxudmFyIGFycmF5X2Zvcl9yYW5kX3Bhc3MgPSBbXCJhXCIsIFwiQVwiLCBcImJcIiwgXCJCXCIsIFwiY1wiLCBcIkNcIiwgXCJkXCIsIFwiRFwiLCBcImVcIiwgXCJFXCIsIFwiZlwiLCBcIkZcIiwgXCJnXCIsIFwiR1wiLCBcImhcIiwgXCJIXCIsIFwiaVwiLCBcIklcIiwgXCJqXCIsIFwiSlwiLCBcImtcIiwgXCJLXCIsIFwibFwiLCBcIkxcIiwgXCJtXCIsIFwiTVwiLCBcIm5cIiwgXCJOXCIsIFwib1wiLCBcIk9cIiwgXCJwXCIsIFwiUFwiLCBcInFcIiwgXCJRXCIsIFwiclwiLCBcIlJcIiwgXCJzXCIsIFwiU1wiLCBcInRcIiwgXCJUXCIsIFwidVwiLCBcIlVcIiwgXCJ2XCIsIFwiVlwiLCBcIndcIiwgXCJXXCIsIFwieFwiLCBcIlhcIiwgXCJ5XCIsIFwiWVwiLCBcInpcIiwgXCJaXCIsIDAsIDEsIDIsIDMsIDQsIDUsIDYsIDcsIDgsIDldO1xyXG52YXIgYXJyYXlfcmFuZCA9IGZ1bmN0aW9uIChhcnJheSkge1xyXG4gICAgdmFyIGFycmF5X2xlbmd0aCA9IGFycmF5Lmxlbmd0aDtcclxuICAgIHZhciByZXN1bHQgPSBNYXRoLnJhbmRvbSgpICogYXJyYXlfbGVuZ3RoO1xyXG4gICAgcmV0dXJuIE1hdGguZmxvb3IocmVzdWx0KTtcclxufTtcclxuXHJcbnZhciBhdXRvY29tcGxldGUgPSBmdW5jdGlvbiAobm9DZW50ZXIpIHtcclxuICAgIHZhciBzdHJpbmdfcmVzdWx0ID0gXCJcIjsgLy8gRW1wdHkgc3RyaW5nXHJcbiAgICBmb3IgKHZhciBpID0gMTsgaSA8PSA4OyBpKyspIHtcclxuICAgICAgICBzdHJpbmdfcmVzdWx0ICs9IGFycmF5X2Zvcl9yYW5kX3Bhc3NbYXJyYXlfcmFuZChhcnJheV9mb3JfcmFuZF9wYXNzKV07XHJcbiAgICB9XHJcblxyXG4gICAgdmFyIF9wb3B1cF9sZWZ0ID0gKE1hdGguY2VpbCh3aW5kb3cuc2NyZWVuLmF2YWlsV2lkdGggLyAyKSAtIDE1MCk7XHJcbiAgICB2YXIgX3BvcHVwX3RvcCA9IChNYXRoLmNlaWwod2luZG93LnNjcmVlbi5hdmFpbEhlaWdodCAvIDIpIC0gNTApO1xyXG5cclxuICAgIGlmICghbm9DZW50ZXIpIHtcclxuICAgICAgICAkKFwiZGl2I2F1dG9jb21wbGV0ZV9wb3B1cFwiKS5jc3Moe1xyXG4gICAgICAgICAgICBsZWZ0OiBfcG9wdXBfbGVmdCArIFwicHhcIiwgdG9wOiBfcG9wdXBfdG9wICsgXCJweFwiXHJcbiAgICAgICAgfSkuc2hvdygxMDAwKTtcclxuICAgIH0gZWxzZSB7XHJcbiAgICAgICAgJChcImRpdiNhdXRvY29tcGxldGVfcG9wdXBcIikuc2hvdygxMDAwKTtcclxuICAgIH1cclxuXHJcbiAgICAkKFwiaW5wdXQjcGFzcywgaW5wdXQjcGFzc19jb25maXJtLCBkaXYjYXV0b2NvbXBsZXRlX3BvcHVwIGlucHV0XCIpLmVhY2goZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICQodGhpcykudmFsKHN0cmluZ19yZXN1bHQpO1xyXG4gICAgfSk7XHJcbn07XHJcblxyXG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbiAoKSB7XHJcbiAgICAkKFwic3BhbiNhdXRvY29tcGxldGVcIikuY2xpY2soZnVuY3Rpb24gKCkge1xyXG4gICAgICAgIGF1dG9jb21wbGV0ZSgpO1xyXG4gICAgfSk7XHJcblxyXG4gICAgLy8g0L/QtdGA0LXQvNC10YnQtdC90LjQtSDQvtC60L3QsFxyXG4gICAgdmFyIF9YLCBfWTtcclxuICAgIHZhciBfYk1vdmVibGUgPSBmYWxzZTtcclxuXHJcbiAgICAkKFwiZGl2I2F1dG9jb21wbGV0ZV9wb3B1cCBkaXYudGl0bGVcIikubW91c2Vkb3duKGZ1bmN0aW9uIChldmVudCkge1xyXG4gICAgICAgIF9iTW92ZWJsZSA9IHRydWU7XHJcbiAgICAgICAgX1ggPSBldmVudC5jbGllbnRYO1xyXG4gICAgICAgIF9ZID0gZXZlbnQuY2xpZW50WTtcclxuICAgIH0pO1xyXG5cclxuICAgICQoXCJkaXYjYXV0b2NvbXBsZXRlX3BvcHVwIGRpdi50aXRsZVwiKS5tb3VzZW1vdmUoZnVuY3Rpb24gKGV2ZW50KSB7XHJcbiAgICAgICAgdmFyIGpGcmFtZSA9ICQoXCJkaXYjYXV0b2NvbXBsZXRlX3BvcHVwXCIpO1xyXG4gICAgICAgIHZhciBqRkxlZnQgPSBwYXJzZUludChqRnJhbWUuY3NzKFwibGVmdFwiKSk7XHJcbiAgICAgICAgdmFyIGpGVG9wID0gcGFyc2VJbnQoakZyYW1lLmNzcyhcInRvcFwiKSk7XHJcblxyXG4gICAgICAgIGlmIChfYk1vdmVibGUpIHtcclxuICAgICAgICAgICAgaWYgKGV2ZW50LmNsaWVudFggPCBfWCkge1xyXG4gICAgICAgICAgICAgICAgakZyYW1lLmNzcyhcImxlZnRcIiwgakZMZWZ0IC0gKF9YIC0gZXZlbnQuY2xpZW50WCkgKyBcInB4XCIpO1xyXG4gICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgakZyYW1lLmNzcyhcImxlZnRcIiwgKGpGTGVmdCArIChldmVudC5jbGllbnRYIC0gX1gpKSArIFwicHhcIik7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIGlmIChldmVudC5jbGllbnRZIDwgX1kpIHtcclxuICAgICAgICAgICAgICAgIGpGcmFtZS5jc3MoXCJ0b3BcIiwgakZUb3AgLSAoX1kgLSBldmVudC5jbGllbnRZKSArIFwicHhcIik7XHJcbiAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICBqRnJhbWUuY3NzKFwidG9wXCIsIChqRlRvcCArIChldmVudC5jbGllbnRZIC0gX1kpKSArIFwicHhcIik7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIF9YID0gZXZlbnQuY2xpZW50WDtcclxuICAgICAgICAgICAgX1kgPSBldmVudC5jbGllbnRZO1xyXG4gICAgICAgIH1cclxuICAgIH0pO1xyXG5cclxuICAgICQoXCJkaXYjYXV0b2NvbXBsZXRlX3BvcHVwIGRpdi50aXRsZVwiKS5tb3VzZXVwKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICBfYk1vdmVibGUgPSBmYWxzZTtcclxuICAgIH0pLm1vdXNlb3V0KGZ1bmN0aW9uICgpIHtcclxuICAgICAgICBfYk1vdmVibGUgPSBmYWxzZTtcclxuICAgIH0pO1xyXG59KTtcclxuIl19
