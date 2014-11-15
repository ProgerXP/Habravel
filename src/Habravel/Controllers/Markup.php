<?php namespace Habravel\Controllers;

class Markup extends BaseController {
  function showHelp($markup = '') {
    try {
      $markup = \Habravel\Markups\Factory::make($markup);
    } catch (\Exception $e) { }

    if (isset($markup)) {
      return Response::make($markup->help(), 200, array(
        'Expires'             => gmdate('D, d M Y H:i:s', time() + 3600 * 6).' GMT',
      ));
    } else {
      App::abort(404);
    }
  }
}