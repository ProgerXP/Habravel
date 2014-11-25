<?php namespace Habravel\Models;

class UserInfo extends BaseModel {
  protected static $rules = array(
    'site'                => 'url|max:128',
    'bitbucket'           => 'url|max:128',
    'github'              => 'url|max:128',
    'facebook'            => 'url|max:128',
    'twitter'             => 'url|max:128',
    'vk'                  => 'url|max:128',
    'jabber'              => 'email|max:128',
    'skype'               => 'max:64',
    'icq'                 => 'max:9|%INT%',
    'info'                => 'max:5000',
  );

  protected $attributes = array(
    'user_id'             => 0,
    'site'                => '',
    'bitbucket'           => '',
    'github'              => '',
    'facebook'            => '',
    'twitter'             => '',
    'vk'                  => '',
    'jabber'              => '',
    'skype'               => '',
    'icq'                 => '',
    'info'                => '',
  );

  protected $table        = 'user_info';
  protected $primaryKey   = 'user_id';
  protected $fillable     = array('*');
  public    $incrementing = false;
  public    $timestamps   = false;

  static function rules() {
    $rules = parent::rules();

    return $rules;
  }

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