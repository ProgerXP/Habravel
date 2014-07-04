<?php /*
  - $classes          - optional; string of space-separated CSS classes
  - $post             - Post instance with loaded author, tags
  - $post->x_children - array of Post, root comments for this post
  - $post->x_polls    - array of Poll with x_voteCount, x_options (with x_voteCount)
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  @include('habravel::part.postTitle', compact('post'), array())
  @include('habravel::part.post', array('captionTag' => 'h1'))

  @if (count($post->x_polls))
    <form action="{{{ Habravel\Core::url() }}}/vote" method="post" id="polls" class="hvl-polls">
      <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

      @foreach ($post->x_polls as $poll)
        <div class="hvl-poll" data-sqa="poll"
             data-hvl-poll="{{{ join(' ', array_pluck($poll->x_options, 'x_voteCount')) }}}">
          <h2 id="poll-{{{ $poll->id }}}" class="hvl-h2">
            {{{ trans('habravel::g.post.poll') }}}
            <span>{{{ $poll->caption }}}</span>
            @if ($poll->x_voteCount) ({{{ (int) $poll->x_voteCount }}}) @endif
          </h2>

          <canvas data-sqa="poll - | [height: h | [width: h"></canvas>

          @foreach ($poll->x_options as $option)
            <div class="hvl-poll-option">
              <p>
                <label>
                  @if ($poll->multiple)
                    <input type="checkbox" name="votes[]" value="{{{ $option->id }}}">
                  @else
                    <input type="radio" name="votes[{{{ $poll->id }}}]" value="{{{ $option->id }}}">
                  @endif
                  {{{ $option->caption }}}
                </label>
                ({{{ (int) $option->x_voteCount }}})
              </p>

              <hr style="width: {{{ $poll->x_voteCount ? $option->x_voteCount / $poll->x_voteCount * 100 : 0 }}}%">
            </div>
          @endforeach
        </div>
      @endforeach

      <p>
        <button type="submit" class="hvl-btn">
          {{{ trans('habravel::g.post.vote'.(count($post->x_polls) > 1 ? 'All' : ''), array('count' => count($post->x_polls))) }}}
        </button>
      </p>
    </form>
  @endif

  <a id="comments"></a>

  @if ($post->x_children)
    <div class="hvl-comments">
      <h2 class="hvl-h2">{{{ trans('habravel::g.post.comments') }}}</h2>

      @foreach ($post->x_children as $comment)
        @include('habravel::part.comment', array('post' => $comment, 'hasReply' => true), array())
      @endforeach
    </div>
  @endif

  <form action="{{{ Habravel\Core::url() }}}/reply" method="post" class="hvl-ncomment">
    <?php $tag = $post->x_children ? 'h3' : 'h2'?>
    <{{ $tag }} class="hvl-{{ $tag }}">{{{ trans('habravel::g.ncomment.title') }}}</{{ $tag }}>

    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
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