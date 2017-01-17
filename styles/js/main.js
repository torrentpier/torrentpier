function $p() {
  var elements = [];
  for (var i = 0; i < arguments.length; i++) {
    var element = arguments[i];
    if (typeof element === 'string')
      element = document.getElementById(element);
    if (arguments.length === 1)
      return element;
    elements.push(element);
  }
  return elements;
}

function addEvent(obj, type, fn) {
  if (obj.addEventListener) {
    obj.addEventListener(type, fn, false);
    EventCache.add(obj, type, fn);
  }
  else if (obj.attachEvent) {
    obj["e" + type + fn] = fn;
    obj[type + fn] = function () {
      obj["e" + type + fn](window.event);
    };
    obj.attachEvent("on" + type, obj[type + fn]);
    EventCache.add(obj, type, fn);
  }
  else {
    obj["on" + type] = obj["e" + type + fn];
  }
}

var EventCache = function () {
  var listEvents = [];
  return {
    listEvents: listEvents,
    add: function (node, sEventName, fHandler) {
      listEvents.push(arguments);
    },
    flush: function () {
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
  if (typeof(img.naturalHeight) === 'undefined') {
    img.naturalHeight = img.height;
    img.naturalWidth = img.width;
  }
  if (img.width > maxW) {
    img.height = Math.round((maxW / img.width) * img.height);
    img.width = maxW;
    img.title = 'Нажмите на изображение, чтобы посмотреть его в полный размер';
    img.style.cursor = 'move';
    return false;
  }
  else if (img.width === maxW && img.width < img.naturalWidth) {
    img.height = img.naturalHeight;
    img.width = img.naturalWidth;
    img.title = 'Размеры изображения: ' + img.naturalWidth + ' x ' + img.naturalHeight;
    return false;
  }
  else {
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

  document.cookie =
    name + '=' + encodeURI(value)
    + ((expires) ? '; expires=' + expires : '')
    + ((path) ? '; path=' + path : ((cookiePath) ? '; path=' + cookiePath : ''))
    + ((domain) ? '; domain=' + domain : ((cookieDomain) ? '; domain=' + cookieDomain : ''))
    + ((secure) ? '; secure' : ((cookieSecure) ? '; secure' : ''));
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
  hideSpeed: 'fast',
  offsetCorrection_X: -4,
  offsetCorrection_Y: 2,

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
    .click(
      function (e) {
        e.preventDefault();
        Menu.clicked($(this));
        return false;
      })
    .hover(
      function () {
        Menu.hovered($(this));
        return false;
      },
      function () {
        Menu.unhovered($(this));
        return false;
      }
    )
  ;
  $('div.menu-sub')
    .mousedown(function (e) {
      e.stopPropagation();
    })
    .find('a')
    .click(function (e) {
      Menu.hide(e);
    })
  ;
  // Input hints
  $('input')
    .filter('.hint').one('focus', function () {
    $(this).val('').removeClass('hint');
  })
    .end()
    .filter('.error').one('focus', function () {
    $(this).removeClass('error');
  })
  ;
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
  form_token: '',

  exec: function (request) {
    this.request[request.action] = request;
    request['form_token'] = this.form_token;
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
      }
      else {
        ajax.clearActionState(action);
        ajax.showErrorMsg('Введен неверный пароль');
      }
    }
    else if (response.prompt_confirm) {
      if (window.confirm(response.confirm_msg)) {
        var req = ajax.request[action];
        req.confirmed = 1;
        ajax.exec(req);
      }
      else {
        ajax.clearActionState(action);
      }
    }
    else if (response.error_code) {
      ajax.showErrorMsg(response.error_msg);
      $('.loading-1').removeClass('loading-1').html('error');
    }
    else {
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
    $("#ajax-error").hide();
    $(this).show();
    ajax.setStatusBoxPosition($(this));
  });
  $("#ajax-loading").ajaxStop(function () {
    $(this).hide();
  });

  // Setup ajax-error box
  $("#ajax-error").ajaxError(function (req, xml) {
    var status = xml.status;
    var text = xml.statusText;
    if (status === 200) {
      status = '';
      text = 'неверный формат данных';
    }
    $(this).html(
      "Ошибка в: <i>" + ajax.url + "</i><br /><b>" + status + " " + text + "</b>"
    ).show();
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
      left: _popup_left + "px",
      top: _popup_top + "px"
    }).show(1000);
  } else {
    $("div#autocomplete_popup").show(1000);
  }

  $("[name='new_pass'],[name='cfm_pass'], div#autocomplete_popup input").each(function () {
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
