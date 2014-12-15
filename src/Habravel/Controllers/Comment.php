<?php namespace Habravel\Controllers;

use Habravel\Models\Post as PostModel;

class Comment extends BaseController {
  function showByUserName($name = '') {
   $title = trans('habravel::g.posts.userComments', compact('name'));
   return Posts::showUserListCustom($name, $title, function ($query) {
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

      $this->sendEmailNotifications($post, $parent);
      return Redirect::to($post->url());
    }
  }

  protected function sendEmailNotifications(PostModel $post, PostModel $parent) {
    $topPost = $parent->top ? $parent->top()->first() : $parent;
    $parent->needHTML();

    $data = compact('topPost', 'parent', 'post');

    foreach ($data as $key => &$value) {
      $value = $value->getAttributes();
      $value['url'] = $$key->url();
    }

    $emails = \Habravel\Models\User
      ::whereIn('id', array($topPost->author, $parent->author))
      ->lists('name', 'email');

    $emails += \Config::get('habravel::g.allNotify');
    unset($emails[user()->email]);

    if ($emails) {
      $data['parent']['author'] = $parent->author()->first();
      $data['post']['author'] = user();

      $subject = trans('habravel::g.comment.mailSubject', array($topPost->caption));

      \Mail::queue('habravel::email.reply', $data,
        function ($message) use ($emails, $subject) {
          foreach ($emails as $email => $name) {
            $message->to($email, $name)->subject($subject);
          }
        }
      );
    }
  }
}