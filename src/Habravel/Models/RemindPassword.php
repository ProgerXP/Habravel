<?php namespace Habravel\Models;

class RemindPassword extends BaseModel {
  static $rules = array(
    'email' => 'required|email|exists:users,email',
  );

  protected $primaryKey = 'token';
  public $timestamps    = false;
  public $incrementing  = false;

  function scopeExpired($query) {
    $date = \Carbon\Carbon::now();
    $expire = $date->subMinutes(\Config::get('habravel::g.remindPasswordExpire'));

    return $query->where('created_at', '<', $expire);
  }

  function scopeToken($query, $token) {
    $date = \Carbon\Carbon::now();
    $expire = $date->subMinutes(\Config::get('habravel::g.remindPasswordExpire'));

    return $query->where('token', '=', $token)->where('created_at', '>', $expire);
  }
}