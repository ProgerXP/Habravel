<?php /*
  - $classes          - optional; string of space-separated CSS classes
  - $post             - Post instance with loaded author, tags
  - $comments         - array of Post instances shown in this order
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  @include('habravel::part.postTitle', compact('post'), array())
  @include('habravel::part.post', array('captionTag' => 'h1'))

  @if (!empty($comments))
    <div class="hvl-comments">
      @foreach ($comments as $comment)
        @include('habravel::part.comment', array('post' => $comment), array())
      @endforeach
    </div>
  @endif
@stop