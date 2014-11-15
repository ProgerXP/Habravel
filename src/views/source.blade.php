<?php /*
  - $post             - Models\Post instance with loaded author, tags
*/?>

@extends('habravel::page')
<?php $pageTitle = $post->caption?>

@section('content')
  @include('habravel::part.postTitle', compact('post'), array())

  <header class="hvl-psource-header">
    <p>
      <b>{{{ trans('habravel::g.source.size') }}}</b>
      {{ trans('habravel::g.post.size', array('chars' => $post->size(), 'words' => $post->wordCount())) }}
    </p>

    <p>
      <b>{{{ trans('habravel::g.source.markup') }}}</b>
      <a class="hvl-markup-help" href="{{{ Habravel\url()."/markup/$post->markup" }}}">
        {{{ trans('habravel::g.markups.'.$post->markup) }}}
      </a>
    </p>

    <p>
      <?php $size = strlen($post->text)?>
      <a href="{{{ Habravel\url() }}}/source/{{{ $post->id }}}?dl=1">
        {{{ trans('habravel::g.source.dl', array('size' => $size >= 1024 ? round($size / 1024).' KiB' : "$size B")) }}}
      </a>
      &darr;
      &nbsp;
      &nbsp;
      <a href="{{{ $post->url() }}}">
        {{{ trans('habravel::g.source.see') }}}
      </a>
      &rarr;
    </p>
  </header>

  <textarea cols="100" rows="30" class="hvl-psource-source" readonly="readonly"
            data-sqa="r - w$body{pb} - wr$~*{ho} -">{{{ $post->text }}}</textarea>
@stop