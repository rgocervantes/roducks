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

namespace App\Models\Users;

use Roducks\Libs\ORM\Model;
use Roducks\Framework\Login;

class Roles extends Model
{

	var $id = "id_role";
	var $fields = [
		'type' 			 => Model::TYPE_INTEGER,
		'name' 			 => Model::TYPE_VARCHAR,
		'config'		 => Model::TYPE_VARCHAR,
		'active'		 => Model::TYPE_BOOL,
		'created_by'	 => Model::TYPE_INTEGER,
		'updated_by'	 => Model::TYPE_INTEGER,
		'created_at'	 => Model::TYPE_DATETIME,
		'updated_at'	 => Model::TYPE_DATETIME
	];

	/**
	*	@param $field string
	*	@param $value string
	*	@return bool
	*/
	public function nameTaken($value, $type)
	{
		return $this->results(["name:upper" => $value, 'type' => $type, 'id_role:>' => 1]);
	}

	public function search($value, $type)
	{
		$ret = [];
		$id_role = Login::getAdminData('id_role');
		$this->filter(["name:%like%" => $value, 'type' => $type, 'id_role:>' => $id_role]);

		if ($this->rows()) {
			while ($row = $this->fetch()) {
				$ret[] = [
					'text' => $row['name'],
					'value' => $row['id_role']
				];
			}
		}

		return $ret;
	}

	public function getAll($type, $page, $limit)
	{

		// Get current admin session
		$id_role = Login::getAdminData('id_role');

		$condition = [
				'id_role:>' => $id_role,
				'type' => $type
		];

        return $this
        ->where($condition)
        ->orderBy(['id_role' => "desc"])
        ->paginate($page, $limit);

	}

	public function getList($type)
	{

		// Get current admin session
		$id_role = Login::getAdminData('id_role');

		// Role for super admin is protected.
		$op = ($id_role == 1) ? '>' : '>=';

		return $this->filter(['id_role:'.$op => $id_role, 'active' => 1, 'type' => $type]);

	}

	public function getCatalog()
	{
		return $this->filter(['id_role:>' => 1]);
	}

}
