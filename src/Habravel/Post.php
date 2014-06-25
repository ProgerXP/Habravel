<?php namespace Habravel;

class Post extends BaseModel {
  protected $with = array('tags');

  static $sortable = array('author', 'score', 'views', 'sourceName', 'caption',
                           'listTime', 'pubTime', 'created_at');

  protected static $rules = array(
    'top'                 => 'exists:posts,id',
    'parent'              => 'exists:posts,id',
    'url'                 => 'max:50|regex:~^[\w\d\\-/#]+$~|unique:posts,url',
    'author'              => 'required|exists:users,id',
    'poll'                => 'exists:poll,id',
    'score'               => '%INT%',
    'views'               => '%INT%|min:0',
    'info'                => '',
    'sourceURL'           => 'regex:~^https?://~',
    'sourceName'          => 'required_with:sourceURL|max:100',
    'caption'             => 'max:150',
    'markup'              => 'required',
    'text'                => 'required',
    'flags'               => '',
    'listTime'            => 'date|after:2000-01-01',
    'pubTime'             => 'date|after:2000-01-01',
  );

  protected $attributes = array(
    'id'                  => 0,
    'top'                 => null,  // Post id or null; for comments.
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
    return User::find($this->author);
  }

  function children() {
    return static::whereParent($this->id);
  }

  function childCount() {
    return static::whereTop($this->id)->count();
  }

  function top() {
    return static::find($this->top);
  }

  function parentPost() {
    return static::find($this->parent);
  }

  function poll() {
    return Poll::find($this->poll);
  }

  function tags() {
    return $this->belongsToMany(NS.'Tag');
  }

  function url() {
    return Core::url().'/'.$this->url;
  }

  function isEditable(User $user) {
    if ($this->id) {
      return $user->hasFlag('can.edit') or
             ($this->author === $user->id and $user->hasFlag('can.editSelf'));
    }
  }

  function size() {
    return mb_strlen($this->text);
  }

  function wordCount() {
    preg_match_all('~[\pC\pZ#>*\-_`|/(\~+=%[]\pL{2,}~u', ' '.$this->text, $matches);
    return count($matches[0]);
  }

  function format() {
    $fmt = Core::markup($this->markup)->format($this->text, $this);
    $this->html = $fmt->html;
    $this->introHTML = $fmt->introHTML;
    return $this;
  }

  protected function finishSave(array $options) {
    parent::finishSave($options);

    $url = str_replace('%ID%', $this->id, $this->url);
    if ($url !== $this->url) {
      $this->url = $url;
      $this->save();
    }
  }
}