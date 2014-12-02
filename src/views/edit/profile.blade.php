<?php /*
  - $user             - Models\User instance
*/?>
@extends('habravel::page')
<?php $pageTitle = trans('habravel::g.profile.changeInfoTitle')?>

@section('content')
<h1 class="hvl-h1">{{{ trans('habravel::g.profile.changeInfoTitle') }}}</h1>
  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif

  <form method="post" action="{{{ Habravel\url().'/editmyinfo' }}}">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

    <p>
      <label for="site">{{{ trans('habravel::g.profile.site') }}}
        <input type="text" id="site" name="site" value="{{{ $user->site }}}">
      </label>
    </p>

    <p>
      <label for="bitbucket">{{{ trans('habravel::g.profile.bitbucket') }}}
        <input type="text" id="bitbucket" name="bitbucket" value="{{{ $user->bitbucket }}}">
      </label>
    </p>

    <p>
      <label for="github">{{{ trans('habravel::g.profile.github') }}}
        <input type="text" id="github" name="github" value="{{{ $user->github }}}">
      </label>
    </p>

    <p>
      <label for="facebook">{{{ trans('habravel::g.profile.facebook') }}}
        <input type="text" id="facebook" name="facebook" value="{{{ $user->facebook }}}">
      </label>
    </p>

    <p>
      <label for="twitter">{{{ trans('habravel::g.profile.twitter') }}}
        <input type="text" id="twitter" name="twitter" value="{{{ $user->twitter }}}">
      </label>
    </p>

    <p>
      <label for="vk">{{{ trans('habravel::g.profile.vk') }}}
        <input type="text" id="vk" name="vk" value="{{{ $user->vk }}}">
      </label>
    </p>

    <p>
      <label for="jabber">{{{ trans('habravel::g.profile.jabber') }}}
        <input type="text" id="jabber" name="jabber" value="{{{ $user->jabber }}}">
      </label>
    </p>

    <p>
      <label for="skype">{{{ trans('habravel::g.profile.skype') }}}
        <input type="text" id="skype" name="skype" value="{{{ $user->skype }}}">
      </label>
    </p>

    <p>
      <label for="icq">{{{ trans('habravel::g.profile.icq') }}}
        <input type="text" id="icq" name="icq" value="{{{ $user->icq }}}">
      </label>
    </p>

    <p>{{{ trans('habravel::g.profile.info') }}} <br />
      <textarea type="text" id="info" name="info">
        {{{ $user->info }}}
      </textarea>
    </p>

    <button type="submit">
      {{{ trans('habravel::g.profile.submit') }}}
    </button>

  </form>
@stop