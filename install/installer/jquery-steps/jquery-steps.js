/*!
    * Steps v1.1.4
    * https://github.com/oguzhanoya/jquery-steps
    *
    * Copyright (c) 2022 oguzhanoya
    * Released under the MIT license
    */
    
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(require('jquery')) :
  typeof define === 'function' && define.amd ? define(['jquery'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.$));
})(this, (function ($$1) { 'use strict';

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  }
  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor);
    }
  }
  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    Object.defineProperty(Constructor, "prototype", {
      writable: false
    });
    return Constructor;
  }
  function _toPrimitive(input, hint) {
    if (typeof input !== "object" || input === null) return input;
    var prim = input[Symbol.toPrimitive];
    if (prim !== undefined) {
      var res = prim.call(input, hint || "default");
      if (typeof res !== "object") return res;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return (hint === "string" ? String : Number)(input);
  }
  function _toPropertyKey(arg) {
    var key = _toPrimitive(arg, "string");
    return typeof key === "symbol" ? key : String(key);
  }

  var DEFAULTS = {
    startAt: 0,
    showBackButton: true,
    showFooterButtons: true,
    onInit: $.noop,
    onDestroy: $.noop,
    onFinish: $.noop,
    onChange: function onChange() {
      return true;
    },
    stepSelector: '.step-steps',
    contentSelector: '.step-content',
    footerSelector: '.step-footer',
    activeClass: 'active',
    doneClass: 'done',
    errorClass: 'error'
  };

  var Steps = /*#__PURE__*/function () {
    function Steps(element, options) {
      _classCallCheck(this, Steps);
      // Extend defaults with the init options.
      this.options = $$1.extend({}, DEFAULTS, options);

      // Store main DOM element.
      this.el = $$1(element);
      this.selectors = {
        step: "".concat(this.options.stepSelector, " [data-step-target]"),
        footer: "".concat(this.options.footerSelector, " [data-step-action]"),
        content: "".concat(this.options.contentSelector, " [data-step]")
      };

      // Initialize
      this.init();
    }
    _createClass(Steps, [{
      key: "stepClick",
      value: function stepClick(e) {
        e.preventDefault();
        var self = e.data.self;
        var all = self.el.find(self.selectors.step);
        var next = e.currentTarget;
        var nextStepIndex = all.index(next);
        var currentStepIndex = e.data.self.getStepIndex();
        e.data.self.setActiveStep(currentStepIndex, nextStepIndex);
      }
    }, {
      key: "btnClick",
      value: function btnClick(e) {
        e.preventDefault();
        var statusAction = $$1(this).data('step-action');
        e.data.self.setAction(statusAction);
      }
    }, {
      key: "init",
      value: function init() {
        this.hook('onInit');
        this.initEventListeners();

        // set default step
        this.setActiveStep(0, this.options.startAt, true);

        // show footer buttons
        if (!this.options.showFooterButtons) {
          this.hideFooterButtons();
          this.updateFooterButtons = $$1.noop;
        }
      }
    }, {
      key: "initEventListeners",
      value: function initEventListeners() {
        // step click event
        $$1(this.el).find(this.selectors.step).on('click', {
          self: this
        }, this.stepClick);

        // button click event
        $$1(this.el).find(this.selectors.footer).on('click', {
          self: this
        }, this.btnClick);
      }
    }, {
      key: "hook",
      value: function hook(hookName) {
        if (this.options[hookName] !== undefined) {
          this.options[hookName].call(this.el);
        }
      }
    }, {
      key: "destroy",
      value: function destroy() {
        this.hook('onDestroy');
        $$1(this.el).find(this.selectors.step).off('click');
        $$1(this.el).find(this.selectors.footer).off('click');
        this.el.removeData('plugin_Steps');
        this.el.remove();
      }
    }, {
      key: "getStepIndex",
      value: function getStepIndex() {
        var all = this.el.find(this.selectors.step);
        var activeClass = this.options.activeClass.split(' ').join('.');
        var stepIndex = all.index(all.filter(".".concat(activeClass)));
        return stepIndex;
      }
    }, {
      key: "getMaxStepIndex",
      value: function getMaxStepIndex() {
        return this.el.find(this.selectors.step).length - 1;
      }
    }, {
      key: "getStepDirection",
      value: function getStepDirection(stepIndex, newIndex) {
        var direction = 'none';
        if (newIndex < stepIndex) {
          direction = 'backward';
        } else if (newIndex > stepIndex) {
          direction = 'forward';
        }
        return direction;
      }
    }, {
      key: "setShowStep",
      value: function setShowStep(idx, removeClass) {
        var addClass = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
        var $targetStep = this.el.find(this.selectors.step).eq(idx);
        $targetStep.removeClass(removeClass).addClass(addClass);
        var $tabContent = this.el.find(this.selectors.content);
        $tabContent.removeClass(this.options.activeClass).eq(idx).addClass(this.options.activeClass);
      }
    }, {
      key: "setActiveStep",
      value: function setActiveStep(currentIndex, newIndex) {
        var init = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
        if (newIndex !== currentIndex || init) {
          var conditionDirection = newIndex > currentIndex ? function (start) {
            return start <= newIndex;
          } : function (start) {
            return start >= newIndex;
          };

          // prettier-ignore
          var conditionIncrementOrDecrement = newIndex > currentIndex ? function (start) {
            return start + 1;
          } : function (start) {
            return start - 1;
          };
          var i = currentIndex;
          while (conditionDirection(i)) {
            var stepDirection = this.getStepDirection(i, newIndex);
            this.updateStep(i, newIndex, stepDirection);
            var validStep = this.isValidStep(i, newIndex, stepDirection);
            if (!validStep) {
              this.updateStep(i, newIndex, stepDirection, validStep);
              i = newIndex;
            }
            i = conditionIncrementOrDecrement(i);
          }
          this.updateFooterButtons();
        }
      }
    }, {
      key: "updateStep",
      value: function updateStep(currentIndex, newIndex, direction) {
        var isValidStep = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : true;
        if (currentIndex === newIndex) {
          this.setShowStep(currentIndex, this.options.doneClass, this.options.activeClass);
        } else if (isValidStep) {
          var checkDone = direction === 'forward' && this.options.doneClass;
          this.setShowStep(currentIndex, "".concat(this.options.activeClass, " ").concat(this.options.errorClass, " ").concat(this.options.doneClass), checkDone);
        } else {
          this.setShowStep(currentIndex, this.options.doneClass, "".concat(this.options.activeClass, " ").concat(this.options.errorClass));
        }
      }
    }, {
      key: "isValidStep",
      value: function isValidStep(currentIndex, newIndex, direction) {
        return this.options.onChange(currentIndex, newIndex, direction);
      }
    }, {
      key: "updateFooterButtons",
      value: function updateFooterButtons() {
        var currentStepIndex = this.getStepIndex();
        var maxStepIndex = this.getMaxStepIndex();
        var $footer = this.el.find(this.selectors.footer);
        var $prevButton = $footer.filter('[data-step-action="prev"]');
        var $nextButton = $footer.filter('[data-step-action="next"]');
        var $finishButton = $footer.filter('[data-step-action="finish"]');

        // hide prev button if current step is the first step
        if (currentStepIndex === 0) {
          $prevButton.hide();
        } else {
          $prevButton.show();
        }

        // hide forward button and show finish button if current step is the last step
        if (currentStepIndex === maxStepIndex) {
          $nextButton.hide();
          $finishButton.show();
        } else {
          $nextButton.show();
          $finishButton.hide();
        }

        // hide back button if showBackButton option is false
        if (!this.options.showBackButton) {
          $prevButton.hide();
        }
      }
    }, {
      key: "setAction",
      value: function setAction(action) {
        var currentStepIndex = this.getStepIndex();
        var nextStep = currentStepIndex;
        if (action === 'prev') {
          nextStep -= 1;
        }
        if (action === 'next') {
          nextStep += 1;
        }
        if (action === 'finish') {
          var validStep = this.isValidStep(currentStepIndex, nextStep, 'forward');
          if (validStep) {
            this.hook('onFinish');
          } else {
            this.setShowStep(currentStepIndex, '', this.options.errorClass);
          }
        } else {
          this.setActiveStep(currentStepIndex, nextStep);
        }
      }
    }, {
      key: "setStepIndex",
      value: function setStepIndex(idx) {
        var maxIndex = this.getMaxStepIndex();
        if (idx <= maxIndex) {
          var currentStepIndex = this.getStepIndex();
          this.setActiveStep(currentStepIndex, idx);
        }
      }
    }, {
      key: "next",
      value: function next() {
        var currentStepIndex = this.getStepIndex();
        var maxIndex = this.getMaxStepIndex();
        return maxIndex === currentStepIndex ? this.setAction('finish') : this.setAction('next');
      }
    }, {
      key: "prev",
      value: function prev() {
        var currentStepIndex = this.getStepIndex();
        return currentStepIndex !== 0 && this.setAction('prev');
      }
    }, {
      key: "finish",
      value: function finish() {
        this.hook('onFinish');
      }
    }, {
      key: "hideFooterButtons",
      value: function hideFooterButtons() {
        this.el.find(this.selectors.footer).hide();
      }
    }], [{
      key: "setDefaults",
      value: function setDefaults(options) {
        $$1.extend(DEFAULTS, $$1.isPlainObject(options) && options);
      }
    }]);
    return Steps;
  }();

  var version = "1.1.4";

  var previousStepsPlugin = $$1.fn.steps;
  $$1.fn.steps = function (options) {
    return this.each(function () {
      if (!$$1.data(this, 'plugin_Steps')) {
        $$1.data(this, 'plugin_Steps', new Steps(this, options));
      }
    });
  };
  $$1.fn.steps.version = version;
  $$1.fn.steps.setDefaults = Steps.setDefaults;

  // No conflict
  $$1.fn.steps.noConflict = function () {
    $$1.fn.steps = previousStepsPlugin;
    return this;
  };

}));
//# sourceMappingURL=jquery-steps.js.map
