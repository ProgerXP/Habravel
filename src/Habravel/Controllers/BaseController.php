<?php namespace Habravel\Controllers;

function user() {
  return call_user_func_array(NS.'user', func_get_args());
}

class BaseController extends \BaseController {
  function __construct() {
    $this->beforeFilter('csrf', array('on' => 'post'));
  }
}