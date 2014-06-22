<?php namespace Habravel;

use App;

class Controller extends BaseController {
  function getPost($id = 0) {
    if ($post = ($id instanceof Post) ? $id : Post::find($id)) {
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
      $post = ($id instanceof Post) ? $id : Post::find($id);
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

  // Input POST:
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
    $input = Core::input();

    if (empty($input['id'])) {
      $post = new Post;
    } else {
      $post = Post;:find($input['id']);
      $post or App::abort(404);
    }

    $errors = Events::until('habravel.check.post', array($post, &$input));
    if ($errors) {
      foreach ($input as $key => &$value) { $post->$key = $value; }
      return Event::until('habravel.out.edit', array($post, $errors));
    } else {
      Events::fire('habravel.save.post', array($post));
      return Redirect::to($post->url());
    }
  }
}