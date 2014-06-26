<?php namespace Habravel;

class Poll extends BaseModel {
  use \Illuminate\Database\Eloquent\SoftDeletingTrait;

  protected static $rules = array(
    'caption'             => 'required',
    'multiple'            => 'required|regex:~^[01]$~',
  );

  protected $attributes = array(
    'id'                  => 0,
    'caption'             => '',
    'multiple'            => 0,   // 1 = multichoice.
  );

  function setCaption($value) {
    return trim($value);
  }

  function options() {
    return PollOption::wherePoll($this->id);
  }

  function votes() {
    return PollVote::wherePoll($this->id);
  }
}