<?php
return array(
  // Root URL for routes relative to Laravel's app URL. No trailing '/'.
  'rootURL'               => '',
  // CSRF token regeneration timeout in seconds.
  'csrfRegenTime'         => 3 * 3600,

  // Read an input variable or all variables if $name is omitted.
  'input'                 => function ($name = null, $default = null) {
    return Input::get($name, $default);
  },

  // If $login is not given - return null or Habravel\User instance that is
  // currently logged in.
  //
  // If $login is given it's an array with 'password', and 'remember' keys
  // and either 'email' or 'name'. Return null or Habravel\User.
  //
  // If $login is === false then current client should be logged out.
  'user'                  => function ($login = null) {
    if (is_array($login)) {
      return Auth::attempt($login) ? Habravel\User::find(Auth::user()->id) : null;
    } elseif ($login === false) {
      Auth::logout();
    } elseif (!func_num_args() and $user = Auth::user()) {
      return Habravel\User::find($user->id);
    }
  },

  'password'              => function ($plain, $hash = null) {
    if (func_num_args() < 2) {
      return \Hash::make($plain);
    } else {
      return \Hash::check($plain, $hash);
    }
  },

  'markups'               => array(
    // Download UverseWiki archive from http://uverse.i-forge.net/wiki and extract
    // it to the configured path (habravel::uversewiki.path, by default into
    // vendor/proger/habravel/uwiki/ directory).
    'uversewiki'          => 'Habravel\\UverseWiki',

    // Markdown dependency must be installed (see Composer suggestions).
    //'githubmarkdown'      => 'Habravel\\GitHubMarkdown',
  ),

  'userPerms'             => array(
    'can.post', 'can.editSelf', 'can.reply', 'can.vote',
  ),
);