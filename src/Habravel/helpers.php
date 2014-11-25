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

function number($num, $decimals = 0) {
  static $locale;
  $locale or $locale = trans('habravel::g.number');
  return number_format($num, $decimals, $locale[0], $locale[1]);
}

// ссылка на внешний сайт
function externalUrl($url, $class = 'eurl') {
  if (starts_with($url, 'http://') or starts_with($url, 'https://')) {
    $text = explode("//", $url);
    return '<a href="'.$url.'" class="'.$class.'" rel="external">'.$text[1].'</a>';
  }
}

// ссылка на jabber
function jabberUrl($mail, $class = 'jurl') {
  return '<a href="xmpp:'.$mail.'" class="'.$class.'">'.$mail.'</a>';
}

// ссылка на скайп
function skypeUrl($login, $class = 'surl') {
  return '<a href="skype:'.$login.'" class="'.$class.'">'.$login.'</a>';
}
