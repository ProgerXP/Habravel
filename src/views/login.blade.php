<?php /*
  - $backURL          - 'rel/to/root/'
  - $badLogin         - if set means user credentials were wrong
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  <h1 class="hvl-h1">{{{ trans('habravel::g.login.title') }}}</h1>

  @if ($badLogin)
    <ul class="hvl-errors">
      <li>{{{ trans('habravel::g.login.wrong') }}}</li>
    </ul>
  @endif

  <form action="{{{ Habravel\Core::url() }}}/login" method="post" class="hvl-form-list">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
    <input type="hidden" name="back" value="{{{ $backURL }}}">

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.login.login') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" name="login" required="required" autofocus="autofocus">
    </p>

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.login.password') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" name="password" type="password" required="required">
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
      <a href="{{{ Habravel\Core::url() }}}/register">{{{ trans('habravel::g.login.register') }}}</a> &rarr;
    </p>
  </form>
@stop