<?php
// Configuration for HyperSafe: https://github.com/ProgerXP/HyperSafe
$hs = new HyperSafe;
return array(
  'lineBreaks'            => "\n",
  'keepComments'          => false,
  // Just keeping defaults.
  'tags'                  => $hs->tags,
  'checks'                => $hs->checks,
  'styles'                => $hs->styles,

  // Habravel-specific.
  'hvlLogWarnings'        => true,
);