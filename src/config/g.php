<?php
return array(
  // Root URL for routes relative to Laravel's app URL. No trailing '/'.
  'rootURL'               => '',

  // Read an input variable.
  'input'                 => function ($name, $default) {
    return Input::get($name, $default);
  },

  // Return null or Habravel\User instance. Result is cached.
  'user'                  => function () {
    if ($user = Auth::user()) {
      return Habravel\User::find($user->id);
    }
  },

  'markups'               => array(
    'githubmarkdown'      => 'Habravel\\GitHubMarkdown',
    'uversewiki'          => 'Habravel\\UverseWiki',
  ),
);