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

namespace Roducks\Blocks\Menu;

use Roducks\Page\Block;
use Roducks\Data\User;

class Menu extends Block
{

	public function nav($type)
	{
		$this->view->load($type);
		return $this->view->output();
	}

	public function simple(array $items = [], $tpl)
	{

		$this->view->data("access", []);
		$this->view->data("items", $items);
		$this->view->load($tpl);

		return $this->view->output();

	}

	public function access($type, array $items = [], $tpl, $permission = "")
	{

		$this->role();
		$access = $this->grantAccess->getData();

		if (!empty($permission) && !User::isSuperAdmin()) {
			$access = $access[$permission];
		}

		$this->view->data("access", $access);
		$this->view->data("items", $items);
		$this->view->load($tpl);

		return $this->view->output();

	}

}
