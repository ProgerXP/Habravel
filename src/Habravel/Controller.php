<?php namespace Habravel;

use App;
use Redirect;
use Illuminate\Support\MessageBag;

class Controller extends BaseController {
  /***
    Article Routes
   ***/

  function getPost($id = 0) {
    if ($post = Post::find($id)) {
      return Event::until('habravel.out.post', array($post));
    } else {
      App::abort(404);
    }
  }

  function getPostByURL($url = '') {
    $post = Post::where('url', '=', $url)->first();
    return $this->getPost($post);
  }

  function getList(Query $query = null) {
    $query or $query = Post
      ::join('tags', 'posts.id', '=', 'tags.id')
      ->where('listTime', '>=', time() - 25 * 3600)
      ->orderBy('score', 'desc');

    return Event::until('habravel.out.list', array($query));
  }

  function getListByTags($tags = '') {
    $tags = array_map('urldecode', explode('/', $tags));

    $query = Post
      ::join('tags', 'posts.id', '=', 'tags.id')
      ->whereIn('tags.tag', $tags)
      ->orderBy('listTime', 'desc');

    return $this->getList($query);
  }

  function getEditPost($id = 0) {
    if ($id) {
      $post = Post::find($id);
      $post or App::abort(404);
    } else {
      $post = new Post;
    }

    return Event::until('habravel.out.edit', array($post));
  }

  function getEditPostByURL($url = '') {
    $post = Post::where('url', '=', $url)->first();
    return $this->getEditPost($post);
  }

  // POST input:
  // - id=123             - optional; updates existing post or creates new one
  // - parent=567         - optional; parent post ID (for comments)
  // - url=foo/bar        - optional; relative document URL
  // - sourceURL=http://  - optional
  // - sourceName=...     - required if sourceURL is given
  // - caption=...        - required
  // - markup=uversewiki  - required
  // - text=...           - required; post body in given markup
  // - tags[]=tag         - optional; array of tag names
  // - polls[]            - optional; array of caption, multiple (0/1)
  // - options[][]        - array of caption, one array per each item in polls
  function postEditPost() {
    static::checkCSRF();
    $input = Core::input();

    if (empty($input['id'])) {
      $post = new Post;
    } else {
      $post = Post::find($input['id']);
      $post or App::abort(404);
    }

    $errors = new MessageBag;
    Event::until('habravel.check.post', array($post, &$input, $errors));

    if (count($errors)) {
      $input = array_intersect_key($input, Post::rules());
      foreach ($input as $key => $value) { $post->$key = $value; }
      return Event::until('habravel.out.edit', array($post, $errors));
    } else {
      Event::fire('habravel.save.post', array($post));
      return Redirect::to($post->url());
    }
  }

  /***
    User Routes
   ***/

  function getCurrentUser() {
    if ($user = Core::user()) {
      return Redirect::to($user->url());
    } else {
      App::abort(401);
    }
  }

  function getUserByName($name = '') {
    $user = User::whereName($name)->first();
    return $this->getUser($user);
  }

  function getUser($id = 0) {
    if ($user = User::find($id)) {
      return Event::until('habravel.out.user', array($user));
    } else {
      App::abort(404);
    }
  }

  function getLogout() {
    Core::user(false);
    return Redirect::to(Core::url());
  }

  // GET input:
  // - back=rel/url       - optional; relative to Core::url()
  // - bad=0/1            - optional
  function getLogin() {
    if ($user = Core::user()) {
      return Redirect::to($user->url());
    } else {
      $input = Core::input();
      return Event::until('habravel.out.login', array(&$input));
    }
  }

  // POST input:
  // - email=a@b.c        - required if name/login not given
  // - name=nick          - required if name/login not given
  // - login=...          - required if email/name not given; if has '@' is
  //                        looked up as 'email', otherwise looked up by 'name'
  // - password=...
  // - remember=0/1       - optional; defaults to 0
  // - back=rel/url       - optional; relative to Core::url()
  function postLogin() {
    static::checkCSRF();
    \Session::regenerate();   // prevent session fixation.
    $input = Core::input();

    if ($back = &$input['back']) {
      // Prevent redirection to an external URL.
      if (strpos($back, $root = Core::url().'/') === 0) {
        $back = substr($back, strlen($root));
      } else {
        // Location: //google.com actually works, as well as \/google.com.
        $back = $root.ltrim($back, '\\/');
      }
    }

    $auth = array_only($input, array('email', 'password', 'remember'));
    if (!isset($auth['email'])) {
      $login = array_get($input, 'login');
      if (strrchr($login, '@')) {
        $auth['email'] = $login;
      } else {
        $auth['name'] = $login ?: array_get($input, 'name');
      }
    }

    if (empty($auth['password']) or (empty($auth['email']) and empty($auth['name']))) {
      return Event::until('habravel.out.login', array(compact('back')));
    } elseif ($user = Core::user($auth)) {
      return Event::until('habravel.save.login', array($user, &$input));
    } else {
      $input = array('bad' => 1, 'back' => $back);
      return Event::until('habravel.out.login', array($input));
    }
  }

  function getRegister() {
    Core::user(false);
    $input = Core::input();
    return Event::until('habravel.out.register', array(&$input));
  }

  // POST input:
  // - password=...       - required
  // - email=a@b.c        - required
  // - name=nick          - required
  function postRegister() {
    static::checkCSRF();
    \Session::regenerate();   // prevent session fixation.

    $user = new User;
    $input = Core::input();
    $errors = new MessageBag;
    Event::until('habravel.check.register', array($user, &$input, $errors));

    if (count($errors)) {
      return Event::until('habravel.out.register', array(&$input, $errors));
    } else {
      Event::fire('habravel.save.register', array($user));
      Core::user(array('id' => $user->id, 'password' => $input['password']));
      return Redirect::to($user->url());
    }
  }
}