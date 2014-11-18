<?php
// Configuration for HyperSafe: https://github.com/ProgerXP/HyperSafe
$hs = new HyperSafe;
return array(
  'lineBreaks'            => "\n",
  'keepComments'          => false,

  'tags'                  => $hs->tags + array(
    // Example (see HyperSafe docs for configuration details):
    //'style'               => array('type mime'),
  ),

  'checks'                => $hs->checks + array(
    // Keeping defaults.
  ),

  'styles'                => $hs->styles + array(
  ),

  // Habravel-specific.
  'hvlLogWarnings'        => true,
);