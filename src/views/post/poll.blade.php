<?php /*
  - $poll             - Models\Poll
  - $options          - array of Models\PollOption with x_voteCount
  - $voteCount        - integer; total vote count for all poll options
*/?>

<div class="hvl-poll" data-sqa="poll"
     data-hvl-poll="{{{ join(' ', array_pluck($options, 'x_voteCount')) }}}">
  <h2 id="poll-{{{ $poll->id }}}" class="hvl-h2">
    {{{ trans('habravel::g.post.poll') }}}
    <span>{{{ $poll->caption }}}</span>
    @if ($voteCount) ({{ Habravel\number($voteCount) }}) @endif
  </h2>

  <canvas data-sqa="poll - | [height: h | [width: h"></canvas>

  @foreach ($options as $option)
    <div class="hvl-poll-option">
      <p>
        <label>
          @if ($poll->multiple)
            <input type="checkbox" name="votes[]" value="{{{ $option->id }}}">
          @else
            <input type="radio" name="votes[{{{ $poll->id }}}]" value="{{{ $option->id }}}">
          @endif
          {{{ $option->caption }}}
        </label>
        ({{ Habravel\number($option->x_voteCount) }})
      </p>

      <hr style="width: {{{ $voteCount ? $option->x_voteCount / $voteCount * 100 : 0 }}}%">
    </div>
  @endforeach
</div>
