<?php /*
  - $user             - Models\User instance
  - $canEdit          - bool
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

      @if ($canEdit)
        <p class="hvl-puser-editbtns">
          <a class="hvl-btn" href="{{{ Habravel\url().'/~/edit' }}}">
            {{{ trans('habravel::g.profile.edit') }}}
          </a>

          <a class="hvl-btn" href="{{{ Habravel\url().'/~/avatar' }}}">
            {{{ trans('habravel::g.profile.editAvatar') }}}
          </a>

          <a class="hvl-btn" href="{{{ Habravel\url().'/~/password' }}}">
            {{{ trans('habravel::g.profile.editPassword') }}}
          </a>
        </p>
      @endif

      <h1 class="hvl-h1">
        <a href="{{{ Habravel\url()."/~".urlencode($user->name)."/up?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-upg"></i></a>
        {{ $user->nameHTML(array('link' => false)) }}
        <a href="{{{ Habravel\url()."/~".urlencode($user->name)."/down?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-downg"></i></a>
      </h1>

      @include('habravel::user.info', compact('user'))
    </header>

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