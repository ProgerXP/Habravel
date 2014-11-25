@extends('habravel::page')
<?php $pageTitle = trans('habravel::g.profile.changeInfoTitle').$user->name?>

@section('content')

  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif

  <form method="post" action="{{{ url("editmyinfo") }}}">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

    <p>
      <label for="site">{{{ trans('habravel::g.profile.site') }}}
        <input type="text" id="site" name="site" value="{{{ $user->info->site }}}">
      </label>
    </p>

    <p>
      <label for="bitbucket">{{{ trans('habravel::g.profile.bitbucket') }}}
        <input type="text" id="bitbucket" name="bitbucket" value="{{{ $user->info->bitbucket }}}">
      </label>
    </p>

    <p>
      <label for="github">{{{ trans('habravel::g.profile.github') }}}
        <input type="text" id="github" name="github" value="{{{ $user->info->github }}}">
      </label>
    </p>

    <p>
      <label for="facebook">{{{ trans('habravel::g.profile.facebook') }}}
        <input type="text" id="facebook" name="facebook" value="{{{ $user->info->facebook }}}">
      </label>
    </p>

    <p>
      <label for="twitter">{{{ trans('habravel::g.profile.twitter') }}}
        <input type="text" id="twitter" name="twitter" value="{{{ $user->info->twitter }}}">
      </label>
    </p>

    <p>
      <label for="vk">{{{ trans('habravel::g.profile.vk') }}}
        <input type="text" id="vk" name="vk" value="{{{ $user->info->vk }}}">
      </label>
    </p>

    <p>
      <label for="jabber">{{{ trans('habravel::g.profile.jabber') }}}
        <input type="text" id="jabber" name="jabber" value="{{{ $user->info->jabber }}}">
      </label>
    </p>

    <p>
      <label for="skype">{{{ trans('habravel::g.profile.skype') }}}
        <input type="text" id="skype" name="skype" value="{{{ $user->info->skype }}}">
      </label>
    </p>

    <p>
      <label for="icq">{{{ trans('habravel::g.profile.icq') }}}
        <input type="text" id="icq" name="icq" value="{{{ $user->info->icq }}}">
      </label>
    </p>

    <p>{{{ trans('habravel::g.profile.info') }}} <br />
      <textarea type="text" id="info" name="info">
        {{{ $user->info->info }}}
      </textarea>
    </p>

    <button type="submit">
      {{{ trans('habravel::g.profile.submit') }}}
    </button>

  </form>
@stop