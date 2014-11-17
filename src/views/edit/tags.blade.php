<?php /*
  - $post             - Models\Post instance
  - $tags             - array of Models\Tag
  - $tagPool          - array of Models\Tag
*/?>

<div class="hvl-pedit-ctl hvl-pedit-tags" data-sqa="wr mnh: Math.max(mnh, h)">
  <p class="hvl-pedit-ctl-caption">
    <b>{{{ trans('habravel::g.edit.tags') }}}</b>
    <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>
  </p>

  <p class="hvl-pedit-tags-to">
    @foreach ($tags as $tag)
      @if ($tag->type !== 'draft')
        {{ Habravel\tagLink($tag, true, '') }}
        <input type="hidden" name="tags[]" value="{{{ $tag->caption }}}">
      @endif
    @endforeach

    <span class="hvl-pedit-tags-none">{{{ trans('habravel::g.edit.tagHelp') }}}</span>
  </p>

  <p class="hvl-pedit-tags-from">
    @foreach ($tagPool as $tag)
      <?php
        $postTagged = false;
        foreach ($tags as $postTag) {
          if ($tag->caption === $postTag->caption) {
            $postTagged = true;
            break;
          }
        }

        $postTagged or print Habravel\tagLink($tag, true, '');
      ?>
    @endforeach

    <u class="hvl-pedit-tags-new">{{{ trans('habravel::g.edit.newTag') }}}</u>
  </p>
</div>
