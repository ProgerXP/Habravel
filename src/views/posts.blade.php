@extends('habravel::page')

@section('content')
  @foreach ($posts as $post)
    <h1>{{{ $post['caption'] }}}</h1>
  @endforeach
@stop