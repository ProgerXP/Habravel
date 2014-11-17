<?php /*
  - $errors           - optional; MessageBag instance
  - $post             - Models\Post instance
  - $tags             - array of Models\Tag
  - $tagPool          - array of Models\Tag
  - $polls            - array of Models\Poll 
  - $markups          - array of markup names ('githubmarkdown', 'uversewiki', etc.)
  - $textPlaceholder  - default text for textarea
*/?>

@extends('habravel::page')

@section('content')
  <form action="{{{ Habravel\url() }}}/edit" method="post"
        class="hvl-pedit-form hvl-split" data-hvl-post="{{{ $post->toJSON() }}}">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
    <input type="hidden" name="id" value="{{{ $post->id }}}">

    <aside>
      <div class="hvl-pedit-topbtn">
        <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>

        <span class="hvl-pedit-preview-hotkey">Ctrl/Alt+Enter &rarr;</span>
        <span class="hvl-pedit-preview-blocked">{{{ trans('habravel::g.edit.blocked') }}}</span>

        <button class="hvl-btn hvl-pedit-preview" type="submit" name="preview" value="1">
          <i class="hvl-i-zoomw"></i> {{{ trans('habravel::g.edit.preview') }}}
        </button>

        <button class="hvl-btn hvl-pedit-expand" type="button">
          <i class="hvl-i-expandw"></i> {{{ trans('habravel::g.edit.expand') }}}
        </button>
      </div>

      <h1 class="hvl-h1">
        @if ($post->id)
          {{{ $post->caption }}}
          <?php $pageTitle = trans('habravel::g.edit.title')?>
        @else
          {{{ $pageTitle = trans('habravel::g.edit.titleNew') }}}
        @endif
      </h1>
    </aside>

    <div class="hvl-split-left">
      @if (isset($errors))
        {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
      @endif

      @if (count($markups) > 1)
        <div class="hvl-pedit-ctl">
          <div class="hvl-pedit-ctl-caption">
            <b>{{{ trans('habravel::g.edit.markup') }}}</b>
            @include('habravel::part.markups', compact('markup'), array('current' => $post->markup))
          </div>
        </div>
      @else
        @include('habravel::part.markups', compact('markup'), array())
      @endif

      <div class="hvl-pedit-ctl">
        <p>
          <input class="hvl-input" name="caption" value="{{{ $post->caption }}}"
                 placeholder="{{{ trans("habravel::g.edit.caption") }}}"
                 required="required" @if (!$post->id) autofocus="autofocus" @endif>
        </p>
      </div>

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.source') }}}</b>
        </p>

        <p>
          <input class="hvl-input" name="sourceName" value="{{{ $post->sourceName }}}"
                 placeholder="{{{ trans("habravel::g.edit.sourceName") }}}">
        </p>

        <p>
          <input class="hvl-input" name="sourceURL" value="{{{ $post->sourceURL }}}"
                 placeholder="{{{ trans('habravel::g.edit.sourceURL') }}}"
                 data-prompt="{{{ trans('habravel::g.edit.isTranslation') }}}">
        </p>
      </div>

      @include('habravel::edit.tags', compact('post', 'tags', 'tagPool'), array())
      @include('habravel::edit.polls', compact('post', 'polls'), array())

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <button type="submit" class="hvl-btn hvl-btn-orange hvl-btn-20" name="publish" value="1">
            {{{ trans('habravel::g.edit.publish') }}}
          </button>
        </p>
      </div>
    </div>

    <div class="hvl-split-right">
      <textarea class="hvl-input hvl-pedit-text" name="text"
                data-sqa="r - w$body{pb} - wr$~*{ho} - $~.hvl-pedit-ctl{ho}"
                rows="20" cols="50" tabindex="2" required="required"
                @if ($post->id) autofocus="autofocus" @endif
                placeholder="{{{ $textPlaceholder }}}">{{{ $post->text }}}</textarea>

      <div class="hvl-pedit-ctl">
        <button class="hvl-btn" type="submit" name="tags[]"
                value="{{{ Config::get('habravel::g.tags.draft') }}}">
          {{{ trans('habravel::g.edit.save') }}}
        </button>
      </div>
    </div>
  </form>
@stop