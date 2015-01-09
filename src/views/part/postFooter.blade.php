<?php /*
  - $post             - Models\Post instance
  - $readMore         - false or string (link text)
*/?>

<?php $root = url(Habravel\url())?>

<footer class="hvl-post-footer">
  @if ($readMore !== false)
    <a class="hvl-post-footer-more hvl-btn" href="{{{ $post->url() }}}">{{{ $readMore }}}</a>
  @endif

  <span class="hvl-post-footer-ctls">
    @if ($post->poll)
      <span class="hvl-post-footer-ctl hvl-post-footer-score">
        <a href="{{{ "$root/up/$post->id?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-upg"></i></a>
        <b>{{{ ($post->score > 0 ? '+' : '').((int) $post->score) }}}</b>
        <a href="{{{ "$root/down/$post->id?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-downg"></i></a>
      </span>
    @endif

    <span class="hvl-post-footer-ctl" title="{{{ trans('habravel::g.post.views') }}}">
      <i class="hvl-i-reloadg"></i>
      {{ Habravel\number($post->views) }}
    </span>

    @if ($count = $post->childCount())
      <span class="hvl-post-footer-ctl" title="{{{ trans('habravel::g.post.commentCount') }}}">
        <i class="hvl-i-commentsg"></i>
        <a href="{{{ $post->url() }}}#comments">{{{ $count }}}</a>
      </span>
    @endif

    <?php $size = $post->size()?>
    <span class="hvl-post-footer-ctl" title="{{{ $post->statString() }}}">
      <i class="hvl-i-file"></i>
      <a href="{{{ "$root/source/$post->id" }}}">
        {{ $size >= 1000 ? Habravel\number(round($size / 1000)).'K' : $size }}</a>
    </span>

    @if ($readMore === false)
      <span class="hvl-post-footer-ctl hvl-credit-ctl">
        <a class="hvl-credit" href="http://laravel.ru/habravel" target="_blank">{{{ trans('habravel::g.credit') }}}</a>
      </span>
    @endif
  </span>
</footer>
