/*!
  Sqaline.js - from Squizzle.me Toolkit
  Dynamic, fast and flexible element alignment
  In public domain | by Proger_XP | http://proger.me
*/

/*
  Sqaline is an expression-based library that lets you set arbitrary CSS
  or HTML attribute of specific elements based on those elements' environment.
  Sqaline expressions are regular JavaScript with certain convenient shortcuts.
  You can also create resize callbacks by listening to Sqaline events.

  The easiest way to align ("sqaline") an element is via data-sqa attribute or
  another attribute name specified in Sqaline.attribute:

    <body data-sqa="wrapper">
      <header>...</header>
      <nav style="right: 0; ...">...</nav>
      <article data-sqa="wrapper height: win{h} - ^$header{ho} - ^$footer{ho}
                               | width: win{w} - ^$nav{wo} - mr">
      <footer>...</footer>
    </body>

  This example will make <article> occupy all horizontal window space not
  taken by <header> and <footer> and will set its width to window's width
  without <nav> outerWidth(true) and css('marginRight').

  Sqaline attribute begins with comma-separated (no spaces) names of ANCHORS
  relative to which the expression is evaluated; first anchor becomes anchor
  name of this element in turn. Anchors are searched upwards in the DOM tree.
  Here <article> has an anchor name of "wrapper" and its parent anchor element
  is <body> because it also has the same name. Nested anchors always shade
  their parents so all calculations will be done in the new context.
  ANCHORS can also begin with "!" to enable verbose console output for this
  particular element (see also Sqaline.opt.verbose): data-sqa="!wr,wr2 ...".

  After anchors follow zero or more pipe-separated RULES (must have spaces
  around "|"). Each rule modifies a particular CSS property or an HTML attribute.
  They can duplicate for one target (property or attribute) and are evaluated in
  order of definition. A rule begins with property/attribute name followed by a
  colon. The target is set to CSS property if the name is listed in $(el).css('name'),
  otherwise $(el).attr('name') is used to set the calculated value. The target
  can be a shortcut CSS defined in Sqaline.exprShortcuts - for example, 'fns'
  equals to 'fontSize' and 'mr' to 'marginRight'.

  The first RULE can omit target name and the colon - in this case it's assumed
  to be Sqaline.defaultProperty: data-sqa="wrapper win{h}".

  After the colon actual EXPRESSION is specified. This is regular JavaScript
  code with several optimizations:

    Certain CSS properties have shortcuts like 'mr' expanding to marginRight
    or wo expanding to $(el).outerWidth(true). The list is specified in
    Sqaline.exprShortcuts and can be changed even on runtime given that cache
    is not used or clearCache() is called after customization.

    Left- and right-side shortcuts can be used to refer to common calculations.
    For example, just "height: -" will return amount of vertical space that
    is available in the parent's anchor element after this (evaluating) element
    topmost border. See Sqaline.opt.leftCut and rightCut for details.

    By default the code is ran in this element's context so 'w' will return
    this element's width. However, it is possible to refer to other elements
    in the vicinity by using {SUBEXPRESSION}.

  SUBEXPRESSION is a way to change the calculation context. The syntax is:

    [^|ANCHOR] [$ [(] jQuery-SELECTOR [)]] { Sqaline-CODE }

  Square [brackets] indicate optional components. Spaces are not allowed.

  ANCHOR references base element by its data-sqa ANCHOR described in the
  beginning. '^' refers to the same anchor name as of this element. If omitted
  the element being sqalined is used as a base element. If no parent element
  is found $(window) is used - this can be both useful (to read its dimensions)
  and dangerous (attempts to read its CSS will result in errors). Referenced
  anchors are resized before calculating the SUBEXPRESSION thus providing actual
  state. Later resizing references of the same anchor element are ignored.

  SELECTOR is a jQuery expression for selecting nodes relative to the located
  ANCHOR or relative to entire document if $(window) is used. This is exactly
  the same as writing $('SELECTOR', $(ANCHOR)). SELECTOR can be wrapped in
  brackets if it contains spaces (without them SUBEXPRESSION will break on
  the last space): ^$([class="first second"]){...} - is valid but without
  brackets will read as: second"]){...} - which is a wrong syntax. SELECTOR
  can also contain other "complex" but valid JS code such as \escape sequences,
  more brackets and various kinds of/nested strings.

  CODE is the same EXPRESSION code contained within a rule. It can in turn
  contain more SUBEXPRESSIONs and regular brackets, for example:

    wrapper top: Math.max(0, (win{h - $footer{ho}} - {h}) / 2) + 'px'

  SUBEXPRESSIONs don't inherit context elements so that ^{...} or similar
  will always locate parent anchor relative to the initial element (the one
  being sqalined). The above compiles to this code (el = initial element):

    $(el).css('top', Math.max(0, (
        $(window).height -
        $('footer', el).outerHeight(true) -
        $(el).height()
      ) / 2) + 'px')

  Examples of SUBEXPRESSIONs (spaces inside { } are put for better readability):

    ^{ ... }
      - execute in context of parent anchor or $(window) if not found
    ^$footer{ ... }
      - find parent anchor or $(window) and then find all <footer> elements
        as both direct and indirect children (the behaviour of $(..., cx))
    $footer{ ... }
      - find all <footer> children of the current element
    someAnchor$footer{ ... }
      - find <footer> under someAnchor parent; if this element's anchor is
        someAnchor then it's identical to ^$footer{ ... }, i.e. finding all
        <footer>s under this element's parent element
    someAnchor{ ... }
      - similar to ^{ ... } but finds specific parent with anchor 'someAnchor'
    $[target="blank"]{ ... }
      - finds all children with target="blank" HTML attribute of this element
    $([target="_blank"]){ ... }
      - identical to the above - since the selector has no spaces brackets
        here are redundant (they are simply removed)
    win$(a[class="foo bar"]:not(:visited)){ ... }
      - locates all <a> elements in this document that have class attribute
        set to exactly "foo bar" and that are not :visited; equivalent to
        this jQuery call: $('a[class="foo bar"]:not(:visited)').
    win$a.foo.bar:not(:visited){ ... }
      - unlike the above will find <a> that have both 'foo' and 'bar' classes,
        in any order, and possibly others
    $#someID.class{ ... }
      - locates an element with id="someID" and having CSS class 'class'
        that belongs to the sqalined element, directly or not
    win#someID.class{ ... }
      - same as above but looks document-wise (provided that 'win' anchor
        parent is not found)
    $>p.foo{ ... }
      - locates all <p> elements with 'foo' CSS class that directly belong
        to the sqalined element: $('> p.foo', el) or $(el).find('> p.foo').

  It is recommended not to use Sqaline-specific contaxt code inside as it might
  change but if you must - the 'cx' variable is an object with these keys:

    - el            - current $(element) according to the SUB/EXPRESSION context;
                      all CSS property reads, etc. are performed on this instance
    - self          - always refers to window.Sqaline
    - opt           - parsed data-sqa value; this is an internal object with lots of
                      useful values - see parse() for details
    - opt.node      - the initial $(element) that is being sqalined; same as cx.el
                      for the outmost EXPRESSION or bare {...} SUBEXPRESSION
    - opt.name      - anchor name of the initial element (first in the anchor list)
    - opt.anchors   - array of all anchors of the element including opt.name
    - sub           - a function used to enter a new SUBEXPRESSION context

  If a SUBEXPRESSION has matched multiple elements they are all evaluated and
  their results are: (1) summed up together if typeof new result == 'number',
  or (2) if result isn't a number it overrides previously calculated value.

    win$p{ ho }
      - finds all <p> in the document and calculates their total outerHeight(true)
    $>a{ cx.el.css('color') }
      - returns the color of last direct <a> child
    ^$~*{ wo }
      - locates parent element with the same anchor name as this element's,
        finds all elements that follow the parent (forward siblings) and sums up
        their outerWidth(true)
    $~*{ wo }
      - similar to above but calculates total width of next siblings of the
        element being sqalined
*/

