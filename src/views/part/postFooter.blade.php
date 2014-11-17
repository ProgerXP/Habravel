<?php /*
  - $post             - Models\Post instance
  - $readMore         - false or string (link text)
*/?>

<?php $root = url(Habravel\url())?>

<footer class="hvl-post-footer">
  @if ($readMore !== false)
    <span class="hvl-post-footer-ctl hvl-post-footer-more">
      <a href="{{{ $post->url() }}}">{{{ $readMore }}}</a> &rarr;
    </span>
  @endif

  <span class="hvl-post-footer-ctl hvl-post-footer-score">
    <a href="{{{ "$root/up/$post->id?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-upg"></i></a>
    <b>{{{ ($post->score > 0 ? '+' : '').((int) $post->score) }}}</b>
    <a href="{{{ "$root/down/$post->id?_token=".urlencode(csrf_token()) }}}"><i class="hvl-i-downg"></i></a>
  </span>

  <span class="hvl-post-footer-ctl" title="{{{ trans('habravel::g.post.views') }}}">
    <i class="hvl-i-reloadg"></i>
    {{{ $post->views }}}
  </span>

  @if ($count = $post->childCount())
    <span class="hvl-post-footer-ctl" title="{{{ trans('habravel::g.post.commentCount') }}}">
      <i class="hvl-i-commentsg"></i>
      <a href="{{{ $post->url() }}}#comments">{{{ $count }}}</a>
    </span>
  @endif

  <?php $size = $post->size()?>
  <span class="hvl-post-footer-ctl" title="{{{ trans('habravel::g.post.size', array('chars' => $size, 'words' => $post->wordCount())) }}}">
    <i class="hvl-i-file"></i>
    <a href="{{{ "$root/source/$post->id" }}}">
      {{{ $size >= 1000 ? round($size / 1000).'K' : $size }}}</a>
  </span>

  @if ($time = ($post->pubTime ?: $post->created_at))
    <time pubdate="pubdate" datetime="{{{ date(DATE_ATOM, $time->timestamp) }}}"
          class="hvl-post-footer-ctl" title="{{{ trans('habravel::g.post.pubTime') }}}">
      @if ($post->pubTime) <i class="hvl-i-pencilg"></i> @endif
      {{{ DateFmt::Format('AGO-AT[s-d]IF>7[d# m__ y##]', $time->timestamp, Config::get('app.locale')) }}}
    </time>
  @endif

  @if ($readMore === false)
    <span class="hvl-post-footer-ctl hvl-credit-ctl">
      <a class="hvl-credit" href="http://laravel.ru/habravel" target="_blank">{{{ trans('habravel::g.credit') }}}</a>
    </span>
  @endif
</footer>
