<?php /*
  - $tags             - array of Tag
*/?>

<p class="hvl-tags">
  @foreach ($tags as $tag)
    <a href="{{{ $tag->url() }}}" class="hvl-tag hvl-tag-{{{ $tag->type }}}">
      {{{ $tag->caption }}}</a>
  @endforeach
</p>
