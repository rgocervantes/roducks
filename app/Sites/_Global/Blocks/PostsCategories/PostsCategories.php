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

namespace App\Sites\_Global\Blocks\PostsCategories;

use Roducks\Page\Block;
use Roducks\Page\View;
use Roducks\Page\JSON;
use Roducks\Framework\Dispatch;

class PostsCategories extends Block{

	protected $_dispatchUrl = true;

	var $x = 1;

	public function __construct(array $setting, View $view){
		parent::__construct($setting, $view);

		$this->accessAdmin();
	}

	public function getJson($data){
		return JSON::encode(['data' => $data]);
	}

	public function listing($id = "", $type = "", $values = ""){

		$values = $this->getArray($values); // Parse String to Array

		$this->params([
			'id' => [$id, 'PARAM', Dispatch::PARAM_INTEGER],
			//'type' => [$type, 'PARAM', Dispatch::PARAM_STRING, Dispatch::values(['one','all'])],
			//'type' => [$type, 'PARAM', Dispatch::PARAM_STRING, '/^\d{4}-\d{2}-\d{2}$/'],
			'type' => [$type, 'PARAM', Dispatch::PARAM_STRING, '/^rdks_[a-z]{3}_\d{1,4}$/'],
			'values' => [$values, 'PARAM', Dispatch::PARAM_NOT_EMPTY_ARRAY],
			'x' => [$this->x, 'GET', Dispatch::PARAM_INTEGER]
		]);

		$this->view->data("id", $id);
		$this->view->data("type", $type);
		$this->view->data("values", $values);
		$this->view->data("x", $this->getJson($this->x));		
		$this->view->load("listing");

		return $this->view->output();
	
	}

}