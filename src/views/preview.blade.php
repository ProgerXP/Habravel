<?php /*
  Accepts all input of views/post.
*/?>

@if (empty($errors))
  @include('habravel::post', array('classes' => 'hvl-ppreview'))
@else
  @section('content')
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @stop

  @include('habravel::page')
@endif