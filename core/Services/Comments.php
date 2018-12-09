<?php
/**
 *
 * This file is part of Roducks.
 *
 *    Roducks is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    Roducks is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with Roducks.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Roducks\Services;

use Roducks\Page\Service;
use Lib\XML;
use Lib\Directory;
use Path;
use Helper;
use User;
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
        $user = $this->model('users/users')->getRow(intval($node['uid']->__toString()));

        foreach ($node->replies->reply as $reply) {
          $replies[] = [];
        }

        $comments[] = [
          'name' => $user->getFirstName(),
          'email' => $user->getEmail(),
          'date' => Date::getDateFormatLong($node['date']->__toString(), $this->getLang()),
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

    Directory::make(Path::getData(), self::FOLDER);
    $file = self::_getPath($id);
    $xml = XML::create($file);
    $xml->root('comments');
    $comment = $this->post->textarea('comment');
    $data = ['comment' => $comment];

    if (User::isLoggedIn()) {

      $data['picture'] = User::getPicture(false, 90);

      $attrs = ['uid' => User::getId(), 'date' => Date::getCurrentDate()];

      $data = $data + $attrs;

      $data['name'] = User::getName();
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
