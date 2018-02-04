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

namespace App\Sites\Admin\Modules\Login\Page;

use Roducks\Framework\Login as LoginAuth;
use Roducks\Modules\_Global\Login\Page\Login as LoginPage;

class Login extends LoginPage{

	protected $_session = LoginAuth::SESSION_ADMIN;

	public function login(){

		parent::login();

		$this->view->title(TEXT_LOGIN);
		$this->view->assets->scriptsInline(['login']);
		$this->view->assets->scriptsOnReady(['login.ready']);
		$this->view->load("login");

		return $this->view->output();

	}

}