<?php namespace Habravel;

use App;
use View;
use Route;
use Config;
use Redirect;
use Carbon\Carbon;
use Illuminate\Support\MessageBag;

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
      throw new Error("Unknown markup [$name].");
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
      // User.
      Route::get    ('logout',                  "$ctl@getLogout");
      Route::get    ('login',                   "$ctl@getLogin");
      Route::post   ('login',                   "$ctl@postLogin");
      Route::get    ('register',                "$ctl@getRegister");
      Route::post   ('register',                "$ctl@postRegister");
      Route::get    ('~',                       "$ctl@getCurrentUser");
      Route::get    ('~{habravel_user}',        "$ctl@getUserByName");
      Route::get    ('users/{habravel_id}',     "$ctl@getUser");
      // Fallback.
      Route::get    ('{habravel_any}',          "$ctl@getPostByURL");
      Route::get    ('',                        "$ctl@getList");
    });
  }

  function events() {
    App::error(function ($e) {
      if (method_exists($e, 'getStatusCode') and $e->getStatusCode() === 401) {
        $url = \Request::fullUrl();
        return Redirect::to(Core::url().'/login?back='.urlencode($url));
      }
    });

    /***
      Article Routes
     ***/

    Event::listen('habravel.out.post', function (Post $post) {
      return View::make('habravel::post', compact('post'));
    });

    Event::listen(array('habravel.out.edit', 'habravel.save.post'), function (Post $post) {
      if ($user = Core::user()) {
        if ($user->hasFlag('can.'.($post->id ? 'edit' : 'post')) or
            ($post->id and $post->author === $user->id and $user->hasFlag('can.editSelf'))) {
          return;
        }
        App::abort(403);
      } else {
        App::abort(401);
      }
    }, VALIDATE);

    Event::listen('habravel.out.edit', function (Post $post, MessageBag $errors = null) {
      return View::make('habravel::edit', compact('post', 'errors'));
    });

    Event::listen('habravel.out.list', function (Query $query) {
      $limit = 10;
      $query->take($limit)->skip(Core::input('page') * $limit);
    }, CUSTOMIZE);

    Event::listen('habravel.out.list', function (Query $query) {
      $posts = $query->get();
      return View::make('habravel::posts', compact('posts'));
    });

    Event::listen('habravel.check.post', function (Post $post, array $input, MessageBag $errors) {
      $user = Core::user();

      if ($user and $user->hasFlag('post.setURL') and isset($input['url'])) {
        $post->url = $input['url'];
      }

      $post->author = $user->id;
      $url = $post->sourceURL = array_get($input, 'sourceURL');
      preg_match('~^https?://~', $url) or $url = 'http://'.ltrim($url, '\\/:');
      $post->sourceName = array_get($input, 'sourceName');
      $post->caption = array_get($input, 'caption');
      $post->markup = array_get($input, 'markup');
      $post->text = array_get($input, 'text');
      $post->listTime = $post->listTime ?: new Carbon;
      $post->publishTime = $post->publishTime ?: new Carbon;
    });

    Event::listen('habravel.check.post', function (Post $post, array $input, MessageBag $errors) {
      $validator = \Validator::make($post->getAttributes(), Post::rules($post));
      $validator->fails() and $errors->merge($validator->messages());
    }, LAST);

    Event::listen('habravel.save.post', function (Post $post) {
      $post->url or $post->url = 'posts/#';
      $post->save();
    });

    /***
      User Routes
     ***/

    Event::listen('habravel.out.login', function (array $input) {
      $vars = array(
        'backURL'         => array_get($input, 'back'),
        'badLogin'        => !empty($input['bad']),
      );

      return View::make('habravel::login', $vars);
    });

    Event::listen('habravel.save.login', function (User $user, array $input) {
      $user->loginTime = new Carbon;
      $user->loginIP = \Request::getClientIp();
      return Redirect::to( array_get($input, 'back', $user->url()) );
    });

    Event::listen('habravel.out.register', function (array $input, MessageBag $errors = null) {
      return View::make('habravel::register', compact('input', 'errors'));
    });

    Event::listen('habravel.check.register', function (User $user, array $input, MessageBag $errors) {
      $user->name = array_get($input, 'name');
      $user->email = array_get($input, 'email');
      $haser = Config::get('habravel::g.password');
      $user->password = call_user_func($haser, array_get($input, 'password'));
    });

    Event::listen('habravel.check.register', function (User $user, array $input, MessageBag $errors) {
      $attrs = array_only($input, 'password') + $user->getAttributes();
      $validator = \Validator::make($attrs, User::rules($user));
      $validator->fails() and $errors->merge($validator->messages());
    }, LAST);

    Event::listen('habravel.save.register', function (User $user) {
      $user->regIP = \Request::getClientIp();
      $user->save();
    });

    /***
      Drafts Support
     *** /

    $checkDraft = function ($action, Post $post) {
      if ($post->hasFlag('draft')) {
        $user = Core::user();
        if (!$user) {
          App::abort(401);
        } elseif ($user->id !== $post->author and !$user->hasFlag("draft.$action")) {
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

      + put drafts link to uheader
      + save to drafts button for compose view
    + post links support
    + comments support
    + polls support
    */
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