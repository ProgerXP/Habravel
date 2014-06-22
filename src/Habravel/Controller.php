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
}