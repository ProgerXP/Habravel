<?php /*
  - $post             - Post instance with loaded $post->tags relationship
  - $markups          - array of markup names ('githubmarkdown', 'uversewiki', etc.)
  - $textPlaceholder  - default text for textarea
*/?>

@extends('habravel::page')

<?php $root = url(Config::get('habravel::g.rootURL'))?>

@section('content')
  @include('habravel::part.uheader')

  <div class="hvl-pedit-topbtn">
    <button class="hvl-btn hvl-pedit-preview">
      <i class="hvl-i-preview"></i> {{{ trans('habravel::g.edit.preview') }}}
    </button>

    <button class="hvl-btn hvl-pedit-expand">
      <i class="hvl-i-expand"></i> {{{ trans('habravel::g.edit.expand') }}}
    </button>
  </div>

  <h1>
    @if ($post->id)
      {{{ $post->caption }}}
    @else
      {{{ trans('habravel::g.edit.titleNew') }}}
    @endif
  </h1>

  <form action="{{{ $root }}}/edit" method="post" class="hvl-splitter hvl-pedit-form"
        data-post="{{{ $post->toJSON() }}}">
    <input type="hidden" name="id" value="{{{ $post['id'] }}}">

    <div class="hvl-splitter-left">
      @if ($markups)
        <div class="hvl-pedit-ctl">
          <p class="hvl-pedit-ctl-caption">
            <b>{{{ trans('habravel::g.edit.markup') }}}</b>
            @foreach ($markups as $markup)
              <label>
                <input type="radio" name="markup" value="{{{ $markup }}}"
                       @if ($post['markup'] === $markup) checked="checked" @endif>
                {{{ trans("habravel::g.markups.$markup") }}}
                <u class="hvl-pedit-markup-help">?</u>
              </label>

              <aside class="hvl-pedit-markup-text">
                {{ trans("habravel::g.markups.{$markup}_help") }}
              </aside>
            @endforeach
          </p>
        </div>
      @endif

      <div class="hvl-pedit-ctl">
        <p>
          <input name="caption" placeholder="{{{ trans("habravel::g.edit.caption") }}}"
                 value="{{{ $post['caption'] }}}">
        </p>
      </div>

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.source') }}}</b>
        </p>

        <p>
          <input name="sourceName" placeholder="{{{ trans("habravel::g.edit.sourceName") }}}"
                 value="{{{ $post['sourceName'] }}}">
        </p>

        <p>
          <input name="sourceURL" placeholder="{{{ trans("habravel::g.edit.sourceURL") }}}"
                 value="{{{ $post['sourceURL'] }}}">
        </p>
      </div>

      <div class="hvl-pedit-ctl hvl-pedit-tags">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.tags') }}}</b>
          <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>
        </p>
      </div>

      <div class="hvl-pedit-ctl hvl-pedit-polls">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.polls') }}}</b>
          <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>
        </p>

        <p>
          <input name="poll[0][caption]" placeholder="{{{ trans('habravel::g.edit.poll') }}}">
        </p>

        <p>
          <label>
            <input type="radio" name="poll[0][multiple]" value="0">
            {{{ trans('habravel::g.edit.pollSingle') }}}
          </label>

          <label>
            <input type="radio" name="poll[0][multiple]" value="1">
            {{{ trans('habravel::g.edit.pollMultiple') }}}
          </label>
        </p>

        <p class="hvl-pedit-poll-opt">
          <b class="hvl-pedit-poll-opt-num">1)</b>
          <input name="pollOption[0][0][caption]" placeholder="{{{ trans('habravel::g.edit.option') }}}">
        </p>

        <p>
          <button type="button" class="hvl-pedit-poll-add">{{{ trans('habravel::g.edit.addPoll') }}}</button>
        </p>
      </div>

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <button type="submit" name="publish" value="1">{{{ trans('habravel::g.edit.publish') }}}</button>
        </p>
      </div>
    </div>

    <div class="hvl-splitter-right">
      <textarea name="text" placeholder="{{{ $textPlaceholder }}}">{{{ $post->text }}}</textarea>

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <button type="submit">{{{ trans('habravel::g.edit.save') }}}</button>
        </p>
      </div>
    </div>
  </div>
@stop