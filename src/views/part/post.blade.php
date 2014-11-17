<?php /*
  - $classes          - optional; string of space-separated CSS classes
  - $post             - Models\Post instance
  - $parentPost       - Models\Post or null
  - $author           - Models\User
  - $tags             - array of Models\Tag
  - $canEdit          - boolean
  - $readMore         - false or string (link text)
  - $html             - string, actual post body to be output
  - $downshift        - integer, minimum <hN> tag to generate
*/?>

<div class="hvl-post {{{ $classes }}}">
  <header class="hvl-post-header">
    <p class="hvl-post-author">
      @if ($post->sourceURL)
        <a href="{{{ $post->sourceURL }}}" title="{{{ trans('habravel::g.post.source') }}}" target="_blank">
          {{{ $post->sourceName }}}</a>
        <span class="hvl-post-author-separ">&larr;</span>
      @endif

      <span title="{{{ trans('habravel::g.post.author') }}}">
        @if ($author->avatar)
          <a href="{{{ $author->url() }}}" class="hvl-post-author-avatar">
            <img src="{{{ $author->avatarURL() }}}" alt="{{{ $author->name }}}">
          </a>
        @endif

        {{ $author->nameHTML() }}
      </span>

      @if (!empty($canEdit))
        <a href="{{{ url(Habravel\url()) }}}/edit/{{{ $post->id }}}" class="hvl-btn">
          {{{ trans('habravel::g.post.edit') }}}</a>
      @endif
    </p>

    @include('habravel::part.tags', compact('tags'), array())
  </header>

  <article class="hvl-markedup hvl-markedup-{{{ $post->markup }}}">
    {{ $html }}
  </article>

  @include('habravel::part.postFooter', compact('post', 'readMore'))
</div>