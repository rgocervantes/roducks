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

namespace Roducks\Modules\All\Login\Page;

use Roducks\Page\Page;
use Roducks\Data\User;

class Login extends Page
{

	private function _redirect()
	{
		$this->redirect(static::LOGIN_URL);
	}

	public function form()
	{
		if (User::isLoggedIn()) {
			$this->_redirect();
		}

		$this->view->title(_text('login'));
		$this->view->assets->jsInline(['login']);
		$this->view->assets->jsOnReady(['login.ready']);
		$this->view->load("login");

		return $this->view->output();

	}

	public function logout()
	{
		User::logout();
		$this->_redirect();
	}

}
