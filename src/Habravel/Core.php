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

  //? normReferer('')     //=> http://mydomain/habravel-home/
  //? normReferer('foo')  //=> http://mydomain/habravel-home/
  //? normReferer('http://foo')             //=> http://mydomain/habravel-home/
  //? normReferer('http://mydomain/habravel-home/path/')   //=> as is
  //? normReferer('http://mydomain/foo')    //=> as is
  //? normReferer('https://mydomain/foo')   //=> as is
  //? normReferer('http://www.mydomain/foo')    //=> as is
  //? normReferer('https://www.mydomain/foo')   //=> as is
  static function normReferer($url) {
    $host = parse_url(static::url(), PHP_URL_HOST);

    if ($url and preg_match('~^https?://(www\.)?'.$host.'/~i', $url)) {
      return $url;
    } else {
      return Core::url();
    }
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

  static function safeHTML($html) {
    static $hs;

    if (!$hs) {
      $hs = new \HyperSafe;

      foreach (Config::get('habravel::hypersafe') as $name => $value) {
        $hs->$name = $value;
      }
    }

    $hs->clearWarnings();
    $clean = $hs->clean($html);

    if ($warnings = $hs->warnings() and
        Config::get('habravel::hypersafe.hvlLogWarnings')) {
      $warnings = array_pluck($warnings, 'msg');
      array_unshift($warnings, $html);
      \Log::warning(sprintf('Habravel: %d warning%s normalizing HTML.',
                            count($warnings) - 1, count($warnings) == 2 ? '' : 's'), $warnings);
    }

    return $clean;
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
      Route::get    ('markup/{name}',           "$ctl@getMarkupHelp");
      Route::get    ('compose',                 "$ctl@getEditPost");
      Route::get    ('posts/{habravel_id}',     "$ctl@getPost");
      Route::get    ('posts',                   "$ctl@getList");
      Route::get    ('source/{habravel_id}',    "$ctl@getPostSource");
      Route::get    ('edit/{habravel_id}',      "$ctl@getEditPost");
      Route::post   ('edit',                    "$ctl@postEditPost");
      Route::get    ('tags/{habravel_any}',     "$ctl@getListByTags");
      Route::post   ('vote',                    "$ctl@postVote");
      Route::get    ('up/{habravel_id}',        "$ctl@postVoteUpByPost");
      Route::get    ('down/{habravel_id}',      "$ctl@postVoteDownByPost");
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
      Route::get    ('~{habravel_id}',          "$ctl@getUser");
      Route::get    ('~{habravel_user}',        "$ctl@getUserByName");
      Route::get    ('~{habravel_user}/posts',  "$ctl@getUserByNamePosts");
      Route::get    ('~{habravel_user}/comments', "$ctl@getUserByNameComments");
      Route::get    ('~{habravel_user}/up',     "$ctl@postVoteUpByUserName");
      Route::get    ('~{habravel_user}/down',   "$ctl@postVoteDownByUserName");
      // Fallback.
      Route::get    ('{habravel_any}',          "$ctl@getPostByURL");
      Route::get    ('',                        "$ctl@getBestListWeek");
    });
  }
}