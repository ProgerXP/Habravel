<?php namespace Habravel;

class Tag extends BaseModel {
  protected $attributes = array(
    'id'                  => 0,
    'parent'              => null,  // Tag id.
    'type'                => '',    // '' (user), 'draft', etc.
    'caption'             => '',
    'flags'               => '',    // '[show.menu][private]'.
  );

  function children() {
    return $this->hasMany(__CLASS__, 'parent', 'id');
  }

  function parentTag() {
    return $this->hasOne(__CLASS__, 'id', 'parent');
  }

  function posts() {
    return $this->belongsToMany('Post');
  }
}