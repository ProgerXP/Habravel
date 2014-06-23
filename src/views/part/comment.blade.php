<?php /*
  - $post             - Post instance, the comment itself
*/?>

<article class="hvl-comment">
  <a href="{{{ $post->author->url() }}}" class="hvl-comment-avatar" title="{{{ $post->author->name }}}">
    <img src="{{{ $post->author->avatarURL() }}}" alt="{{{ $post->author->name }}}">
  </a>

  {{ $post->html }}

  <footer class="hvl-comment-footer">
    <a class="hvl-btn" href="{{{ Habravel\Core::url() }}}/reply/{{{ $post->parent()->url }}}">
      {{{ trans('habravel::g.comment.reply') }}}
    </a>

    {{ $comment->author->nameHTML() }}

    <time pubdate="pubdate" datetime="{{{ date(DATE_ATOM, $post->pubTime->timestamp) }}}">
      {{{ DateFmt::Format('AGO-AT[s-db]IF>2[d# m__ y##]AT h#m', $post->pubTime->timestamp, Config::get('application.language')) }}}
    </time>
  </footer>
</article>