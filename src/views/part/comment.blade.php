<?php /*
  - $post             - Post instance, the comment itself, with authorModel
  - $post->children   - array of Post instances, replies to this comment
*/?>

<div class="hvl-comment" id="cmt-{{{ $post->id }}}">
  <a href="{{{ $post->authorModel->url() }}}" class="hvl-comment-avatar" title="{{{ $post->authorModel->name }}}">
    <img src="{{{ $post->authorModel->avatarURL() }}}" alt="{{{ $post->authorModel->name }}}">
  </a>

  <article class="hvl-markedup">
    {{ $post->html }}
  </article>

  <footer class="hvl-comment-footer">
    <a class="hvl-btn" href="{{{ Habravel\Core::url() }}}/reply/{{{ $post->id }}}">
      {{{ trans('habravel::g.post.reply') }}}</a>

    {{ $post->authorModel->nameHTML() }}

    <time pubdate="pubdate" datetime="{{{ date(DATE_ATOM, $post->pubTime->timestamp) }}}">
      <a href="{{{ $post->url() }}}">
        {{{ DateFmt::Format('AGO-AT[s-d]IF>2[d# m__ y##]AT h#m', $post->pubTime->timestamp, Config::get('application.language')) }}}
      </a>
    </time>
  </footer>

  @if ($post->children)
    <div class="hvl-comment-children">
      @foreach ($post->children as $comment)
        @include('habravel::part.comment', array('post' => $comment), array())
      @endforeach
    </div>
  @endif
</div>