<?php /*
  - $user             - Models\User instance
  - $link             - true/false
*/?>

@if ($link)
  <a href="{{{ $user->url() }}}"
@else
  <span
@endif
  class="hvl-uname hvl-uname-{{{ $user->score == 0 ? 'zero' : ($user->score > 0 ? 'above' : 'below') }}}">

  <i>{{{ $user->name }}}</i>

  @if ($user->score)
    <sup>
      {{ ($user->score > 0 ? '+' : '').Habravel\number($user->score) }}
    </sup>
  @endif

@if ($link)
  </a>
@else
  </span>
@endif
