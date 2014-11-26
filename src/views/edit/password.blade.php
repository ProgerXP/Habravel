@extends('habravel::page')
<?php $pageTitle = trans('habravel::g.profile.changePasswordTitle').$user->name?>

@section('content')
<h1 class="hvl-h1">{{{ trans('habravel::g.profile.changePasswordTitle') }}}</h1>
  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif
  <form action="{{{ url('changemypassword') }}}" method="post" class="hvl-form-list">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

    <p>
      <label for="password">{{{ trans('habravel::g.profile.password') }}}
        <input type="password" id="password" name="password" value="">
      </label>
    </p>

    <p>
      <label for="newPassword">{{{ trans('habravel::g.profile.newPassword') }}}
        <input type="password" id="newPassword" name="newPassword" value="">
      </label>
    </p>

    <p>
      <label for="newPassword_confirmation">{{{ trans('habravel::g.profile.newPassword_confirmation') }}}
        <input type="passwordp" id="newPassword_confirmation" name="newPassword_confirmation" value="">
      </label>
    </p>

    <button type="submit">
      {{{ trans('habravel::g.profile.submit') }}}
    </button>

  </form>
@stop