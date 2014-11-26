<?php namespace Habravel\Models;

class UserInfo extends \Eloquent {

  protected $table        = 'user_info';
  protected $primaryKey   = 'user_id';
  protected $fillable     = array('*');
  public    $incrementing = false;
  public    $timestamps   = false;

  function setSiteAttribute($value) {
    $this->attributes['site'] = e(trim($value));
  }

  function setBitbucketAttribute($value) {
    $this->attributes['bitbucket'] = e(trim($value));
  }

  function setGithubAttribute($value) {
    $this->attributes['github'] = e(trim($value));
  }

  function setFacebookAttribute($value) {
    $this->attributes['facebook'] = e(trim($value));
  }

  function setTwitterAttribute($value) {
    $this->attributes['twitter'] = e(trim($value));
  }

  function setVkAttribute($value) {
    $this->attributes['vk'] = e(trim($value));
  }

  function setJabberAttribute($value) {
    $this->attributes['jabber'] = e(trim($value));
  }

  function setSkypeAttribute($value) {
    $this->attributes['skype'] = e(trim($value));
  }

  function setIcqAttribute($value) {
    $this->attributes['icq'] = e(trim($value));
  }

  function setInfoAttribute($value) {
    $this->attributes['info'] = e(trim($value));
  }

}