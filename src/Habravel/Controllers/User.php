<?php namespace Habravel\Controllers;

use Habravel\Models\User as UserModel;

class User extends BaseController {
  function __construct() {
    parent::__construct();
    $this->beforeFilter('csrf', array(
      'only' => array(
        'voteUpByName',
        'voteDownByName',
      ),
    ));
  }

  function voteUpByName($name = '') {
    return Poll::voteOn(UserModel::whereName($name)->first(), true);
  }

  function voteDownByName($name = '') {
    return Poll::voteOn(UserModel::whereName($name)->first(), false);
  }

  function showCurrent() {
    user() or App::abort(401);
    return Redirect::to(user()->url());
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

  function showEditProfile() {
    if ($user = user()) {
      return View::make('habravel::user.edit', compact('user'));
    } else {
      App::abort(401);
    }
  }

  function showEditPassword() {
    if ($user = user()) {
      return View::make('habravel::user.editPassword', compact('user'));
    } else {
      App::abort(401);
    }
  }

  function showEditAvatar() {
    if ($user = user()) {
      return View::make('habravel::user.editAvatar', compact('user'));
    } else {
      App::abort(401);
    }
  }

  function editProfile() {
    $user = user();
    $input = Input::only(array('site', 'bitbucket', 'github', 'facebook', 'twitter',
                               'vk', 'jabber', 'skype', 'icq', 'info'));
    $input = array_map('trim', $input);

    $errors = new MessageBag;
    $validation = new UserModel;

    $validation->setRawAttributes($input + $user->getAttributes());
    $validation->validateAndMerge($errors);

    if (count($errors)) {
      return View::make('habravel::user.edit', compact('input', 'errors', 'user'));
    } else {
      foreach ($input as $name => $value) { $user->$name = $value; }
      $user->save();
      return Redirect::to($user->url());
    }
  }

  function editPassword() {
    $user = user();
    $curMatches = (int) \Hash::check(Input::get('password'), $user->password);

    $input = array(
      'currentPassword'           => $curMatches,
      'newPassword'               => Input::get('newPassword'),
      'newPassword_confirmation'  => Input::get('newPassword_confirmation'),
    );

    $rules = array(
      'currentPassword'     => 'in:1',
      'newPassword'         => array_get(UserModel::rules(), 'password').'|confirmed',
    );

    $validator = \Validator::make($input, $rules);

    if ($validator->passes()) {
      $user->password = \Hash::make($input['newPassword']);
      $user->save();
      return Redirect::to($user->url());
    } else {
      return Redirect::back()->withErrors($validator->errors());
    }
  }

  function editAvatar() {
    $user = user();
    $dir = \Habravel\publicPath('avatars/');
    $name = ((int) $user->id).'.png';
    $destination = $dir.$name;

    if (\Input::has('delete')) {
      $file = $destination;
      $name = '';
    } else {
      $rules = array('avatar' => $user::$avatarImageRule);

      $validator = \Validator::make(Input::all(), $rules);

      if ($validator->passes()) {
        $file = \Input::file('avatar');

        if (is_dir($dir) === false) {
          \File::makeDirectory($dir, 0775, true);
        }

        $width = \Config::get('habravel::g.avatarWidth');
        $height = \Config::get('habravel::g.avatarHeight');

        \Habravel\resizeImage($file, $destination, $width, $height);
      } else {
        return Redirect::back()->withErrors($validator->errors());
      }
    }

    if (is_file($file)) {
      \File::delete($file);
    }

    $user->avatar = $name;
    $user->save();

    return Redirect::to($user->url());
  }

  // GET input:
  // - back=rel/url       - optional; relative to Habravel\url()
  // - bad=0/1            - optional
  function showLogin() {
    if (user()) {
      return Redirect::to(user()->url());
    } else {
      $vars = array(
        'backURL'         => Input::get('back'),
        'badLogin'        => Input::get('bad'),
        'badRestoreLink'  => Input::get('badrepw'),
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

  function showRemindPassword() {
    if (user()) {
      return Redirect::to(user()->url());
    } else {
      \Habravel\Models\RemindPassword::expired()->delete();
      return View::make('habravel::user.remindPassword');
    }
  }

  function remindPassword() {
    $input = Input::get();
    $rules = \Habravel\Models\RemindPassword::rules();

    $validator = \Validator::make($input, $rules);

    if ($validator->passes()) {
      $email = array_get($input, 'email');
      $subject = trans('habravel::g.remindPassword.mailSubject');
      $token = str_random(64);
      $data = array('url' => url()."/resetpw/$token");

      \Mail::queue('habravel::email.remindPassword', $data,
        function ($message) use ($email, $subject) {
          $message->to($email)->subject($subject);
        }
      );

      $reminder = new \Habravel\Models\RemindPassword;
      $reminder->token = $token;
      $reminder->email = $email;
      $reminder->save();

      return View::make('habravel::user.remindPassword', array('sent' => $email));
    } else {
      return Redirect::back()->withErrors($validator->errors());
    }
  }

  function showResetPassword($token) {
    if (user()) {
      return Redirect::to(user()->url());
    } else {
      $obliviousUser = \Habravel\Models\RemindPassword::token($token)->first();
      if ($obliviousUser){
        return View::make('habravel::user.resetPassword', compact('token'));
      } else {
        return Redirect::to(\Habravel\url().'/login?badrepw=1');
      }
    }
  }

  function resetPassword($token) {
    $input = array(
      'newPassword'              => Input::get('newPassword'),
      'newPassword_confirmation' => Input::get('newPassword_confirmation'),
    );

    $rules = array(
      'newPassword' => array_get(UserModel::rules(), 'password').'|confirmed',
    );

    $validator = \Validator::make($input, $rules);

    if ($validator->passes()) {
      $obliviousUser = \Habravel\Models\RemindPassword::token($token)->first();

      $user = UserModel::whereEmail($obliviousUser->email)->first();

      $user->password = \Hash::make($input['newPassword']);
      $user->save();

      user(array('email' => $obliviousUser->email, 'password' => $input['newPassword']));

      $obliviousUser->delete();

      return Redirect::to($user->url());
    } else {
      return Redirect::back()->withErrors($validator->errors());
    }
  }
}