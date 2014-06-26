<?php namespace Habravel;

abstract class BaseMarkup {
  static $extension = 'txt';

  public $text;

  // If set is an object for which the text is being formatted - like Post.
  public $target;

  // doToHTML() must populate these fields.
  public $html;
  public $introHTML;

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

  function getDates() {
    $list = parent::getDates();
    $list[] = 'deleted_at';
    return $list;
  }

  function toHTML() {
    $this->doToHTML();
    $this->html = trim($this->html);

    if (!$this->introHTML) {
      $this->introHTML = trim(strtok($this->html, "\r\n"));
    }

    return $this;
  }

  protected abstract function doToHTML();
}