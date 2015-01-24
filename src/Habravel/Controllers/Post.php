<?php namespace Habravel\Controllers;

use Habravel\Models\Post as PostModel;

class Post extends BaseController {
  static function draftAccess(PostModel $post, $action) {
    if ($post->listTime !== null) {
      return;
    } elseif (!user()) {
      App::abort(401);
    } elseif ($post->id > 0 and user()->id !== $post->author and
              !user()->hasFlag("can.draft.$action")) {
      App::abort(403, "Cannot $action author's draft.");
    }
  }

  static function writeAccess(PostModel $post) {
    static::draftAccess($post, 'write');

    $user = user() or App::abort(401);

    $isCreated = $post->id;
    $isAccessible = $isCreated ? $post->isEditable($user) : $user->hasFlag('can.post');
    $isAccessible or App::abort(403);
  }

  function __construct() {
    parent::__construct();
    $this->beforeFilter('csrf', array('only' => array('voteUp', 'voteDown')));
  }

  function show($idOrModel = 0) {
    $post = PostModel::find($idOrModel);
    $post or App::abort(404);
    static::draftAccess($post, 'read');

    if ($post->addSeen(user() ?: Request::getClientIp())) {
      ++$post->views;
      $post->save();
    }

    $post->needHTML();
    return View::make('habravel::post', compact('post'));
  }

  function showByURL($url = '') {
    $post = PostModel::where('url', '=', $url)->first();
    return $this->show($post);
  }

  function showSource($idOrModel = 0) {
    $post = PostModel::find($idOrModel);
    $post or App::abort(404);
    return $this->showSourceOn($post, Input::get('dl'));
  }

  function showSourceOn(PostModel $post, $download = false) {
    static::draftAccess($post, 'read');

    if ($download) {
      return $this->downloadSource($post);
    } else {
      return View::make('habravel::source', compact('post'));
    }
  }

  protected function downloadSource(PostModel $post) {
    $class = get_class(\Habravel\Markups\Factory::make($post->markup));
    $name = preg_replace('~[\0-\x1F"]+~u', '', $post->caption ?: $post->id).
            '.'.$class::$extension;

    // Cannot use Response::download() because it expects a local file.
    return Response::make($post->text, 200, array(
      'Content-Description'       => 'File Transfer',
      'Content-Disposition'       => 'attachment; filename="'.$name.'"',
      'Content-Length'            => strlen($post->text),
      'Content-Transfer-Encoding' => 'binary',
      'Content-Type'              => 'text/plain; charset=utf-8',
    ));
  }

  function showCreate() {
    return $this->showEdit();
  }

  function showEdit($idOrModel = 0) {
    if ($idOrModel) {
      $post = PostModel::find($idOrModel);
      $post or App::abort(404);
    } else {
      $post = new PostModel;
    }

    return $this->showEditOn($post);
  }

  function showEditByURL($url = '') {
    $post = PostModel::where('url', '=', $url)->first();
    return $this->showEditOn($post);
  }

  protected function showEditOn(PostModel $post, MessageBag $errors = null) {
    static::writeAccess($post);

    $markups = array_keys(Config::get('habravel::g.markups'));
    return View::make('habravel::edit', compact('post', 'errors', 'markups'));
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
  function edit() {
    $input = Input::get();
    $isNew = empty($input['id']);

    if ($isNew) {
      $post = new PostModel;
      $oldPost = null;
    } else {
      $post = PostModel::find($input['id']);
      $post or App::abort(404);
      $oldPost = $post->getAttributes();
    }

    static::writeAccess($post);

    $editor = \Habravel\PostEditor::make($post)
      ->applyInput($input);

    $editor->votable = $isNew;
    $errors = $editor->errors();

    if (!empty($input['preview'])) {
      count($errors) or $errors = null;

      View::composer('habravel::part.post', function ($view) use ($editor) {
        $view->tags = $editor->newTags();
      });

      return View::make('habravel::preview', compact('post', 'errors'));
    } elseif (count($errors)) {
      $input = array_intersect_key($input, PostModel::rules());
      foreach ($input as $key => $value) { $post->$key = $value; }
      return $this->showEditOn($post, $errors);
    } else {
      $editor->save();

      $event = $isNew ? 'habravel.post' : 'habravel.edit';
      \Event::fire($event, compact('post', 'oldPost'));

      return Redirect::to($post->url());
    }
  }

  function voteUp($idOrModel = 0) {
    return $this->doVote($idOrModel, true);
  }

  function voteDown($idOrModel = 0) {
    return $this->doVote($idOrModel, false);
  }

  protected function doVote($idOrModel, $up) {
    $post = PostModel::find($idOrModel);

    if ($post and user() and $post->author === user()->id) {
      return Redirect::to( \Habravel\referer(\URL::previous()) );
    } else {
      return Poll::voteOn($post, $up);
    }
  }
}