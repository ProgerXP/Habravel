<?php /*
  - $user             - Models\User instance
  - $errors           - optional; MessageBag
*/?>

@extends('habravel::page')

@section('content')
  <h1 class="hvl-h1">{{{ trans('habravel::g.profile.editPasswordTitle') }}}</h1>

  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif

  <form action="{{{ Habravel\url().'/~/password' }}}" method="post"
        class="hvl-form-list">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.profile.oldPassword') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" type="password" name="password" autofocus="autofocus" required="required">
    </p>

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.profile.newPassword') }}}</b>
      {{{ trans('habravel::g.register.passwordHint', array('min' => Config::get('habravel::g.minPassword'))) }}}
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" type="password" name="newPassword"  required="required">
    </p>

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.profile.newPassword_confirmation') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" type="password" name="newPassword_confirmation"  required="required">
    </p>

    <p class="hvl-form-list-btn">
      <button type="submit" class="hvl-btn">
        {{{ trans('habravel::g.profile.submit') }}}
      </button>
    </p>
  </form>
@stop