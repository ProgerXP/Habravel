<?php /*
  - $user             - Models\User instance
  - $posts            - array of Models\Post
  - $postCount        - integer
  - $comments         - array of Models\Post
  - $commentCount     - integer
*/?>

@extends('habravel::page')
<?php $pageTitle = $user->name?>

@section('content')
  <div class="hvl-split hvl-puser {{{ $user->score < 0 ? 'hvl-puser-below' : '' }}}">
    <header class="hvl-puser-header">
      <img src="{{{ $user->avatarURL() }}}" alt="{{{ $user->name }}}"
           class="hvl-puser-avatar" title="ID: {{{ $user->id }}}">

      <h1 class="hvl-h1">
        <a href="{{{ Habravel\url()."/~".urlencode($user->name)."/up?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-upg"></i></a>
        {{ $user->nameHTML(array('link' => false)) }}
        <a href="{{{ Habravel\url()."/~".urlencode($user->name)."/down?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-downg"></i></a>
      </h1>

      <p>
        <b>{{{ trans('habravel::g.user.regTime') }}}</b>
        {{{ DateFmt::Format('AGO-AT[s-d]IF>7[d# m__ y##]', $user->created_at->timestamp, Config::get('app.locale')) }}}
      </p>

      @if ($user->loginTime)
        <p>
          <b>{{{ trans('habravel::g.user.loginTime') }}}</b>
          {{{ DateFmt::Format('AGO-AT[s-d]IF>7[d# m__ y##]', $user->loginTime->timestamp, Config::get('app.locale')) }}}
        </p>
      @endif
    </header>

    <div><!-- Info Block -->
      @include('habravel::part.userInfo')
    </div>

    @if (count($posts) and count($comments))
      <div class="hvl-split-left">
        @include('habravel::user.posts')
      </div>

      <div class="hvl-split-right">
        @include('habravel::user.comments')
      </div>
    @elseif (count($posts))
      @include('habravel::user.posts')
    @elseif (count($comments))
      @include('habravel::user.comments')
    @endif
  </div>
@stop