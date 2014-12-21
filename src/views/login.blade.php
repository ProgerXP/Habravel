<?php /*
  - $backURL          - 'rel/to/root/'
  - $badLogin         - if set means user credentials were wrong
  - $badRestoreLink   - if set means password recovery link is outdated or wrong
*/?>

@extends('habravel::page')

@section('content')
  <h1 class="hvl-h1">{{{ trans('habravel::g.login.title') }}}</h1>

  @if ($badLogin)
    <ul class="hvl-errors">
      <li>{{{ trans('habravel::g.login.wrong') }}}</li>
    </ul>
  @elseif ($badRestoreLink)
    <ul class="hvl-errors">
      <li>{{{ trans('habravel::g.login.wrongRestore') }}}</li>
    </ul>
  @endif

  <form action="{{{ Habravel\url() }}}/login" method="post" class="hvl-form-list">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
    <input type="hidden" name="back" value="{{{ $backURL }}}">

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.login.login') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" name="login" required="required" autofocus="autofocus" tabindex="1">
    </p>

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.login.password') }}}</b>
      {{ trans('habravel::g.login.remindPassword', array('<a href="'.Habravel\url().'/remindpw">', '</a>')) }}
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" name="password" type="password" required="required" tabindex="2">
    </p>

    <p class="hvl-form-list-btn">
      <button type="submit" class="hvl-btn">
        {{{ trans('habravel::g.login.submit') }}}
      </button>

      <button type="submit" class="hvl-btn" name="remember" value="1">
        {{{ trans('habravel::g.login.remember') }}}
      </button>
    </p>

    <p class="hvl-form-list-label">
      <a href="{{{ Habravel\url() }}}/register">{{{ trans('habravel::g.login.register') }}}</a> &rarr;
    </p>
  </form>
@stop