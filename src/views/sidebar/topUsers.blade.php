<?php /*
  - $users            - array of Models\User
*/?>

<h6 class="hvl-sidebar-title">
  {{{ trans('habravel::g.sidebar.topUsers') }}}
</h6>

<ol>
  @foreach ($users as $user)
    <li>
      {{-- One liner to prevent underscored spaces around the name (link) --}}
      <a href="{{{ $user->url() }}}"><img src="{{{ $user->avatarURL() }}}" alt="{{{ $user->name }}}">{{{ $user->name }}}</a>

      <span>+{{{ $user->score }}}</span>
    </li>
  @endforeach
</ol>