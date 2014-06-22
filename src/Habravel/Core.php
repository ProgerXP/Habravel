<?php namespace Habravel;

use Config;
use Route;
use View;
use App;

// Priorities for Event::listen().
define('VALIDATE', 10);
define('CUSTOMIZE', 5);

class Core extends \Illuminate\Support\ServiceProvider {
  static function input($name, $default = null) {
    return call_user_func(Config::get('habravel::g.input'), $name, $default);
  }

  static function markups() {
    return array_keys(Config::get('habravel::g.markups'));
  }

  static function markup($name) {
    $class = Config::get("habravel::g.markups.$name");
    if ($class) {
      return new $class;
    } else {
      throw new Error("Unknown markup [$name].");
    }
  }

  static function user() {
    static $user;

    if (!$user) {
      $user = call_user_func(Config::get('habravel::g.user'));
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
    Route::group(array('prefix' => Config::get('habravel::g.rootURL')), function () {
      Route::pattern('habravel_any', '.+');
      Route::pattern('habravel_user', '[\w\d]+');
      Route::pattern('habravel_id', '\d+');

      $ctl = 'Habravel\\Controller';
      Route::get    ('compose',                 "$ctl@getEditPost");
      Route::get    ('edit/{habravel_any}',     "$ctl@getEditPostByURL");
      Route::post   ('edit',                    "$ctl@postEditPost");
      Route::get    ('tags/{habravel_any}',     "$ctl@getListByTags");
      Route::get    ('~{habravel_user}',        "$ctl@getUserByName");
      Route::get    ('users/{habravel_id}',     "$ctl@getUser");
      Route::get    ('{habravel_any}',          "$ctl@getPostByURL");
      Route::get    ('',                        "$ctl@getList");
    });
  }

  function events() {
    Event::listen('habravel.out.post', function (Post $post) {
      return \View::make('habravel::post', compact('post'));
    });

    Event::listen('habravel.out.edit', function (Post $post) {
      return \View::make('habravel::edit', compact('post'));
    });

    Event::listen('habravel.out.list', function (Query $query) {
      $limit = 10;
      $query->take($limit)->skip(Core::input('page') * $limit);
    }, CUSTOMIZE);

    Event::listen('habravel.out.list', function (Query $query) {
      $posts = $query->get();
      return \View::make('habravel::posts', compact('posts'));
    });

    /***
      Drafts Support
     ***/

    $checkDraft = function ($action, Post $post) {
      if ($post->hasFlag('draft')) {
        $user = Core::user();
        if (!$user or ($user->id !== $post->author and !$user->hasFlag("draft.$action"))) {
          App::abort(403, "Cannot $action author's draft.");
        }
      }
    };

    Event::listen('habravel.out.post', function (Post $post) use ($checkDraft) {
      $checkDraft('read', $post);
    }, VALIDATE);

    Event::listen('habravel.out.edit', function (Post $post) use ($checkDraft) {
      $checkDraft('edit', $post);
    }, VALIDATE);

    Event::listen('habravel.out.list', function (Query $query) {
      $user = Core::user();
      if (!$user or !$user->hasFlag('read.draft')) {
        $query->where('posts.flags', 'NOT LIKE', '%[draft]%');
      }
    }, CUSTOMIZE);
  }

  function composers() {
    View::composer('habravel::page', function ($view) {
      isset($view->pageTitle) or $view->pageTitle = trans('habravel::g.pageTitle');
    });

    View::composer('habravel::part.uheader', function ($view) {
      isset($view->pageUser) or $view->pageUser = Core::user();
    });

    View::composer('habravel::edit', function ($view) {
      isset($view->markups) or $view->markups = Core::markups();

      if (!isset($view->textPlaceholder)) {
        $list = trans('habravel::g.edit.placeholders');
        $view->textPlaceholder = $list[array_rand($list)];
      }
    });
  }
}