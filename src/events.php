<?php namespace Habravel;

/* Included into Habravel\Core, $this = instance. */

use App;
use View;
use Config;
use Request;
use Redirect;
use Carbon\Carbon;
use Illuminate\Support\MessageBag;

App::error(function ($e) {
  if (method_exists($e, 'getStatusCode') and $e->getStatusCode() === 401) {
    $url = Request::fullUrl();
    return Redirect::to(Core::url().'/login?back='.urlencode($url));
  }
});

if (App::isLocal()) {
  Event::listen('illuminate.query', function ($sql, array $bindings) {
    foreach ($bindings as $binding) {
      $sql = preg_replace('/\?/', $binding, $sql, 1);
    }

    file_put_contents('/1.sql', "$sql\n", FILE_APPEND | LOCK_EX);
  });
}

/***
  Article Routes
 ***/

Event::listen('habravel.out.markup', function (BaseMarkup $markup) {
  return \Response::make($markup->help(), 200, array(
    'Expires'             => gmdate('D, d M Y H:i:s', time() + 3600 * 6).' GMT',
  ));
});

Event::listen('habravel.out.source', function (Post $post, $dl) {
  if ($dl) {
    $class = get_class(Core::markup($post->markup));
    $name = preg_replace('~[\0-\x1F"]+~u', '', $post->caption).
            '.'.$class::$extension;

    return \Response::make($post->text, 200, array(
      'Content-Description'       => 'File Transfer',
      'Content-Disposition'       => 'attachment; filename="'.$name.'"',
      'Content-Length'            => strlen($post->text),
      'Content-Transfer-Encoding' => 'binary',
      'Content-Type'              => 'text/plain; charset=utf-8',
    ));
  } else {
    return View::make('habravel::source', compact('post'));
  }
});

Event::listen('habravel.out.post', function (Post $post) {
  if ($post->addSeen(Core::user() ?: Request::getClientIp())) {
    ++$post->views;
    $post->save();
  }
});

Event::listen('habravel.out.post', function (Post $post) {
  return View::make('habravel::post', compact('post'));
});

Event::listen(
  array('habravel.out.edit', 'habravel.save.post', 'habravel.out.source'),
  function (Post $post) {
    if (!($user = Core::user())) {
      App::abort(401);
    } elseif ( $post->id ? $post->isEditable($user) : $user->hasFlag('can.post') ) {
      return;   // Okay, permit.
    } else {
      App::abort(403);
    }
  },
  VALIDATE
);

Event::listen('habravel.out.edit', function (Post $post, MessageBag $errors = null) {
  $markups = Core::markups();
  return View::make('habravel::edit', compact('post', 'errors', 'markups'));
});

Event::listen('habravel.out.preview', function (Post $post, MessageBag $errors = null) {
  return View::make('habravel::preview', compact('post', 'errors'));
});

Event::listen('habravel.out.list', function (Query $query, array &$vars) {
  $vars['page'] = Core::input('page') ?: 1;
  $vars['perPage'] = Core::input('limit') ?: 10;
  $url = Request::fullUrl();
  $vars['pageURL'] = $url.(strrchr($url, '?') ? '&' : '?').'page=';

  $query->forPage(Core::input('page'), $vars['perPage']);
}, CUSTOMIZE);

Event::listen('habravel.out.list', function (Query $query, array &$vars) {
  $posts = $query->get();
  $vars['morePages'] = $vars['perPage'] <= count($posts);
  return View::make('habravel::posts', compact('posts') + $vars);
});

Event::listen('habravel.check.post', function (Post $post, array $input, MessageBag $errors) {
  $user = Core::user();

  if ($user and $user->hasFlag('post.setURL') and isset($input['url'])) {
    $post->url = $input['url'];
  }

  if (isset($input['sourceURL'])) {
    $url = $post->sourceURL = (string) $input['sourceURL'];
    if ($url !== '' and !preg_match('~^https?://~', $url)) {
      $post->sourceURL = 'http://'.ltrim($url, '\\/:');
    }
  }

  $post->author or $post->author = $user->id;
  isset($input['sourceName']) and $post->sourceName = $input['sourceName'];
  isset($input['caption']) and $post->caption = $input['caption'];
  isset($input['markup']) and $post->markup = $input['markup'];
  isset($input['text']) and $post->text = $input['text'];
  isset($post->listTime) or $post->listTime = new Carbon;
  isset($post->pubTime) or $post->pubTime = new Carbon;
  $post->format();

  if (!$post->id or ($post->caption === '' and $post->getOriginal('caption') !== '')) {
    $validator = \Validator::make(array('caption' => trim(array_get($input, 'caption'))),
                                  array('caption' => 'required'));
    $validator->fails() and $errors->merge($validator->messages());
  }
});

