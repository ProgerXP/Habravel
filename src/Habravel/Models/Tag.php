<?php namespace Habravel\Models;

class Tag extends BaseModel {
  protected $attributes = array(
    'id'                  => 0,
    'parent'              => null,  // Tag id.
    'type'                => '',    // '' (user), 'draft', etc.
    'caption'             => '',
    'flags'               => '',    // '[show.menu][private]'.
  );

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