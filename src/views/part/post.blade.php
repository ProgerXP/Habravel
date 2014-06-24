<?php /*
  - $classes          - optional; string of space-separated CSS classes
  - $post             - Post instance with loaded _author, _tags
*/?>

<?php $root = url(Habravel\Core::url())?>

<div class="hvl-post {{{ $classes }}}">
  <header class="hvl-post-header">
    <p class="hvl-post-author">
      @if ($post->sourceURL)
        <a href="{{{ $post->sourceURL }}}" title="{{{ trans('habravel::g.post.source') }}}">
          {{{ $post->sourceName }}}
        </a>
        &larr;
      @endif

      <span title="{{{ trans('habravel::g.post.author') }}}">
        <a href="{{{ $post->_author->url() }}}" class="hvl-post-author-avatar">
          <img src="{{{ $post->_author->avatarURL() }}}" alt="{{{ $post->_author->name }}}">
        </a>

        {{ $post->_author->nameHTML() }}
      </span>
    </p>

    @include('habravel::part.tags', array('tags' => $post->_tags), array())
  </header>

  <article class="hvl-markedup">
    {{ $post->html }}
  </article>

  <footer class="hvl-post-footer">
    <span class="hvl-post-footer-ctl hvl-post-footer-score">
      <a href="{{{ "$root/up/$post->url?csrf=".urlencode(csrf_token()) }}}"><i class="hvl-i-upg"></i></a>
      <b>{{{ ($post->score > 0 ? '+' : '').((int) $post->score) }}}</b>
      <a href="{{{ "$root/down/$post->url?csrf=".urlencode(csrf_token()) }}}"><i class="hvl-i-downg"></i></a>
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

    <time pubdate="pubdate" datetime="{{{ date(DATE_ATOM, $post->pubTime->timestamp) }}}"
          class="hvl-post-footer-ctl" title="{{{ trans('habravel::g.post.pubTime') }}}">
      <i class="hvl-i-pencilg"></i>
      {{{ DateFmt::Format('AGO-AT[s-d]IF>7[d# m__ y##]', $post->pubTime->timestamp, Config::get('application.language')) }}}
    </time>
  </footer>
</div>