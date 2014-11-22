<?php

// {{cut Text for "Read more" link.}}
class Ucut_Root extends UWikiBaseElement {
  public $isBlock = true;
  public $kind = 'anchor';
  public $htmlTag = 'a';
  public $htmlClasses = array('hvl-cut');
  public $htmlAttributes = array('name' => 'cut', 'href' => '#cut');

  function Parse() {
    if ($format = &$this->settings->format) {
      $params = $format->current['params'];
      $format->topmostDoc->meta['cut'] = key($params);
    }
  }
}

// %%(hvlraw) \n <table>... \n %%
class Uhvlraw_Root extends UWikiBaseElement {
  public $isBlock = true;
  public $isFormatter = true;
  public $isAction = false;
  public $htmlTag = 'div';
  public $htmlClasses = array('hvl-raw');

  function SelfToHtmlWith($html) {
    $prefix = Habravel\HyperSafe::transformBody($this->raw);
    return parent::SelfToHtmlWith($prefix.$html);
  }
}
