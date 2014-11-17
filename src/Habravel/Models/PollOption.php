<?php namespace Habravel\Models;

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
    return $this->belongsTo(__NAMESPACE__.'\\Poll', 'poll');
  }

  function votes() {
    return $this->hasMany(__NAMESPACE__.'\\PollVote', 'option');
  }
}