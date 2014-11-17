<?php /*
  - $user             - Models\User instance
  - $comments         - array of Models\Post
  - $commentCount     - integer
*/?>

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
      {{{ trans('habravel::g.user.allComments') }}}</a>
    &rarr;
  </p>
@endif

