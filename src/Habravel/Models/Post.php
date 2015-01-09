<?php namespace Habravel\Models;

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
    'data'                => '',
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
    'data'                => '',    // serialized.
    'sourceURL'           => '',
    'sourceName'          => '',    // translation/other source.
    'caption'             => '',
    'markup'              => '',    // 'githubmarkdown', 'uversewiki'.
    'text'                => '',
    'html'                => '',
    'introHTML'           => '',
    'flags'               => '',    // custom info about this post: '[flag.1][aa.bb]...'.
    'listTime'            => null,
    'pubTime'             => null,
  );

  static function rules(Post $model = null) {
    $rules = parent::rules();
    $rules['markup'] .= '|in:'.join(',', array_keys(\Config::get('habravel::g.markups')));

    if ($model) {
      $rules['url'] .= ','.$model->id;
    }

    return $rules;
  }

  static function stripCodeFrom($html) {
    $html = preg_replace('~<fieldset class="toc">.*?</fieldset>~us', '', $html);
    $withCodeTags = strip_tags($html, '<kbd><code><pre>');
    $withoutCode = '';
    $parts = preg_split('~(<[^>]+>)~u', $withCodeTags, -1, PREG_SPLIT_DELIM_CAPTURE);
    $level = 0;

    foreach ($parts as $i => $part) {
      if ($i % 2 == 0) {
        $level or $withoutCode .= $part;
      } elseif ($part[1] === '/') {
        if (--$level < 0) {
          throw new \Exception('stripCodeFrom(): bad tag nesting.');
        }
      } else {
        ++$level;
      }
    }

    return $withoutCode;
  }

  static function calcStatsFor($html) {
    $text = strip_tags($html);
    $text = preg_replace(array('~[^\pL\pZ]+~u', '~\pZ+~u'), ' ', $text);

    return array(
      'words'             => $words = mb_substr_count($text, ' ') + 1,
      'symbols'           => $symbols = mb_strlen($text),
      'noSpaceSymbols'    => $symbols - $words + 1,
    );
  }

  function __construct(array $attributes = array()) {
    parent::__construct($attributes);
    $this->markup or $this->markup = head(array_keys(\Config::get('habravel::g.markups')));
  }

  function getDates() {
    $list = parent::getDates();
    $list[] = 'listTime';
    $list[] = 'pubTime';
    return $list;
  }

  function setUrlAttribute($value) {
    $this->attributes['url'] = trim($value);
  }

  function setSourceUrlAttribute($value) {
    $this->attributes['sourceURL'] = trim($value);
  }

  function setSourceNameAttribute($value) {
    $this->attributes['sourceName'] = trim($value);
  }

  function setCaptionAttribute($value) {
    $this->attributes['caption'] = trim($value);
  }

  function setTextAttribute($value) {
    $this->attributes['text'] = rtrim($value);
  }

  function author() {
    return $this->belongsTo(__NAMESPACE__.'\\User', 'author');
  }

  function children() {
    return $this->hasMany(__CLASS__, 'parent');
  }

  function childCount() {
    return static::whereTop($this->id)->count();
  }

  function top() {
    return $this->belongsTo(__CLASS__, 'top');
  }

  function parentPost() {
    return $this->belongsTo(__CLASS__, 'parent');
  }

  function poll() {
    return $this->belongsTo(__NAMESPACE__.'\\Poll', 'poll');
  }

  function polls() {
    return $this->belongsToMany(__NAMESPACE__.'\\Poll');
  }

  function tags() {
    return $this->belongsToMany(__NAMESPACE__.'\\Tag');
  }

  function url($absolute = true) {
    return ($absolute ? \Habravel\url().'/' : '').$this->url;
  }

  function isEditable(User $user) {
    if ($this->id) {
      return $user->hasFlag('can.edit') or
             ($this->author === $user->id and $user->hasFlag('can.editSelf'));
    }
  }

  function data($new = null) {
    if (func_num_args()) {
      $this->data = $new ? serialize($new) : '';
      return $new;
    } else {
      return $this->data ? unserialize($this->data) : array();
    }
  }

  function size() {
    return mb_strlen($this->text);
  }

  function format($safe = true) {
    $fmt = \Habravel\Markups\Factory::make($this->markup)->format($this->text, $this);
    $this->html = $fmt->html;
    $this->introHTML = $fmt->introHTML;

    $data = $this->data();
    if ('' === $cut = trim($fmt->meta['cut'])) {
      unset($data['cut']);
    } else {
      $data['cut'] = $cut;
    }
    $this->data($data);

    if ($safe) {
      $this->html = \Habravel\HyperSafe::transformBody($this->html);
      $this->introHTML = \Habravel\HyperSafe::transformIntro($this->introHTML);
    }

    return $this;
  }

  function needHTML() {
    if ((!$this->html or !$this->introHTML) and $this->text) {
      // Post not rendered yet.
      $this->format();
      $this->save();
    }
    return $this;
  }

  function needStats() {
    $data = $this->data();

    if (empty($data['stats'])) {
      $data = array('stats' => $this->calcStats());
      $this->id > 0 and $this->save();
    }

    return $data['stats'];
  }

  function calcStats() {
    $data = $this->data();

    $data['stats'] = array(
      'code'      => $this->calcStatsFor($this->html),
      'noCode'    => $this->calcStatsFor($this->stripCodeFrom($this->html)),
    );

    $this->data($data);
    return $data['stats'];
  }

  function statString() {
    $stats = $this->needStats();
    $vars = $stats['code'];

    foreach ($stats['noCode'] as $name => $value) {
      $vars["nc$name"] = $value;
    }

    foreach ($vars as &$ref) { $ref = \Habravel\number($ref); }

    return trans('habravel::g.post.size', $vars);
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