<?php namespace Habravel;

use View;

View::composer('habravel::post', function ($view) {
  $post = $view->post;

  if (!isset($post->x_children)) {
    $all = Models\Post::whereTop($post->id)->orderBy('listTime')->get()->all();
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
      $options = Models\PollOption::whereIn('poll', $x_polls->lists('id'))->get()->all();

      // array('optionID' => vote_count).
      $votes = Models\PollVote
        ::whereIn('poll', $x_polls->lists('id'))
        ->groupBy('option', 'poll')
        ->get(array(\DB::raw('COUNT(1) AS voteCount'), 'poll', 'option'));

      foreach ($x_polls as $poll) {
        $options[] = $option = new Models\PollOption;
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

  isset($view->tagPool) or $view->tagPool = Models\Tag::where('flags', 'LIKE', '%[pool.edit]%')->get();
  isset($view->post->x_tags) or $view->post->x_tags = $view->post->tags()->get();

  if (!isset($view->post->x_polls)) {
    $view->post->x_polls = $polls = $view->post->polls()->get();
    count($polls) and $options = Models\PollOption::whereIn('poll', $polls->lists('id'))->get();

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
    $query = $user->publishedArticles()->orderBy('listTime', 'desc');
    $view->posts = $query->take(10)->get();
    foreach ($view->posts as $post) { $post->needHTML(); }
    $view->postCount = $query->count();
  }

  if (!isset($view->comments)) {
    $query = $user->comments()->orderBy('listTime', 'desc');
    $view->comments = $query->take(20)->get();
    foreach ($view->comments as $post) { $post->needHTML(); }
    $view->commentCount = $query->count();
  }
});

View::composer('habravel::part.uheader', function ($view) {
  isset($view->pageUser) or $view->pageUser = user();

  if (!isset($view->pageDraftCount)) {
    $view->pageDraftCount = !$view->pageUser ? 0 : $view->pageUser->drafts()->count();
  }
});

View::composer('habravel::part.markups', function ($view) {
  isset($view->markups) or $view->markups = array_keys(\Config::get('habravel::g.markups'));

  if (isset($view->current) and $user = user()) {
    $view->current = array_get($user->info, 'defaultMarkup');
  }

  isset($view->current) or $view->current = head($view->markups);
});

View::composer('habravel::part.post', function ($view) {
  $post = $view->post;

  isset($post->x_parent) or $post->x_parent = $post->parentPost()->first();
  isset($post->x_author) or $post->x_author = $post->author()->first();
  isset($post->x_tags) or $post->x_tags = $post->tags()->get();

  isset($view->classes) or $view->classes = '';
  $post->sourceURL and $view->classes .= ' hvl-post-sourced';
  $post->score > 0 and $view->classes .= ' hvl-post-above';
  $post->score < 0 and $view->classes .= ' hvl-post-below';

  isset($view->canEdit) or $view->canEdit = ($user = user() and $post->isEditable($user));

  if (!isset($view->readMore)) {
    $view->readMore = false;
  } elseif ($view->readMore === true) {
    $view->readMore = array_get($post->info, 'cut', trans('habravel::g.post.more'));
  }

  if (!isset($view->html)) {
    $view->html = $view->readMore === false ? $post->html : $post->introHTML;
  }

  if ($minH = $view->downshift) {
    $view->html = preg_replace_callback('~(</?h)(\d)\b~ui', function ($match) use ($minH) {
      return $match[1].min($match[2] + $minH - 1, 6);
    }, $view->html);
  }
});

View::composer('habravel::part.comment', function ($view) {
  $post = $view->post;

  isset($post->x_top) or $post->x_top = $post->top()->first();
  isset($post->x_author) or $post->x_author = $post->author()->first();
  isset($post->x_children) or $post->x_children = array();

  isset($view->canEdit) or $view->canEdit = ($user = user() and $post->isEditable($user));
});

View::composer('habravel::page', function ($view) {
  isset($view->pageTitle) or $view->pageTitle = trans('habravel::g.pageTitle');
  isset($view->pageMetaDesc) or $view->pageMetaDesc = '';
  isset($view->pageMetaKeys) or $view->pageMetaKeys = '';
  $view->pageHeader .= View::make('habravel::part.uheader');
  isset($view->pageSidebar) or $view->pageSidebar = array();
});
