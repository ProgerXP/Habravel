<?php /*
  - $page             - boolean
  - $perPage          - number
  - $pageURL          - string
  - $morePages       - boolean
*/?>

<p class="hvl-pages">
  &larr;

  @if ($page > 1)
    <a href="{{{ $pageURL.($page - 1) }}}">
  @endif

  {{{ trans('habravel::g.pages.back') }}}@if ($page > 1)</a>@endif

  <span class="hvl-pages-separ">|</span>

  @if ($morePages)
    <a href="{{{ $pageURL.($page + 1) }}}">
  @endif

  {{{ trans('habravel::g.pages.next') }}}@if ($morePages)</a>@endif

  &rarr;

  <a class="hvl-credit" href="http://laravel.ru/habravel" target="_blank">{{{ trans('habravel::g.credit') }}}</a>
</p>