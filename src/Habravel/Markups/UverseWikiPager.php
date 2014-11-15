<?php namespace Habravel\Markups;

class UverseWikiPager extends \UWikiPager {
  protected $target;  //= null or Model.

  static function isID($page) {
    return $page > 0 and ltrim($page, '0..9') === '';
  }

  static function clusterURL($page) {
    return rtrim($page, '\\/').'/';
  }

  function __construct($target = null) {
    parent::__construct();
    $this->target = $target;
  }

  function pageTitleBy($url) {
    $url = ltrim($url, '\\/');

    if ($url === '') {
      return;
    } elseif ($url[0] === '=') {
      // =Ta/gs.
      return substr($url, 1);
    } elseif (substr($url, 0, 5) === 'tags/') {
      return substr($url, 5);
    } elseif ($url[0] === '~') {
      // ~User.
      $url = substr($url, 1);
      $model = static::isID($url) ? User::find($url) : User::whereName($url)->first();
      return $model ? $model->name : null;
    } elseif ($model = $this->postModelBy($url)) {
      return $model->caption;
    }
  }

  // $page = '123' (ID) or 'url/path' or 'clus/ter/'.
  function postModelBy($page) {
    $page = ltrim($page, '\\/');

    if (static::isID($page)) {
      $model = Post::find($page);
    } else {
      $model = Post::where('url', '=', $page)->first();
    }

    if (false !== $text = $this->accessibleText($model)) {
      $model->text = $text;
      return $model;
    }
  }

  // Returns false if current user isn't allowed to access given $model.
  protected function accessibleText(Post $model = null) {
    try {
      if ($model) {
        $resp = Route::call(NS.'Controllers\\Post@showShourceOn', array($model, true));
        if (method_exists($resp, 'getStatusCode') and $resp->getStatusCode() == 200) {
          return $resp->getContent();
        }
      }
    } catch (\Exception $e) {
      // App::abort() could have been called.
    }

    return false;
  }

  function ReadPage($page, $format = self::AutoFormat) {
    return ($model = static::postModelBy($page)) ? $model->text : static::NotFound;
  }

  function PageExists($page, $format = self::AutoFormat) {
    return static::pageTitleBy($page) !== null;
  }

  function ClusterExists($page) {
    return (bool) static::postModelBy(static::clusterURL($page));
  }

  protected function ReadCluster($cluster) {
    $cluster = static::clusterURL($cluster);
    $rows = Post::where('url', 'LIKE', "$cluster%");
    $result = array();

    foreach ($rows as $page) {
      list($head, $tail) = explode($cluster, $page->url, 2);

      if ($cluster === $head and !strpbrk(rtrim($tail, '\\/'), '\\/')) {
        $this->accessibleText($page) === false or $result[] = $tail;
      }
    }

    return $result;
  }

  // Mimicing UWikiFilePager result.
  function PageInfo($page) {
    if ($model = $this->postModelBy($page)) {
      return array(
        'name'            => $page->url,
        'isDir'           => $isDir = substr($page->url, -1) === '/',
        'isFile'          => !$isDir,
        'isReadable'      => true,
        'isWritable'      => false,
        'modTime'         => $model->edited_at,
        'type'            => $isDir ? 'dir' : 'file',
        'size'            => strlen($model->text),
      );
    }
  }

  function ClusterInfo($page) {
    return $this->PageInfo(static::clusterURL($page));
  }

  function GetTitleOf($fileOrCluster, $format = self::AutoFormat) {
    return $this->pageTitleBy($fileOrCluster);
  }
}

// {{cut Text for "Read more" link.}}
class Ucut_Root extends \UWikiBaseElement {
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

class_alias(NS.'Ucut_Root', 'Ucut_Root');