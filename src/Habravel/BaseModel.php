<?php namespace Habravel;

class BaseModel extends \Eloquent {
  protected static $rules = array();

  static function rules() {
    $rules = static::$rules;

    foreach ($rules as &$rule) {
      $rule = strtr($rule, array(
        // Just 'integer' has too relaxed format and will match hexadecimal, etc.
        '%INT%'           => 'integer|regex:~^[-+]\d+$~',
      ));
    }

    return $rules;
  }

  // Usable if underlying table has 'flags' TEXT.
  function hasFlags($flag_1) {
    $flags = func_get_args();
    return count(array_intersect($this->flags(), $flags)) == count($flags);
  }

  // Usable if underlying table has 'flags' TEXT.
  function flags() {
    return explode(' ', $this->flags);
  }
}