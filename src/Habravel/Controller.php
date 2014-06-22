<?php namespace Habravel;

class Controller extends BaseController {
  function getPost($id = 0) {
    if ($post = ($id instanceof Post) ? $id : Post::find($id)) {
      return Event::until('habravel.out.get.post', array($post));
    } else {
      \App::abort(404);
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

    return Event::until('habravel.out.get.list', array($query));
  }

  function getListByTags($tags = '') {
    $tags = array_map('urldecode', explode('/', $tags));

    $query = Post
      ::join('tags', 'posts.id', '=', 'tags.id')
      ->whereIn('tags.tag', $tags)
      ->orderBy('listTime', 'desc');

    return $this->getList($query);
  }
}