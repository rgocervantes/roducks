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

namespace rdks\core\modules\admin\Dashboard\page;

use rdks\core\page\AdminPage;
use rdks\core\framework\Role;
use rdks\app\sites\admin\modules\Users\helper\Users as UsersHelper;

class Dashboard extends AdminPage{

	const ROWS_PER_PAGE = 5;

	public function index(){

		$this->role(Role::TYPE_USERS);
		$access = $this->grantAccess->getData();

		$this->view->assets->scriptsInline(["grid","grid-subscribers","grid-clients","popover","users","roles.modal"]);
		$this->view->assets->scriptsOnReady(["grid.ready","grid-subscribers.ready","grid-clients.ready"]);	

		$this->view->data("url", UsersHelper::URL);
		$this->view->data("access", $access);
		$this->view->layout("sidebar-content",[
			'SIDEBAR' => [
				$this->view->setTemplate("sidebar-left")
			],
			'SIDEBAR-CHILD-LEFT' => [
				$this->view->setTemplate("profile"),
				$this->view->setTemplate("sidebar-users"),		
				$this->view->setTemplate("sidebar-roles"),								
			],			
			'CONTENT' => [
				$this->view->setView("index")
			]
		]);
		
		return $this->view->output();

	}

} 