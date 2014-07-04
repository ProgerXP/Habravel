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
    'poll'                => 'exists:polls,id',
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
    'seen'                => '',    // binary string, each chunk is 4
                                    // bytes (IP or user ID).
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

  function setUrl($value) { return trim($value); }
  function setSourceURL($value) { return trim($value); }
  function setSourceName($value) { return trim($value); }
  function setCaption($value) { return trim($value); }
  function setText($value) { return rtrim($value); }

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

  function polls() {
    return $this->belongsToMany(NS.'Poll');
  }

  function tags() {
    return $this->belongsToMany(NS.'Tag');
  }

  function url($absolute = true) {
    return ($absolute ? Core::url().'/' : '').$this->url;
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

  function format($safe = true) {
    $fmt = Core::markup($this->markup)->format($this->text, $this);
    $this->html = $fmt->html;
    $this->introHTML = $fmt->introHTML;

    if ($safe) {
      $this->html = Core::safeHTML($this->html);
      $this->introHTML = Core::safeHTML($this->introHTML);
    }

    return $this;
  }

  // Returns true if $client hasn't yet seen this post and also adds it
  // to $this->seen. $this->views isn't incremented.
  //
  //? addSeen('1.2.3.4')        // IP
  //? addSeen(User::find(33))
  function addSeen($client) {
    if (is_object($client)) {
      $id = pack('N', $client->id);
    } else {
      // $ip = 32-bit integer in reverse (BE) order - since 0.xx.xx.xx is
      // a reserved IP $ip[0] will always be !== '\0' and so we get 24 bits
      // for local users' IDs which are ~16 mln accounts. Should be enough.
      $id = pack('N', ip2long($client));
    }

    for ($seen = $this->seen, $pos = 0; isset($seen[$pos]); $pos += 4) {
      if ($seen[$pos][0] === $id[0] and substr($seen, $pos, 4) === $id) {
        return false;
      }
    }

    $this->seen .= $id;
    return true;
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