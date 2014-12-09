<?php namespace Habravel\Models;

class User extends BaseModel {
  protected static $rules = array(
    'password'            => 'required|min:',
    'email'               => 'required|max:200|email|unique:users,email',
    'name'                => 'required|max:50|regex:~^\w[\w\d]+$~|unique:users,name',
    'data'                => '',
    'poll'                => 'exists:polls,id',
    'score'               => '%INT%',
    'rating'              => '%INT%',
    'regIP'               => 'ip',
    'loginTime'           => 'date|after:2000-01-01',
    'loginIP'             => 'ip',
    'flags'               => '',
    'avatar'              => 'max:200',
    'site'                => 'url',
    'bitbucket'           => 'alpha_dash',
    'github'              => 'alpha_dash',
    'facebook'            => 'regex:/^[\w\d.]+$/',
    'twitter'             => 'alpha_dash',
    'vk'                  => 'alpha_dash',
    'jabber'              => 'email',
    'skype'               => 'regex:/^[\w\d\-_.,]+$/',
    'icq'                 => '%INT%',
    'info'                => 'max:5000',
  );

  static $avatarImageRule = 'required|mimes:jpeg,gif,png|max:500';

  protected $attributes = array(
    'id'                  => 0,
    'password'            => '',    // hash.
    'remember_token'      => '',
    'email'               => '',
    'name'                => '',    // display nickname.
    'data'                => '',    // custom serialized data.
    'poll'                => null,  // Poll id; for counting score.
    'score'               => 0,
    'rating'              => 0,
    'regIP'               => '',
    'loginTime'           => null,
    'loginIP'             => '',
    'flags'               => '',    // '[group.perm][foo.bar]'.
    'avatar'              => '',    // 'pub/path.jpg'.
    'site'                => '',
    'bitbucket'           => '',
    'github'              => '',
    'facebook'            => '',
    'twitter'             => '',
    'vk'                  => '',
    'jabber'              => '',
    'skype'               => '',
    'icq'                 => '',
    'info'                => '',
  );

  static function rules(User $model = null) {
    $rules = parent::rules();
    $rules['password'] .= \Config::get('habravel::g.minPassword');

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

  function setEmailAttribute($value) {
    $this->attributes['email'] = trim($value);
  }

  function setNameAttribute($value) {
    $this->attributes['name'] = trim($value);
  }

  // Published = all but drafts.
  function publishedArticles() {
    return $this->articles()->whereNotNull('listTime');
  }

  function drafts() {
    return $this->posts()->whereNull('listTime');
  }

  function articles() {
    return $this->posts()->whereTop(null);
  }

  function comments() {
    return $this->posts()->whereNotNull('top');
  }

  // Queries all posts that this user can see - everyone's published articles,
  // comments or his drafts.
  function allVisiblePosts() {
    $self = $this;

    return Post::where(function ($query) use ($self) {
      $query
        ->whereNotNull('listTime')
        ->orWhere('author', '=', $self->id);
    });
  }

  // This returns just all existing Post rows for this User including drafts and
  // comments (which are also posts).
  function posts() {
    return $this->hasMany(__NAMESPACE__.'\\Post', 'author');
  }

  function votes() {
    return $this->hasMany(__NAMESPACE__.'\\PollVote', 'user');
  }

  function flags() {
    $flags = (string) $this->flags;
    if ($flags === '-') {
      return array();
    } elseif ($flags === '') {
      return \Config::get('habravel::g.userPerms');
    } elseif ($flags[0] === '+') {
      return array_merge(\Config::get('habravel::g.userPerms'), parent::flags());
    } else {
      return parent::flags();
    }
  }

  function url($absolute = true) {
    return ($absolute ? \Habravel\url().'/' : '').'~'.urlencode($this->name);
  }

  function avatarURL() {
    $url = $this->avatar ?: 'default.png';
    return asset('packages/proger/habravel/avatars').'/'.$url;
  }

  function nameHTML(array $options = array()) {
    $options += array('link' => true);
    $html = \View::make('habravel::part.user', array('user' => $this) + $options);
    return preg_replace('~\s+</~u', '</', $html);
  }
}