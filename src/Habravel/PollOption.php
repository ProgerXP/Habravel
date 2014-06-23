<?php namespace Habravel;

class PollOption extends BaseModel {
  protected $attributes = array(
    'id'                  => 0,
    'poll'                => 0,   // Poll id.
    'caption'             => '',
  );

  function poll() {
    return $this->hasOne(NS.'Poll', 'id', 'poll');
  }

  function votes() {
    return $this->hasMany(NS.'PollVote', 'id', 'option');
  }
}