<?php namespace Habravel;

use App;
use Route;
use Config;

define('NS', __NAMESPACE__.'\\');

// Priorities for Event::listen().
define('VALIDATE', 10);
define('CUSTOMIZE', 5);
define('LAST', -10);

class Core extends \Illuminate\Support\ServiceProvider {
  // Without trailing '/'.
  static function url($absolute = true) {
    $url = Config::get('habravel::g.rootURL');
    return $absolute ? url($url) : $url;
  }

  static function input($name = null, $default = null) {
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
      App::abort(500, "Unknown markup [$name].");
    }
  }

  static function user($login = null) {
    static $user;

    if (!$user) {
      $user = call_user_func_array(Config::get('habravel::g.user'), func_get_args());
      if ($user and ! $user instanceof User) {
        App::abort(400, 'habravel::user returned wrong value');
      }
    }

    return $user;
  }

  function boot() {
    $this->package('proger/habravel');
    class_alias('Illuminate\\Database\\Eloquent\\Builder', NS.'Query');

    app('config')     ->addNamespace('habravel', __DIR__.'/../config');
    app('view')       ->addNamespace('habravel', __DIR__.'/../views');
    app('translator') ->addNamespace('habravel', __DIR__.'/../lang');

    if (Config::get('habravel::g.csrfRegenTime') < time() - \Session::get('time')) {
      \Session::regenerateToken();
    }

    App::after(array($this, 'shutdown'));
    $this->routes();
    require __DIR__.'/../events.php';
  }

  function register() { }

  static function shutdown() {
    \Session::put('time', time());
  }

  function routes() {
    Route::group(array('prefix' => Core::url(false)), function () {
      Route::pattern('habravel_any', '.+');
      Route::pattern('habravel_user', '[\w\d]+');
      Route::pattern('habravel_id', '\d+');

      $ctl = 'Habravel\\Controller';
      // Article.
      Route::get    ('compose',                 "$ctl@getEditPost");
      Route::get    ('edit/{habravel_any}',     "$ctl@getEditPostByURL");
      Route::post   ('edit',                    "$ctl@postEditPost");
      Route::get    ('tags/{habravel_any}',     "$ctl@getListByTags");
      Route::get    ('up/{habravel_id}',        "$ctl@getVoteUpByURL");
      Route::get    ('down/{habravel_id}',      "$ctl@getVoteDownByURL");
      Route::post   ('reply',                   "$ctl@postReply");
      Route::get    ('best/day',                "$ctl@getBestListDay");
      Route::get    ('best/week',               "$ctl@getBestListWeek");
      Route::get    ('best',                    "$ctl@getBestList");
      // User.
      Route::get    ('logout',                  "$ctl@getLogout");
      Route::get    ('login',                   "$ctl@getLogin");
      Route::post   ('login',                   "$ctl@postLogin");
      Route::get    ('register',                "$ctl@getRegister");
      Route::post   ('register',                "$ctl@postRegister");
      Route::get    ('~',                       "$ctl@getCurrentUser");
      Route::get    ('~{habravel_user}',        "$ctl@getUserByName");
      Route::get    ('~{habravel_user}/posts',  "$ctl@getUserByNamePosts");
      Route::get    ('~{habravel_user}/comments', "$ctl@getUserByNameComments");
      Route::get    ('users/{habravel_id}',     "$ctl@getUser");
      // Fallback.
      Route::get    ('{habravel_any}',          "$ctl@getPostByURL");
      Route::get    ('',                        "$ctl@getBestListWeek");
    });
  }
}