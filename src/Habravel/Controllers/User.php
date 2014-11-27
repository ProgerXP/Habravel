<?php namespace Habravel\Controllers;

use Habravel\Models\User as UserModel;
use Habravel\Models\UserInfo as UserInfoModel;

class User extends BaseController {
  function __construct() {
    parent::__construct();
    $this->beforeFilter('csrf', array(
    'only' => array(
        'voteUpByname',
        'voteDownByName',
        'editMyInfo',
       )
    ));
  }

  function voteUpByName($name = '') {
    return Poll::voteOn(UserModel::whereName($name)->first(), true);
  }

  function voteDownByName($name = '') {
    return Poll::voteOn(UserModel::whereName($name)->first(), false);
  }

  function showCurrent($view = 'user.current') {
    if (user()) {
      $user = user();
      $input = array();
      return View::make('habravel::'.$view, compact('user', 'input'));
    } else {
      App::abort(401);
    }
  }

  function showByName($name = '') {
    $user = UserModel::whereName($name)->first();
    return $this->show($user);
  }

  function show($idOrModel = 0) {
    if ($user = UserModel::find($idOrModel)) {
      return View::make('habravel::user', compact('user'));
    } else {
      App::abort(404);
    }
  }

  function logout() {
    user(false);
    return Redirect::to(\Habravel\url());
  }

  function showEditMyInfo() {
    return $this->showCurrent('edit.profile');
  }

  function showChangeMyPassword() {
    return $this->showCurrent('edit.password');
  }

  function showChangeMyAvatar() {
    return $this->showCurrent('edit.avatar');
  }

  function editMyInfo() {
    $rules = array(
      'site'                => 'url|max:128',
      'bitbucket'           => 'url|max:128',
      'github'              => 'url|max:128',
      'facebook'            => 'url|max:128',
      'twitter'             => 'url|max:128',
      'vk'                  => 'url|max:128',
      'jabber'              => 'email|max:128',
      'skype'               => 'max:64',
      'icq'                 => 'integer',
      'info'                => 'max:5000',
    );

    $user = user();
    $input = Input::get();
    $validator = \Validator::make($input, $rules);

    $errors = $validator->errors();

    if ($validator->passes()) {
      $info = UserInfoModel::firstOrNew(array('user_id' => user()->id));

      $info->user_id = user()->id;
      $info->site = Input::get('name');
      $info->bitbucket = Input::get('bitbucket');
      $info->github = Input::get('github');
      $info->facebook = Input::get('facebook');
      $info->twitter = Input::get('twitter');
      $info->vk = Input::get('vk');
      $info->jabber = Input::get('jabber');
      $info->skype = Input::get('skype');
      $info->icq = Input::get('icq');
      $info->info = Input::get('info');
      $info->save();

      return Redirect::to('~');
    } else {
      return View::make('habravel::edit.profile', compact('input', 'errors', 'user'));
    }
  }

  function changeMyPassword() {
    $accepted = \Hash::check(Input::get('password'), user()->password);
    $minPassword = \Config::get('habravel::g.minPassword');
    $userPassword = user()->password;

    $input = array(
      'password'                 => Input::get('password'),
      'hash'                     => $accepted,
      'newPassword'              => Input::get('newPassword'),
      'newPassword_confirmation' => Input::get('newPassword_confirmation'),
    );
    $rules = array(
      'password'                 => "required|min:$minPassword",
      'hash'                     => 'accepted',
      'newPassword'              => "required|confirmed|min:$minPassword",
      'newPassword_confirmation' => "required|min:$minPassword",
    );
    $validator = \Validator::make($input, $rules);

    if ($validator->passes()) {
      $user = UserModel::find(user()->id);
      $user->password = \Hash::make($input['newPassword']);
      $user->save();
      return Redirect::to('~');
    } else {
      return Redirect::back()->withErrors($validator->errors());
    }
  }

  function changeMyAvatar() {
    $rules = array(
        'avatar' => 'required|mimes:jpeg,gif,png|max:128',
     );
    $validator = \Validator::make(Input::all(), $rules);

    if ($validator->passes()) {
      UserModel::saveAvatar(user()->id);
      return Redirect::to('~');
    } else {
      return Redirect::back()->withErrors($validator->errors());
    }
  }

  // GET input:
  // - back=rel/url       - optional; relative to Habravel\url()
  // - bad=0/1            - optional
  function showLogin() {
    if ($user = user()) {
      return Redirect::to($user->url());
    } else {
      $vars = array(
        'backURL'         => Input::get('back'),
        'badLogin'        => Input::get('bad'),
      );

      return View::make('habravel::login', $vars);
    }
  }

  // POST input:
  // - email=a@b.c        - required if name/login not given
  // - name=nick          - required if name/login not given
  // - login=...          - required if email/name not given; if has '@' is
  //                        looked up as 'email', otherwise looked up by 'name'
  // - password=...
  // - remember=0/1       - optional; defaults to 0
  // - back=rel/url       - optional; relative to Habravel\url()
  function login() {
    \Session::regenerate();   // prevent session fixation.
    $input = Input::get();
    $back = $input['back'] = \Habravel\referer(array_get($input, 'back'));

    $auth = array_only($input, array('email', 'password', 'remember'));
    if (!isset($auth['email'])) {
      $login = array_get($input, 'login');
      if (strrchr($login, '@')) {
        $auth['email'] = $login;
      } else {
        $auth['name'] = $login ?: array_get($input, 'name');
      }
    }

    if (empty($auth['password']) or (empty($auth['email']) and empty($auth['name']))) {
      Input::merge(array('back' => $back));
      return $this->showLogin();
    } elseif ($user = user($auth)) {
      $user->loginTime = new \Carbon\Carbon;
      $user->loginIP = Request::getClientIp();
      return Redirect::to( array_get($input, 'back', $user->url()) );
    } else {
      Input::merge(array('bad' => 1, 'back' => $back));
      return $this->showLogin();
    }
  }

  function showRegister() {
    user(false);
    return View::make('habravel::register', array('input' => array()));
  }

  // POST input:
  // - password=...       - required
  // - email=a@b.c        - required
  // - name=nick          - required
  function register() {
    \Session::regenerate();   // prevent session fixation.

    $user = new UserModel;
    $input = Input::get();
    $errors = new MessageBag;

    $user->name = array_get($input, 'name');
    $user->email = array_get($input, 'email');
    $user->password = \Hash::make(array_get($input, 'password'));
    $user->regIP = Request::getClientIp();

    $copy = new UserModel;
    $copy->setRawAttributes(array_only($input, 'password') + $user->getAttributes());
    $copy->validateAndMerge($errors);

    if (count($errors)) {
      return View::make('habravel::register', compact('input', 'errors'));
    } else {
      if (!$user->poll) {
        $poll = new \Habravel\Models\Poll;
        // System poll captions don't matter, just for pretty database output.
        $poll->caption = '~'.$user->name;
        $poll->save();
        $user->poll = $poll->id;
      }

      $user->save();

      user(array('id' => $user->id, 'password' => $input['password']));
      return Redirect::to($user->url());
    }
  }
}