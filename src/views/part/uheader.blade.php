<?php /*
  - $pageUser         - User instance or null (unauthorized)
*/?>

<?php $root = Habravel\Core::url()?>

<header class="hvl-uheader">
  <div class="hvl-uheader-left">
    <a href="{{{ $root }}}/~" class="hvl-uheader-icon"><i class="hvl-i-laravel24"></i></a>

    @if ($pageUser)
      {{ $pageUser->nameHTML() }}
      <a href="{{{ $root }}}/logout?_token={{{ urlencode(csrf_token()) }}}">{{{ trans('habravel::g.uheader.logout') }}}</a>
    @else
      <a href="{{{ $root }}}/~">{{{ trans('habravel::g.uheader.login') }}}</a>
    @endif
  </div>

  <div class="hvl-uheader-right">
    <a href="{{{ $root }}}/compose">{{{ trans('habravel::g.uheader.compose') }}}</a>
    <a href="{{{ $root }}}/~">{{{ trans('habravel::g.uheader.profile') }}}</a>
  </div>
</header>