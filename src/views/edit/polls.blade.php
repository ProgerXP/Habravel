<?php /*
  - $post             - Models\Post instance
  - $polls            - array of Models\Poll
*/?>

<div class="hvl-pedit-ctl hvl-pedit-polls">
  <p class="hvl-pedit-ctl-caption">
    <b>{{{ trans('habravel::g.edit.polls') }}}</b>
    <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>
  </p>

  @if (count($polls))
    @foreach ($polls as $index => $poll)
      @include('habravel::edit.poll', compact('index', 'poll'), array())
    @endforeach
  @else
    @include('habravel::edit.poll', array('index' => 0, 'poll' => new Habravel\Models\Poll), array())
  @endif

  <p>
    <button type="button" class="hvl-btn hvl-pedit-poll-add">
      {{{ trans('habravel::g.edit.addPoll') }}}
    </button>
  </p>
</div>
