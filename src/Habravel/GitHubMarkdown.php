<?php namespace Habravel;

// Uses https://github.com/michelf/php-markdown
use Michelf\MarkdownExtra as Markdown;

class GitHubMarkdown extends BaseMarkup {
  static $extension = 'md';

  protected function doToHTML() {
    if (!class_exists('Michelf\MarkdownExtra')) {
      throw new \Exception('Markdown dependency must be installed (see Composer suggestions).');
    }

    $parser = new Markdown;

    foreach (\Config::get('habravel::markdown') as $key => $value) {
      $parser->$key = $value;
    }

    if ($this->target) {
      $parser->fn_id_prefix = str_replace('#', $this->target->id, $parser->fn_id_prefix);
    }

    $html = $parser->transform($this->text);
    $html = $this->makeCut($html);
    $this->html = $html;
  }

  function makeCut($html) {
    $p = '(</?p\b[^>]*>)?';
    $regexp = "~$p\s*<cut(?:\s+text=['\"]([^>]*)['\"])?>\s*$p()~u";

    return preg_replace_callback($regexp, function ($match) {
      list(, $headTag, $text, $tailTag) = $match;

      if (isset($text)) {
        $this->meta['cut'] = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'utf-8');
      }

      // <p><cut> -> swap   <cut><p> -> keep   </p><cut> -> keep   <cut></p> -> swap
      // <p><cut></p> -> unwrap   </p><cut><p> -> keep
      if ($headTag or $tailTag) {
        $headOpens = $headTag ? $headTag[1] !== '/' : null;
        $tailOpens = $tailTag ? $tailTag[1] !== '/' : null;

        if ($headOpens and $tailOpens === false) {
          $headTag = $tailTag = '';
        } elseif ($headOpens !== null and $tailOpens !== null) {
          // Keep as is.
        } elseif ($headOpens) {
          $tailTag = $headTag;
          $headTag = '';
        } elseif ($tailOpens === false) {
          $headTag = $tailTag;
          $tailTag = '';
        }
      }

      return $headTag.'<a href="#cut" name="cut"></a>'.$tailTag;
    }, $html);
  }
}