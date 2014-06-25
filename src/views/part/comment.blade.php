<?php /*
  - $post             - Post instance, the comment itself, with _author
  - $post->-children  - array of Post instances, replies to this comment
  - $hasTop           - optional; true to display original post title; needs _top
  - $hasReply         - optional; true to display Reply button
  - $canEdit          - boolean
*/?>

<div class="hvl-comment" id="cmt-{{{ $post->id }}}" data-hvl-post-id="{{{ $post->id }}}">
  @if (!empty($hasTop))
    <div class="hvl-comment-top">
      <a href="{{{ $post->_top->url() }}}">{{{ $post->_top->caption }}}</a>
    </div>
  @endif

  <a href="{{{ $post->_author->url() }}}" class="hvl-comment-avatar" title="{{{ $post->_author->name }}}">
    <img src="{{{ $post->_author->avatarURL() }}}" alt="{{{ $post->_author->name }}}">
  </a>

  <article class="hvl-markedup">
    {{ $post->html }}
  </article>

  <footer class="hvl-comment-footer">
    @if (!empty($hasReply))
      <u class="hvl-comment-reply-btn">
        {{{ trans('habravel::g.comment.reply') }}}</u>
    @endif

    @if (!empty($canEdit))
      <u class="hvl-comment-edit-btn">
        {{{ trans('habravel::g.post.edit') }}}
      </u>
    @endif

    {{ $post->_author->nameHTML() }}

    <time pubdate="pubdate" datetime="{{{ date(DATE_ATOM, $post->pubTime->timestamp) }}}">
      <a href="{{{ $post->url() }}}" title="{{{ trans('habravel::g.comment.anchor') }}}">
        {{{ DateFmt::Format('AGO-AT[s-d]IF>2[d# m__ y##]AT h#m', $post->pubTime->timestamp, Config::get('application.language')) }}}
      </a>
    </time>
  </footer>

  {{-- Must be present because Reply button puts reply form here. --}}
  <div class="hvl-comment-children">
    @foreach ($post->_children as $post)
      @include('habravel::part.comment')
    @endforeach
  </div>
</div>