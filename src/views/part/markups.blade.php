<?php /*
  - $markups          - array of string
  - $current          - string
*/?>

<span class="hvl-markups">
  @if (count($markups) === 1)
    <input type="hidden" name="markup" value-"{{{ head($markups) }}}">
  @else
    @foreach ($markups as $markup)
      <label>
        <input type="radio" name="markup" value="{{{ $markup }}}"
               @if ($current === $markup) checked="checked" @endif>
        {{{ trans("habravel::g.markups.$markup") }}}
      </label>

      <u class="hvl-markup-help">?</u>

      <aside class="hvl-markup-text">
        @include("habravel::markup.{$markup}", array(), array())
      </aside>
    @endforeach
  @endif
</span>