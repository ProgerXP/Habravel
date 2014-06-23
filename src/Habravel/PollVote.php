<?php namespace Habravel;

class PollVote extends BaseModel {
  protected $attributes = array(
    'poll'                => 0,   // Poll id.
    'option'              => 0,   // PollOption id.
    'user'                => 0,   // User id.
    'ip'                  => '',
  );

  function poll() {
    return $this->hasOne(NS.'Poll', 'id', 'poll');
  }

  function option() {
    return $this->hasOne(NS.'PollOption', 'id', 'option');
  }

  function user() {
    return $this->hasOne(NS.'User', 'id', 'user');
  }
}