<?php namespace Habravel\Markups;

abstract class BaseMarkup {
  static $extension = 'txt';

  static $defaultMeta = array(
    // Used to replace "Read more" link text.
    'cut'                 => '',
  );

  // Marked up source text.
  public $text;

  // If set - is an object for which the text is being formatted - like Models\Post.
  public $target;

  // doToHTML() must populate these fields.
  public $html;

  // Fields below are to be set by doToHTML(). If not they are populated
  // automatically which might not be the optimal way to go.
  public $introHTML;
  // array('someKey' => 'arbitraryValue'/array/object/etc.).
  public $meta;

  static function help() {
    return \View::make('habravel::markup.'.strtolower(class_basename(get_called_class())))
      ->render();
  }

  static function format($text, $target = null) {
    $self = new static;
    $self->text = $text;
    $self->target = $target;
    return $self->toHTML();
  }

  function toHTML() {
    $this->meta = array();
    $this->doToHTML();
    $this->html = trim($this->html);
    $this->fillMissing();
    return $this;
  }

  protected abstract function doToHTML();

  protected function fillMissing() {
    $this->meta += static::$defaultMeta;

    $regexp = '/<\w+\b[^>]*(id|name)=[\'"]cut[\'"]/u';
    if (!$this->introHTML and preg_match($regexp, $this->html, $match, PREG_OFFSET_CAPTURE)) {
      $this->introHTML = trim(substr($this->html, 0, $match[0][1]));
    }

    if (!$this->introHTML) {
      $html = preg_replace('~<fieldset class="toc">.*?</fieldset>~us', '', $this->html);
      $this->introHTML = trim(\Str::words($html));
    }
  }

  protected function rebaseLinks($html, $baseURL) {
    return preg_replace('~(<a\b[^>]+href=.)(#)~ui', '\1'.$baseURL.'\2', $html);
  }
}