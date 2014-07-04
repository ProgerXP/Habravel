<?php
// Configuration keys are UWikiSettings (autoload/settings.php) field names.
// Before UWiki can be used it should be placed into 'path' below so that
// is_file($path.'/uversewiki.php') holds true.
//
// In addition to this file you may want to publish UWiki assets (if you're using
// smileys) and put your app-specific UWiki *.conf files into app/config/ENV/.

return array(
  'setup'                 => function (UWikiDocument $doc) { },
  'path'                  => __DIR__.'/../../uwiki/',
  // '/xxx' - absolute path.
  //  'xxx' - relative to Laravel root.
  // '$xxx' - relative to UverseWiki root.
  'settingsPaths'         => array('$config/', 'app/config/'.App::environment().'/'),
  'fetchRemoteTitles'     => false,
  'headingMode'           => 'shifted',
  'showComments'          => false,
  'linkExt'               => '',
  // If false anchors won't be prefixed (risk of collisions).
  'anchorPrefix'          => 'uw-',
  'baseURL'               => Habravel\Core::url(false),
  'mediaURL'              => $mediaURL = asset('packages/proger/habravel/uwiki/'),
  'smileyURL'             => $mediaURL,
);