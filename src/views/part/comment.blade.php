<?php /*
  - $post             - Models\Post instance, the comment itself, with x_author
  - $post->-children  - array of Models\Post instances, replies to this comment
  - $hasTop           - optional; true to display original post title; needs x_top
  - $hasReply         - optional; true to display Reply button
  - $canEdit          - boolean
*/?>

<div class="hvl-comment" id="cmt-{{{ $post->id }}}" data-hvl-post-id="{{{ $post->id }}}">
  @if (!empty($hasTop))
    <div class="hvl-comment-top">
      <a href="{{{ $post->x_top->url() }}}">{{{ $post->x_top->caption }}}</a>
    </div>
  @endif

  <a href="{{{ $post->x_author->url() }}}" class="hvl-comment-avatar" title="{{{ $post->x_author->name }}}">
    <img src="{{{ $post->x_author->avatarURL() }}}" alt="{{{ $post->x_author->name }}}">
  </a>

  <article class="hvl-markedup hvl-markedup-{{{ $post->markup }}}">
    {{ $post->html }}
  </article>

  <footer class="hvl-comment-footer">
    @if (!empty($hasReply))
      <u class="hvl-comment-reply-btn">
        {{{ trans('habravel::g.comment.reply') }}}</u>
    @endif

    @if (!empty($canEdit))
      <u class="hvl-comment-edit-btn">
        {{{ trans('habravel::g.post.edit') }}}</u>
    @endif

    {{ $post->x_author->nameHTML() }}

    @if ($post->pubTime)
      <time pubdate="pubdate" datetime="{{{ date(DATE_ATOM, $post->pubTime->timestamp) }}}">
        <a href="{{{ $post->url() }}}" title="{{{ trans('habravel::g.comment.anchor') }}}">
          {{{ DateFmt::Format('AGO-AT[s-d]IF>2[d# m__ y##]AT h#m', $post->pubTime->timestamp, Config::get('app.locale')) }}}
        </a>
      </time>
    @endif
  </footer>

  {{-- Must be present because Reply button puts reply form here. --}}
  <div class="hvl-comment-children">
    @foreach ($post->x_children as $post)
      @include('habravel::part.comment')
    @endforeach
  </div>
</div>