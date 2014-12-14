<?php /*
  - $posts            - array of Models\Post
*/?>

<h6 class="hvl-sidebar-title">
  {{{ trans('habravel::g.sidebar.topPostsTitle') }}}
</h6>

<ol>
  @foreach ($posts as $post)
    <li>
      <a href="{{{ $post->url() }}}">
        {{{ $post->caption }}}
      </a>

      <span>+{{{ $post->score }}}</span>
    </li>
  @endforeach
</ol>