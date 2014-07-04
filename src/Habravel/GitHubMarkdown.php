<?php namespace Habravel;

// Uses https://github.com/michelf/php-markdown
use \Michelf\MarkdownExtra as Markdown;

class GitHubMarkdown extends BaseMarkup {
  static $extension = 'md';

  protected function doToHTML() {
    $parser = new Markdown;

    foreach (\Config::get('habravel::markdown') as $key => $value) {
      $parser->$key = $value;
    }

    if ($this->target) {
      $parser->fn_id_prefix = $parser->fn_id_prefix.$this->target->id;
    }

    $this->html = $parser->transform($this->text);
  }
}