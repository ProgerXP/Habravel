<?php namespace Habravel;

use App;
use Redirect;
use Illuminate\Support\MessageBag;

class Controller extends BaseController {
  /***
    Article Routes
   ***/

  function getUserByNamePosts($name = '', $comments = true) {
    $user = User::whereName($name)->first();
    $user or App::abort(404);
    $query = Post::whereAuthor($user->id)->orderBy('listTime', 'desc');
    $comments ? $query->whereNotNull('top') : $query->whereTop(null);
    return $this->getList($query);
  }

  function getUserByNameComments($name = '') {
    return $this->getUserByNamePosts($name, true);
  }

  // GET input:
  // - sort=score         - optional
  // - desc=0/1           - optional; reverse sorting; defaults to 0
  // - tags[]             - optional; array of tag captions
  function getList(Query $query = null, $title = '') {
    $query or $query = Post::whereNull('top')->orderBy('listTime', 'desc');

    if ($sort = Core::input('sort') and in_array($sort, Post::$sortable)) {
      $query->orders = array();
      $query->orderBy($sort, Core::input('desc') ? 'desc' : 'asc');
    }

    if ($tags = (array) Core::input('tags')) {
      $query
        ->join('tags', 'posts.id', '=', 'tags.id')
        ->whereIn('tags.caption', $tags);
    }

    $title or $title = trans('habravel::g.posts.title');
    $vars = compact('title');
    return Event::until('habravel.out.list', array($query, &$vars));
  }

  function getBestList($interval = 0, $title = '') {
    $query = Post::whereNull('top')->orderBy('score', 'desc');

    if ($interval > 0) {
      $query->where('listTime', '>=', \Carbon\Carbon::now()
        ->subDays($interval)
        ->subHours(2));
    }

    return $this->getList($query, $title ?: trans('habravel::g.posts.bestEver'));
  }

  function getBestListDay() {
    return $this->getBestList(1, trans('habravel::g.posts.bestDay'));
  }

  function getBestListWeek() {
    return $this->getBestList(7, trans('habravel::g.posts.bestWeek'));
  }

  function getListByTags($tags = '') {
    $sorted = $tags = array_filter(array_map('urldecode', explode('/', $tags)));
    sort($sorted, SORT_LOCALE_STRING);

    if ($sorted !== $tags) {
      $tags = array_map('urlencode', $sorted);
      return Redirect::to(Core::url().'/tags/'.join('/', $tags), 301);
    } elseif (!$tags) {
      return Redirect::to(Core::url());
    } else {
      $query = Post
        ::join('post_tag', 'post_tag.post_id', '=', 'posts.id')
        ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
        ->whereIn('tags.caption', $tags)
        ->groupBy('post_tag.post_id')
        ->orderBy('posts.listTime', 'desc')
        ->havingRaw('COUNT(post_tag.post_id) = ?', array(count($tags)))
        ->select('posts.*');

      return $this->getList($query);
    }
  }

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

  function getMarkupHelp($markup = '') {
    try {
      $markup = Core::markup($markup);
    } catch (\Exception $e) { }

    if (isset($markup)) {
      return Event::until('habravel.out.markup', array($markup));
    } else {
      App::abort(404);
    }
  }

  function getPostSource($id = 0) {
    if ($post = Post::find($id)) {
      return Event::until('habravel.out.source', array($post, Core::input('dl')));
    } else {
      App::abort(404);
    }
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
  // - preview=0/1        - optional; if given renders the post instead of saving
  // - id=123             - optional; updates existing post or creates new one
  // - parent=567         - optional; parent post ID (for comments)
  // - url=foo/bar        - optional; relative document URL
  // - sourceURL=http://  - optional
  // - sourceName=...     - required if sourceURL is given
  // - caption=...        - required
  // - markup=uversewiki  - required
  // - text=...           - required; post body in given markup
  // - tags[]=tag         - optional; array of tag captions
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

    if (!empty($input['preview'])) {
      count($errors) or $errors = null;
      return Event::until('habravel.out.preview', array($post, $errors));
    } elseif (count($errors)) {
      $input = array_intersect_key($input, Post::rules());
      foreach ($input as $key => $value) { $post->$key = $value; }
      return Event::until('habravel.out.edit', array($post, $errors));
    } else {
      Event::fire('habravel.save.post', array($post));
      return Redirect::to($post->url());
    }
  }

