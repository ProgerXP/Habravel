<?php namespace Habravel;

use App;
use Event;

Event::listen('habravel.save.post', function (Post $post) {
  if (!($user = user())) {
    App::abort(401);
  } elseif ( $post->id ? $post->isEditable($user) : $user->hasFlag('can.post') ) {
    return;   // Okay, permit.
  } else {
    App::abort(403);
  }
}, -10);

Event::listen('habravel.check.post', function (Post $post, array $input, MessageBag $errors) {
  $user = user();

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
  isset($post->listTime) or $post->listTime = new \Carbon\Carbon;
  isset($post->pubTime) or $post->pubTime = new \Carbon\Carbon;
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

Event::listen('habravel.check.post', function (Post $post, array $input, MessageBag $errors) {
  $post->validateAndMerge($errors);
}, -10);

Event::listen('habravel.save.post', function (Post $post) {
  \DB::transaction(function () use ($post) {
    if (!$post->poll) {
      $poll = new Poll;
      $poll->caption = '"'.$post->caption.'"';
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
      foreach (Models\Tag::whereIn('caption', $captions)->lists('id') as $id) {
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
}, 5);
