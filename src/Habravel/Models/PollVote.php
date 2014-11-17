<?php namespace Habravel\Models;

class PollVote extends BaseModel {
  protected $attributes = array(
    'poll'                => 0,   // Poll id.
    'option'              => 0,   // PollOption id.
    'user'                => 0,   // User id.
    'ip'                  => '',
  );

  function poll() {
    return $this->belongsTo(__NAMESPACE__.'\\Poll', 'poll');
  }

  function option() {
    return $this->belongsTo(__NAMESPACE__.'\\PollOption', 'option');
  }

  function user() {
    return $this->belongsTo(__NAMESPACE__.'\\User', 'user');
  }
}