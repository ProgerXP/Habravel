<?php /*
  - $title            - string
  - $posts            - array of Post instances
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  <h1 class="hvl-h1">{{{ $title }}}</h1>

  @foreach ($posts as $post)
    @include('habravel::part.postTitle', compact('post'), array('level' => 2, 'link' => true))
    @include('habravel::part.post', compact('post'))
  @endforeach
@stop