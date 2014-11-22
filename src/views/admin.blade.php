@extends('habravel::page')

@section('content')
  {{ Form::open(array('url' => Habravel\url().'/admin/resethtml')) }}
    {{ Form::token() }}
    {{ Form::submit('Clear post HTMLs, regenerate on display') }}
  {{ Form::close() }}

  {{ Form::open(array('url' => Habravel\url().'/admin/regenhtml')) }}
    {{ Form::token() }}
    {{ Form::submit('Regenerate post HTMLs') }}
  {{ Form::close() }}
@stop