Event::listen('habravel.check.post', function (Post $post, array $input, MessageBag $errors) {
  $post->x_tags = array_map(function ($caption) {
    $tag = new Tag;
    $tag->caption = trim($caption);
    return $tag;
  }, (array) array_get($input, 'tags'));
});

Event::listen('habravel.check.post', function (Post $post, array $input, MessageBag $errors) {
  // Input:
  // - polls[index][caption]=...
  // - polls[index][multiple]=0/1
  // - polls[index][id] - if editing existing poll
  // - options[index][optindex][caption]=...
  // - options[index][optindex][id] - if editing existing option

  $x_polls = $x_deletedPolls = $x_deletedOptions = array();

  if ($polls = array_get($input, 'polls') and $options = array_get($input, 'options')) {
    foreach ($post->polls()->get() as $pollIndex => $poll) {
      foreach ($polls as &$pollItem) {
        if ($pollItem and array_get($pollItem, 'id') == $poll->id and
            trim($pollItem['caption']) !== '') {
          // Update existing and kept poll.
          $poll->caption = $pollItem['caption'];
          $poll->multiple = $pollItem['multiple'];
          $x_options = array();

          // Remove/update its options.
          foreach ($poll->options()->get() as $option) {
            foreach ($options[$pollIndex] as &$optItem) {
              if ($optItem and array_get($optItem, 'id') == $option->id and
                  trim($optItem['caption']) !== '') {
                // Update existing option.
                $option->caption = $optItem['caption'];
                $x_options[] = $option;
                $option->validateAndMerge($errors);
                $optItem = null;
                $option = null;
                break;
              }
            }

            $option and $x_deletedOptions[] = $option;
          }

          // Add new options.
          foreach ($options[$pollIndex] as &$optItem) {
            if ($optItem and trim($optItem['caption']) !== '') {
              $option = new PollOption;
              $option->caption = $optItem['caption'];
              $option->poll = $poll->id;
              $x_options[] = $option;
              $option->validateAndMerge($errors);
            }
          }

          // If poll has no options - delete it by keeping non-null after the cycle.
          if ($x_options) {
            $poll->x_options = $x_options;
            $x_polls[] = $poll;
            $poll->validateAndMerge($errors);
            $poll = null;
          }

          $pollItem = null;
          break;
        }
      }

      // Old poll not found, has empty caption or no options.
      $poll and $x_deletedPolls[] = $poll;
    }

    foreach ($polls as $pollIndex => &$pollItem) {
      if ($pollItem and trim($pollItem['caption']) !== '') {
        // Found a new poll to be created. Input [id] values must not be used.
        $poll = new Poll;
        $poll->caption = $pollItem['caption'];
        $poll->multiple = $pollItem['multiple'];
        $poll->validateAndMerge($errors);
        $x_options = array();

        // Add its options.
        foreach ($options[$pollIndex] as &$optItem) {
          if (trim($optItem['caption']) !== '') {
            $option = new PollOption;
            $option->caption = $optItem['caption'];
            $x_options[] = $option;
            $option->validateAndMerge($errors);
          }
        }

        $poll->x_options = $x_options and $x_polls[] = $poll;
      }
    }
  }

  $post->x_deletedPolls = $x_deletedPolls;
  $post->x_deletedOptions = $x_deletedOptions;
  $post->x_polls = $x_polls;
});

Event::listen(
  array('habravel.check.post', 'habravel.check.reply'),
  function (Post $post, array $input, MessageBag $errors) {
    $post->validateAndMerge($errors);
  },
  LAST
);

Event::listen('habravel.save.post', function (Post $post) {
  \DB::transaction(function () use ($post) {
    if (!$post->poll) {
      $poll = new Poll;
      $poll->save();
      $post->poll = $poll->id;
    }

    $post->url or $post->url = 'posts/%ID%';
    $exists = $post->exists;
    $post->save();

    if (!$exists) {
      // Anchors are prefixed with post ID which we didn't know before saving.
      $post->format();
      $post->save();
    }
  });
});

