<?php namespace Habravel;

class Post extends BaseModel {
  protected $with = array('tags');

  static $sortable = array('author', 'score', 'views', 'sourceName', 'caption',
                           'listTime', 'pubTime', 'created_at');

  protected static $rules = array(
    'parent'              => 'exists:posts,id',
    'url'                 => 'max:50|regex:~^[\w\d\\-]+$~|unique:post,url',
    'author'              => 'required|exists:users,id',
    'poll'                => 'exists:poll,id',
    'score'               => '%INT%',
    'views'               => '%INT%|min:0',
    'info'                => '',
    'sourceURL'           => 'regex:~^https?://~',
    'sourceName'          => 'required_with:sourceURL|max:100',
    'caption'             => 'required|min:2|max:150',
    'markup'              => 'required',
    'text'                => 'required|min:10',
    'flags'               => '',
    'listTime'            => 'date|after:2000-01-01',
    'pubTime'             => 'date|after:2000-01-01',
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
    'sourceURL'           => '',
    'sourceName'          => '',    // translation/other source.
    'caption'             => '',
    'markup'              => '',    // 'githubmarkdown', 'uversewiki'.
    'text'                => '',
    'html'                => '',
    'introHTML'           => '',
    'flags'               => '',    // '[draft][aa.bb]'.
    'listTime'            => null,
    'pubTime'             => null,
  );

  static function rules(Post $model = null) {
    $rules = parent::rules();
    $rules['markup'] .= '|in:'.join(',', Core::markups());

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
    $list[] = 'pubTime';
    return $list;
  }

  function author() {
    return $this->hasOne(NS.'User', 'id', 'author');
  }

  function children() {
    return $this->hasMany(__CLASS__, 'parent', 'id');
  }

  function parentPost() {
    return $this->belongsTo(__CLASS__, 'id', 'parent');
  }

  function poll() {
    return $this->hasOne(NS.'Poll', 'id', 'poll');
  }

  function tags() {
    return $this->belongsToMany(NS.'Tag');
  }

  function url() {
    return Core::url().'/'.$this->url;
  }

  function format() {
    $fmt = Core::markup($this->markup)->format($this->text, $this);
    $this->html = $fmt->html;
    $this->introHTML = $fmt->introHTML;
    return $this;
  }

  protected function finishSave(array $options) {
    parent::finishSave($options);

    $url = str_replace('#', $this->id, $this->url);
    if ($url !== $this->url) {
      $this->url = $url;
      $this->save();
    }
  }
}