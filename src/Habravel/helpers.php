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

function publicPath($path) {
  return \Config::get('habravel::g.publicPath').$path;
}

// Will fit the image into given $width * $height box.
//
//? resizeImage('/tmp/php5E1.tmp', '/.../public/upload.png', 200, 150)
function resizeImage($source, $destination, $width, $height) {
  list($srcWidth, $srcHeight, $type) = getimagesize($source);

  $ratio = min($width / $srcWidth, $height / $srcHeight);
  $width = $srcWidth * $ratio;
  $height = $srcHeight * $ratio;

  switch ($type) {
  case IMAGETYPE_JPEG:  $ext = 'jpeg'; break;
  case IMAGETYPE_GIF:   $ext = 'gif'; break;
  case IMAGETYPE_PNG:   $ext = 'png'; break;
  default:
    App::fail(400, "Bad image type [$type] to resize.");
  }

  $function = "imagecreatefrom$ext";
  $srcResource = $function($source);

  $destinationResource = imagecreatetruecolor($width, $height);

  imagealphablending($destinationResource, false);

  imagecopyresampled($destinationResource, $srcResource, 0, 0, 0, 0,
                     $width, $height, $srcWidth, $srcHeight);

  imagesavealpha($destinationResource, true);

  if (!imagepng($destinationResource, $destination)) {
    App::abort(500, "Cannot save resized image to $destination.");
  }

  imagedestroy($destinationResource);
  imagedestroy($srcResource);
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

function externalLink($url, $domainTitle = false, $class = 'eurl') {
  $re = $domainTitle ? '([^/]+)' : '(.+)';
  if (!preg_match("~(?:\w+://)$re~u", $url, $match)) {
    $match = array($url, $url);
  }

  return \HTML::link($url, $match[1], array(
    'class'   => $class,
    'rel'     => 'external nofollow',
    'target'  => '_blank',
  ));
}

function alert($message, $class = 'success') {
  \Session::flash('message', $message);
  \Session::flash('class', $class);
}

function captcha() {
  $digitStrings = trans('habravel::captcha.digits');

  $digit1 = mt_rand(0, 9);
  $digit2 = mt_rand(0, 9);
  $isSub = mt_rand(0, 1);

  if ($isSub and $digit1 > $digit2) {
    list($digit1, $digit2) = array($digit2, $digit1);
  }

  $result = $isSub ? ($digit2 - $digit1) : ($digit1 + $digit2);

  return array(
    'hash'          => \Habravel\captchaHash($result),
    'question'      => trans('habravel::captcha.question', array(
      'action'      => trans("habravel::captcha.actions.$isSub"),
      'digit1'      => $digitStrings[$digit1],
      'conjunction' => trans("habravel::captcha.conjunctions.$isSub"),
      'digit2'      => $digit2,
    )),
  );
}

function captchaHash($result, $oneHourBack = false) {
  $appKey = \Config::get('app.key');
  $ip = \Request::getClientIp();

  $date = \Carbon\Carbon::now();
  $oneHourBack and $date->subHour();
  $date = $date->format('dmyh');

  return md5("$appKey.$ip.$date.$result");
}
