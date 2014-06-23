<?php /*
  - $user             - User instance
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  <div class="hvl-puser {{{ $user->score < 0 ? 'hvl-puser-below' : '' }}}">
    <header class="hvl-puser-header">
      <img src="{{{ $user->avatarURL() }}}" alt="{{{ $user->name }}}"
           class="hvl-puser-avatar" title="ID: {{{ $user->id }}}">

      <h1 class="hvl-h1">
        {{ $user->nameHTML(array('link' => false)) }}
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

    <?php $posts = $user->posts()->get()?>
    <?php $comments = array()?>

    @if ($posts and $comments)
      <div class="hvl-split">
        <div class="hvl-split-left">
          <h2 class="hvl-h2">{{{ trans('habravel::g.user.posts') }}}</h2>

          @foreach ($posts as $post)
            @include('habravel::part.postTitle', compact('post'), array('level' => 3, 'link' => true))
            @include('habravel::part.post', compact('post'), array())
          @endforeach
        </div>

        <div class="hvl-split-right">
          <h2 class="hvl-h2">{{{ trans('habravel::g.user.comments') }}}</h2>

          @foreach ($comments as $post)
            @include('habravel::part.comment', compact('post'), array())
          @endforeach
        </div>
      </div>
    @elseif ($posts)
      <h2 class="hvl-h2">{{{ trans('habravel::g.user.posts') }}}</h2>

      @foreach ($posts as $post)
        @include('habravel::part.postTitle', compact('post'), array('level' => 3, 'link' => true))
        @include('habravel::part.post', compact('post'), array())
      @endforeach
    @elseif ($comments)
      <h2 class="hvl-h2">{{{ trans('habravel::g.user.comments') }}}</h2>

      @foreach ($comments as $post)
        @include('habravel::part.comment', compact('post'), array())
      @endforeach
    @endif
  </div>
@stop