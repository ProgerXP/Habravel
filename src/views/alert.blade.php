<?php /*
  - $class   - 'success|error|info'
  - $message - string
*/?>
<div class="{{{ Session::get('class', 'success') }}}">
  <p class="message">{{{ Session::get('message') }}}</p>
</div>