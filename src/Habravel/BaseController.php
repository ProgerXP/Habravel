<?php namespace Habravel;

class BaseController extends \BaseController {
  static function checkCSRF() {
    if (Core::input('_token') !== csrf_token()) {
      \App::abort(403, 'Bad CSRF token.');
    }
  }
}