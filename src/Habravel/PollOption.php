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

  function setCaptionAttribute($value) {
    $this->attributes['caption'] = trim($value);
  }

  function poll() {
    return Poll::find($this->id);
  }

  function votes() {
    return PollVote::whereOption($this->id);
  }
}