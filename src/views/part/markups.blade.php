<?php /*
  - $markups          - array of string
  - $current          - string
*/?>

<span class="hvl-markups {{{ count($markups) > 1 ? '' : 'hvl-markups-none' }}}">
  @if (count($markups) === 1)
    <input type="hidden" name="markup" value="{{{ head($markups) }}}">
  @else
    @foreach ($markups as $markup)
      <label>
        <input type="radio" name="markup" value="{{{ $markup }}}"
               @if ($current === $markup) checked="checked" @endif>
        {{{ trans("habravel::g.markups.$markup") }}}
      </label>

      <a class="hvl-markup-help" href="{{{ Habravel\url()."/markup/$markup" }}}">?</a>
    @endforeach
  @endif
</span>