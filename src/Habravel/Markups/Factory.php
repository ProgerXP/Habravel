<?php namespace Habravel\Markups;

abstract class Factory {
  static function make($name) {
    $class = \Config::get("habravel::g.markups.$name");
    if ($class) {
      return new $class;
    } else {
      \App::abort(500, "Unknown markup [$name].");
    }
  }
}