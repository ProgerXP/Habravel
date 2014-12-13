<?php namespace Habravel;

use App;
use Route;

define('NS', __NAMESPACE__.'\\');

class ServiceProvider extends \Illuminate\Support\ServiceProvider {
  static function shutdown() {
    \Session::put('time', time());
  }

  function boot() {
    $this->package('proger/habravel');

    app('translator')->addNamespace('habravel', __DIR__.'/../lang');
    app('config')    ->addNamespace('habravel', __DIR__.'/../config');
    app('view')      ->addNamespace('habravel', __DIR__.'/../views');

    setlocale(LC_ALL, trans('habravel::g.locale'));
    mb_internal_encoding('utf-8');
    App::after(array($this, 'shutdown'));

    $this->alias();
    $this->routes();
    $this->events();
    $this->helpers();
  }

  function register() { }

  function alias() {
    class_alias('Illuminate\\Database\\Eloquent\\Builder', NS.'Controllers\\Query');
    class_alias('Illuminate\\Support\\MessageBag', NS.'Controllers\\MessageBag');

    class_alias('App',          NS.'Controllers\\App');
    class_alias('Config',       NS.'Controllers\\Config');
    class_alias('Input',        NS.'Controllers\\Input');
    class_alias('Redirect',     NS.'Controllers\\Redirect');
    class_alias('Request',      NS.'Controllers\\Request');
    class_alias('Response',     NS.'Controllers\\Response');
    class_alias('View',         NS.'Controllers\\View');
  }

  function routes() {
    Route::pattern('habravel_any', '.+');
    Route::pattern('habravel_user', '[\w\d]+');
    Route::pattern('habravel_id', '\d+');

    Route::group(array('prefix' => \Config::get('habravel::g.rootURL')), function () {
      $ns = NS.'Controllers';
      // Article.
      Route::get    ('markup/{name}',                "$ns\\Markup@showHelp");
      Route::get    ('compose',                      "$ns\\Post@showCreate");
      Route::get    ('posts/{habravel_id}',          "$ns\\Post@show");
      Route::get    ('posts',                        "$ns\\Posts@showAll");
      Route::get    ('source/{habravel_id}',         "$ns\\Post@showSource");
      Route::get    ('edit/{habravel_id}',           "$ns\\Post@showEdit");
      Route::post   ('edit',                         "$ns\\Post@edit");
      Route::get    ('tags/{habravel_any}',          "$ns\\Posts@showByTags");
      Route::post   ('vote',                         "$ns\\Poll@vote");
      Route::get    ('up/{habravel_id}',             "$ns\\Post@voteUp");
      Route::get    ('down/{habravel_id}',           "$ns\\Post@voteDown");
      Route::post   ('reply',                        "$ns\\Comment@reply");
      Route::get    ('drafts',                       "$ns\\Posts@showDrafts");
      Route::get    ('best/day',                     "$ns\\Posts@showBestDay");
      Route::get    ('best/week',                    "$ns\\Posts@showBestWeek");
      Route::get    ('best',                         "$ns\\Posts@showBestAllTime");
      // User.
      Route::get    ('logout',                       "$ns\\User@logout");
      Route::get    ('login',                        "$ns\\User@showLogin");
      Route::post   ('login',                        "$ns\\User@login");
      Route::get    ('register',                     "$ns\\User@showRegister");
      Route::post   ('register',                     "$ns\\User@register");
      Route::get    ('remindpw',                     "$ns\\User@showRemindPassword");
      Route::post   ('remindpw',                     "$ns\\User@RemindPassword");
      Route::get    ('resetpw/{habravel_any}',       "$ns\\User@showResetPassword");
      Route::post   ('resetpw/{habravel_any}',       "$ns\\User@resetPassword");
      Route::get    ('~',                            "$ns\\User@showCurrent");
      Route::get    ('~{habravel_id}',               "$ns\\User@show");
      Route::get    ('~{habravel_user}',             "$ns\\User@showByName");
      Route::get    ('~{habravel_user}/posts',       "$ns\\Posts@showByUserName");
      Route::get    ('~{habravel_user}/comments',    "$ns\\Comment@showByUserName");
      Route::get    ('~{habravel_user}/up',          "$ns\\User@voteUpByName");
      Route::get    ('~{habravel_user}/down',        "$ns\\User@voteDownByName");
      // User Profile.
      Route::get    ('~/edit',                       "$ns\\User@showEditProfile");
      Route::post   ('~/edit',                       "$ns\\User@editProfile");
      Route::get    ('~/password',                   "$ns\\User@showEditPassword");
      Route::post   ('~/password',                   "$ns\\User@editPassword");
      Route::get    ('~/avatar',                     "$ns\\User@showEditAvatar");
      Route::post   ('~/avatar',                     "$ns\\User@editAvatar");
      // Admin.
      Route::post   ('admin/regenhtml',              "$ns\\Admin@regenHTML");
      Route::post   ('admin/resethtml',              "$ns\\Admin@resetHTML");
      Route::get    ('admin',                        "$ns\\Admin@show");
      // Fallback.
      Route::get    ('{habravel_any}',               "$ns\\Post@showByURL");
      Route::get    ('',                             "$ns\\Posts@showBestWeek");
    });
  }

  function events() {
    require_once __DIR__.'/composers.php';

    App::error(function ($e) {
      if (method_exists($e, 'getStatusCode') and $e->getStatusCode() === 401) {
        $url = \Request::fullUrl();
        return \Redirect::to(url().'/login?back='.urlencode($url));
      }
    });

    if (App::isLocal()) {
      \Event::listen('illuminate.query', function ($sql, array $bindings) {
        foreach ($bindings as $binding) {
          $sql = preg_replace('/\?/', $binding, $sql, 1);
        }

        file_put_contents(storage_path('logs/queries.sql'), "$sql\n", FILE_APPEND | LOCK_EX);
      });
    }
  }

  function helpers() {
    require_once __DIR__.'/helpers.php';
  }
}
