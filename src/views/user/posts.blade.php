<?php /*
  - $user             - Models\User instance
  - $posts            - array of Models\Post
  - $postCount        - integer
*/?>

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
      {{{ trans('habravel::g.user.allPosts') }}}</a>
    &rarr;
  </p>
@endif

