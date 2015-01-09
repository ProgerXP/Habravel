<?php /*
  - $post             - array of Models\Post
  - $author           - array of Models\User
*/?>

<p class="hvl-post-author">
  <a href="{{{ $author->url() }}}" class="hvl-post-author-avatar">
    <img src="{{{ $author->avatarURL() }}}" alt="{{{ $author->name }}}">
  </a>
  {{ $author->nameHTML() }}
</p>

@if ($author->site)
  <p>
    {{{ trans('habravel::g.profile.site') }}}
    {{ Habravel\externalLink($author->site) }}
  </p>
@endif

<p>
  {{{ trans('habravel::g.source.size') }}}
  {{{ $post->statString() }}}
</p>

<p>
  {{{ trans('habravel::g.source.markup') }}}
  <a class="hvl-markup-help" href="{{{ Habravel\url()."/markup/$post->markup" }}}">
    {{{ trans('habravel::g.markups.'.$post->markup) }}}</a>
</p>

<p>
  <a href="{{{ Habravel\url()."/source/$post->id" }}}">
    {{{ trans('habravel::g.sidebar.postSource') }}}</a>
</p>