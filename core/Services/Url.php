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

/*
  UrlService::init()->set([
    'es' => [
      'url' => '/muestra/ejemplo',
      'dispatch' => 'Home/Page/Home::index'
    ],
    'en' => [
      'url' => '/sample/example',
      'dispatch' => 'Home/Page/Home::index'
    ],
  ]);
*/

namespace Roducks\Services;

use Roducks\Page\Service;
use Roducks\Framework\Language;

class Url extends Service
{

  public function set(array $urls)
  {

    $urlsTable = $this->model('seo/urls')->prepare();
    $urlsTable->setCreatedAt('NOW()');
    $urlsTable->save();
    $urlId = $urlsTable->getId();

    foreach ($urls as $iso => $url) {
      $langId = Language::getId($iso);

      $urlLangTable = $this->model('seo/urls-lang')->prepare();
      $urlLangTable->setIdUrl($urlId);
      $urlLangTable->setIdLang($langId);
      $urlLangTable->setUrl($url['url']);
      $urlLangTable->setDispatch($url['dispatch']);

      if (isset($url['title'])) {
        $urlLangTable->setDispatch($url['title']);
      }

      if (isset($url['layout'])) {
        $urlLangTable->setDispatch($url['layout']);
      }

      if (isset($url['template'])) {
        $urlLangTable->setDispatch($url['template']);
      }

      if (isset($url['tpl'])) {
        $urlLangTable->setDispatch($url['tpl']);
      }

      $urlLangTable->save();

    }

  }

}
