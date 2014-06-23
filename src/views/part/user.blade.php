<?php /*
  - $user             - User instance
  - $link             - true/false
*/?>

@if ($link)
  <a href="{{{ $user->url() }}}"
@else
  <span
@endif
  class="hvl-uname hvl-uname-{{{ $user->score == 0 ? 'zero' : ($user->score > 0 ? 'above' : 'below') }}}">

  {{{ $user->name }}}

  @if ($user->score)
    <sup>
      {{{ ($user->score > 0 ? '+' : '').(int) $user->score }}}
    </sup>
  @endif

@if ($link)
  </a>
@else
  </span>
@endif
