<?php
return array(
  // Root URL for routes relative to Laravel's app URL. No trailing '/'.
  'rootURL'               => '',

  // If $login is not given - return null or Habravel\Models\User instance that is
  // currently logged in.
  //
  // If $login is given it's an array with 'password', and 'remember' keys
  // and either 'email' or 'name'. Return null or Habravel\Models\User.
  //
  // If $login is === false then current client should be logged out.
  'user'                  => function ($login = null) {
    if (is_array($login)) {
      return Auth::attempt($login) ? Habravel\Models\User::find(Auth::user()->id) : null;
    } elseif ($login === false) {
      Auth::logout();
    } elseif (!func_num_args() and $user = Auth::user()) {
      return Habravel\Models\User::find($user->id);
    }
  },

  'markups'               => array(
    // Download UverseWiki archive from http://uverse.i-forge.net/wiki and extract
    // it to the configured path (habravel::uversewiki.path, by default into
    // vendor/proger/habravel/uwiki/ directory).
    //'uversewiki'          => 'Habravel\\Markups\\UverseWiki',

    // Markdown dependency must be installed (see Composer suggestions).
    //'githubmarkdown'      => 'Habravel\\Markups\\GitHubMarkdown',
  ),

  'userPerms'             => array(
    'can.post', 'can.editSelf', 'can.reply', 'can.vote',
  ),

  'minPassword'           => 8,
  'introWords'            => 200,
  'avatarWidth'           => 150,
  'avatarHeight'          => 150,

  // Hardlinks on Windows may cause PHP file functions to fail on such a path.
  // In this case point it to base_path('vendor/proger/habravel/public/').
  'publicPath'            => public_path('packages/proger/habravel/'),

  // Special tags that have extra functionality. Since their IDs can be arbitrary
  // they are matched by exact name (caption).
  'tags'                  => array(
    'draft'               => 'draft',
    'tl'                  => 'tl',
  ),
);