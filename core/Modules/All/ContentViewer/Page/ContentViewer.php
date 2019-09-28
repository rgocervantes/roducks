<?php

namespace Roducks\Modules\All\ContentViewer\Page;

use Helper;

class ContentViewer extends \Roducks\Page\FrontPage
{

  public function index()
  {
    $urlData = $this->view->getUrlData();
    $content = $this->model('content/content')->filter(['id_url' => $urlData['id_url']])->getData();

    if (!isset($content[0])) {
      return $this->notFound();
    }

    $page = $content[0];

    $this->view->data($page);

    return $this->view->output();

  }
}
