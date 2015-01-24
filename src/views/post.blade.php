<?php /*
  - $classes          - optional; string of space-separated CSS classes
  - $polls            - array of Models\Poll
  - $post             - Models\Post instance with loaded author, tags
  - $post->x_children - array of Models\Post, root comments for this post
  - $commentCount     - integer; total replies to this post including nested comments
*/?>

@extends('habravel::page')
<?php $pageTitle = $post->caption?>

@section('content')
  @include('habravel::part.postTitle', compact('post'), array())
  @include('habravel::part.post', array('captionTag' => 'h1', 'afterVote' => true))

  @if (count($polls))
    <form action="{{{ Habravel\url() }}}/vote" method="post" id="polls" class="hvl-polls">
      <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

      @foreach ($polls as $poll)
        @include('habravel::post.poll', compact('poll'), array())
      @endforeach

      <p>
        <button type="submit" class="hvl-btn">
          {{{ trans('habravel::g.post.vote'.(count($polls) > 1 ? 'All' : ''), array('count' => count($polls))) }}}
        </button>
      </p>
    </form>
  @endif

  <a id="comments"></a>

  @if ($post->x_children)
    <div class="hvl-comments">
      <h2 class="hvl-h2">
        {{{ trans('habravel::g.post.comments') }}}
        ({{ Habravel\number($commentCount) }})
      </h2>

      @foreach ($post->x_children as $comment)
        @include('habravel::part.comment', array('post' => $comment, 'hasReply' => true), array())
      @endforeach
    </div>
  @endif

  @include('habravel::post.reply', compact('post'), array())
@stop