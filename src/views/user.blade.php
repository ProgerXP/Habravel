<?php /*
  - $user             - User instance
  - $posts            - array of Post
  - $postCount        - integer
  - $comments         - array of Post
  - $commentCount     - integer
*/?>

@extends('habravel::page')

@section('posts')
  <h2 class="hvl-h2">
    <a href="{{{ $user->url() }}}/posts">{{{ trans('habravel::g.user.posts') }}}</a>
    ({{{ $postCount }}})
  </h2>

  @foreach ($posts as $post)
    @include('habravel::part.postTitle', compact('post'), array('level' => 3, 'link' => true))
    @include('habravel::part.post', compact('post'), array())
  @endforeach

  @if ($postCount > count($posts))
    <p>
      <a href="{{{ $user->url() }}}/posts">
        {{{ trans('habravel::g.user.allPosts') }}}
      </a>
      &rarr;
    </p>
  @endif
@stop

@section('comments')
  <h2 class="hvl-h2">
    <a href="{{{ $user->url() }}}/comments">{{{ trans('habravel::g.user.comments') }}}</a>
    ({{{ $commentCount }}})
  </h2>

  <?php $prevTop = null?>
  @foreach ($comments as $post)
    @include('habravel::part.comment', compact('post'), array('hasTop' => $prevTop !== $post->top))
    <?php $prevTop = $post->top?>
  @endforeach

  @if ($commentCount > count($comments))
    <p>
      <a href="{{{ $user->url() }}}/comments">
        {{{ trans('habravel::g.user.allComments') }}}
      </a>
      &rarr;
    </p>
  @endif
@stop

@section('content')
  @include('habravel::part.uheader', array(), array())

  <div class="hvl-split hvl-puser {{{ $user->score < 0 ? 'hvl-puser-below' : '' }}}">
    <header class="hvl-puser-header">
      <img src="{{{ $user->avatarURL() }}}" alt="{{{ $user->name }}}"
           class="hvl-puser-avatar" title="ID: {{{ $user->id }}}">

      <h1 class="hvl-h1">
        <a href="{{{ Habravel\Core::url()."/~".urlencode($user->name)."/up?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-upg"></i></a>
        {{ $user->nameHTML(array('link' => false)) }}
        <a href="{{{ Habravel\Core::url()."/~".urlencode($user->name)."/down?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-downg"></i></a>
      </h1>

      <p>
        <b>{{{ trans('habravel::g.user.regTime') }}}</b>{{{ $user->created_at->formatLocalized('%d %B %Y') }}}
      </p>

      @if ($user->loginTime)
        <p>
          <b>{{{ trans('habravel::g.user.loginTime') }}}</b>{{{ $user->loginTime->formatLocalized('%d %B %Y') }}}
        </p>
      @endif
    </header>

    @if (count($posts) and count($comments))
      <div class="hvl-split-left">
        @yield('posts')
      </div>

      <div class="hvl-split-right">
        @yield('comments')
      </div>
    @elseif (count($posts))
      @yield('posts')
    @elseif (count($comments))
      @yield('comments')
    @endif
  </div>
@stop