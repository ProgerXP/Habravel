<?php /*
  - $errors           - optional; MessageBag
  - $sent             - optional; e-mail to which reminder has been successfully sent
*/?>

@extends('habravel::page')

@section('content')
  <h1 class="hvl-h1">{{{ trans('habravel::g.remindPassword.title') }}}</h1>

  @if (isset($sent))
    <p>
      {{ trans('habravel::g.remindPassword.sent', array('email' => '<b>'.e($sent).'</b>')) }}
    </p>
  @else
    @if (count($errors))
      {{ HTML::ul(array(trans('habravel::g.remindPassword.wrongEmail')), array('class' => 'hvl-errors')) }}
    @endif

    <form action="{{{ Habravel\url().'/remindpw' }}}" method="post"
          class="hvl-form-list">
      <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

      <p class="hvl-form-list-label">
        <b>{{{ trans('habravel::g.remindPassword.email') }}}</b>
      </p>
      <p class="hvl-form-list-value">
        <input class="hvl-input" name="email" type="email"
               required="required" autofocus="autofocus">
      </p>

      <p class="hvl-form-list-btn">
        <button type="submit" class="hvl-btn">
          {{{ trans('habravel::g.remindPassword.submit') }}}
        </button>
      </p>

      <p class="hvl-form-list-label">
        <a href="{{{ Habravel\url() }}}/register">{{{ trans('habravel::g.login.register') }}}</a> &rarr;
      </p>
    </form>
  @endif
@stop