<?php namespace Habravel;

use Route;
use View;

class Core extends \Illuminate\Support\ServiceProvider {
  static function input($name, $default = null) {
    return call_user_func(\Config::get('habravel::g.input'), $name, $default);
  }

  static function user() {
    static $user;

    if (!$user) {
      $user = call_user_func(\Config::get('habravel::g.user'));
      if ($user and ! $user instanceof User) {
        App::abort(400, 'habravel::user returned wrong value');
      }
    }

    return $user;
  }

  function boot() {
    $this->package('proger/habravel');
    class_alias('Illuminate\\Database\\Eloquent\\Builder', __NAMESPACE__.'\\Query');

    app('config')     ->addNamespace('habravel', __DIR__.'/../config');
    app('view')       ->addNamespace('habravel', __DIR__.'/../views');
    app('translator') ->addNamespace('habravel', __DIR__.'/../lang');

    $this->routes();
    $this->events();
    $this->composers();
  }

  function register() { }

  function routes() {
    Route::group(array('prefix' => \Config::get('habravel::g.rootURL')), function () {
      Route::pattern('habravel_any', '.+');
      Route::pattern('habravel_user', '[\w\d]+');
      Route::pattern('habravel_id', '\d+');

      $ctl = 'Habravel\\Controller';
      Route::get    ('edit/{habravel_any}',     "$ctl@getEditPostByURL");
      Route::post   ('edit/{habravel_any}',     "$ctl@postEditPostByURL");
      Route::get    ('tags/{habravel_any}',     "$ctl@getListByTags");
      Route::get    ('~{habravel_user}',        "$ctl@getUserByName");
      Route::get    ('users/{habravel_id}',     "$ctl@getUser");
      Route::get    ('{habravel_any}',          "$ctl@getPostByURL");
      Route::get    ('',                        "$ctl@getList");
    });
  }

  function events() {
    Event::listen('habravel.out.get.post', function (Post $post) {
      return \View::make('habravel::post', compact('post'));
    });

    Event::listen('habravel.out.get.list', function (Query $query) {
      $limit = 10;
      $query->take($limit)->skip(Core::input('page') * $limit);
    }, 5);

    Event::listen('habravel.out.get.list', function (Query $query) {
      $posts = $query->get();
      return \View::make('habravel::posts', compact('posts'));
    });

    /***
      Drafts Support
     ***/

    Event::listen('habravel.out.get.post', function (Post $post) {
      if ($post->tags->first(function ($t) { return $t->type == 'draft'; })) {
        $user = Core::user();
        if (!$user or ($user->id !== $post->author and !$user->hasFlag('read.draft'))) {
          App::abort(403, 'This is an author\'s draft.');
        }
      }
    }, 10);

    Event::listen('habravel.out.get.list', function (Query $query) {
      $user = Core::user();
      if (!$user or !$user->hasFlag('read.draft')) {
        $query->where('tags.type', '!=', 'draft');
      }
    }, 5);
  }

  function composers() {
    View::composer('habravel::page', function ($view) {
      isset($view->pageTitle) or $view->pageTitle = trans('habravel::g.pageTitle');
    });
  }
}