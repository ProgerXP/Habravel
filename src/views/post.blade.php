<?php /*
  - $classes          - optional; string of space-separated CSS classes
  - $post             - Post instance with loaded author, tags
  - $post->children   - array of Post, root comments for this post
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  @include('habravel::part.postTitle', compact('post'), array())
  @include('habravel::part.post', array('captionTag' => 'h1'))

  @if ($post->children)
    <div class="hvl-comments">
      <h2 class="hvl-h2">{{{ trans('habravel::g.post.comments') }}}</h2>

      @foreach ($post->children as $comment)
        @include('habravel::part.comment', array('post' => $comment), array())
      @endforeach
    </div>
  @endif

  <form action="{{{ Habravel\Core::url() }}}/reply" method="post" class="hvl-ncomment" id="comments">
    <h3 class="hvl-h3">{{{ trans('habravel::g.ncomment.title') }}}</h3>
    <input type="hidden" name="parent" value="{{{ $post->id }}}">

    <b>{{{ trans('habravel::g.ncomment.markup') }}}</b>
    @include('habravel::part.markups', array(), array())

    <textarea name="text" class="hvl-input" rows="15" cols="80" required="required"
              placeholder="{{{ trans('habravel::g.ncomment.text') }}}"></textarea>

    <button type="submit" class="hvl-btn hvl-btn-orange">
      {{{ trans('habravel::g.ncomment.submit') }}}
    </button>

    <button type="submit" class="hvl-btn hvl-ncomment-preview-btn" name="preview" value="1">
      <i class="hvl-i-zoomw"></i>
      {{{ trans('habravel::g.ncomment.preview') }}}
    </button>
  </form>
@stop