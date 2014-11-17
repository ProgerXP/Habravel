<?php /*
  - $user             - Models\User instance
  - $posts            - array of Models\Post
  - $postCount        - integer
  - $comments         - array of Models\Post
  - $commentCount     - integer
*/?>

@extends('habravel::page')
<?php $pageTitle = '~'.$user->name?>

@section('posts')
  <h2 class="hvl-h2">
    <a href="{{{ $user->url() }}}/posts">{{{ trans('habravel::g.user.posts') }}}</a>
    ({{{ $postCount }}})
  </h2>

  @foreach ($posts as $post)
    @include('habravel::part.postTitle', compact('post'), array('level' => 3, 'link' => true))
    @include('habravel::part.post', compact('post'), array('readMore' => true, 'downshift' => 4))
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
    @include('habravel::part.comment', compact('post'), array('hasTop' => $prevTop !== $post->top, 'canEdit' => false))
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