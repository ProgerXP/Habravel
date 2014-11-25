@if(isset($user->info->user_id))
<p>
  {{{ trans('habravel::g.profile.site') }}} {{ Habravel\externalUrl($user->info->site) }}
</p>
<p>
  {{{ trans('habravel::g.profile.bitbucket') }}} {{ Habravel\externalUrl($user->info->bitbucket) }}
</p>
<p>
  {{{ trans('habravel::g.profile.github') }}} {{ Habravel\externalUrl($user->info->github) }}
</p>
<p>
  {{{ trans('habravel::g.profile.facebook') }}} {{ Habravel\externalUrl($user->info->facebook) }}
</p>
<p>
  {{{ trans('habravel::g.profile.twitter') }}} {{ Habravel\externalUrl($user->info->twitter) }}
</p>
<p>
  {{{ trans('habravel::g.profile.vk') }}} {{ Habravel\externalUrl($user->info->vk) }}
</p>
<p>
  {{{ trans('habravel::g.profile.icq') }}} {{{ $user->info->icq }}}
</p>
<p>
  {{{ trans('habravel::g.profile.jabber') }}} {{ Habravel\jabberUrl($user->info->jabber) }}
</p>
<p>
  {{{ trans('habravel::g.profile.skype') }}} {{ Habravel\skypeUrl($user->info->skype) }}
</p>
<p>
  {{{ trans('habravel::g.profile.info') }}} {{{ $user->info->info or '' }}}
</p>
@endif