<?php namespace Habravel\Controllers;

use Habravel\Models\Post as PostModel;

class Comment extends BaseController {
  function showByUserName($name = '') {
    return Posts::showUserListCustom($name, $name, function ($query) {
      $query->whereNotNull('top');
    });
  }

  // POST input:
  // - preview=0/1        - optional
  // - parent=123         - required
  // - markup=uversewiki  - required
  // - text=...           - required
  function reply() {
    if (!user()) {
      App::abort(401);
    } elseif (!user()->hasFlag('can.reply')) {
      App::abort(403);
    }

    $input = Input::get();
    $parent = PostModel::find($input['parent']);
    $parent or App::abort(404, 'Parent post not found.');
    $post = new PostModel;

    $post->author = user()->id;
    $post->top = $parent->top ?: $parent->id;
    $post->parent = $parent->id;
    $post->markup = array_get($input, 'markup');
    $post->text = array_get($input, 'text');
    $post->listTime = new \Carbon\Carbon;
    $post->pubTime = new \Carbon\Carbon;
    $post->format();

    $errors = new MessageBag;
    $post->validateAndMerge($errors);

    if (count($errors)) {
      return Response::json($errors->all(), 400);
    } elseif (!empty($input['preview'])) {
      return $post->html;
    } else {
      $post->url = strtok($parent->url, '#').'#cmt-%ID%';
      $post->save();

      return Redirect::to($post->url());
    }
  }
}