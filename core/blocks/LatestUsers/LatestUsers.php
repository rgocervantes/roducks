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

namespace rdks\core\blocks\LatestUsers;

use rdks\core\page\Block;
use rdks\core\framework\Login;
use rdks\core\framework\Role;
use rdks\app\models\Users\UsersRoles;
use rdks\app\sites\admin\modules\Roles\helper\Roles as RolesHelper;

class LatestUsers extends Block{

	public function grid($type, $url, $access, $data, $alt = ""){

		$this->view->data("icon", RolesHelper::getIcon($type));
		$this->view->data("url", $url);
		$this->view->data("access", $access);		
		$this->view->data("data", $data);
		$this->view->data("alt", $alt);
		$this->view->load("grid");

		return $this->view->output();
	}

	public function current($type, $url){

		$this->role(Role::TYPE_USERS, $url);
		$access = $this->grantAccess->getAccess();
		
		$filter = [];
		$filter['u.id_user'] = Login::getAdminId();
			
		$db = $this->db();
		$users = UsersRoles::open($db);
		$data = $users->getAll($type, 1, 1, "desc", $filter);

		return $this->grid($type, $url, $access, $data);

	}

	public function output($type, $url, $limit, $alt = ""){

		$this->role(Role::TYPE_USERS, $url);
		$access = $this->grantAccess->getAccess();

		$filter = [];
		$filter['u.trash'] = 0; 

		if(!Login::isSuperAdmin()){

			if($this->grantAccess->hasAccess("descendants")/* || in_array($type, RolesHelper::getIds())*/){
				$filter['u.id_user_parent'] = Login::getAdminId();
			}

			if($this->grantAccess->hasAccess("tree") && Login::roleSuperAdmin()){
				$filter['[BEGIN_COND]'] = "(";
					$filter['[COND_AND]u.id_user_parent:>'] = Login::getAdminData('id_user_parent');
					$filter['[COND_OR]u.id_role:>'] = Login::getAdminData('id_role');
				$filter['[END_COND]'] = ")";
			}
		} else {
			$access['tree'] = 1;
		}

		$db = $this->db();
		$users = UsersRoles::open($db);
		$data = $users->getAll($type, 1, $limit, "desc", $filter);

		return $this->grid($type, $url, $access, $data, $alt);

	}

} 