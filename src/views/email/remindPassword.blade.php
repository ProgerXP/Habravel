<?php /*
  - $url              - string, remind URL
*/?>

<!DOCTYPE html>
<html lang="{{{ Config::get('app.locale') }}}">
  <head>
    <meta charset="utf-8">
  </head>
  <body>
    <h3>{{{ trans('habravel::g.remindPassword.mailSubject') }}}</h3>
    <p>{{{ trans('habravel::g.remindPassword.mailText') }}}</p>
    <p>
      {{ HTML::link($url, $url) }}
    </p>
    <hr>
    <p><small>{{ HTML::link(Habravel\url(), trans('habravel::g.pageTitle')) }}</small></p>
  </body>
</html>