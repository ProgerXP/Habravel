<?php namespace Habravel;

class Poll extends BaseModel {
  protected static $rules = array(
    'target'              => 'required',
    'caption'             => 'required',
    'multiple'            => 'required|regex:~^[01]$~',
  );

  protected $attributes = array(
    'id'                  => 0,
    'target'              => '',  // 'user', 'post'.
    'caption'             => '',
    'multiple'            => 0,   // 1 = multichoice.
  );

  function options() {
    return PollOption::wherePoll($this->id);
  }

  function votes() {
    return PollVote::wherePoll($this->id);
  }
}