window.Sqaline = new (function ($, opt) {
  var self = this
  var Sqaline = this
  var testEl = $('<div>')
  var $win = $(window)

  /***
    Sqaline options. Some can be changed on runtime, some not, some
    require clearCache(). See details below.
   ***/

  self.opt = $.extend({
    // Controls how often resizeTimed() will initiate resizing of all
    // Sqalinable elements on this page. Generally it means the delay
    // for onresize window event refresh.
    resizeDelay:      200,

    // If set Sqaline will store parsed data-sqa for each element and
    // reuse that value without checking that attribute again. This
    // boosts performance a lot because it also implies parsing of rules
    // like ^{wo - {h}} into JavaScript code. When this is set you can
    // still use clearCache() to reset all previously parsed data and
    // cache resizing nodes again.
    cache:            true,

    // If set will log a lot of info about each element being sqalined.
    // Overrides individual elements' verbose flag (set with '!').
    verbose:          false,

    // If first property listed in data-sqa has no 'prefix:' this value
    // is used. Note that it only works for the first property on the list;
    // others must be named: data-sqa="wr def" but not "wr prop: w | def".
    // If changed on runtime clearCache() must be called.
    defaultProperty:  'height',

    // Name of jQuery function used to fire events on each node being
    // sqalined. Makes sense to use 'trigger' or 'triggerHandler'.
    // 'trigger' will cause events bubbling throughout the entire DOM which
    // is useful but might cause lags. Can also be set to false to disable
    // event triggering completely. See other options below.
    trigger:          'triggerHandler',

    // Only used if trigger is set. Arbitrary name of event being fired.
    // Empty value disables that particular event from firing.
    beforeEvent:      'sqalining',
    // The same but fired after the element has finished resizing.
    afterEvent:       'sqalined',

    // HTML attribute scanned for Sqaline rules. Recommended to start with
    // 'data-' to be W3C-compliant.
    // If changed on runtime clearCache() must be called.
    attribute:        'data-sqa',

    // "Cuts" are shortcuts for common rules on certain properties that are
    // activated by starting (leftCut) or ending (rightCut) an expression
    // with either '+' or '-' signs. If expression is just '+' or '-' then
    // leftCut is used. If you need to start with a negative number put
    // '0 - ...' to avoid leftCut being used.
    // If changed on runtime clearCache() must be called.
    //
    // min/maxHeight/Width are mapped to height/width if undefined.
    // If no leftCut or rightCut is found for an expression where such a
    // shortcut was used an error will be thrown and element will be skipped.
    //
    // Examples:
    //    data-sqa="wr height: -"
    //      - will use leftCut.height in place of the entire expression
    //    data-sqa="wr top: - -"
    //      - equivalent to "leftCut - rightCut" for the 'top' property
    //    data-sqa="wr - ^{wo / 2} -"
    //      - first defaultProperty is determined (usually 'height'), then
    //        the rule is prepended with leftCut, then "- ^... -" is put and
    //        finally rightCut wraps up the expression
    //    data-sqa="wr ^{wo / 2} -"
    //      - just like above but doesn't use leftCut, starting with "^... -"
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

  /***
    Definition of shortcuts available in rule expressions. They are expanded
    and inlined into the resulting JS code. Current context is available as 'cx'
    variable (see _context() for details).

    If changed on runtime clearCache() must be called.
    Keys should not contain any RegExp-special chars.

    Values without '(' are treated as numeric CSS property reads and set
    to parseFloat($().css('VALUE')) - thus not suitable for non-numeric
    properties (use cx.el.css('PROP') for these).

    Values without '(' can also be used before colon in RULES to be expanded:
    data-sqa="wr fns: ^{w * .1} + 'em'" turns 'fns' into 'fontSize' given that
    it's listed in this object.
   ***/

  self.exprShortcuts = {
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

    // 'o' is the largest value, 'b' omits margin, 'w' also omits border,
    // 'i' also omits paddig thus being the smallest value.
    'wo':   'cx.el.outerWidth(true)',
    // 'b' = with 'b'order but without margin ('wo').
    'wb':   'cx.el.outerWidth()',
    'w':    'cx.el.width()',
    'wi':   'cx.el.innerWidth()',

    'ho':   'cx.el.outerHeight(true)',
    'hb':   'cx.el.outerHeight()',
    'hi':   'cx.el.innerHeight()',
    'h':    'cx.el.height()',

    // Offset of the element relative to context element, for example, to skip
    // all preceding elements. For example, in:
    //    <article data-sqa="wr"><p data-sqa="wr ...">
    // 'lt' will return Y distance from left-top corner of <p> to left-top
    // corner of <article>. 'll' will do the same for X.
    'lt':   'cx.self._nodeDistance(cx.el, cx.opt.node).top',
    'll':   'cx.self._nodeDistance(cx.el, cx.opt.node).left',

    // Absolute offset relative to document body.
    'ot':   'cx.el.offset().top',
    'ol':   'cx.el.offset().left',

    'btm':  'bottom',
    'top':  'top',
    'lft':  'left',
    'rht':  'right',

    // Naming scheme for the following shortcuts is simple:
    //    first letter + first consonant + first capitalized letter
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

  /***
    Internal State
   ***/

  // Used to generate document-wise unique sequences.
  self._keySeq = 0
  // Used to discard old data-sqa cache after clearCache() was called.
  self._cacheSeq = 1
  // Used to trigger delayed resizing in resizeTimed().
  self._resizeTimer = null
  self._resizePending = false
  // If set means that Sqaline currently resizes the entire DOM in resizeAll().
  self._resizing = false
  // Used to keep track of already resized elements in resizeAll() to avoid
  // recursion when elements refer to each other as anchors{...}.
  self._resized = {}
  // Compiled expressions. Keys are source JS code (expanded, ready for eval()),
  // values are Function instances that expect 'cx' as the only argument.
  self._funcs = {}    // {'cx.code ...': Function}.
  // Used to replace exprShortcuts with their code equivalents. '%%' is replaced
  // by all shortcuts glued with '|'. Is set in init().
  self._exprShortcutRE = '\\b(%%)\\b'

  /***
    Methods
   ***/

  // Something went critically wrong.
  self.fail = function (msg) {
    // Outputs stack trace in Firebug.
    console && console.error(msg)
    throw msg
  }

  // Re-evaluates all data-sqa attributes on next resize. Usually used after
  // changing some option on runtime (see opt and exprShortcuts above).
  // Not useful if opt.cache is set to false.
  self.clearCache = function () {
    self._cacheSeq++
    return self
  }

  // Initiates resizing of entire DOM now or, if it has been already called
  // in the previous opt.resizeDelay msec, - after this period has elapsed.
  // Somewhat equivalent to _.debounce().
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

  // If delay is not given or 0 - launches DOM resize in another thread which
  // is useful to let the browser finish remaining updates and start anew.
  // Equivalent to _.defer().
  //
  // If delay is given waits the specified amount of msec before doing so.
  // Note that it doesn't prevent other resizeDelayed() threads from being
  // scheduled if it has been called several times - use resizeTimed() for this.
  //
  //? resizeDelayed(0)
  //? resizeDelayed()     // identical
  //? resizeDelayed(300)  // 0.3 sec delay until resizeAll()
  self.resizeDelayed = function (delay) {
    setTimeout(function () {
      var time = Date.now()
      var nodes = self.resizeAll()

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

  // Without arguments calls resizeAll(). With one argument resizes that
  // specific element taking current cache into account. With two arguments
  // resizes the element parsing given options every time.
  //
  // If node matches multiple elements takes the first one. node can be also
  // a selector, a DOM node or a jQuery object.
  // Returns jQuery collection of the resized element(s).
  //
  //? resize()    //=> $( [data-sqa], [data-sqa], ... )
  //
  //? resize($('a[data-sqa]'))
  //    // resizes first <a> with data-sqa attribute found in this document;
  //    // caches parsed data-sqa value and reuses on subsequent calls;
  //    // returns $(<a>)
  //
  //? resize('a', {expr: '!wr top: ^{h} - ...'})
  //    // takes first <a> in the document and resizes it according to given
  //    // options (see parse() for the object format); returns $(<a>)
  self.resize = function (node, opt) {
    if (!arguments.length) {
      return self.resizeAll()
    } else {
      node = $(node).eq(0)
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

  // More flexible setup of a node to be sqalined on next resize. Lets you
  // set resize rules as an object instead of data-sqa string - see parse()
  // for the exact format. Once called on node (a DOM node, a jQuery object
  // or a selector) will discard its data-sqa value, if any, and remember
  // given opt so that it is used on next resize pass on this element.
  //
  // If node matches multiple elements takes the first one.
  // If opt has {now: true} item calls resize(node) before returning.
  // Returns $(node).
  //
  //= resizex('a', {trigger: 'trigger', cacheSeq: null, expr: '!wr top: ^{h} - ...'})
  //    //=> $(<a>), makes the node fire opt.beforeEvent and opt.afterEvent
  //    // on each resize with $().trigger() and also makes it immune to
  //    // opt.cache value, never clearing this particular cache.
  self.resizex = function (node, opt) {
    node = $(node).eq(0)
    node.length || self.fail('No node given to Sqaline.resizex()')
    node.attr(self.opt.attribute, '#').data('sqao', self.parse(node, opt))
    opt.now && self.resize(node)
    return node
  }

  // Resizes all nodes in the document. Can be called multiple times - if
  // a resize is in progress such calls are ignored (this happens regardless
  // of root). root is a selector, a DOM node or a jQuery object under which
  // elements with data-sqa attribute are searched and resized. If root itself
  // has data-sqa it's resized as well.
  //
  // If an error occurs while resizing an element it's skipped and the
  // error is written to the console, if present.
  //
  // Returns $() of all nodes that were resized (or would-be resized if a
  // resize was currently active).
  //
  //? resizeAll()
  //    //=> $('[data-sqa]'), resize all elements in the DOM
  //
  //? resizeAll($('<header>'))
  //    //=> $('header [data-sqa]'), resize all elements under all <header>s
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

  // Checks if node has assigned cache of previously parsed opt and if that
  // cache is actual according to _cacheSeq - if so returns the cache, if
  // not - parses and returns opt, also caching it for future calls.
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

  // Parses given opt into final Sqaline format with rules mapped to compiled
  // JS expressions, etc. opt format is considered internal so use at your own
  // risk - it's not future-proof. Usually you don't need to call this function.
  //
  // opt can be given members of various "raw" levels to achieve more
  // flexibility. If opt.expr is not given it's read from data-sqa and broken
  // down into some flags and rules (see parseAttribute()). Rules are then
  // parsed into more flags and their code is compiled.
  //
  // Returns entirely parsed opt which has this format:
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
    node = $(node).eq(0)
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
          prop = self.exprShortcuts[prop] || prop
        } else if (i) {
          self.fail('Only first Sqaline |rule| can have omitted property: ' + rule)
        } else {
          prop = self.opt.defaultProperty
        }

        innerExpr || (expr = leftCut + '0' + rightCut)

        var cut = leftCut && self._normalizeCut('left', self.opt.leftCut, prop)
        cut && (expr = (cut[1] || '') + ' ' + expr)

        var cut = rightCut && self._normalizeCut('right', self.opt.rightCut, prop)
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

  // Extracts common fields from the given data-sqa value string.
  //
  //? parseAttribute('!wr,wr2 top: lt | left: 0')
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

  // Used to map leftCut or rightCut shortcuts into full expressions.
  //
  //? _normalizeCut('left', {height: 'foo'}, 'maxHeight')
  //    //=> ['height', 'foo']
  self._normalizeCut = function (sideName, shortcuts, prop) {
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

  // Internal function used to resize a parsed element. Recursion-unsafe.
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
          console.warn('Error follows - see if you are not trying to read window\'s' +
                       ' CSS properties due to a referenced anchor{...} not' +
                       ' found above this ' + opt.node[0].tagName + '.')
        }

        self.fail('Error "' + e + '" evaluating Sqaline of ' + prop + ': ' + opt.expr)
      }
    })
  }

  // Returns a function that evaluates Sqaline JS code as a SUB/EXPRESSION.
  //
  // target = anchor ('', '^' or 'anchorName').
  // selector = CSS selector to find descendants of target.
  // code = Function object or a string to be eval()'ed. It will have cx
  // variable visible in scope and should return the calculated value (number
  // or something else).
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

      selector && (all = $(selector, all !== $win && all))
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

  // Calculates offset of el relative to parent offset. $().offset() returns
  // offset absolute to document body.
  //
  //? _nodeDistance($(el), $(el).find('p').eq(1))
  //    //=> {left: 15, top: 22}
  self._nodeDistance = function (parent, el) {
    parent = parent.offset()
    el = el.offset()
    return {left: el.left - parent.left, top: el.top - parent.top}
  }

  // Registers global Sqaline event handlers. Called by $.ready(). Makes
  // Sqaline automatically resize document elements on window size change.
  self.run = function () {
    $win.on('resize.sqaline', self.resizeTimed)
    $win.on('load', self.resizeTimed)
    self.resizeTimed()
  }

  // Unregisters global Sqaline handlers. If you want to resume Sqaline
  // automatically resizing document elements call run().
  self.stop = function () {
    $win.off('.sqaline')
  }

  /***
    Expression Parser

    Given a raw rule expression like (^{h - {h}} / 2) turns it into a compiled
    Function object. Handles recursive {subexpressions} and other stuff.
   ***/

  self.Parser = function (rule) {
    var self = this
    var pos = 0
    var len = rule.length

    // Parse error near specific offset in rule (or unspecific, if errorPos is
    // not given or is out of bounds).
    self.fail = function (msg, errorPos) {
      arguments.length > 1 || (errorPos = pos)
      if (errorPos >= 0 && errorPos < len) {
        errorPos > 0 || (errorPos = 0)
        rule = rule.substr(0, errorPos - 1) + '*HERE*' + rule.substr(errorPos - 1)
      }

      msg = 'Bad Sqaline rule "' + rule + '": ' + msg
      Sqaline.fail(msg)
    },

    // Parses the rule string given to the constructor. Returns Function.
    // Stores intermediate subexpressions in global Sqaline._funcs.
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

      // Since generated closures don't rely on scope in this place but only
      // on their arguments we safely reuse these objects if subexpressions
      // turn out to be identical to those compiled before.
      if (!(code in Sqaline._funcs)) {
        var func = new Function('cx', 'return ' + code)
        Sqaline._funcs[func._sqaCode = code] = func
      }

      return Sqaline._funcs[code]
    }

    // Tracks pos back to the beginning of SUBEXPRESSION. It's not that simple
    // given that it may have $(...) with spaces, strings (with \escapers) and
    // nested brackets. Returns offset of the exact first symbol of SUBEXPRESSION
    // - either '^', anchor name, '$' or '{' or none of these were found.
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

    // Turns an isolated piece of Sqaline expression code into valid JavaScript
    // by expanding exprShortcuts. Note that the startPos..endPos part can have
    // uncloser brackets, etc. so it won't run by its own and this should not
    // make changes that will break original expression syntax when it's merged.
    self._genCode = function (startPos, endPos) {
      return rule
        .substr(startPos, endPos - startPos)
        .replace(Sqaline._exprShortcutRE, function (match, prop) {
          var code = Sqaline.exprShortcuts[prop]
          code.indexOf('(') == -1 && (code = 'parseFloat(cx.el.css(\'' + code + '\'))')
          return code
        })
    }

    // Turns a part of valid JS code func that should be evaluated inside a
    // specific context into a proper JS call to cx.sub (see _context()).
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

    // Escapes string so that it can be evaluated as 'str' without \ and '
    // breaking the syntax. Doesn't wrap str into quotes.
    //
    //? slashes('f\\o\'o')    //=> 'fo\\\\o\\\'o'
    //? eval('return ' + slashes('f\\o\'o'))    //=> f\o'o
    self.slashes = function (str) {
      return (str || '').replace(/([\\'])/g, '\\$1')
    }
  }

  /***
    Entry Point
   ***/

  // Called immediately after loading this library.
  self.init = function () {
    $ || self.fail('Sqaline requires jQuery')

    var re = ''
    for (var prop in self.exprShortcuts) { re += '|' + prop }
    self._exprShortcutRE = new RegExp(self._exprShortcutRE.replace('%%', re.substr(1)), 'g')
  }

  self.init()
  $(self.run)
})(jQuery, window.Sqaline);