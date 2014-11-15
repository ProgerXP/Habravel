<?php namespace Habravel\Controllers;

use Habravel\Models\Post as PostModel;
use Habravel\Models\User as UserModel;

class Posts extends BaseController {
  function showByUserName($name = '') {
    return static::showUserListCustom($name, function ($query) {
      $query->whereTop(null);
    });
  }

  static function showUserListCustom($name, $customizer) {
    $user = UserModel::whereName($name)->first();
    $user or App::abort(404);
    $query = PostModel::whereAuthor($user->id)->orderBy('listTime', 'desc');
    call_user_func($customizer, $query, $user);
    return with(new static)->showOn($query);
  }

  function showAll() {
    return $this->showOn();
  }

  // GET input:
  // - sort=score         - optional
  // - desc=0/1           - optional; reverse sorting; defaults to 0
  // - tags[]             - optional; array of tag captions
  protected function showOn($query = null, $title = '') {
    $query or $query = PostModel::whereNull('top')->orderBy('listTime', 'desc');

    if ($sort = Input::get('sort') and in_array($sort, PostModel::$sortable)) {
      $query->orders = array();
      $query->orderBy($sort, Input::get('desc') ? 'desc' : 'asc');
    }

    if ($tags = (array) Input::get('tags')) {
      $query
        ->join('tags', 'posts.id', '=', 'tags.id')
        ->whereIn('tags.caption', $tags);
    }

    $title or $title = trans('habravel::g.posts.title');
    $page = Input::get('page') ?: 1;
    $perPage = min(40, Input::get('limit') ?: 10);
    $pageURL = ($url = Request::fullUrl()).(strrchr($url, '?') ? '&' : '?').'page=';

    $query->forPage(Input::get('page'), $perPage);
    $posts = $query->get();

    foreach ($posts as $post) { $post->needHTML(); }

    $morePages = $perPage <= count($posts);

    $vars = compact('title', 'page', 'perPage', 'pageURL', 'morePages', 'posts');
    return View::make('habravel::posts', $vars);
  }

  function showBest($interval = 0, $title = '') {
    $query = PostModel::whereNull('top')->orderBy('score', 'desc');

    if ($interval > 0) {
      $query->where('listTime', '>=', \Carbon\Carbon::now()
        ->subDays($interval)
        ->subHours(2));
    }

    return $this->showOn($query, $title ?: trans('habravel::g.posts.bestEver'));
  }

  function showBestDay() {
    return $this->showBest(1, trans('habravel::g.posts.bestDay'));
  }

  function showBestWeek() {
    return $this->showBest(7, trans('habravel::g.posts.bestWeek'));
  }

  function showBestAllTime() {
    return $this->showBest();
  }

  function showByTags($tags = '') {
    $sorted = $tags = array_filter(array_map('urldecode', explode('/', $tags)));
    sort($sorted, SORT_LOCALE_STRING);

    if ($sorted !== $tags) {
      $tags = array_map('urlencode', $sorted);
      return Redirect::to(\Habravel\url().'/tags/'.join('/', $tags), 301);
    } elseif (!$tags) {
      return Redirect::to(\Habravel\url());
    } else {
      $query = PostModel
        ::join('post_tag', 'post_tag.post_id', '=', 'posts.id')
        ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
        ->whereIn('tags.caption', $tags)
        ->groupBy('post_tag.post_id')
        ->orderBy('posts.listTime', 'desc')
        ->havingRaw('COUNT(post_tag.post_id) = ?', array(count($tags)))
        ->select('posts.*');

      return $this->showOn($query);
    }
  }
}