<?php /*
  - $poll             - Poll with x_options
  - $index            - integer
*/?>

<div class="hvl-pedit-ctl hvl-pedit-poll">
  <input type="hidden" name="polls[{{{ $index }}}][id]" value="{{{ $poll->id }}}">

  <p>
    <input class="hvl-input" name="polls[{{{ $index }}}][caption]"
           value="{{{ $poll->caption }}}"
           placeholder="{{{ trans('habravel::g.edit.poll') }}}">
  </p>

  <p>
    <label>
      <input type="radio" name="polls[{{{ $index }}}][multiple]" value="0"
             @if (!$poll->multiple) checked="checked" @endif>
      {{{ trans('habravel::g.edit.pollSingle') }}}
    </label>

    <label>
      <input type="radio" name="polls[{{{ $index }}}][multiple]" value="1"
             @if ($poll->multiple) checked="checked" @endif>
      {{{ trans('habravel::g.edit.pollMultiple') }}}
    </label>
  </p>

  <?php $i = -1?>
  @if (!empty($poll->x_options))
    @foreach ($poll->x_options as $i => $option)
      <p class="hvl-pedit-poll-opt">
        <b class="hvl-pedit-poll-opt-num">{{{ $i + 1 }}})
        </b>

        <input type="hidden" name="options[{{{ $index }}}][{{{ $i }}}][id]" value="{{{ $option->id }}}">

        <input class="hvl-input" name="options[{{{ $index }}}][{{{ $i }}}][caption]"
               value="{{{ $option->caption }}}"
               placeholder="{{{ trans('habravel::g.edit.option') }}}">
      </p>
    @endforeach
  @endif

  <p class="hvl-pedit-poll-opt">
    <b class="hvl-pedit-poll-opt-num">{{{ $i + 2 }}})
    </b><input class="hvl-input" name="options[{{{ $index }}}][{{{ $i + 1 }}}][caption]"
               placeholder="{{{ trans('habravel::g.edit.option') }}}">
  </p>
</div>