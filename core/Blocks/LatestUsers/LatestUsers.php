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

namespace Roducks\Blocks\LatestUsers;

use Roducks\Page\Block;
use Roducks\Data\User;
use Roducks\Framework\Role;
use DB\Models\Users\UsersRoles;
use App\Sites\Admin\Modules\Roles\Helper\Roles as RolesHelper;

class LatestUsers extends Block
{

	public function grid($type, $url, $access, $data, $alt = "")
	{

		$this->view->data("icon", RolesHelper::getIcon($type));
		$this->view->data("url", $url);
		$this->view->data("access", $access);
		$this->view->data("data", $data);
		$this->view->data("alt", $alt);
		$this->view->load("grid");

		return $this->view->output();
	}

	public function current($type, $url)
	{

		$this->role($url);
		$access = $this->grantAccess->getAccess();

		$filter = [];
		$filter['u.id_user'] = User::getId();

		$db = $this->db();
		$users = UsersRoles::open($db);
		$data = $users->getAll($type, 1, 1, "desc", $filter);

		return $this->grid($type, $url, $access, $data);

	}

	public function output($type, $url, $limit, $alt = "")
	{

		$this->role($url);
		$access = $this->grantAccess->getAccess();

		$filter = [];
		$filter['u.trash'] = 0;

		if (!User::isSuperAdmin()) {

			if ($this->grantAccess->hasAccess("descendants")/* || in_array($type, RolesHelper::getIds())*/) {
				$filter['u.id_user_parent'] = User::getId();
			}

			if ($this->grantAccess->hasAccess("tree") && User::roleSuperAdmin()) {
				$filter['[BEGIN_COND]'] = "(";
					$filter['[NON]u.id_user_parent:>'] = User::getData('id_user_parent');
					$filter['[OR]u.id_role:>'] = User::getData('id_role');
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
