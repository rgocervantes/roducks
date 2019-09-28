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

namespace Roducks\Modules\Admin\Content\Page;

use Roducks\Page\AdminPage;
use JSON;
use Helper;

class Content extends AdminPage
{

  var $preview = 0;

  public function index()
	{

    if ($this->preview == 0) {

  		$this->view->assets->jsInline([
  			'layout'
  		]);

  		$this->view->assets->jsOnReady([
  			'layout.ready'
  		]);

  		$this->view->assets->plugins([
  			'jquery-ui',
  			'roducks-layout'
  		]);

      $this->view->load("layout");

    } else {

			$configString = '{"grid":false,"regions":[{"name":"col-3","blocks":[{"container":[]},{"container":[]},{"container":[]}]},{"name":"col-1","blocks":[{"container":[{"name":"social-network","title":"Social Network","data":{"option":1,"number":5}}]}]},{"name":"col-4","blocks":[{"container":[]},{"container":[]},{"container":[]},{"container":[]}]},{"name":"col-2","blocks":[{"container":[{"name":"poll","title":"Poll","data":{}},{"name":"header","title":"Header","data":{}}]},{"container":[{"name":"facebook-graph","title":"Facebook Graph","data":{"api_key":78493829}}]}]}]}';
			$config = JSON::decode($configString);

			$this->view->data('regions', $config['regions']);

			$this->view->load("page-layout");
    }

		return $this->view->output();
	}

  public function add($name)
  {
    $data = $this->model('content/page-types')->getByName($name);

    if (empty($data->title)) {
      return $this->notFound();
    }

    $this->view->assets->jsInline([
      "form", // app/Js/form.inc
    ]);

    $title = mb_strtolower($data->title);
    $this->view->title(__("New {$title}"));
    $this->view->data('id', $data->id);
    $this->view->data('type', $name);
    $this->view->data('fields', $this->model('content/page-type-fields')->getFields($name));
    $this->view->load("form");

    return $this->view->output();
  }

}
