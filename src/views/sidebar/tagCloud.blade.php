<?php /*
  - $tags             - array of Models\User
*/?>

<h6 class="hvl-sidebar-title">
  {{{ trans('habravel::g.sidebar.tagPool') }}}
</h6>

@include('habravel::part.tags', compact('tags'), array())
