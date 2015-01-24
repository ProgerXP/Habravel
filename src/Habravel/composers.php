<?php namespace Habravel;

use View;

View::composer('habravel::post', function ($view) {
  $post = $view->post;

  $view->polls = $post->polls()->get();

  // Add root comments.
  $all = Models\Post::whereTop($post->id)->orderBy('listTime')->get()->all();
  $view->commentCount = count($all);
  array_unshift($all, $post);
  $byID = $tree = array();

  foreach ($all as $comment) {
    $tree[$comment->parent][] = $comment;
    $byID[$comment->id] = $comment;
  }

  foreach ($all as $comment) {
    $comment->x_children = (array) array_get($tree, $comment->id);
  }

  // Add sidebars.
  $list = isset($view->pageSidebar) ? $view->pageSidebar  : array();

  $list['post-info'] = View::make('habravel::sidebar.post')
    ->with('post', $post)
    ->with('author', $post->author()->first())
    ->render();

  $view->pageSidebar = $list;
});

View::composer('habravel::post.poll', function ($view) {
  if (isset($view->options)) { return; }

  $poll = $view->poll;
  $options = $view->options = Models\PollOption::wherePoll($poll->id)->get()->all();

  // array('optionID' => vote_count).
  $votes = Models\PollVote
    ::wherePoll($poll->id)
    ->groupBy('option', 'poll')
    ->get(array(\DB::raw('COUNT(1) AS voteCount'), 'poll', 'option'));

  $options[] = $abstainOption = new Models\PollOption;
  $abstainOption->id = '-'.$poll->id;
  $abstainOption->poll = $poll->id;
  $abstainOption->caption = trans('habravel::g.post.abstain');

  // Add vote count for "abstain from voting".
  foreach ($votes as $vote) {
    if (!$vote->option) {
      $abstainOption->x_voteCount = $vote->voteCount;
      break;
    }
  }

  // Add vote counts for other (real) options.
  foreach ($votes as $vote) {
    foreach ($options as $option) {
      if ($option->id === $vote->option) {
        $option->x_voteCount = $vote->voteCount;
        break;
      }
    }
  }

  $view->voteCount = 0;

  foreach ($options as $option) {
    $view->voteCount += $option->x_voteCount ?: 0;
  }
});

View::composer('habravel::posts', function ($view) {
  // Add comments.
  $parents = array();

  foreach ($view->posts as $index => $post) {
    $parents[$index] = $post->id;
  }

  $rows = !$parents ? array() : Models\Post
    ::whereIn('parent', $parents)
    ->orderBy('listTime', 'desc')
    ->get();

  $comments = array();

  foreach ($rows as $comment) {
    $ref = &$comments[ array_search($comment->parent, $parents) ];
    $ref or $ref = array($comment);
  }

  $view->comments = $comments;

  // Add sidebars.
  $list = isset($view->pageSidebar) ? $view->pageSidebar  : array();

  $list['tag-cloud'] = \Cache::remember('hvl.tagcloud', 60, function () {
    return View::make('habravel::sidebar.tagCloud')
      ->with('tags', Models\Tag
        ::where('flags', 'LIKE', '%[pool.cloud]%')
        ->orderBy('caption')
        ->get())
      ->render();
  });

  $list['top-users'] = \Cache::remember('hvl.topusers', 30, function () {
    return View::make('habravel::sidebar.topUsers')
      ->with('users', Models\User
        ::where('score', '>', 3)
        ->orderBy('score', 'desc')
        ->take(10)
        ->get())
      ->render();
  });

  $list['top-posts'] = \Cache::remember('hvl.topposts', 30, function () {
    return View::make('habravel::sidebar.topPosts')
      ->with('posts', Models\Post
        ::where('score', '>', 3)
        ->orderBy('score', 'desc')
        ->take(10)
        ->get())
      ->render();
  });

  $view->pageSidebar = $list;
});

