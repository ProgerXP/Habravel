<?php /*
  - $errors           - optional; MessageBag instance
  - $input            - optional; array of old input (given on error)
*/?>

@extends('habravel::page')

@section('content')
  <h1 class="hvl-h1">{{{ trans('habravel::g.register.title') }}}</h1>

  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif

  <form action="{{{ Habravel\url() }}}/register" method="post" class="hvl-form-list">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
    <input type="hidden" name="hash" value="{{{ $captcha['hash'] }}}">

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.register.name') }}}</b>
      {{ trans('habravel::g.register.nameHint', array('chars' => '<kbd>A-Z _ 0-9</kbd>')) }}
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" name="name" required="required" autofocus="autofocus"
             value="{{{ array_get($input, 'name') }}}">
    </p>

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.register.email') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" name="email" type="email" required="required"
             value="{{{ array_get($input, 'email') }}}">
    </p>

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.register.password') }}}</b>
      {{{ trans('habravel::g.register.passwordHint', array('min' => Config::get('habravel::g.minPassword'))) }}}
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" name="password" type="password" required="required"
             value="{{{ array_get($input, 'password') }}}">
    </p>

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.register.captcha') }}}</b>
      {{{ trans('habravel::g.register.captchaHint') }}}
    </p>
    <p class="hvl-form-list-value">
      {{{ $captcha['question'] }}}
      <input class="hvl-input" name="captcha" type="text" required="required" value="">
    </p>

    <p class="hvl-form-list-btn">
      <button type="submit" class="hvl-btn">
        {{{ trans('habravel::g.register.submit') }}}
      </button>
    </p>

    <p class="hvl-form-list-label">
      {{{ trans('habravel::g.register.login1') }}}
      <a href="{{{ Habravel\url() }}}/login">{{{ trans('habravel::g.register.login2') }}}</a> &rarr;
    </p>
  </form>
@stop