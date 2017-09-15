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

namespace rdks\core\modules\_global\Login\page;

use rdks\core\framework\Role;
use rdks\core\framework\Event;
use rdks\core\framework\Login as LoginAuth;
use rdks\core\page\Page;
use rdks\app\models\Users\Users as UsersTable;

class Login extends Page{

	protected $_type;

	public function login(){

		$login = new LoginAuth($this->_type, $this->loginUrl);
		$login->redirect(); // obligatory

	}
	
	public function logout(){

		switch ($this->_type) {
			case Role::TYPE_USERS:
				$id_user = LoginAuth::getAdminId();
				LoginAuth::logoutAdmin();
				break;
			case Role::TYPE_SUBSCRIBERS:
				$id_user = LoginAuth::getSubscriberId();
				LoginAuth::logoutSubscriber();
				break;
			case Role::TYPE_CLIENTS:
				$id_user = LoginAuth::getClientId();
				LoginAuth::logoutClient();
				break;				
		}

		Event::dispatch('onEventLogout', [$id_user]);

		$db = $this->db();
		UsersTable::open($db)->logInOut($id_user, 0);
		$db->close();
		
		$this->redirect($this->loginUrl);
	}

} 