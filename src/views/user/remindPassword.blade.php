<?php /*
  - $errors           - optional; MessageBag
*/?>

@extends('habravel::page')

@section('content')
  <h1 class="hvl-h1">{{{ trans('habravel::g.remindPassword.title') }}}</h1>

  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif

  <form action="{{{ Habravel\url().'/remindpassword' }}}" method="post"
        class="hvl-form-list">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.remindPassword.email') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input class="hvl-input" name="email" required="required" autofocus="autofocus">
    </p>

    <p class="hvl-form-list-btn">
      <button type="submit" class="hvl-btn">
        {{{ trans('habravel::g.remindPassword.submit') }}}
      </button>
    </p>
  </form>
@stop