Event::listen('habravel.save.post', function (Post $post) {
  \DB::transaction(function () use ($post) {
    $captions = array();

    foreach ($post->x_tags as $tag) {
      $captions[] = $tag->caption;
      try {
        $tag->save();
      } catch (\Illuminate\Database\QueryException $e) {
        // Ignore duplicate entry error.
      }
    }

    $records = array();

    if ($captions) {
      foreach (Tag::whereIn('caption', $captions)->lists('id') as $id) {
        $records[] = array('post_id' => $post->id, 'tag_id' => $id);
      }
    }

    \DB::table('post_tag')->where('post_id', '=', $post->id)->delete();
    $records and \DB::table('post_tag')->insert($records);
  });
});

Event::listen('habravel.save.post', function (Post $post) {
  \DB::transaction(function () use ($post) {
    foreach ($post->x_deletedOptions as $option) { $option->delete(); }
    foreach ($post->x_deletedPolls as $poll) { $poll->delete(); }

    \DB::table('poll_post')->where('post_id', '=', $post->id)->delete();
    $records = array();

    foreach ($post->x_polls as $poll) {
      $poll->save();
      $records[] = array('post_id' => $post->id, 'poll_id' => $poll->id);

      foreach ($poll->x_options as $option) {
        $option->poll = $poll->id;  // new poll might have been created.
        $option->save();
      }
    }

    $records and \DB::table('poll_post')->insert($records);
  });
});

Event::listen('habravel.check.reply', function (Post $post, array $input, MessageBag $errors, Post $parent) {
  $post->author = Core::user()->id;
  $post->top = $parent->top ?: $parent->id;
  $post->parent = $parent->id;
  $post->markup = array_get($input, 'markup');
  $post->text = array_get($input, 'text');
  $post->listTime = new Carbon;
  $post->pubTime = new Carbon;
  $post->format();
});

Event::listen('habravel.save.reply', function (Post $post) {
  if (!($user = Core::user())) {
    App::abort(401);
  } elseif (!$user->hasFlag('can.reply')) {
    App::abort(403);
  }
}, VALIDATE);

Event::listen('habravel.save.reply', function (Post $post, Post $parent) {
  $post->url = strtok($parent->url, '#').'#cmt-%ID%';
  $post->save();
});

Event::listen('habravel.check.vote', function (array $votes) {
  if ($user = Core::user()) {
    if (!$user->hasFlag('can.vote')) {
      App::abort(403);
    }
  } else {
    App::abort(401);
  }
}, VALIDATE);

Event::listen('habravel.check.vote', function (array &$votes) {
  $multiple = Poll
    ::whereMultiple(1)
    ->whereIn('id', array_pluck($votes, 'poll'))
    ->lists('id', 'id');

  $norm = array();

  foreach ($votes as $vote) {
    if (!$vote['option']) {   // abstained from vote.
      unset($multiple[$vote['poll']]);
      $norm[] = $vote;
    }
  }

  // Remove multiple voted options for single-option polls.
  foreach ($votes as $vote) {
    if (!isset($multiple[$vote['poll']])) {
      foreach ($norm as $normVote) {
        if ($normVote['poll'] === $vote['poll']) {
          $vote = null;
          break;
        }
      }
    }

    $vote and $norm[] = $vote;
  }

  $votes = $norm;
}, VALIDATE);

Event::listen('habravel.save.vote', function (array $votes) {
  $records = array();
  $user = Core::user()->id;
  $ip = Request::getClientIp();

  PollVote
    ::whereIn('poll', array_pluck($votes, 'poll'))
    ->whereUser($user)
    ->delete();

  foreach ($votes as $vote) {
    $records[] = array_only($vote, array('poll', 'option')) + compact('user', 'ip');
  }

  \DB::table('poll_votes')->insert($records);
});

/***
  User Routes
 ***/

Event::listen('habravel.out.user', function (User $user) {
  return View::make('habravel::user', compact('user'));
});

