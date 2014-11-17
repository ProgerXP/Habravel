<?php namespace Habravel;

use Illuminate\Support\MessageBag;
use Habravel\Models\Post as PostModel;

class PostEditor {
  protected $user;      // Models\User - the user that is editing the post.
  protected $post;      // Models\Post.
  protected $postExisted;
  protected $input;     // array('var' => 'value').
  protected $errors;    // MessageBag.

  protected $newTags = array();               // array of Models\Tag.

  protected $newPolls = array();              // array of Models\Poll.
  protected $deletedPolls = array();          // array of Models\Poll.
  protected $deletedPollOptions = array();    // array of Models\PollOptions.

  static function make(PostModel $post) {
    return new static($post);
  }

  function __construct(PostModel $post) {
    $this->user = user() or App::abort(401);
    $this->post = $post;
    // $exists will change once $post->save() is called and we won't know if
    // an existing post was created or edited.
    $this->postExisted = $post->exists;
    $this->errors = new MessageBag;
  }

  function post() {
    return $this->post;
  }

  function postExisted() {
    return $this->postExisted;
  }

  function errors() {
    return $this->errors;
  }

  function input($name, $default = null) {
    return array_get($this->input, $name, $default);
  }

  function applyInput(array $input) {
    $this->input = $input;

    $this->applyBasic();
    $this->applyTags();
    $this->applyPolls();

    $this->post->validateAndMerge($this->errors);
    return $this;
  }

  // Copies basic Post properties (source, text, etc.) from the user's input.
  protected function applyBasic() {
    $post = $this->post;

    if ($this->user and $this->user->hasFlag('post.setURL') and $this->input('url') !== null) {
      $post->url = $this->input('url');
    }

    if ($this->input('sourceURL') !== null) {
      $url = $post->sourceURL = (string) $this->input('sourceURL');
      if ($url !== '' and !preg_match('~^https?://~', $url)) {
        $post->sourceURL = 'http://'.ltrim($url, '\\/:');
      }
    }

    foreach (array('sourceName', 'caption', 'markup', 'text') as $prop) {
      $value = $this->input($prop);
      $value === null or $post->$prop = $value;
    }

    $post->author or $post->author = $this->user->id;

    $post->format();

    if (!$post->id or ($post->caption === '' and $post->getOriginal('caption') !== '')) {
      $validator = \Validator::make(array('caption' => trim($this->input('caption'))),
                                    array('caption' => 'required'));
      $validator->fails() and $this->errors->merge($validator->messages());
    }
  }

  protected function applyTags() {
    foreach ((array) $this->input('tags') as $caption) {
      $tag = Models\Tag::fromCaption($caption);
      $tag and $this->newTags[] = $tag;
    }
  }

