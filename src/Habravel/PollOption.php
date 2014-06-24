<?php namespace Habravel;

class PollOption extends BaseModel {
  protected $attributes = array(
    'id'                  => 0,
    'poll'                => 0,   // Poll id.
    'caption'             => '',
  );

  function poll() {
    return Poll::find($this->id);
  }

  function votes() {
    return PollVote::whereOption($this->id);
  }
}