  // POST input:
  // - preview=0/1        - optional
  // - parent=123         - required
  // - markup=uversewiki  - required
  // - text=...           - required
  function postReply() {
    static::checkCSRF();
    $input = Core::input();
    $parent = Post::find($input['parent']);
    $parent or App::abort(404, 'Parent post not found.');
    $post = new Post;

    $errors = new MessageBag;
    Event::until('habravel.check.reply', array($post, &$input, $errors, $parent));

    if (count($errors)) {
      return \Response::json($errors->all(), 400);
    } elseif (!empty($input['preview'])) {
      return $post->html;
    } else {
      Event::fire('habravel.save.reply', array($post, $parent));
      return Redirect::to($post->url());
    }
  }

  function postVoteUpByPost($id = 0) {
    return $this->outModelVote(Post::find($id), true);
  }

  function postVoteDownByPost($id = 0) {
    return $this->outModelVote(Post::find($id), false);
  }

  function postVoteUpByUserName($name = '') {
    return $this->outModelVote(User::whereName($name)->first(), true);
  }

  function postVoteDownByUserName($name = '') {
    return $this->outModelVote(User::whereName($name)->first(), false);
  }

  protected function outModelVote($model, $up) {
    $model or App::abort(404);
    $model->poll or App::abort(403, 'This '.class_basename($model).' cannot be voted for.');
    $res = $this->outVote(array(array('poll' => $model->poll, 'option' => $up + 1)));

    $votes = PollVote
      ::wherePoll($model->poll)
      ->whereIn('option', array(1, 2))
      ->groupBy('option')
      ->lists(\DB::raw('COUNT(1)'), 'option');
    $model->score = array_get($votes, 2, 0) - array_get($votes, 1, 0);
    $model->save();

    return $res;
  }

  // POST input:
  // - votes[]=optionID   - adds user's vote for given option.
  // - votes[]=-pollID    - abstain.
  function postVote() {
    $votes = array();

    if ($input = Core::input('votes')) {
      $abstain = [];
      foreach ($input as $id) {
        $id[0] === '-' and $abstain[] = (int) substr($id, 1);
      }

      $polls = Poll
        ::join('poll_options', 'poll_options.poll', '=', 'polls.id')
        ->whereNull('poll_options.deleted_at')
        ->whereNull('polls.deleted_at')
        ->whereIn('poll_options.id', $input)
        ->get(array('polls.*', 'poll_options.id AS optionID'));

      foreach ($polls as $poll) {
        $votes[] = array(
          'poll'            => $poll->id,
          'option'          => $poll->optionID,
        );
      }

      if ($abstain) {
        $abstain = Poll::whereNull('deleted_at')->whereIn('id', $abstain)->lists('id');

        foreach ($abstain as $poll) {
          $votes[] = array('poll' => $poll, 'option' => null);
        }
      }
    }

    return $this->outVote($votes);
  }

  // $votes = array(array('option' => id/null, 'poll' => id), ...).
  // Warning: IDs are not verified and thus must exist and belong to the polls.
  protected function outVote(array $votes) {
    static::checkCSRF();

    if ($votes and $resp = Event::until('habravel.check.vote', array(&$votes))) {
      return $res;
    } else {
      $votes and Event::fire('habravel.save.vote', array(&$votes));
      $url = Core::normReferer(\URL::previous());
      strrchr($url, '#') or $url .= '#polls';
      return Redirect::to($url);
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
    static::checkCSRF();
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
    $back = $input['back'] = Core::normReferer(array_get($input, 'back'));

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