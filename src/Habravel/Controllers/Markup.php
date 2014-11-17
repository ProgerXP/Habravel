<?php namespace Habravel\Controllers;

class Markup extends BaseController {
  function showHelp($markup = '') {
    try {
      return \Habravel\Markups\Factory::make($markup)
        ->help();
    } catch (\Exception $e) {
      // Unknown markup name given.
      App::abort(404);
    }
  }
}