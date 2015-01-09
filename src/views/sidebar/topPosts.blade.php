<?php /*
  - $posts            - array of Models\Post
*/?>

@if (count($posts))
  <h6 class="hvl-sidebar-title">
    {{{ trans('habravel::g.sidebar.topPosts') }}}
  </h6>

  <ol>
    @foreach ($posts as $post)
      <li>
        <a href="{{{ $post->url() }}}">
          {{{ $post->caption }}}</a>

        <span>+{{{ $post->score }}}</span>
      </li>
    @endforeach
  </ol>
@endif