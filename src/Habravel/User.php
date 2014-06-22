<?php namespace Habravel;

class User extends BaseModel {
  protected static $rules = array(
    'password'            => 'required',
    'email'               => 'required|max:200|email|unique:users',
    'name'                => 'required|min:2|max:50|regex:~^[\w\d]+$|unique:users',
    'info'                => '',
    'poll'                => 'exists:poll,id',
    'score'               => '%INT%',
    'rating'              => '%INT%',
    'signupTime'          => 'date|after:2000-01-01',
    'signupIP'            => 'ip',
    'loginTime'           => 'date|after:2000-01-01',
    'loginIP'             => 'ip',
    'flags'               => '',
    'avatar'              => 'max:200',
  );

  protected $attributes = array(
    'id'                  => 0,
    'password'            => '',    // hash.
    'remember_token'      => '',
    'email'               => '',
    'name'                => '',    // display nickname.
    'info'                => '',    // serialized.
    'poll'                => null,  // Poll id; for counting score.
    'score'               => 0,
    'rating'              => 0,
    'signupTime'          => 0,
    'signupIP'            => '',
    'loginTime'           => 0,
    'loginIP'             => '',
    'flags'               => '',    // '[group.perm][foo.bar]'.
    'avatar'              => '',    // 'pub/path.jpg'.
  );

  static function rules(User $model = null) {
    $rules = parent::rules();

    if ($model) {
      $rules['email'] .= ','.$model->id;
      $rules['name'] .= ','.$model->id;
    }

    return $rules;
  }

  function getDates() {
    $list = parent::getDates();
    $list[] = 'listTime',
    $list[] = 'publishTime',
    return $list;
  }

  function posts() {
    return $this->hasMany('Post', 'author');
  }

  function votes() {
    return $this->hasMany('PollVote', 'id', 'user');
  }
}