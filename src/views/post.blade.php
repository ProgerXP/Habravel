<?php /*
  - $classes          - optional; string of space-separated CSS classes
  - $post             - Post instance with loaded author, tags
  - $post->_children   - array of Post, root comments for this post
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  @include('habravel::part.postTitle', compact('post'), array())
  @include('habravel::part.post', array('captionTag' => 'h1'))

  <a id="comments"></a>

  @if ($post->_children)
    <div class="hvl-comments">
      <h2 class="hvl-h2">{{{ trans('habravel::g.post.comments') }}}</h2>

      @foreach ($post->_children as $comment)
        @include('habravel::part.comment', array('post' => $comment, 'hasReply' => true), array())
      @endforeach
    </div>
  @endif

  <form action="{{{ Habravel\Core::url() }}}/reply" method="post" class="hvl-ncomment">
    <?php $tag = $post->_children ? 'h3' : 'h2'?>
    <{{ $tag }} class="hvl-{{ $tag }}">{{{ trans('habravel::g.ncomment.title') }}}</{{ $tag }}>
    <input type="hidden" name="parent" value="{{{ $post->id }}}">

    <b>{{{ trans('habravel::g.ncomment.markup') }}}</b>
    @include('habravel::part.markups', array(), array())

    <textarea name="text" class="hvl-input" rows="15" cols="80" required="required"
              placeholder="{{{ trans('habravel::g.ncomment.text') }}}"></textarea>

    <p class="hvl-ncomment-btns">
      <button type="submit" class="hvl-btn hvl-btn-orange">
        {{{ trans('habravel::g.ncomment.submit') }}}
      </button>

      <button type="submit" class="hvl-btn hvl-ncomment-preview-btn" name="preview" value="1">
        <i class="hvl-i-zoomw"></i>
        {{{ trans('habravel::g.ncomment.preview') }}}
      </button>
    </p>
  </form>
@stop