<!DOCTYPE html>
<html data-sqa="wr" style="height: 100%">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>{{{ $pageTitle }}}</title>

    <link rel="shortcut icon" href="{{{ asset('favicon.ico') }}}" type="image/x-icon">

    <link type="text/css" rel="stylesheet" media="all"
          href="{{{ asset('packages/proger/habravel/normalize.css') }}}">
    <link rel="stylesheet/less" type="text/css" media="all"
          href="{{{ asset('packages/proger/habravel/styles.less') }}}">

    <script>
      var less = {env: 'development', async: false}
    </script>
    <script src="https://raw.githubusercontent.com/less/less.js/master/dist/less-1.7.2.min.js"></script>
  </head>
  <body class="hvl-root">
    @yield('content')

    <script src="{{{ asset('packages/proger/habravel/underscore.js') }}}"></script>
    <script src="{{{ asset('packages/proger/habravel/jquery.js') }}}"></script>
    <script src="{{{ asset('packages/proger/habravel/sqaline.js') }}}"></script>
    <script src="{{{ asset('packages/proger/habravel/chart.js') }}}"></script>
    <script src="{{{ asset('packages/proger/habravel/app.js') }}}"></script>
  </body>
</html>