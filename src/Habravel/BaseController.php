<?php namespace Habravel;

class BaseController extends \BaseController {
  static function checkCSRF() {
    if (Core::input('csrf') !== csrf_token()) {
      \App::abort(403, 'Bad CSRF token.');
    }
  }
}