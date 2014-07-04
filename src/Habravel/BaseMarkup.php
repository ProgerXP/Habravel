<?php namespace Habravel;

abstract class BaseMarkup {
  static $extension = 'txt';

  // Marked up source text.
  public $text;

  // If set - is an object for which the text is being formatted - like Post.
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
    $this->meta = array(
      // Used to replace "Read more" link text.
      'cut'               => '',
    );

    $this->doToHTML();
    $this->html = trim($this->html);
    $this->fillMissing();
    return $this;
  }

  protected abstract function doToHTML();

  protected function fillMissing() {
    if (!$this->introHTML) {
      $this->introHTML = trim(strtok($this->html, "\r\n"));
    }
  }
}