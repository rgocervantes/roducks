<?php

namespace Roducks\Blocks\Comments;

use Roducks\Page\Block;
use Roducks\Page\View;
use Roducks\Services\Comments as CommentsService;

class Comments extends Block
{

  public function output($id)
  {
    $comments = CommentsService::init()->getComments($id);
    $this->view->data('id', $id);
    $this->view->data('comments', $comments);
    $this->view->load('default');

    return $this->view->output();
  }
}
