<?php /*
  - $user             - Models\User instance
  - $errors           - optional; MessageBag
*/

  $fields = array(
    'site'        => 'url',
    'bitbucket'   => 'text',
    'github'      => 'text',
    'facebook'    => 'text',
    'twitter'     => 'text',
    'vk'          => 'text',
    'jabber'      => 'email',
    'skype'       => 'text',
    'icq'         => 'text',
  );

  $fieldIndex = 0;
?>

@extends('habravel::page')

@section('content')
  <h1 class="hvl-h1">{{{ trans('habravel::g.profile.editTitle') }}}</h1>

  @if (isset($errors))
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif

  <form method="post" action="{{{ Habravel\url().'/~/edit' }}}"
        class="hvl-puser-edit">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

    <div>
      @foreach ($fields as $field => $type)
        @if (++$fieldIndex % 6 == 0)
          </div><div>
        @endif

        <p class="hvl-puser-edit-line">
          <b>{{{ trans("habravel::g.profile.$field") }}}</b>
          <input type="{{{ $type }}}" class="hvl-input"
                 name="{{{ $field }}}" value="{{{ $user->$field }}}"
                 @if ($fieldIndex === 1) autofocus="autofocus" @endif
                 placeholder="{{{ trans("habravel::g.profile.{$field}PH") }}}">
        </p>
      @endforeach
    </div>

    <div>
      <p>
        <b>{{{ trans("habravel::g.profile.info") }}}</b>
      </p>
      <p>
        <textarea name="info" class="hvl-input" cols="10" rows="6">{{{ $user->info }}}</textarea>
      </p>
    </div>

    <p class="hvl-puser-edit-btn">
      <button type="submit" class="hvl-btn">
        {{{ trans('habravel::g.profile.submit') }}}
      </button>
    </p>
  </form>
@stop