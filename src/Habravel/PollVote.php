<?php namespace Habravel;

class PollVote extends BaseModel {
	protected $attributes = array(
    'poll'                => 0,   // Poll id.
    'option'              => 0,   // PollOption id.
    'user'                => 0,   // User id.
    'ip'                  => '',
	);

	function poll() {
    return $this->hasOne('Poll', 'id', 'poll');
  }

	function option() {
    return $this->hasOne('PollOption', 'id', 'option');
  }

	function user() {
    return $this->hasOne('User', 'id', 'user');
  }
}