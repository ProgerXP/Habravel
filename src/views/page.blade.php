<?php /*
  - $pageTitle        - string
  - $pageMetaDesc     - string
  - $pageMetaKeys     - string
  - $pageHeader       - string
  - $pageSidebar     - array of string
*/?>

<!DOCTYPE html>
<html data-sqa="wr" style="height: 100%" lang="{{{ Config::get('app.locale') }}}">
  <head>
    <meta charset="utf-8">

    <title>{{{ $pageTitle }}}</title>

    <meta name="description" content="{{{ $pageMetaDesc }}}">
    <meta name="keywords" content="{{{ $pageMetaKeys }}}">
    <meta name="generator" content="Habravel community blog engine">

    <link rel="shortcut icon" href="{{{ asset('favicon.ico') }}}" type="image/x-icon">

    <link rel="stylesheet/less" type="text/css" media="all"
          href="{{{ asset('packages/proger/habravel/styles.less') }}}">

    <script>
      var less = {env: 'development', async: false}
    </script>
    <script src="https://raw.githubusercontent.com/less/less.js/master/dist/less-1.7.2.min.js"></script>
  </head>
  <body class="hvl-root {{ $pageSidebar ? 'hvl-with-sidebar' : '' }}">
    {{ $pageHeader }}

    <div class="hvl-content">
      @yield('content')
    </div>

    @include('habravel::part.sidebar', compact('pageSidebar'), array())

    <script src="{{{ asset('packages/proger/habravel/underscore.js') }}}"></script>
    <script src="{{{ asset('packages/proger/habravel/jquery.js') }}}"></script>
    <script src="{{{ asset('packages/proger/habravel/sqaline.js') }}}"></script>
    <script src="{{{ asset('packages/proger/habravel/chart.js') }}}"></script>
    <script src="{{{ asset('packages/proger/habravel/app.js') }}}"></script>
  </body>
</html>