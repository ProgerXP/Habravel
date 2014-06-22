<?php namespace Habravel;

class Post extends BaseModel {
  protected $with = array('tags');

  protected static $rules = array(
    'parent'              => 'exists:posts,id',
    'url'                 => 'required|max:50|regex:~^[\w\d\\-]+$~|unique:post',
    'author'              => 'required|exists:users,id',
    'poll'                => 'exists:poll,id',
    'score'               => '%INT%',
    'views'               => '%INT%|min:0',
    'info'                => '',
    'sourceName'          => 'required_with:sourceURL|max:100',
    'sourceURL'           => 'regex:~^https?://~',
    'title'               => 'required|min:2|max:150',
    'markup'              => 'required',
    'text'                => 'required|min:10',
    'flags'               => '',
    'listTime'            => 'date|after:2000-01-01',
    'publishTime'         => 'date|after:2000-01-01',
  );

  protected $attributes = array(
    'id'                  => 0,
    'parent'              => null,  // Post id or null; for comments.
    'url'                 => '',
    'author'              => 0,     // User id.
    'poll'                => null,  // Poll id; for counting score.
    'score'               => 0,     // +/- int.
    'views'               => 0,     // uint.
    'info'                => '',    // serialized.
    'sourceName'          => '',    // translation/other source.
    'sourceURL'           => '',
    'title'               => '',
    'markup'              => '',    // 'githubmarkdown', 'uversewiki'.
    'text'                => '',
    'html'                => '',
    'introHTML'           => '',
    'flags'               => '',    // '[draft][aa.bb]'.
    'listTime'            => 0,
    'publishTime'         => 0,
  );

  static function rules(Post $model = null) {
    $rules = parent::rules();

    if ($model) {
      $rules['url'] .= ','.$model->id;
    }

    return $rules;
  }

  function __construct(array $attributes = array()) {
    parent::__construct($attributes);
    $this->markup or $this->markup = head(Core::markups());
  }

  function getDates() {
    $list = parent::getDates();
    $list[] = 'listTime';
    $list[] = 'publishTime';
    return $list;
  }

  function author() {
    return $this->hasOne('User', 'id', 'author');
  }

  function children() {
    return $this->hasMany(__CLASS__, 'parent', 'id');
  }

  function parentPost() {
    return $this->belongsTo(__CLASS__, 'id', 'parent');
  }

  function poll() {
    return $this->hasOne('Poll', 'id', 'poll');
  }

  function tags() {
    return $this->belongsToMany('Tag');
  }
}