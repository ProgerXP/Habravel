<?php /*
  - $post             - Models\Post instance, the comment itself
  - $author           - Models\User
  - $post->x_children - array of Models\Post instances, replies to this comment
  - $hasTop           - optional; true to display original post title; needs $top
  - $topPost          - optional unless $hasTop is false; Models\Post
  - $hasReply         - optional; true to display Reply button
  - $canEdit          - optional; boolean
  - $canEditValue     - boolean (set by composer, do not pass)
*/?>

<div class="hvl-comment" id="cmt-{{{ $post->id }}}" data-hvl-post-id="{{{ $post->id }}}">
  @if (!empty($hasTop))
    <div class="hvl-comment-top">
      <a href="{{{ $topPost->url() }}}">{{{ $topPost->caption }}}</a>
    </div>
  @endif

  <a href="{{{ $author->url() }}}" class="hvl-comment-avatar" title="{{{ $author->name }}}">
    <img src="{{{ $author->avatarURL() }}}" alt="{{{ $author->name }}}"></a>

  <article class="hvl-markedup hvl-markedup-{{{ $post->markup }}}">
    {{ $post->html }}
  </article>

  <footer class="hvl-comment-footer">
    @if (!empty($hasReply))
      <u class="hvl-comment-reply-btn">
        {{{ trans('habravel::g.comment.reply') }}}</u>
    @endif

    @if (!empty($canEditValue))
      <u class="hvl-comment-edit-btn">
        {{{ trans('habravel::g.post.edit') }}}</u>
    @endif

    {{ $author->nameHTML() }}

    @if ($post->pubTime)
      <time pubdate="pubdate" datetime="{{{ date(DATE_ATOM, $post->pubTime->timestamp) }}}">
        <a href="{{{ $post->url() }}}" title="{{{ trans('habravel::g.comment.anchor') }}}">
          {{{ DateFmt::Format('AGO-AT[s-d]IF>2[d# m__ y##]AT h#m', $post->pubTime->timestamp, Config::get('app.locale')) }}}</a>
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