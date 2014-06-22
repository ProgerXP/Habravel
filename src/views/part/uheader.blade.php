<?php /*
  - $pageUser         - User instance or null (unauthorized)
*/?>

<?php $root = url(Config::get('habravel::g.rootURL'))?>

<header class="hvl-uheader">
  <div class="hvl-uheader-left">
    <a href="{{{ $root }}}/~"><i class="hvl-i-laravel22"></i></a>

    @if ($pageUser)
      <a href="{{{ $root }}}/~">{{ $pageUser->nameHTML() }}</a>
      <a href="{{{ $root }}}/logout">{{{ trans('habravel::g.uheader.logout') }}}</a>
    @else
      <a href="{{{ $root }}}/~">{{{ trans('habravel::g.uheader.login') }}}</a>
    @endif
  </div>

  <div class="hvl-uheader-right">
    <a href="{{{ $root }}}/tags/draft">{{{ trans('habravel::g.uheader.drafts') }}}</a>
    <a href="{{{ $root }}}/compose">{{{ trans('habravel::g.uheader.compose') }}}</a>
    <a href="{{{ $root }}}/~">{{{ trans('habravel::g.uheader.profile') }}}</a>
  </div>
</header>