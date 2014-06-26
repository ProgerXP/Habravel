<?php /*
  - $errors           - optional; MessageBag instance
  - $post             - Post instance with x_tags, x_polls
  - $markups          - array of markup names ('githubmarkdown', 'uversewiki', etc.)
  - $textPlaceholder  - default text for textarea
  - $tagPool          - array of string
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  <form action="{{{ Habravel\Core::url() }}}/edit" method="post" class="hvl-pedit-form"
        data-hvl-post="{{{ $post->toJSON() }}}">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
    <input type="hidden" name="id" value="{{{ $post->id }}}">

    <aside>
      <div class="hvl-pedit-topbtn">
        <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>

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
        @else
          {{{ trans('habravel::g.edit.titleNew') }}}
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
                 required="required" autofocus="autofocus">
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
                 placeholder="{{{ trans("habravel::g.edit.sourceURL") }}}">
        </p>
      </div>

      <div class="hvl-pedit-ctl hvl-pedit-tags" data-sqa="wr mnh: Math.max(mnh, h)">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.tags') }}}</b>
          <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>
        </p>

        <p class="hvl-pedit-tags-to">
          @foreach ($post->x_tags as $tag)
            @if (false !== $index = array_search($tag->caption, $tagPool))
              <?php unset($tagPool[$index])?>
              <a target="_blank" href="{{{ $tag->url() }}}">{{{ $tag->caption }}}</a>
            @else
              <a class="hvl-pedit-tags-custom" target="_blank" href="{{{ $tag->url() }}}">{{{ $tag->caption }}}</a>
            @endif
          @endforeach

          <span class="hvl-pedit-tags-none">{{{ trans('habravel::g.edit.tagHelp') }}}</span>
        </p>

        <p class="hvl-pedit-tags-from">
          @foreach ($tagPool as $tag)
            <a target="_blank" href="{{{ Habravel\Core::url().'/tags/'.urlencode($tag) }}}">
              {{{ $tag }}}
            </a>
          @endforeach

          <u class="hvl-pedit-tags-new">{{{ trans('habravel::g.edit.newTag') }}}</u>
        </p>
      </div>

      <div class="hvl-pedit-ctl hvl-pedit-polls">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.polls') }}}</b>
          <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>
        </p>

        <?php $index = -1?>
        @foreach ($post->x_polls as $index => $poll)
          @include('habravel::part.editPoll', compact('index', 'poll'), array())
        @endforeach

        @include('habravel::part.editPoll', array('index' => $index + 1, 'poll' => new Habravel\Poll), array())

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

    <div class="hvl-split-right">
      <textarea class="hvl-input hvl-pedit-text" name="text" data-sqa="wr - w$body{pb}"
                rows="20" cols="50" tabindex="2" required="required"
                placeholder="{{{ $textPlaceholder }}}">{{{ $post->text }}}</textarea>
    </div>
  </form>
@stop