  // This big method figures what polls were deleted and which were added and
  // for added ones also figures added/deleted options. Removes polls which got
  // empty captions or no options and options with empty captions.
  protected function applyPolls() {
    // Input:
    // - polls[index][caption]=...
    // - polls[index][multiple]=0/1
    // - polls[index][id] - if editing existing poll
    // - options[index][optindex][caption]=...
    // - options[index][optindex][id] - if editing existing option

    $post = $this->post;

    if ($inputPolls = $this->input('polls') and $inputOptions = $this->input('options')) {
      foreach ($post->polls()->get() as $pollIndex => $poll) {
        foreach ($inputPolls as &$pollItem) {
          if ($pollItem and array_get($pollItem, 'id') == $poll->id and
              trim($pollItem['caption']) !== '') {
            // Update existing and kept poll.
            $poll->caption = $pollItem['caption'];
            $poll->multiple = $pollItem['multiple'];
            $newOptions = array();

            // Remove/update its options.
            foreach ($poll->options()->get() as $option) {
              foreach ($inputOptions[$pollIndex] as &$optItem) {
                if ($optItem and array_get($optItem, 'id') == $option->id and
                    trim($optItem['caption']) !== '') {
                  // Update existing option.
                  $option->caption = $optItem['caption'];
                  $newOptions[] = $option;
                  $option->validateAndMerge($this->errors);
                  $optItem = null;
                  $option = null;
                  break;
                }
              }

              $option and $this->deletedPollOptions[] = $option;
            }

            // Add new options.
            foreach ($inputOptions[$pollIndex] as &$optItem) {
              if ($optItem and trim($optItem['caption']) !== '') {
                $option = new Models\PollOption;
                $option->caption = $optItem['caption'];
                $option->poll = $poll->id;
                $newOptions[] = $option;
                $option->validateAndMerge($this->errors);
              }
            }

            // If poll has no options - delete it by keeping non-null after the cycle.
            if ($newOptions) {
              $poll->_editing_newOptions = $newOptions;
              $this->newPolls[] = $poll;
              $poll->validateAndMerge($this->errors);
              $poll = null;
            }

            $pollItem = null;
            break;
          }
        }

        // Old poll not found, has empty caption or no options - delete it.
        $poll and $this->deletedPolls[] = $poll;
      }

      foreach ($inputPolls as $pollIndex => &$pollItem) {
        if ($pollItem and trim($pollItem['caption']) !== '') {
          // Found a new poll to be created. Input [id] values must not be used.
          $poll = new Models\Poll;
          $poll->caption = $pollItem['caption'];
          $poll->multiple = $pollItem['multiple'];
          $poll->validateAndMerge($this->errors);
          $newOptions = array();

          // Add its options.
          foreach ($inputOptions[$pollIndex] as &$optItem) {
            if (trim($optItem['caption']) !== '') {
              $option = new Models\PollOption;
              $option->caption = $optItem['caption'];
              $newOptions[] = $option;
              $option->validateAndMerge($this->errors);
            }
          }

          // Create new poll if it has any options.
          $poll->_editing_newOptions = $newOptions and $this->newPolls[] = $poll;
        }
      }
    }
  }

  function save() {
    if (!is_array($this->input)) {
      \App::abort(500, __CLASS__.'->save() must be called after applyInput().');
    }

    $self = $this;
    \DB::transaction(function () use ($self) {
      $self->updateTimeAndFlags();
      $self->saveBasic();
      $self->saveTags();
      $self->savePolls();
    });

    return $this;
  }

  protected function updateTimeAndFlags() {
    $post = $this->post;

    $draftTag = \Config::get('habravel::g.tags.draft');

    // See if post is being saved/moved to drafts and remove it from public post
    // listings by resetting listTime (pubTime keeps the first publication date so
    // moving post to drafts and back will set listTime to pubTime, not current date).
    foreach ($this->newTags as $tag) {
      if ($tag->caption === $draftTag) {
        $post->listTime = null;
        return;
      }
    }

    // If we got here then the post is public, not a draft.
    isset($post->pubTime) or $post->pubTime = new \Carbon\Carbon;
    $post->listTime = clone $post->pubTime;
  }

  protected function saveBasic() {
    $post = $this->post;

    // Each post (article/comment) should be vote-able so it must have a Poll.
    if (!$post->poll) {
      $poll = new Models\Poll;
      $poll->caption = '"'.$post->caption.'"';
      $poll->save();
      $post->poll = $poll->id;
    }

    $post->url or $post->url = 'posts/%ID%';
    $post->save();

    if (!$this->postExisted) {
      // #anchors are prefixed with post ID which we didn't know before saving
      // so need to reformat the markup into HTML with proper #anchors.
      $post->format();
      $post->save();
    }
  }

  protected function saveTags() {
    $captions = array();

    foreach ($this->newTags as $tag) {
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
        $records[] = array('post_id' => $this->post->id, 'tag_id' => $id);
      }
    }

    \DB::table('post_tag')->where('post_id', '=', $this->post->id)->delete();
    $records and \DB::table('post_tag')->insert($records);
  }

  protected function savePolls() {
    foreach ($this->deletedPollOptions as $option) { $option->delete(); }
    foreach ($this->deletedPolls as $poll) { $poll->delete(); }

    \DB::table('poll_post')->where('post_id', '=', $this->post->id)->delete();
    $records = array();

    foreach ($this->newPolls as $poll) {
      $poll->save();
      $records[] = array('post_id' => $this->post->id, 'poll_id' => $poll->id);

      foreach ($poll->_editing_newOptions as $option) {
        $option->poll = $poll->id;  // since a new poll might have been created.
        $option->save();
      }
    }

    $records and \DB::table('poll_post')->insert($records);
  }
}