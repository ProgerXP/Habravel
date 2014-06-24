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
    return static::whereParent($this->id);
  }

  function parentTag() {
    return static::find($this->parent);
  }

  function posts() {
    return $this->belongsToMany(NS.'Post');
  }
}