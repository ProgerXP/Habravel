<?php
return array(
  'needJS'                => 'JavaScript is required.',
  'pageTitle'             => 'Habravel',

  'markups'               => array(
    'githubmarkdown'      => 'Markdown',
    'githubmarkdown_help' => '<a href="https://help.github.com/articles/github-flavored-markdown">Docs</a>',
    'uversewiki'          => 'UverseWiki',
    'uversewiki_help'     => '<a href="http://uverse.i-forge.net/wiki/">Docs</a>',
  ),

  'uheader'               => array(
    'logout'              => 'Logout',
    'login'               => 'Who are you?',
    'compose'             => 'Compose Article',
    'profile'             => 'Profile',
  ),

  /***
    Article Routes
   ***/

  'edit'                  => array(
    'title'               => 'Article Modification',
    'titleNew'            => 'Article Composition',
    'preview'             => 'Preview',
    'expand'              => 'Expand editor',
    'markup'              => 'Markup Style:',
    'caption'             => 'Article Title',
    'source'              => 'Source link (e.g. if a translation):',
    'sourceName'          => 'Source name (author)',
    'sourceURL'           => 'Source URL',
    'tags'                => 'Tags:',
    'polls'               => 'Polls:',
    'poll'                => 'Poll question',
    'pollSingle'          => 'Single choice',
    'pollMultiple'        => 'Multiple choice',
    'option'              => 'Poll option',
    'addPoll'             => 'Add another poll',
    'publish'             => 'Publish',

    'placeholders'        => array(
      'Here is a good place to put your first mark.',
      'Toss a coin and choose an opening. “Oh, brave eagle…”',
      'Scratch your head and see if it helps to start up.',
      'Make sure not to fall into meditation thinking about the introduction.',
      'Start writing - but don’t rewrite the first line more than 500 times.',
    ),
  ),

  /***
    User Routes
   ***/

  'login'                 => array(
    'title'               => 'Have we met before?',
    'wrong'               => 'Sorry, can’t find anyone like that.',
    'register'            => 'You can register an account here',
    'login'               => 'Your e-mail or display name:',
    'password'            => 'Your password:',
    'submit'              => 'Log in',
    'remember'            => '…and remember me next time',
  ),

  'register'              => array(
    'title'               => 'Let’s get to know each other!',
    'login1'              => 'Already registered?',
    'login2'              => 'Login here',
    'name'                => 'How to call you on public?',
    'nameHint'            => '(start with a letter, use only A-Z _ 0-9)',
    'email'               => 'What’s your e-mail?',
    'password'            => 'Choose your very secret password',
    'passwordHint'        => '(6 symbols or longer):',
    'submit'              => 'Register',
  ),
);