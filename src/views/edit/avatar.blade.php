@extends('habravel::page')
<?php $pageTitle = trans('habravel::g.profile.changeAvatarTitle').$user->name?>

@section('content')
<h1 class="hvl-h1">{{{ trans('habravel::g.profile.changeAvatarTitle') }}}</h1>
  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif
  <form action="{{{ url('changemyavatar') }}}" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
    <img src="{{{ $user->avatarURL() }}}" alt="{{{ $user->name }}}" />
    <input name="avatar" type="file">
    <button type="submit">
      {{{ trans('habravel::g.profile.submit') }}}
    </button>
  </form>
@stop