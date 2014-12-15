<?php /*
  - $topPost          - Models\Post instance
  - $parent           - Models\Post instance, may be === $topPost
  - $post             - Models\Post instance, the comment itself
  - $post['author']   - Models\User instance
  - $parent['author'] - Models\User instance
*/

  $textVars = array(
    'user'          => '<b>'.e($post['author']['name']).'</b>',
    'article'       => HTML::link($topPost['url'], $topPost['caption']),
    'links'         => '<a href="'.e($post['url']).'">',
    'linke'         => '</a>',
  );

  if ($parent['id'] !== $topPost['id']) {
    $replyVars = array(
      'user'        => '<b>'.e($parent['author']['name']).'</b>',
      'links'         => '<a href="'.e($parent['url']).'">',
      'linke'       => '</a>',
    );
  }
?>

<!DOCTYPE html>
<html lang="{{{ Config::get('app.locale') }}}">
  <head>
    <meta charset="utf-8">
  </head>
  <body>
    <p>{{ trans('habravel::g.comment.mailText', $textVars) }}</p>
    <article>
      {{ $post['html'] }}
    </article>

    @if ($parent['id'] !== $topPost['id'])
      <hr>
      <p>{{ trans('habravel::g.comment.'.($parent['author']['id'] == $post['author']['id'] ? 'mailTextReplyToSelf' : 'mailTextReply'), $replyVars) }}</p>
      <article>
        {{ $parent['html'] }}
      </article>
    @endif

    <hr>
    <p><small>{{ HTML::link(Habravel\url(), trans('habravel::g.pageTitle')) }}</small></p>
  </body>
</html>