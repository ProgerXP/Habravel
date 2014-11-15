<?php namespace Habravel\Controllers;

// Alias this function because it's used a lot in the controllers' code.
function user() {
  return call_user_func_array(NS.'user', func_get_args());
}

class BaseController extends \BaseController {
  function __construct() {
    $this->beforeFilter('csrf', array('on' => 'post'));
  }
}