Event::listen('habravel.out.login', function (array $input) {
  $vars = array(
    'backURL'         => array_get($input, 'back'),
    'badLogin'        => !empty($input['bad']),
  );

  return View::make('habravel::login', $vars);
});

Event::listen('habravel.save.login', function (User $user, array $input) {
  $user->loginTime = new Carbon;
  $user->loginIP = Request::getClientIp();
  return Redirect::to( array_get($input, 'back', $user->url()) );
});

Event::listen('habravel.out.register', function (array $input, MessageBag $errors = null) {
  return View::make('habravel::register', compact('input', 'errors'));
});

Event::listen('habravel.check.register', function (User $user, array $input, MessageBag $errors) {
  $user->name = array_get($input, 'name');
  $user->email = array_get($input, 'email');
  $haser = Config::get('habravel::g.password');
  $user->password = call_user_func($haser, array_get($input, 'password'));
  $user->regIP = Request::getClientIp();
});

Event::listen('habravel.check.register', function (User $user, array $input, MessageBag $errors) {
  $copy = new User;
  $copy->setRawAttributes(array_only($input, 'password') + $user->getAttributes());
  $copy->validateAndMerge($errors);
}, LAST);

Event::listen('habravel.save.register', function (User $user) {
  if (!$user->poll) {
    $poll = new Poll;
    $poll->save();
    $user->poll = $poll->id;
  }

  $user->save();
});

/***
  Drafts Support
 ***/

$checkDraft = function ($action, Post $post) {
  if ($post->hasFlag('draft')) {
    $user = Core::user();
    if (!$user) {
      App::abort(401);
    } elseif ($user->id !== $post->author and !$user->hasFlag("can.draft.$action")) {
      App::abort(403, "Cannot $action author's draft.");
    }
  }
};

Event::listen('habravel.out.post', function (Post $post) use ($checkDraft) {
  $checkDraft('read', $post);
}, VALIDATE);

Event::listen('habravel.out.edit', function (Post $post) use ($checkDraft) {
  $checkDraft('edit', $post);
}, VALIDATE);

Event::listen('habravel.save.post', function (Post $post) {
  foreach ($post->x_tags as $tag) {
    if ($tag->caption === 'draft') {
      $post->flags = '[draft]';
      $post->listTime = null;
      $post->pubTime = null;
      return;
    }
  }

  $post->flags = str_replace('[draft]', '', $post->flags);
}, CUSTOMIZE);

Event::listen('habravel.out.list', function (Query $query) {
  //$user = Core::user();
  //if (!$user or !$user->hasFlag('read.draft')) {
  //  $query->where('posts.flags', 'NOT LIKE', '%[draft]%');
  //}
}, CUSTOMIZE);

/***
  View Composers
 ***/

View::composer('habravel::post', function ($view) {
  $post = $view->post;

  if (!isset($post->x_children)) {
    $all = Post::whereTop($post->id)->orderBy('listTime')->get()->all();
    array_unshift($all, $post);
    $byID = $tree = array();

    foreach ($all as $post) {
      $tree[$post->parent][] = $post;
      $byID[$post->id] = $post;
    }

    foreach ($all as $post) {
      $post->x_children = (array) array_get($tree, $post->id);
    }
  }
});

View::composer('habravel::post', function ($view) {
  if (!isset($view->post->x_polls)) {
    $x_polls = $view->post->polls()->get();

    if (count($x_polls)) {
      $options = PollOption::whereIn('poll', $x_polls->lists('id'))->get()->all();

      // array('optionID' => vote_count).
      $votes = PollVote
        ::whereIn('poll', $x_polls->lists('id'))
        ->groupBy('option', 'poll')
        ->get(array(\DB::raw('COUNT(1) AS voteCount'), 'poll', 'option'));

      foreach ($x_polls as $poll) {
        $options[] = $option = new PollOption;
        $option->id = '-'.$poll->id;
        $option->poll = $poll->id;
        $option->caption = trans('habravel::g.post.abstain');

        foreach ($votes as $vote) {
          if (!$vote->option and $option->poll === $vote->poll) {
            $option->x_voteCount = $vote->voteCount;
            break;
          }
        }
      }

      foreach ($votes as $vote) {
        foreach ($options as $option) {
          if ($option->id === $vote->option) {
            $option->x_voteCount = $vote->voteCount;
            break;
          }
        }
      }

      foreach ($x_polls as $poll) {
        $sumVotes = 0;
        $x_options = array();

        foreach ($options as $option) {
          if ($option->poll === $poll->id) {
            $sumVotes += $option->x_voteCount ?: 0;
            $x_options[] = $option;
          }
        }

        $poll->x_options = $x_options;
        $poll->x_voteCount = $sumVotes;
      }
    }

    $view->post->x_polls = $x_polls;
  }
});

