<?php

namespace Roducks\Services;

use Roducks\Page\Service;
use Lib\XML;
use Lib\Directory;
use Path;
use Helper;
use Login;
use Date;

class Comments extends Service
{
  const FOLDER = "comments/";

  protected $_dispatchUrl = true;

  static private function _getPath($id)
  {
    return Path::getData(self::FOLDER."comment_{$id}.xml");
  }

  public function getComments($id)
  {
    $file = self::_getPath($id);

    $comments = [];
    if (file_exists($file)) {

      $xml = XML::parse($file);
      $content = $xml->content();

      foreach ($content->children() as $node) {
        $replies = [];
        $user = $this->model('users/users')->getRow(intval($node['id']->__toString()));

        foreach ($node->replies->reply as $reply) {
          $replies[] = [];
        }

        $comments[] = [
          'name' => $user->getFirstName(),
          'email' => $user->getEmail(),
          'date' => Date::getDateFormat($node['date']->__toString(), $this->getLang()),
          'picture' => Path::getPublicUploadedUsers($user->getPicture(), 90),
          'post' => $node->post->__toString(),
          'replies' => $replies
        ];
      }
    }

    return $comments;
  }

  public function add($id)
  {
    $this->post->required();

    Directory::make(Path::getData(self::FOLDER));
    $file = self::_getPath($id);
    $xml = XML::create($file);
    $xml->root('comments');
    $comment = $this->post->textarea('comment');
    $data = ['comment' => $comment];

    if (Login::isSubscriberLoggedIn()) {

      $data['picture'] = Path::getPublicUploadedUsers(Login::getSubscriberPicture(), 90);

      $attrs = ['id' => Login::getSubscriberId(), 'date' => Date::getCurrentDate()];

      $data = $data + $attrs;

      $data['name'] = Login::getSubscriberName();
      $data['date'] = Date::getDateFormat($attrs['date'], $this->getLang());

      $node = $xml->createNode([
              'name' => 'comment',
              'attributes' => $attrs
      ]);

      $post = $xml->createNode([
              'name' => 'post',
              'cdata' => $comment
      ]);

      $replies = $xml->createNode([
              'name' => 'replies'
      ]);

      $xml->appendChild($node);

      $node->appendChild($post);
      $node->appendChild($replies);

      $xml->save();

    } else {
      $this->setError(401, "Authentication failed!");
    }

    $this->data($data);
    parent::output();

  }
}
