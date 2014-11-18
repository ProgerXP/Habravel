<?php
// Configuration keys are UWikiSettings (autoload/settings.php) field names.
// Before UWiki can be used it should be placed into 'path' below so that
// is_file($path.'/uversewiki.php') holds true.
//
// In addition to this file you may want to publish UWiki assets (if you're using
// smileys) and put your app-specific UWiki *.conf files into app/config/ENV/.

return array(
  'setup'                 => function (UWikiDocument $doc) {
    // Custom initialization.
  },

  'path'                  => __DIR__.'/../../uwiki/',

  // /xxx   - absolute path.
  // xxx    - relative to your public folder.
  // $xxx   - relative to UverseWiki root.
  'settingsPaths'         => array(
    '$config/',                                       // /.../uversewiki/config/
    '../app/config/uwiki/',                           // /.../laravel/app/config/uwiki/
    '../app/config/'.App::environment().'/uwiki/',    // /.../laravel/app/config/<local>/uwiki/
    '../app/lang/'.Config::get('app.locale').'/uwiki/',   // /.../laravel/app/lang/<ru>/uwiki/
  ),

  'fetchRemoteTitles'     => false,
  'headingMode'           => 'shifted',
  'showComments'          => false,
  'linkExt'               => '',
  // If false anchors won't be prefixed (risk of collisions). # stands for post ID.
  'anchorPrefix'          => 'uw#-',
  'baseURL'               => parse_url(Habravel\url(), PHP_URL_PATH),
  'mediaURL'              => $mediaURL = asset('packages/proger/habravel/uwiki/'),
  'smileyURL'             => $mediaURL,
);