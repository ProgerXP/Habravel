<?php namespace Habravel;

use App;
use Config;

// Without trailing '/'.
function url() {
  return \url(Config::get('habravel::g.rootURL'));
}

//? referer('')     //=> http://mydomain/habravel-home/
//? referer('foo')  //=> http://mydomain/habravel-home/
//? referer('http://foo')             //=> http://mydomain/habravel-home/
//? referer('http://mydomain/habravel-home/path/')   //=> as is
//? referer('http://mydomain/foo')    //=> as is
//? referer('https://mydomain/foo')   //=> as is
//? referer('http://www.mydomain/foo')    //=> as is
//? referer('https://www.mydomain/foo')   //=> as is
function referer($url) {
  $host = parse_url(url(), PHP_URL_HOST);
  preg_match('~^https?://(www\.)?'.$host.'/~i', $url) or $url = url();
  return $url;
}

function user($login = null) {
  static $user;

  if (!$user) {
    $user = call_user_func_array(Config::get('habravel::g.user'), func_get_args());
    if ($user and ! $user instanceof Models\User) {
      App::abort(400, 'habravel::user returned wrong value');
    }
  }

  return $user;
}

function tagLink(Models\Tag $tag, $newTab = false, $class = 'hvl-tag') {
  $key = "habravel::tags.$tag->type";

  if ($tag->type and $trans = trans($key) and $trans !== $key) {
    $caption = $trans;
  } else {
    $caption = $tag->caption;
  }

  empty($newTab) or $target = '_blank';
  $tag->type and $class .= " hvl-tag-$tag->type";

  return \HTML::link($tag->url(), $caption, compact('target', 'class'));
}
