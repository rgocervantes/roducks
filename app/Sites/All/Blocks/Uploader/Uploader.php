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

namespace App\Sites\All\Blocks\Uploader;

use Roducks\Page\Block;

class Uploader extends Block
{

	public function output($module = "", $config = "", $picture = "")
	{

		$this->view->data("module", $module);
		$this->view->data("config", $config);
		$this->view->data("picture", $picture);
		$this->view->load("form");

		return $this->view->output();

	}

}
