<?php /*
  - $tags             - array of Tag instances
*/?>

<p class="hvl-tags">
  @foreach ($tags as $tag)
    <a href="{{{ $root }}}/tags/{{{ $tag->caption }}}"
       class="hvl-tag-{{{ $tag->type }}}">
      {{{ $tag->caption }}}
    </a>
  @endforeach
</p>
