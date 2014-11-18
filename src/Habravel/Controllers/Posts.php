<?php namespace Habravel\Controllers;

use Habravel\Models\Post as PostModel;
use Habravel\Models\User as UserModel;

class Posts extends BaseController {
  function showByUserName($name = '') {
    $title = trans('habravel::g.posts.userPosts', compact('name'));
    return static::showUserListCustom($name, $title, function ($query) {
      $query->whereTop(null);
    });
  }

  static function showUserListCustom($name, $title, $customizer) {
    $user = UserModel::whereName($name)->first();
    $user or App::abort(404);
    $query = $user->posts()->whereNotNull('listTime')->orderBy('listTime', 'desc');
    call_user_func($customizer, $query, $user);
    return with(new static)->showOn($query, $title);
  }

  function showDrafts() {
    $user = user() or App::abort(401);
    $query = $user->drafts()->orderBy('listTime', 'desc');
    return $this->showOn($query, trans('habravel::g.posts.drafts'));
  }

  function showAll() {
    $query = PostModel::whereNull('top')->whereNotNull('listTime')->orderBy('listTime', 'desc');
    return $this->showOn($query);
  }

  // GET input:
  // - sort=score         - optional
  // - desc=0/1           - optional; reverse sorting; defaults to 0
  // - tags[]             - optional; array of tag captions
  protected function showOn($query, $title = '') {
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

  function showBest($interval = 0, $title) {
    $query = PostModel::whereNull('top')->whereNotNull('listTime')->orderBy('score', 'desc');

    if ($interval > 0) {
      $time = \Carbon\Carbon::now()
        ->subDays($interval)
        ->subHours(2);    // give it a bit relaxed time frame.

      $query->where('listTime', '>=', $time);
    }

    return $this->showOn($query, $title);
  }

  function showBestDay() {
    return $this->showBest(1, trans('habravel::g.posts.bestDay'));
  }

  function showBestWeek() {
    return $this->showBest(7, trans('habravel::g.posts.bestWeek'));
  }

  function showBestAllTime() {
    return $this->showBest(0, trans('habravel::g.posts.bestEver'));
  }

  function showByTags($inputTags = '') {
    $sorted = $tags = array();

    foreach (explode('/', $inputTags) as $inputTag) {
      if ($inputTag = trim(urldecode($inputTag))) {
        $sorted[] = $tags[] = $inputTag;
      }
    }

    sort($sorted, SORT_LOCALE_STRING);

    if ($sorted !== $tags) {
      $tags = array_map('urlencode', $sorted);
      return Redirect::to(\Habravel\url().'/tags/'.join('/', $tags), 301);
    } elseif (!$tags) {
      return Redirect::to(\Habravel\url());
    } else {
      // Optimization - guests will never see any drafts so remove extra
      // constraints on the query.
      if (user()) {
        $query = user()->allVisiblePosts();
      } else {
        $query = PostModel::whereNotNull('posts.listTime');
      }

      $query
        ->join('post_tag', 'post_tag.post_id', '=', 'posts.id')
        ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
        ->whereIn('tags.caption', $tags)
        ->groupBy('post_tag.post_id')
        ->orderBy('posts.listTime', 'desc')
        ->havingRaw('COUNT(post_tag.post_id) = ?', array(count($tags)))
        ->select('posts.*');

      $title = join(' | ', $tags);
      $title = mb_strtoupper(mb_substr($title, 0, 1)).mb_substr($title, 1);

      return $this->showOn($query, $title);
    }
  }

  // Special version for "system" (fixed) tags like documentation that only
  // displays published articles (no user drafts even if he's logged in).
  // There must be only one tag defined with given $tagType.
  function showByTag($tagType, $title) {
    $query = PostModel
      ::whereNotNull('posts.listTime')
      ->join('post_tag', 'post_tag.post_id', '=', 'posts.id')
      ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
      ->where('tags.type', $tagType)
      ->groupBy('post_tag.post_id')
      ->orderBy('posts.listTime', 'desc')
      ->select('posts.*');

    return $this->showOn($query, $title);
  }
}