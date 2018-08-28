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

use Roducks\Framework\Role;
use Roducks\Framework\Event;
use Roducks\Framework\Login as LoginAuth;
use Roducks\Page\Page;
use DB\Models\Users\Users as UsersTable;

class Login extends Page
{

	protected $_session;

	public function login()
	{

		$login = new LoginAuth($this->_session, static::LOGIN_URL);
		$login->redirect(); // obligatory

	}

	public function logout()
	{

		$id_user = LoginAuth::getId($this->_session);
		LoginAuth::logout($this->_session);

		Event::dispatch('onEventLogout', [$id_user]);

		$db = $this->db();
		UsersTable::open($db)->logInOut($id_user, 0);
		$db->close();

		$this->redirect(static::LOGIN_URL);
	}

}
