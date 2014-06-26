<?php namespace Habravel;

class PollOption extends BaseModel {
  use \Illuminate\Database\Eloquent\SoftDeletingTrait;

  protected static $rules = array(
    'caption'             => 'required',
  );

  protected $attributes = array(
    'id'                  => 0,
    'poll'                => 0,   // Poll id.
    'caption'             => '',
  );

  function setCaption($value) {
    return trim($value);
  }

  function poll() {
    return Poll::find($this->id);
  }

  function votes() {
    return PollVote::whereOption($this->id);
  }
}