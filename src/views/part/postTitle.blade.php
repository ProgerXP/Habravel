<?php /*
  - $post             - Post instance
  - $level            - integer; defaults to 1
  - $link             - optional; true or false
*/?>

<?php $tag = 'h'.(int) (isset($level) ? $level : 1)?>

<{{ $tag }} class="hvl-{{ $tag }}">
  @if (!empty($link)) <a href="{{{ $post->url() }}}"> @endif
  {{{ $post->caption }}}
  @if (!empty($link)) </a> @endif
</{{ $tag }}>