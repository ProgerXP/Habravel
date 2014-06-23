<?php namespace Habravel;

class User extends BaseModel {
  protected static $rules = array(
    'password'            => 'required|min:6',
    'email'               => 'required|max:200|email|unique:users,email',
    'name'                => 'required|max:50|regex:~^\w[\w\d]+$~|unique:users,name',
    'info'                => '',
    'poll'                => 'exists:poll,id',
    'score'               => '%INT%',
    'rating'              => '%INT%',
    'regIP'               => 'ip',
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
    'regIP'               => '',
    'loginTime'           => null,
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
    $list[] = 'loginTime';
    return $list;
  }

  function posts() {
    return $this->hasMany('Post', 'author');
  }

  function votes() {
    return $this->hasMany('PollVote', 'id', 'user');
  }

  function flags() {
    $flags = $this->flags;
    if ($flags === '-') {
      return array();
    } elseif ($flags === '') {
      return \Config::get('habravel::g.userPerms');
    } else {
      return parent::flags();
    }
  }

  function url() {
    return Core::url().'/~'.urlencode($this->name);
  }

  function nameHTML() {
    $name = e($this->name);
    $score = $this->score;

    if ($score != 0) {
      $sign = $score > 0 ? '+' : '-';
      $name .= ' <sup>'.$sign.((int) $this->score).'</sup>';
    }

    $class = $score == 0 ? 'zero' : ($score > 0 ? 'above' : 'below');
    return "<span class=\"hvl-uname hvl-uname-$class\">$name</span>";
  }
}