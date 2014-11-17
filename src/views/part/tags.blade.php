<?php /*
  - $tags             - array of Models\Tag
*/?>

<p class="hvl-tags">
  @foreach ($tags as $tag)
    {{ Habravel\tagLink($tag) }}
  @endforeach
</p>
