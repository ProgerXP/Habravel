<?php /*
  - $token            - string
  - $errors           - optional; MessageBag
*/?>

@extends('habravel::page')

@section('content')
  <h1 class="hvl-h1">{{{ trans('habravel::g.resetPassword.title') }}}</h1>

  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif

  <form action="{{{ Habravel\url()."/resetpw/$token" }}}" method="post"
        class="hvl-form-list">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.profile.newPassword') }}}</b>
      {{{ trans('habravel::g.register.passwordHint', array('min' => Config::get('habravel::g.minPassword'))) }}}
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" type="password" name="newPassword" required="required" autofocus="autofocus">
    </p>

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.profile.newPassword_confirmation') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" type="password" name="newPassword_confirmation"  required="required">
    </p>

    <p class="hvl-form-list-btn">
      <button type="submit" class="hvl-btn">
        {{{ trans('habravel::g.resetPassword.submit') }}}
      </button>
    </p>
  </form>
@stop