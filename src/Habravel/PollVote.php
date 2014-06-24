<?php namespace Habravel;

class PollVote extends BaseModel {
  protected $attributes = array(
    'poll'                => 0,   // Poll id.
    'option'              => 0,   // PollOption id.
    'user'                => 0,   // User id.
    'ip'                  => '',
  );

  function poll() {
    return Poll::find($this->poll);
  }

  function option() {
    return PollOption::find($this->option);
  }

  function user() {
    return User::find($this->user);
  }
}