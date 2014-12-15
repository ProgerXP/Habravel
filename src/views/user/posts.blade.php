<?php /*
  - $user             - Models\User instance
  - $posts            - array of Models\Post
  - $postCount        - integer
  - $canEdit          - bool
*/?>

<h2 class="hvl-h2">
  <a href="{{{ $user->url() }}}/posts">{{{ trans('habravel::g.user.posts') }}}</a>
  ({{ Habravel\number($postCount) }})
</h2>

@if (!count($posts) and $canEdit)
  <p class="hvl-none">
    <a href="{{{ Habravel\url() }}}/compose" class="hvl-btn">
      {{{ trans('habravel::g.user.writeFirstPost') }}}</a>
  </p>
@endif

@foreach ($posts as $post)
  @include('habravel::part.postTitle', compact('post'), array('level' => 3, 'link' => true))
  @include('habravel::part.post', compact('post'), array('readMore' => true, 'downshift' => 4))
@endforeach

@if ($postCount > count($posts))
  <p>
    <a href="{{{ $user->url() }}}/posts">
      {{{ trans('habravel::g.user.allPosts') }}}</a>
    &rarr;
  </p>
@endif

