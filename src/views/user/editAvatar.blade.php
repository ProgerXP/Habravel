<?php /*
  - $user             - Models\User instance
  - $errors           - optional; MessageBag
*/?>

@extends('habravel::page')

@section('content')
  <h1 class="hvl-h1">{{{ trans('habravel::g.profile.editAvatarTitle') }}}</h1>

  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif

  <form action="{{{ Habravel\url().'/~/avatar' }}}" method="post"
        class="hvl-form-list hvl-puser-edit-avatar" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
    <img src="{{{ $user->avatarURL() }}}" alt="{{{ $user->name }}}" />

    <p class="hvl-form-list-label">
      <b>{{{ trans('habravel::g.profile.avatarFile') }}}</b>
    </p>
    <p class="hvl-form-list-value">
      <input name="avatar" type="file">
    </p>

    <p class="hvl-form-list-btn">
      <button type="submit" class="hvl-btn">
        {{{ trans('habravel::g.profile.submit') }}}
      </button>

      <button type="submit" class="hvl-btn" name="delete" value="1">
        {{{ trans('habravel::g.profile.deleteAvatar') }}}
      </button>
    </p>
  </form>
@stop