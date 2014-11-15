<?php namespace Habravel\Models;

class BaseModel extends \Eloquent {
  protected static $rules = array();

  static function find($id, $columns = array('*')) {
    if ($id instanceof static) {
      return $id;
    } else {
      return parent::find($id, $columns);
    }
  }

  static function rules() {
    $rules = static::$rules;

    foreach ($rules as &$rule) {
      $rule = strtr($rule, array(
        // Just 'integer' has too relaxed format and will match hexadecimal, etc.
        '%INT%'           => 'integer|regex:~^[-+]?\d+$~',
      ));
    }

    return $rules;
  }

  function validateAndMerge(\Illuminate\Support\MessageBag $errors) {
    $validator = \Validator::make($this->getAttributes(), $this->rules($this));
    $validator->fails() and $errors->merge($validator->messages());
    return $this;
  }

  // Usable if underlying table has 'flags' field. Returns true if all given
  // flags are present.
  function hasFlag($flag_1) {
    $flags = is_array($flag_1) ? $flag_1 : func_get_args();
    return count(array_intersect($this->flags(), $flags)) == count($flags);
  }

  // Usable if underlying table has 'flags' field.
  function flags() {
    preg_match_all('~\[([^\]]+)]~u', $this->flags, $matches);
    return $matches ? $matches[1] : array();
  }

  function save(array $options = array()) {
    $attrs = array();
    foreach ($this->attributes as $key => &$value) {
      if (substr($key, 0, 2) === 'x_') {
        $attrs[$key] = &$value;
        unset($this->attributes[$key]);
      }
    }

    try {
      $res = parent::save($options);
      $this->attributes += $attrs;
      return $res or \App::abort(500, 'Cannot save '.get_class().'.');
    } catch (\Exception $e) {
      $this->attributes += $attrs;
      throw $e;
    }
  }
}