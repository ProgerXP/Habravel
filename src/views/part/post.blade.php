<?php /*
  - $classes          - optional; string of space-separated CSS classes
  - $post             - Post instance with loaded x_author, x_tags
  - $canEdit          - boolean
*/?>

<?php $root = url(Habravel\Core::url())?>

<div class="hvl-post {{{ $classes }}}">
  <header class="hvl-post-header">
    <p class="hvl-post-author">
      @if ($post->sourceURL)
        <a href="{{{ $post->sourceURL }}}" title="{{{ trans('habravel::g.post.source') }}}">
          {{{ $post->sourceName }}}
        </a>
        <span class="hvl-post-author-separ">&larr;</span>
      @endif

      <span title="{{{ trans('habravel::g.post.author') }}}">
        <a href="{{{ $post->x_author->url() }}}" class="hvl-post-author-avatar">
          <img src="{{{ $post->x_author->avatarURL() }}}" alt="{{{ $post->x_author->name }}}">
        </a>

        {{ $post->x_author->nameHTML() }}
      </span>

      @if (!empty($canEdit))
        <a href="{{{ $root }}}/edit/{{{ $post->id }}}" class="hvl-btn">
          {{{ trans('habravel::g.post.edit') }}}
        </a>
      @endif
    </p>

    @include('habravel::part.tags', array('tags' => $post->x_tags), array())
  </header>

  <article class="hvl-markedup">
    {{ $post->html }}
  </article>

  <footer class="hvl-post-footer">
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

    <time pubdate="pubdate" datetime="{{{ date(DATE_ATOM, $post->pubTime->timestamp) }}}"
          class="hvl-post-footer-ctl" title="{{{ trans('habravel::g.post.pubTime') }}}">
      <i class="hvl-i-pencilg"></i>
      {{{ DateFmt::Format('AGO-AT[s-d]IF>7[d# m__ y##]', $post->pubTime->timestamp, Config::get('application.language')) }}}
    </time>
  </footer>
</div>