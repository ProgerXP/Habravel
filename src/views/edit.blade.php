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

  <h1 class="hvl-h1">
    @if ($post->id)
      {{{ $post->caption }}}
    @else
      {{{ trans('habravel::g.edit.titleNew') }}}
    @endif
  </h1>

  <form action="{{{ $root }}}/edit" method="post" class="hvl-splitter hvl-pedit-form"
        data-post="{{{ $post->toJSON() }}}" data-sqa="wr - ^$body{pb} -">
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
              </label>

              <u class="hvl-pedit-markup-help">?</u>
            @endforeach
          </p>
        </div>
      @endif

      <div class="hvl-pedit-ctl">
        <p>
          <input class="hvl-input" name="caption" value="{{{ $post['caption'] }}}"
                 placeholder="{{{ trans("habravel::g.edit.caption") }}}">
        </p>
      </div>

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.source') }}}</b>
        </p>

        <p>
          <input class="hvl-input" name="sourceName" value="{{{ $post['sourceName'] }}}"
                 placeholder="{{{ trans("habravel::g.edit.sourceName") }}}">
        </p>

        <p>
          <input class="hvl-input" name="sourceURL" value="{{{ $post['sourceURL'] }}}"
                 placeholder="{{{ trans("habravel::g.edit.sourceURL") }}}">
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
          <input class="hvl-input" name="polls[0][caption]"
                 placeholder="{{{ trans('habravel::g.edit.poll') }}}">
        </p>

        <p>
          <label>
            <input type="radio" name="polls[0][multiple]" value="0">
            {{{ trans('habravel::g.edit.pollSingle') }}}
          </label>

          <label>
            <input type="radio" name="polls[0][multiple]" value="1">
            {{{ trans('habravel::g.edit.pollMultiple') }}}
          </label>
        </p>

        <p class="hvl-pedit-poll-opt">
          <b class="hvl-pedit-poll-opt-num">1)
          </b><input class="hvl-input" name="options[0][0][caption]"
                     placeholder="{{{ trans('habravel::g.edit.option') }}}">
        </p>

        <p>
          <button type="button" class="hvl-btn hvl-pedit-poll-add">
            {{{ trans('habravel::g.edit.addPoll') }}}
          </button>
        </p>
      </div>

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <button type="submit" class="hvl-btn hvl-btn-orange hvl-btn-20" name="publish" value="1">
            {{{ trans('habravel::g.edit.publish') }}}
          </button>
        </p>
      </div>
    </div>

    <div class="hvl-splitter-right">
      <textarea class="hvl-input hvl-pedit-text" name="text" data-sqa="wr - -"
                rows="20" cols="50"
                placeholder="{{{ $textPlaceholder }}}">{{{ $post->text }}}</textarea>
    </div>
  </div>

  @foreach ($markups as $markup)
    <aside class="hvl-pedit-markup-text" data-markup="{{{ $markup }}}">
      {{ trans("habravel::g.markups.{$markup}_help") }}
    </aside>
  @endforeach
@stop