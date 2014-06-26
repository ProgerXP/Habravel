<?php
return array(
  'needJS'                => 'JavaScript is required.',
  'pageTitle'             => 'Habravel',

  'markups'               => array(
    'githubmarkdown'      => 'Markdown',
    'uversewiki'          => 'UverseWiki',
  ),

  'pages'                 => array(
    'back'                => 'Back',
    'next'                => 'Forward',
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
    'markup'              => 'Markup style:',
    'caption'             => 'Article title',
    'source'              => 'Source link (e.g. if a translation):',
    'sourceName'          => 'Source name (author)',
    'sourceURL'           => 'Source URL',
    'tags'                => 'Tags:',
    'tagHelp'             => 'Click on a tag below to assign',
    'newTag'              => '+ custom tag',
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

  'post'                  => array(
    'edit'                => 'Edit',
    'source'              => 'Original source',
    'author'              => 'Post author',
    'views'               => 'View count, all time',
    'size'                => ':chars characters; :words words',
    'commentCount'        => 'Number of comments',
    'pubTime'             => 'Publication date',
    'poll'                => 'Poll:',
    'abstain'             => 'Abstain',
    'vote'                => 'Vote',
    'voteAll'             => 'Vote for :count polls',
    'comments'            => 'Comments',
  ),

  'ncomment'              => array(
    'title'               => 'Post a Comment',
    'markup'              => 'Markup style:',
    'text'                => 'What\'s on your mind?',
    'submit'              => 'Post',
    'preview'             => 'Preview',
  ),

  'source'                => array(
    'size'                => 'Text statistics:',
    'markup'              => 'Markup style used:',
    'dl'                  => 'Download (:size KiB)',
    'see'                 => 'View normal',
  ),

  'comment'               => array(
    'reply'               => 'Reply',
    'anchor'              => 'Link to this comment',
  ),

  'posts'                 => array(
    'title'               => 'Articles',
    'bestDay'             => 'Best of the day',
    'bestWeek'            => 'Best of the week',
    'bestEver'            => 'Best articles, ever',
    'none'                => 'End of the road. No posts here.',
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

  'user'                  => array(
    'posts'               => 'Articles',
    'allPosts'            => 'There are more articles',
    'comments'            => 'Comments',
    'allComments'         => 'Even more comments',
    'regTime'             => 'Joined at: ',
    'loginTime'           => 'Last seen around: ',
  ),
);