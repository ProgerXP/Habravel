<?php /*
  Accepts all input of views/post.
*/?>

@extends('habravel::page')

@section('content')
  @if (empty($errors))
    @include('habravel::part.postTitle', compact('post'), array())
    @include('habravel::part.post', array('classes' => 'hvl-ppreview'))
  @else
    {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
  @endif
@stop