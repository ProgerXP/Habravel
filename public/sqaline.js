/*!
  Sqaline.js - from Squizzle.me Toolkit
  Dynamic, fast and flexible element alignment
  In public domain | by Proger_XP | http://proger.me
*/

window.Sqaline = new (function ($, opt) {
  var self = this
  var Sqaline = this
  var testEl = $('<div>')
  var $win = $(window)

  self.opt = $.extend({
    resizeDelay:      200,
    cache:            true,
    verbose:          false,
    defaultProperty:  'height',
    // trigger() will cause events bubbling throughout the entire DOM which
    // might cause noticeable lags. Can also be set to false.
    trigger:          'triggerHandler',
    // Empty event name disables that particular event firing.
    beforeEvent:      'sqalining',
    afterEvent:       'sqalined',
    attribute:        'data-sqa',
    // min/maxHeight/Width are mapped to height/width if undefined.
    leftCut:          {
      height:         '^{hi - pb - lt}',
      width:          '^{wi - pr - ll}',
      top:            '^{hi / 2} - {ho / 2}',
      left:           '^{wi / 2} - {wo / 2}',
    },
    rightCut:         {
      height:         '$~*{ho}',
      width:          '$~*{wo}',
      top:            '$~*{ho / 2}',
      left:           '$~*{wo / 2}',
    },
  }, opt)

  // Keys should not contain any RegExp-special chars.
  // Values without '(' are treated as numeric CSS property reads.
  self.ruleProps = {
    'pt':   'paddingTop',
    'pb':   'paddingBottom',
    'pl':   'paddingLeft',
    'pr':   'paddingRight',

    'mt':   'marginTop',
    'mb':   'marginBottom',
    'ml':   'marginLeft',
    'mr':   'marginRight',

    'bt':   'borderTopWidth',
    'bb':   'borderBottomWidth',
    'bl':   'borderLeftWidth',
    'br':   'borderRightWidth',

    'oo':   'outlineOffset',
    'ow':   'outlineWidth',

    'wo':   'cx.el.outerWidth(true)',
    'wb':   'cx.el.outerWidth()',
    'wi':   'cx.el.innerWidth()',
    'w':    'cx.el.width()',

    'ho':   'cx.el.outerHeight(true)',
    'hb':   'cx.el.outerHeight()',
    'hi':   'cx.el.innerHeight()',
    'h':    'cx.el.height()',

    // Offset of the element relative to context element, for example, to skip
    // all preceding elements.
    'lt':   'cx.self._nodeDistance(cx.el, cx.opt.node).top',
    'll':   'cx.self._nodeDistance(cx.el, cx.opt.node).left',

    'ot':   'cx.el.offset().top',
    'ol':   'cx.el.offset().left',

    'btm':  'bottom',
    'top':  'top',
    'lft':  'left',
    'rht':  'right',

    // Naming scheme for the following is simple:
    // first letter + first consonant + first capitalized letter.
    'mxw':  'maxWidth',
    'mxh':  'maxHeight',
    'mnw':  'minWidth',
    'mnh':  'minHeight',

    'lts':  'letterSpacing',
    'wrs':  'wordSpacing',
    'lnh':  'lineHeight',
    'txi':  'textIndent',
    'fns':  'fontSize',
    'fnw':  'fontWeight',

    'clc':  'columnCount',
    'clg':  'columnGap',
    'clr':  'columnRuleWidth',
    'cls':  'columnSpan',
    'clw':  'columnWidth',
  }

  self._keySeq = 0
  self._cacheSeq = 1
  self._resizeTimer = null
  self._resizePending = false
  self._resizing = false
  self._resized = {}
  self._funcs = {}    // {'cx.code ...': Function}.
  self._rulePropRE = '\\b(%%)\\b'  // set in init().

  self.fail = function (msg) {
    console && console.error(msg)
    throw msg
  }

  self.clearCache = function () {
    self._cacheSeq++
    return self
  }

  self.resizeTimed = function () {
    if (self._resizeTimer) {
      self._resizePending = true
    } else {
      self._resizePending = false
      self._resizeTimer = setTimeout(function () {
        self._resizeTimer = null
        self._resizePending && self.resizeTimed()
      }, self.opt.resizeDelay)

      self.resizeDelayed()
    }
  }

  self.resizeDelayed = function (delay) {
    setTimeout(function () {
      var time = Date.now()
      var nodes = self.resize()

      if (self.opt.verbose && 'now' in Date) {
        if (!nodes.length) {
          console.info('Nothing to Sqaline - no nodes with ' +
                       self.opt.attribute + ' attribute.')
        } else {
          s = nodes.length == 1 ? '' : 's'
          console.info('Sqalined ' + nodes.length + ' node' + s + ' in ' +
                       (Date.now() - time) + ' msec.')
        }
      }
    }, typeof delay == 'number' ? delay : 0)
  }

  // function ()
  //= $( [data-sqa, data-sqa, ...] )
  //
  // function ( node|$ [, {expr: '!na,me e - ...'}] )
  //= $(node)
  self.resize = function (node, opt) {
    if (!arguments.length) {
      return self.resizeAll()
    } else {
      node = $(node)
      node.length || self.fail('No node given to Sqaline.resize()')

      if (opt) {
        opt = self.parse(node, opt)
      } else if (self.opt.cache) {
        opt = self._cachingParse(node)
      } else {
        opt = node.attr(self.opt.attribute) == '#' ? node.data('sqao') : {}
        opt = self.parse(node, opt)
      }

      var event = opt.trigger && self.opt.beforeEvent
      event && opt.node[opt.trigger](event, [opt])

      if (opt.props.length) {
        var key = node.data('sqak')
        key || node.data('sqak', key = ++self._keySeq)
        self._resized[key] = true
        self._resize($.extend({}, opt))
      }

      var event = opt.trigger && self.opt.afterEvent
      event && opt.node[opt.trigger](event, [opt])
      return node
    }
  }

  // opt += {now: bool}.
  self.resizex = function (node, opt) {
    node = $(node)
    node.length || self.fail('No node given to Sqaline.resizex()')
    node.attr(self.opt.attribute, '#').data('sqao', self.parse(node, opt))
    opt.now && self.resize(node)
    return node
  }

  self.resizeAll = function (root) {
    var nodes = $('[' + self.opt.attribute + ']', root)
    $(root).attr(self.opt.attribute) == null || (nodes = nodes.add(root))

    if (!self._resizing) {
      self._resizing = true

      nodes.each(function () {
        try {
          self.resize(this)
        } catch (e) {
          console && console.error(e)
        }
      })

      self._resizing = false
    }

    return nodes
  }

  self._cachingParse = function (node, opt) {
    var data = node.data('sqao')

    if (data &&
        (data.cacheSeq == null || data.cacheSeq >= self._cacheSeq) &&
        (data.expr == null || data.expr == node.attr(self.opt.attribute))) {
      return data
    } else {
      opt = $.extend({cacheSeq: self._cacheSeq}, opt)
      node.data('sqao', opt = self.parse(node, opt))
      return opt
    }
  }

  // opt = {
  //   node: $, trigger: 'trigger' | 'triggerHandler' | false,
  //   cacheSeq: this._cacheSeq,
  //   expr: 'na,me ^{eval} ... | ...',
  //   verbose: false, name: 'na', anchors: ['na', 'me'], rules: ['^{eval} ...', '...'],
  //   props: [{  - props may duplicate (evaluated sequentially)
  //     prop:    'width',
  //     source:  '^{eval} ...',
  //     code:    'cx("^", "eval") ...',
  //     func:    Function ('cx', <code>),
  //     applier: 'css' | 'attr' | Function (Sqaline, opt, prop, value),
  //   }, ...],
  // }
  self.parse = function (node, opt) {
    opt = $.extend({trigger: self.opt.trigger}, opt, {node: node})

    if (!opt.props && !opt.expr) {
      opt.expr = $.trim(opt.node.attr(self.opt.attribute))
      self.opt.verbose && console.log('Sqaline parsing ' + opt.expr)
    } else if (self.opt.verbose) {
      console.log('Sqaline parsing:')
      console.dir([opt])
    }

    opt.expr && $.extend(opt, self.parseAttribute(opt.expr))

    if (opt.rules) {
      opt.props = []

      $.each(opt.rules, function (i, rule) {
        rule = $.trim(rule)
        // [prop:] [-] [...] [-]
        var parsed = rule.match(/^(?:([\w-]+):)?\s*(([-+]?\s*)(.*?)(\s*[-+]?))$/)
        parsed || self.fail('Bad Sqaline rule: ' + rule)

        var prop = parsed[1], expr = parsed[2], leftCut = parsed[3],
            innerExpr = parsed[4], rightCut = parsed[5]

        if (prop) {
          prop = self.ruleProps[prop] || prop
        } else if (i) {
          self.fail('Only first Sqaline |rule| can have omitted property: ' + rule)
        } else {
          prop = self.opt.defaultProperty
        }

        innerExpr || (expr = leftCut + '0' + rightCut)
        var cut = self._normCut('left', self.opt.leftCut, leftCut, prop)
        cut && (expr = (cut[1] || '') + ' ' + expr)
        var cut = self._normCut('right', self.opt.rightCut, rightCut, prop)
        cut && (expr += ' ' + (cut[1] || ''))
        var func = (new self.Parser(expr)).parse()

        opt.props.push({
          prop:       prop,
          source:     rule,
          code:       func._sqaCode,
          func:       func,
          applier:    testEl.css(prop) === undefined ? 'attr' : 'css',
        })
      })
    }

    return opt
  }

  self._normCut = function (sideName, shortcuts, op, prop) {
    if (op) {
      var match = prop.match(/^(min|max)([A-Z])(.*)$/)

      if (match && !(prop in shortcuts)) {
        prop = match[2].toLowerCase() + match[3]
      }
      if (!(prop in shortcuts)) {
        self.fail('Sqaline has no ' + sideName + '-hand shortcut for ' +
                  prop + ': ' + rule)
      }

      return [prop, shortcuts[prop]]
    }
  }

  self.parseAttribute = function (str) {
    var parsed = str.match(/^(!?)(\w+(?:,\w+)*)(\s+(.+))?$/)
    parsed || self.fail('Bad Sqaline attribute format: ' + str)
    var anchors = parsed[2].split(',')

    return {
      verbose:  parsed[1],
      name:     anchors[0],
      anchors:  anchors,
      rules:    parsed[4] ? parsed[4].split(' | ') : [],
    }
  }

  self._resize = function (opt) {
    opt.verbose = opt.verbose || self.opt.verbose

    if (opt.verbose) {
      console.info('Sqaline running on ' + opt.node[0].tagName + ': ' + opt.expr)
    }

    $.each(opt.props, function (i, item) {
      try {
        var prop = item.prop
        var value = self._context(opt)('', '', item.func)
        opt.verbose && console.log(prop + ' = ' + value)

        if (isNaN(value) && typeof value == 'number') {
          // isNaN('1.1em') => true
          if (console) {
            console.warn('Sqaline of ' + prop + ' evaluated to NaN: ' + opt.expr)
            console.dir && console.dir([$.extend({}, opt, item)])
          }
        } else if (typeof item.applier == 'string') {
          opt.node[item.applier](prop, value)
        } else {
          item.applier.call(self, opt, prop, value)
        }
      } catch (e) {
        if (console) {
          console.dir && console.dir([$.extend({}, opt, item)])
          if ((e + '').indexOf('Could not convert ') != -1) {
            console.warn('Looks like trying to read window\'s CSS properties due' +
                         ' to a referenced ^{anchor} node{...} not found above' +
                         ' this ' + opt.node[0].tagName + '.')
          }
        }

        self.fail('Error "' + e + '" evaluating Sqaline of ' + prop + ': ' + opt.expr)
      }
    })
  }

  self._context = function (opt) {
    return function (target, selector, code) {
      if (target == '') {
        var all = opt.node
      } else {
        // '^' or 'name'.
        target == '^' && (target = opt.name)
        var len = target.length
        var all = $win

        opt.node.parents('[' + self.opt.attribute + ']').each(function () {
          var attr = $(this).attr(self.opt.attribute)
          if (self.parseAttribute(attr).anchors.indexOf(target) != -1) {
            all = $(this)
            return false
          }
        })
      }

      selector && (all = $(selector, all))
      var cx = {self: self, opt: opt, sub: arguments.callee}
      var sum = 0

      all.each(function () {
        cx.el = $(this)
        var value = (code instanceof Function) ? code(cx) : eval(code)
        sum = typeof value == 'number' ? sum + value : value
        opt.verbose && console.dir([ {code: code._sqaCode || code, el: cx.el, res: value} ])
      })

      opt.verbose && all.length > 1 && console.log('  total = ' + sum)
      return sum
    }
  },

  self._nodeDistance = function (parent, el) {
    parent = parent.offset()
    el = el.offset()
    return {left: el.left - parent.left, top: el.top - parent.top}
  }

  self.run = function () {
    $win.on('resize.sqaline', self.resizeTimed)
    $win.on('load', self.resizeTimed)
    self.resizeTimed()
  }

  self.stop = function () {
    $win.off('.sqaline')
  }

  self.Parser = function (rule) {
    var self = this
    var pos = 0
    var len = rule.length

    self.fail = function (msg, errorPos) {
      arguments.length > 1 || (errorPos = pos)
      if (errorPos >= 0 && errorPos < len) {
        errorPos > 0 || (errorPos = 0)
        rule = rule.substr(0, errorPos - 1) + '*HERE*' + rule.substr(errorPos - 1)
      }

      msg = 'Bad Sqaline rule "' + rule + '": ' + msg
      Sqaline.fail(msg)
    },

    self.parse = function () {
      var code = ''
      var openPos = pos

      while (pos < len && rule[pos] != '}') {
        if (rule[pos] == '{') {
          var start = self._findStart(pos - 1)
          var end = pos++
          code += self._genCode(openPos, start) +
                  self._genSub(start, end, self.parse())
          rule[pos] == '}' || self.fail('unterminated "{"', start)
          openPos = ++pos
        } else {
          pos++
        }
      }

      code += self._genCode(openPos, pos)
      if (!(code in Sqaline._funcs)) {
        var func = new Function('cx', 'return ' + code)
        Sqaline._funcs[func._sqaCode = code] = func
      }

      return Sqaline._funcs[code]
    }

    self._findStart = function (pos) {
      // ... [name|^]  [$[(]...[)]]  { expr }
      //     ^ return                ^ pos
      var braceLevel = 0
      var string = false

      for (; pos >= 0; pos--) {
        if (string) {
          if (rule[pos] == string) {
            for (var i = pos - 1; i >= 0 && rule[i] == '\\'; i--) { }
            (pos - i) % 2 && (string = false)
            pos = i + 1
          }
        } else if (rule[pos] == '"' || rule[pos] == "'") {
          string = rule[pos]
        } else if (rule[pos] == ')') {
          braceLevel++
        } else if (rule[pos] == '(') {
          if (--braceLevel < 0) {
            // E.g. '1 + ({ ... } / 2)' where '(' isn't part of selector but of
            // the parent expression.
            return pos + 1
          } else if (braceLevel) {
            // continue
          } else if (rule[pos - 1] != '$') {
            // Bracket was opened and closed so it must be of '...$(foo){ ... }'.
            self.fail('el reference must start with "$"', pos)
          } else {
            pos--
          }
        } else if (braceLevel) {
          // continue
        } else if (/\s/.test(rule[pos])) {
          return pos + 1
        }
      }

      if (string) {
        self.fail('unterminated string')
      } else if (braceLevel) {
        self.fail('too many ")"')
      } else {
        return 0  // e.g. rule == 'wrapper{...}'.
      }
    }

    self._genCode = function (startPos, endPos) {
      return rule
        .substr(startPos, endPos - startPos)
        .replace(Sqaline._rulePropRE, function (match, prop) {
          var code = Sqaline.ruleProps[prop]
          code.indexOf('(') == -1 && (code = 'parseFloat(cx.el.css(\'' + code + '\'))')
          return code
        })
    }

    self._genSub = function (startPos, endPos, func) {
      // name$(data-foo="with spaces")
      var prefix = rule.substr(startPos, endPos - startPos)
      var match = prefix.match(/^((?:\w+|\^)?)\$(?:\((.*)\)|(.*))$/) || ['', prefix, '']
      var target = match[1]
      var selector = match[2] || match[3]

      return "cx.sub('" + self.slashes(target) + "'," +
             " '" + self.slashes(selector) + "'," +
             " cx.self._funcs['" + self.slashes(func._sqaCode) + "'])"
    }

    self.slashes = function (str) {
      return (str || '').replace(/([\\'])/g, '\\$1')
    }
  }

  self.init = function () {
    $ || self.fail('Sqaline requires jQuery')

    var re = ''
    for (var prop in self.ruleProps) { re += '|' + prop }
    self._rulePropRE = new RegExp(self._rulePropRE.replace('%%', re.substr(1)), 'g')
  }

  self.init()
  $(self.run)
})(jQuery, window.Sqaline);