View::composer('habravel::edit', function ($view) {
  if (!isset($view->textPlaceholder)) {
    $list = trans('habravel::g.edit.placeholders');
    $view->textPlaceholder = $list[array_rand($list)];
  }

  isset($view->tagPool) or $view->tagPool = Models\Tag::where('flags', 'LIKE', '%[pool.edit]%')->get();
  isset($view->tags) or $view->tags = $view->post->tags()->get();

  if (!isset($view->polls)) {
    $view->polls = $polls = $view->post->polls()->get();
  }
});

View::composer('habravel::edit.poll', function ($view) {
  isset($view->options) or $view->options = Models\PollOption::wherePoll($view->poll->id)->get();
});

View::composer('habravel::register', function ($view) {
  $view->captcha = captcha();
});

View::composer('habravel::user', function ($view) {
  $user = $view->user;

  isset($view->canEdit) or $view->canEdit = (user() and $user->id === user()->id);

  $query = $user->publishedArticles()->orderBy('listTime', 'desc');
  $view->posts = $query->take(10)->get();
  foreach ($view->posts as $post) { $post->needHTML(); }
  $view->postCount = $query->count();

  $query = $user->comments()->orderBy('listTime', 'desc');
  $view->comments = $query->take(20)->get();
  foreach ($view->comments as $post) { $post->needHTML(); }
  $view->commentCount = $query->count();

  $view->badges = array();
  foreach ($user->flags() as $flag) {
    $parts = explode('badge.', $flag, 2);
    $parts[0] === '' and $view->badges[] = $parts[1];
  }
});

View::composer('habravel::part.userHeader', function ($view) {
  $view->pageUser = user();
  $view->pageDraftCount = !$view->pageUser ? 0 : $view->pageUser->drafts()->count();
});

View::composer('habravel::part.markups', function ($view) {
  isset($view->markups) or $view->markups = array_keys(\Config::get('habravel::g.markups'));
  isset($view->current) or $view->current = head($view->markups);
});

View::composer('habravel::part.post', function ($view) {
  $post = $view->post;

  $view->parentPost = $post->parentPost()->first();
  $view->author = $post->author()->first();
  $view->tags = $post->tags()->get();

  isset($view->classes) or $view->classes = '';
  $post->sourceURL and $view->classes .= ' hvl-post-sourced';
  $post->score > 0 and $view->classes .= ' hvl-post-above';
  $post->score < 0 and $view->classes .= ' hvl-post-below';

  isset($view->canEdit) or $view->canEdit = ($user = user() and $post->isEditable($user));

  if (!isset($view->readMore)) {
    $view->readMore = false;
  } elseif ($view->readMore !== true) {
    // Keep as is, it's explicitly set.
  } elseif (strlen($post->introHTML) === strlen($post->html)) {
    // Post too short.
    $view->readMore = false;
  } else {
    $view->readMore = array_get($post->data(), 'cut', trans('habravel::g.post.more'));
  }

  $view->html = $view->readMore === false ? $post->html : $post->introHTML;

  if ($minH = $view->downshift) {
    $view->html = preg_replace_callback('~(</?h)(\d)\b~ui', function ($match) use ($minH) {
      return $match[1].min($match[2] + $minH - 1, 6);
    }, $view->html);
  }
});

View::composer('habravel::part.comment', function ($view) {
  $post = $view->post;

  $view->topPost = $post->top()->first();
  $view->author = $post->author()->first();
  isset($post->x_children) or $post->x_children = array();

  $view->canEditValue = isset($view->canEdit)
    ? $view->canEdit : ($user = user() and $post->isEditable($user));
});

View::composer('habravel::page', function ($view) {
  isset($view->pageTitle) or $view->pageTitle = trans('habravel::g.pageTitle');
  isset($view->pageMetaDesc) or $view->pageMetaDesc = '';
  isset($view->pageMetaKeys) or $view->pageMetaKeys = '';
  $view->pageHeader .= View::make('habravel::part.userHeader');
  isset($view->pageSidebar) or $view->pageSidebar = array();
});
