<?php /*
  - $user             - Models\User instance
  - $badges           - array of string
*/

  $fields = array(
    'bitbucket' => array('https://bitbucket.org/'),
    'github'    => array('https://github.com/'),
    'facebook'  => array('https://www.facebook.com/'),
    'twitter'   => array('https://twitter.com/'),
    'vk'        => array('https://vk.com/'),
    'skype'     => array('skype:'),
    'jabber'    => array('xmpp:'),
  );
?>

<div class="hvl-puser-info">
  @if ($badges)
    <p>
      @foreach ($badges as $badge)
        <img src="{{{ asset("packages/proger/habravel/badges/$badge.png") }}}"
             title="{{{ trans("habravel::badges.$badge", $user->getAttributes()) }}}">
      @endforeach
    </p>
  @endif

  <p>
    <b>{{{ trans('habravel::g.user.regTime') }}}</b>
    {{{ DateFmt::Format('AGO-AT[s-d]IF>7[d# m__ y##]', $user->created_at->timestamp, Config::get('app.locale')) }}}
  </p>

  @if ($user->loginTime)
    <p>
      <b>{{{ trans('habravel::g.user.loginTime') }}}</b>
      {{{ DateFmt::Format('AGO-AT[s-d]IF>7[d# m__ y##]', $user->loginTime->timestamp, Config::get('app.locale')) }}}
    </p>
  @endif

  @if ($user->site)
    <p>
      <b>{{{ trans('habravel::g.profile.site') }}}</b>
      {{ Habravel\externalLink($user->site) }}
    </p>
  @endif

  @foreach ($fields as $field => $display)
    @if ($user->$field)
      <p>
        <b>{{{ trans("habravel::g.profile.$field") }}}</b>
        <a href="{{{ $display[0].$user->$field }}}" rel="nofollow">
          {{{ $user->$field }}}
        </a>
      </p>
    @endif
  @endforeach

  @if ($name = $user->icq)
    <p>
      <b>{{{ trans('habravel::g.profile.icq') }}}</b>
      {{{ $name }}}
    </p>
  @endif

  @if ("$user->info" !== '')
    <article>
      {{{ $user->info }}}
    </article>
  @endif
</div>