View::composer('habravel::posts', function ($view) {
  if (!isset($view->comments)) {
    $list = array();

    foreach ($view->posts as $post) {
      $list[] = $post->children()
        ->orderBy('listTime', 'desc')
        ->take(1)
        ->get();
    }

    $view->comments = $list;
  }
});

View::composer('habravel::edit', function ($view) {
  if (!isset($view->textPlaceholder)) {
    $list = trans('habravel::g.edit.placeholders');
    $view->textPlaceholder = $list[array_rand($list)];
  }

  isset($view->tagPool) or $view->tagPool = Tag::take(14)->lists('caption');
  isset($view->post->x_tags) or $view->post->x_tags = $view->post->tags()->get();

  if (!isset($view->post->x_polls)) {
    $view->post->x_polls = $polls = $view->post->polls()->get();
    count($polls) and $options = PollOption::whereIn('poll', $polls->lists('id'))->get();

    foreach ($polls as $poll) {
      $x_options = array();

      foreach ($options as $option) {
        $option['poll'] === $poll->id and $x_options[] = $option;
      }

      $poll->x_options = $x_options;
    }
  }
});

View::composer('habravel::user', function ($view) {
  $user = $view->user;

  if (!isset($view->posts)) {
    $query = $user->posts()->whereTop(null)->orderBy('pubTime', 'desc');
    $view->posts = $query->take(10)->get();
    $view->postCount = $query->count();
  }

  if (!isset($view->comments)) {
    $query = $user->posts()->whereNotNull('top')->orderBy('pubTime', 'desc');
    $view->comments = $query->take(20)->get();
    $view->commentCount = $query->count();
  }
});

View::composer('habravel::part.uheader', function ($view) {
  isset($view->pageUser) or $view->pageUser = Core::user();

  if (!isset($view->pageDraftCount)) {
    $view->pageDraftCount = !$view->pageUser ? 0 : $view->pageUser->posts()
      ->where('posts.flags', 'LIKE', '%[draft]%')
      ->count();
  }
});

View::composer('habravel::part.markups', function ($view) {
  isset($view->markups) or $view->markups = Core::markups();

  if (isset($view->current) and $user = Core::user()) {
    $view->current = array_get($user->info, 'defaultMarkup');
  }

  isset($view->current) or $view->current = head($view->markups);
});

View::composer('habravel::part.post', function ($view) {
  $post = $view->post;

  isset($post->x_parent) or $post->x_parent = $post->parentPost();
  isset($post->x_author) or $post->x_author = $post->author();
  isset($post->x_tags) or $post->x_tags = $post->tags()->get();

  isset($view->classes) or $view->classes = '';
  $post->sourceURL and $view->classes .= ' hvl-post-sourced';
  $post->score > 0 and $view->classes .= ' hvl-post-above';
  $post->score < 0 and $view->classes .= ' hvl-post-below';

  isset($view->canEdit) or $view->canEdit = ($user = Core::user() and $post->isEditable($user));

  if (!isset($view->readMore)) {
    $view->readMore = false;
  } elseif ($view->readMore === true) {
    $view->readMore = array_get($post->info, 'cut', trans('habravel::g.post.more'));
  }
});

View::composer('habravel::part.comment', function ($view) {
  $post = $view->post;

  isset($post->x_top) or $post->x_top = $post->top();
  isset($post->x_author) or $post->x_author = $post->author();
  isset($post->x_children) or $post->x_children = array();

  isset($view->canEdit) or $view->canEdit = ($user = Core::user() and $post->isEditable($user));
});

View::composer('habravel::page', function ($view) {
  isset($view->pageTitle) or $view->pageTitle = trans('habravel::g.pageTitle');
  isset($view->pageMetaDesc) or $view->pageMetaDesc = '';
  isset($view->pageMetaKeys) or $view->pageMetaKeys = '';
});
