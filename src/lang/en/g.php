<?php
return array(
  'locale'                => 'en_US.UTF-8',
  'number'                => array('.', ','),
  'needJS'                => 'JavaScript is required.',
  'pageTitle'             => 'Habravel',
  'credit'                => 'Powered by Habravel',

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
    'drafts'              => 'Drafts',
    'compose'             => 'Compose Article',
    'profile'             => 'Profile',
  ),

  404                           => array(
    'title'                     => 'Not Found',
    'text'                      => 'Requested resource does not exist.',
  ),

  /***
    Article Routes
   ***/

  'edit'                  => array(
    'title'               => 'Article Modification',
    'titleNew'            => 'Article Composition',
    'preview'             => 'Preview',
    'blocked'             => 'Preview popup window blocked :(',
    'expand'              => 'Expand editor',
    'markup'              => 'Markup style:',
    'caption'             => 'Article title',
    'source'              => 'Origin link or translation source:',
    'sourceName'          => 'Source name or author',
    'sourceURL'           => 'http://',
    'isTranslation'       => 'Is your article a translation of this page?',
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
    'save'                => 'Save to drafts',

    'placeholders'        => array(
      'Here is a good place to put your first mark.',
      'Toss a coin and choose an opening. “Oh, brave eagle…”',
      'Scratch your head and see if it helps to start up.',
      'Make sure not to fall into meditation thinking about the introduction.',
      'Start writing - but don’t rewrite the first line more than 500 times.',
    ),
  ),

  'post'                  => array(
    'more'                => 'Read more',
    'edit'                => 'Edit',
    'source'              => 'Original source',
    'author'              => 'Post author',
    'views'               => 'View count, all time',
    'size'                => ':chars characters; :words words',
    'commentCount'        => 'Number of comments',
    'pubTime'             => 'Publication date: :date',
    'poll'                => 'Poll:',
    'abstain'             => 'Abstain',
    'afterVote'           => 'Do you think this post has been useful?',
    'afterVoteUp'         => 'Yes',
    'afterVoteDown'       => 'Nope',
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
    'mailSubject'         => 'New reply to «:0»',
    'mailText'            => ':user has just posted a :linksnew comment:linke on «:article»:',
    'mailTextReply'       => '…in reply to :user:\'s :linkscomment:linke:',
    'mailTextReplyToSelf' => '…in reply to his own :linkscomment:linke:',
  ),

  'posts'                 => array(
    'title'               => 'Articles',
    'titleTag'            => '“:tag”',
    'bestDay'             => 'Best of the day',
    'bestWeek'            => 'Best of the week',
    'bestEver'            => 'Best articles, ever',
    'userPosts'           => 'Articles by :name',
    'userComments'        => 'Comments by :name',
    'drafts'              => 'Drafts',
    'none'                => 'End of the road. No posts here.',
  ),

  /***
    User Routes
   ***/

  'login'                 => array(
    'title'               => 'Have we met before?',
    'wrong'               => 'Sorry, can’t find anyone like that.',
    'wrongRestore'        => 'It looks like password reset link has expired. Try again – but be more responsive.',
    'register'            => 'You can register an account here',
    'login'               => 'Your e-mail or display name:',
    'password'            => 'Your password',
    'remindPassword'      => '(:0forgot?:1):',
    'submit'              => 'Log in',
    'remember'            => '…and remember me next time',
  ),

  'register'              => array(
    'title'               => 'Let’s get to know each other!',
    'login1'              => 'Already registered?',
    'login2'              => 'Login here',
    'name'                => 'How to call you on public?',
    'nameHint'            => '(start with a letter, use only :chars)',
    'email'               => 'What’s your e-mail?',
    'password'            => 'Choose your very secret password',
    'passwordHint'        => '(:min symbols or longer):',
    'captcha'             => 'You sure are human?',
    'captchaHint'         => '(the answer is an integer)',
    'submit'              => 'Register',
  ),

  'user'                  => array(
    'posts'               => 'Articles',
    'allPosts'            => 'There are more articles',
    'comments'            => 'Comments',
    'writeFirstPost'      => 'Compose your first article',
    'allComments'         => 'Even more comments',
    'regTime'             => 'Joined at: ',
    'loginTime'           => 'Last seen around: ',
  ),

  'profile'                     => array(
    'editTitle'                 => 'Profile info change',
    'editPasswordTitle'         => 'Password change',
    'editAvatarTitle'           => 'Avatar change',
    'edit'                      => 'Edit profile',
    'editAvatar'                => '...avatar',
    'editPassword'              => '...password',
    'submit'                    => 'Save profile',
    'deleteAvatar'              => 'Delete avatar',

    'site'                      => 'Website:',
    'bitbucket'                 => 'Bitbucket:',
    'github'                    => 'GitHub:',
    'facebook'                  => 'Facebook:',
    'twitter'                   => 'Twitter:',
    'vk'                        => 'VKontakte:',
    'jabber'                    => 'Jabber:',
    'skype'                     => 'Skype:',
    'icq'                       => 'ICQ:',
    'info'                      => 'A few extra words:',
    'sitePH'                    => 'http://',
    'bitbucketPH'               => 'bitbucket-login',
    'githubPH'                  => 'github-login',
    'facebookPH'                => 'facebook.login',
    'twitterPH'                 => 'twitter-login',
    'vkPH'                      => 'vk.login',
    'jabberPH'                  => 'jabber@email.com',
    'skypePH'                   => 'skype_login',
    'icqPH'                     => '1234567',

    'avatarFile'                => 'Pick a file (JPEG, PNG or GIF):',
    'oldPassword'               => 'Remember your current password?',
    'newPassword'               => 'New password',
    'newPassword_confirmation'  => 'And repeat:',
  ),

  'remindPassword'              => array(
    'title'                     => 'Forgot your password? Common thing…',
    'wrongEmail'                => 'No such e-mail. Are you sure you\'ve signed up with us?',
    'email'                     => 'What was the e-mail you\'ve signed up with?',
    'submit'                    => 'Restore access',
    'sent'                      => 'We\'ve sent instructions to :email – follow the link there to reset your password.',
    'mailSubject'               => 'Password reset',
    'mailText'                  => 'Follow this link to reset your password:',
  ),

  'resetPassword'               => array(
    'title'                     => 'Almost there! Pick your new password',
    'submit'                    => 'Save password and log in',
  ),

  'sidebar'                     => array(
    'topUsers'                  => 'Best People',
    'topPosts'                  => 'Best Reading',
    'tagPool'                   => 'Tags',
    'postSource'                => 'Text source of this post',
  ),
);