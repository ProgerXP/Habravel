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
    return static::whereParent($this->id);
  }

  function parentTag() {
    return static::find($this->parent);
  }

  function posts() {
    return $this->belongsToMany(__NAMESPACE__.'\\Post');
  }

  function url() {
    return \Habravel\url().'/tags/'.urlencode($this->caption);
  }
}