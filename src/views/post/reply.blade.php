<?php /*
  - $post             - Models\Post instance with loaded author, tags
*/?>

<form action="{{{ Habravel\url() }}}/reply" method="post" class="hvl-ncomment">
  <?php $tag = $post->x_children ? 'h3' : 'h2'?>
  <{{ $tag }} class="hvl-{{ $tag }}">{{{ trans('habravel::g.ncomment.title') }}}</{{ $tag }}>

  <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
  <input type="hidden" name="parent" value="{{{ $post->id }}}">

  <b>{{{ trans('habravel::g.ncomment.markup') }}}</b>
  @include('habravel::part.markups', array(), array())

  <textarea name="text" class="hvl-input" rows="15" cols="80" required="required"
            placeholder="{{{ trans('habravel::g.ncomment.text') }}}"></textarea>

  <p class="hvl-ncomment-btns">
    <button type="submit" class="hvl-btn hvl-btn-orange">
      {{{ trans('habravel::g.ncomment.submit') }}}
    </button>

    <button type="submit" class="hvl-btn hvl-ncomment-preview-btn" name="preview" value="1">
      <i class="hvl-i-zoomw"></i>
      {{{ trans('habravel::g.ncomment.preview') }}}
    </button>
  </p>
</form>
