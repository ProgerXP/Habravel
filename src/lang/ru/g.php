<?php
return array(
  'locale'                      => 'ru_RU.UTF-8',
  'number'                      => array(',', ' ' /*nbsp*/),
  'needJS'                      => 'Нужен JavaScript.',
  'pageTitle'                   => 'Habravel',
  'credit'                      => 'Движется на Habravel',

  'markups'                     => array(
    'githubmarkdown'            => 'Markdown',
    'uversewiki'                => 'UverseWiki',
  ),

  'pages'                       => array(
    'back'                      => 'Назад',
    'next'                      => 'Дальше',
  ),

  'uheader'                     => array(
    'logout'                    => 'Выйти',
    'login'                     => 'Может войдёшь?',
    'drafts'                    => 'Черновики',
    'compose'                   => 'Написать статью',
    'profile'                   => 'Профиль',
  ),

  /***
    Article Routes
   ***/

  'edit'                        => array(
    'title'                     => 'Правка статьи',
    'titleNew'                  => 'Написание статьи',
    'preview'                   => 'Предпросмотр',
    'blocked'                   => 'Браузер заблокировал новое окно :(',
    'expand'                    => 'Скрыть лишнее',
    'markup'                    => 'Разметка:',
    'caption'                   => 'Заголовок',
    'source'                    => 'Источник или оригинал, если перевод:',
    'sourceName'                => 'Имя оригинала или автор',
    'sourceURL'                 => 'http://',
    'isTranslation'             => 'Ваша статья — перевод?',
    'tags'                      => 'Теги:',
    'tagHelp'                   => 'Нажми на тег внизу для выбора',
    'newTag'                    => '+ свой тег',
    'polls'                     => 'Голосование:',
    'poll'                      => 'Вопрос',
    'pollSingle'                => 'Один ответ',
    'pollMultiple'              => 'Несколько',
    'option'                    => 'Вариант ответа',
    'addPoll'                   => 'Ещё один опрос',
    'publish'                   => 'Опубликовать',
    'save'                      => 'Сохранить в черновики',

    'placeholders'              => array(
      'Не думай над вступлением — просто начни писать.',
      'Орёл или решко? Монетка поможет выбрать хорошее вступление. «Вот в мои годы…»',
      'Попробуй почесать в затылке, если не знаешь как начать.',
      'Если будешь долго думать над вступлением — рискуешь впасть в медитацию.',
      'Можешь начинать писать — только не переписывай вступление большее 500 раз.',
    ),
  ),

  'post'                        => array(
    'more'                      => 'Читать далее',
    'edit'                      => 'Править',
    'source'                    => 'Источник',
    'author'                    => 'Автор статьи',
    'views'                     => 'Число просмотров за всё время',
    'size'                      => 'Символов — :chars, слов — :words',
    'commentCount'              => 'Число комментариев',
    'pubTime'                   => 'Дата публикации: :date',
    'poll'                      => 'Опрос:',
    'abstain'                   => 'Воздержаться',
    'vote'                      => 'Голосовать',
    'voteAll'                   => 'Голосовать за все опросы (:count)',
    'comments'                  => 'Комментарии',
  ),

  'ncomment'                    => array(
    'title'                     => 'Написать комментарий',
    'markup'                    => 'Разметка:',
    'text'                      => 'Какие мысли на этот счёт?',
    'submit'                    => 'Отправить',
    'preview'                   => 'Предпросмотр',
  ),

  'source'                      => array(
    'size'                      => 'Статистика:',
    'markup'                    => 'Разметка:',
    'dl'                        => 'Скачать (:size)',
    'see'                       => 'Просмотр',
  ),

  'comment'                     => array(
    'reply'                     => 'Ответить',
    'anchor'                    => 'Ссылка на этот отзыв',
  ),

  'posts'                       => array(
    'title'                     => 'Статьи',
    'titleTag'                  => '«:tag»',
    'bestDay'                   => 'Лучшее за сутки',
    'bestWeek'                  => 'Лучшее за неделю',
    'bestEver'                  => 'Лучшее всех времён',
    'userPosts'                 => 'Статьи :name',
    'userComments'              => 'Комментарии :name',
    'drafts'                    => 'Черновики',
    'none'                      => 'Сообщения закончились. Дороги дальше нет.',
  ),

  /***
    User Routes
   ***/

  'login'                       => array(
    'title'                     => 'Мы уже встречались?',
    'wrong'                     => 'Извини, не могу тебя найти.',
    'wrongRestore'              => 'Похоже, ссылка на восстановление уже истекла. Попробуй снова — только будь расторопнее.',
    'register'                  => 'Можешь зарегистрироваться тут',
    'login'                     => 'Твой e-mail или ник:',
    'password'                  => 'Твой пароль',
    'remindPassword'            => '(:0забыл?:1):',
    'submit'                    => 'Войти',
    'remember'                  => '…и запомнить меня',
  ),

  'register'                    => array(
    'title'                     => 'Пора познакомиться!',
    'login1'                    => 'Уже регистрировался?',
    'login2'                    => 'Войди в систему',
    'name'                      => 'Как тебя называть на людях?',
    'nameHint'                  => '(начни с буквы, используй только :chars)',
    'email'                     => 'Куда слать корреспонденцию?',
    'password'                  => 'Введи свой самый-самый секретный пароль',
    'passwordHint'              => '(:min символов или больше):',
    'captcha'                   => 'А ты точно не робот?',
    'captchaHint'               => '(ответ — целое число)',
    'submit'                    => 'Готово',
  ),

  'user'                        => array(
    'posts'                     => 'Статьи',
    'allPosts'                  => 'Есть ещё статьи',
    'comments'                  => 'Комментарии',
    'allComments'               => 'Ещё больше отзывов',
    'regTime'                   => 'Вступил в наши ряды: ',
    'loginTime'                 => 'Последний раз среди нас: ',
  ),

  'profile'                     => array(
    'editTitle'                 => 'Редактирование профиля',
    'editPasswordTitle'         => 'Смена пароля',
    'editAvatarTitle'           => 'Смена аватара',
    'edit'                      => 'Редактировать профиль',
    'editAvatar'                => '...аватар',
    'editPassword'              => '...пароль',
    'submit'                    => 'Сохранить профиль',
    'deleteAvatar'              => 'Удалить аватар',

    'site'                      => 'Сайт:',
    'bitbucket'                 => 'Bitbucket:',
    'github'                    => 'GitHub:',
    'facebook'                  => 'Facebook:',
    'twitter'                   => 'Twitter:',
    'vk'                        => 'ВКонтакте:',
    'jabber'                    => 'Jabber:',
    'skype'                     => 'Skype:',
    'icq'                       => 'ICQ:',
    'info'                      => 'Немного о себе:',
    'sitePH'                    => 'http://',
    'bitbucketPH'               => 'bitbucket-login',
    'githubPH'                  => 'github-login',
    'facebookPH'                => 'facebook.login',
    'twitterPH'                 => 'twitter-login',
    'vkPH'                      => 'vk.login',
    'jabberPH'                  => 'jabber@email.ru',
    'skypePH'                   => 'skype_login',
    'icqPH'                     => '1234567',

    'avatarFile'                => 'Выбери файл (JPEG, PNG или GIF):',
    'oldPassword'               => 'Помнишь свой текущий пароль?',
    'newPassword'               => 'Новый пароль',
    'newPassword_confirmation'  => 'И ещё раз:',
  ),

  'remindPassword'              => array(
    'title'                     => 'Забыл пароль? Бывает…',
    'wrongEmail'                => 'Такого e-mail у нас нет. Ты точно регистрировался?',
    'email'                     => 'С каким e-mail ты регистрировался?',
    'submit'                    => 'Восстановить',
    'sent'                      => 'Мы отправили инструкцию на :email — перейди по ссылке в письме и восстанови доступ.',
    'mailSubject'               => 'Сброс пароля',
    'mailText'                  => 'Для сброса пароля проследуй по ссылке:',
  ),

  'resetPassword'               => array(
    'title'                     => 'Полдела сделано! Введи свой новый пароль',
    'submit'                    => 'Сохранить пароль и войти',
  ),

  'sidebar'                     => array(
    'topUsers'                  => 'Лучшие',
    'topPosts'                  => 'Лучшее',
    'tagPool'                   => 'Теги',
    'postSource'                => 'Исходник статьи',
  ),
);