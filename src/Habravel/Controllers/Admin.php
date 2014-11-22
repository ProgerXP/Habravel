<?php namespace Habravel\Controllers;

class Admin extends BaseController {
  function __construct() {
    parent::__construct();

    $this->beforeFilter(function () {
      if (!user()) {
        App::abort(401);
      } elseif (!user()->hasFlag('admin')) {
        App::abort(403);
      }
    });
  }

  function show() {
    return View::make('habravel::admin');
  }

  function resetHTML() {
    \DB::update("UPDATE posts SET html = '', introHTML = ''");
    return Redirect::to(\Habravel\url().'/admin');
  }

  function regenHTML() {
    set_time_limit(3600);

    foreach (\Habravel\Models\Post::all() as $post) {
      $post->html = $post->introHTML = '';
      $post->needHTML();
    }

    return Redirect::to(\Habravel\url().'/admin');
  }
}