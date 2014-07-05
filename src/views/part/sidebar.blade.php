<?php /*
  - $pageSidebar       - array of string
*/?>

@if ($pageSidebar)
  <aside class="hvl-sidebar">
    @foreach ($pageSidebar as $key => $sidebar)
      <div class="hvl-sidebar-box hvl-sidebar-{{{ $key }}}">{{ $sidebar }}</div>
    @endforeach
  </aside>
@endif