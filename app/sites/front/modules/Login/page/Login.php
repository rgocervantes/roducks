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

namespace rdks\app\sites\front\modules\Login\page;

use rdks\core\framework\Role;
use rdks\core\framework\Login as LoginAuth;
use rdks\core\modules\_global\Login\page\Login as LoginPage;

class Login extends LoginPage{

	protected $_type = Role::TYPE_SUBSCRIBERS;

	public function login(){

		parent::login();

		$this->view->title(TEXT_LOGIN);
		$this->view->assets->scriptsInline(['login']);
		$this->view->assets->scriptsOnReady(['login.ready']);
		$this->view->load("login");

		return $this->view->output();

	}

} 


