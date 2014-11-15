<?php namespace Habravel;

use Config;

class HyperSafe extends \HyperSafe {
  static function transform($html, $autoClose = false) {
    static $hs;

    if (!$hs) {
      $hs = new static;

      foreach (Config::get('habravel::hypersafe') as $name => $value) {
        $hs->$name = $value;
      }
    }

    $hs->clearWarnings();
    $hs->autoClose = $autoClose ? 'eof' : false;
    $clean = $hs->clean($html);

    if ($warnings = $hs->warnings() and
        Config::get('habravel::hypersafe.hvlLogWarnings')) {
      $warnings = array_pluck($warnings, 'msg');
      array_unshift($warnings, $html);
      \Log::warning(sprintf('Habravel: %d warning%s normalizing HTML.',
                            count($warnings) - 1, count($warnings) == 1 ? '' : 's'), $warnings);
    }

    return $clean;
  }
}