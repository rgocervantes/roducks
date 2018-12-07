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

namespace Roducks\Modules\Admin\Content\JSON;

use Roducks\Services\Url as UrlService;
use JSON;
use Helper;
use Login;
use Dispatch;

class Content extends JSON
{
  protected $_dispatchUrl = true;
	protected $_authentication = true;

  public function save()
  {
    $this->post->required();

    $id = $this->post->hidden('id');
    $title = $this->post->param('title');
    $description = $this->post->param('description');

    if (empty($this->post->param('url'))) {
      $url = Helper::removeSpecialChars($title);
    } else {
      $url = $this->post->param('url');
    }

    $urlTakenRule1 = $this->model('SEO/urls-lang')->filter(['url:regexp' => $url.'-[0-9]([a-z./])?$']);
    $urlTakenRule2 = $this->model('SEO/urls-lang')->results(['url' => $url]);

    if ($urlTakenRule1->foundRow()) {
      $urlSaved = $urlTakenRule1->fetch();
      $urls = preg_match('/(.+)\-(\d+)([a-z\.\/])?$/', $urlSaved['url'], $u);
      $i = intval($u[2]) + 1;
      $e = (isset($u[3])) ? $u[3] : '';
      $url = $u[1].'-'.$i.$e;
    } else if ($urlTakenRule2) {
      $url .= '-1';
    }

    $urlService = UrlService::init()->set([
      'en' => [
        'url' => $url,
        'dispatch' => Dispatch::page('content-viewer', 'index'),
        'tpl' => $this->post->hidden('tpl')
      ],
    ]);

    $content = $this->model('content/content')->prepare();
    $content->setIdType($id);
    $content->setIdLayout(1);
    $content->setIdUser(User::getId());
    $content->setCreatedAt('NOW()');
    $content->setUpdatedAt('NOW()');
    $content->save();

    $contentLang = $this->model('content/content-lang')->prepare();

    foreach ($urlService->getIds() as $iso => $id) {
      $contentLang->setIdContent($urlService->getId());
      $contentLang->setIdUrlLang($id);
      $contentLang->setTitle($this->post->param('title'));
      $contentLang->setDescription($this->post->param('description'));
      $contentLang->save();
    }

    $this->data($this->post->data());

    parent::output();
  }
}
