<?php namespace Habravel\Models;

class Tag extends BaseModel {
  protected $attributes = array(
    'id'                  => 0,
    'parent'              => null,  // Tag id.
    // Custom info about this tag that is added to tag link as a CSS class (so
    // it must be just A-Za-z0-9_) and can be localized.
    'type'                => '',
    'caption'             => '',
    'flags'               => '',    // custom info about this tag - '[pool.edit][foo]'.
  );

  static function fromCaption($caption) {
    if ($caption = trim($caption)) {
      $tag = new static;
      $tag->caption = $caption;

      $types = \Config::get('habravel::g.tags');
      $tag->type = isset($types[$caption]) ? $types[$caption] : '';

      return $tag;
    }
  }

  function children() {
    return $this->hasMany(__CLASS__, 'parent');
  }

  function parentTag() {
    return $this->belongsTo(__CLASS__, 'parent');
  }

  function posts() {
    return $this->belongsToMany(__NAMESPACE__.'\\Post');
  }

  function url() {
    return \Habravel\url().'/tags/'.urlencode($this->caption);
  }
}