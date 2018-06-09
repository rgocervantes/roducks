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

namespace Roducks\Modules\Admin\Roles\JSON;

use Roducks\Framework\Login;
use Roducks\Framework\Role;
use Roducks\Framework\Language;
use Roducks\Framework\Path;
use Roducks\Page\_JSON;
use Roducks\Libs\Utils\Date;
use Roducks\Libs\Files\Directory;
use Roducks\Libs\Files\File;
use Roducks\Libs\Data\Session;
use App\Models\Users\Roles as RolesTable;

class Roles extends _JSON
{

	protected $_dispatchUrl = true;

	private function _getType()
	{
		$role_type = Session::get('ROLE_TYPE');
		$type = (!empty($role_type)) ? $role_type : 1;

		return $type;
	}

	public function __construct(array $settings)
	{
		parent::__construct($settings);

		$this->post->required();
		$this->role(Role::TYPE_USERS);
		$this->grantAccess->json();
	}

	public function search()
	{
		$db = $this->db();
		$value = strtoupper($this->post->text("q"));
		$RolesTable = RolesTable::open($db);

		$this->data($RolesTable->search($value, $this->_getType()));

		parent::output();
	}

	public function nameTaken()
	{

		$db = $this->db();
		$value = strtoupper($this->post->text("q"));
		$RolesTable = RolesTable::open($db);

		if ($RolesTable->nameTaken($value, $this->_getType())) {
			$this->setError(0, Language::translate("Name already taken, please choose another.","Ya existe este nombre, por favor elige otro."));
		}

		parent::output();
	}

	public function save($id_role = "")
	{

		$method = $this->post->hidden("method");
		$config = $this->post->hidden("config", "");
		$name = ucwords($this->post->text("_name"));
		
		if ($method == "insert") {
			$this->grantAccess->create();
		} else {
			$this->grantAccess->edit();
		}

		$db = $this->db();
		$RolesTable = RolesTable::open($db);
		$valid = true;
		$type = $this->_getType();

		if ($method == "insert") {

			// Make sure role's name does not exist yet, if so, refuse.
			if ($RolesTable->nameTaken($name, $type)) {
				$this->setError(0, Language::translate("Name already taken, please choose another.","Ya existe este nombre, por favor elige otro."));
				$valid = false;
			} else {
			
				$configName = "role_" . Date::getCurrentDateFlat() . "_" . mt_rand();

				$RolesTable->insert([
					'name' => $name,
					'type' => $type,
					'config' => "{$configName}.json",
					'active' => 1,
					'created_by' => Login::getAdminId(),
					'updated_by' => Login::getAdminId(),
					'created_at' => Date::getCurrentDateTime()
				]);

			}
			
		} else if ($method == "update" && !empty($id_role)) {
			$RolesTable->update($id_role, ['name' => $name, 'updated_by' => Login::getAdminId(), 'updated_at' => Date::getCurrentDateTime()]);
		}

		if ($valid) {
			$data = $this->post->data();
			unset($data['method']);
			if (empty($config)) {
				$data['config'] = $configName;
			}

			Directory::make(Path::get(), DIR_ROLES);
			File::createJSON(Path::get(DIR_ROLES), $config, $data);
		}

		parent::output();
	}

	public function visibility()
	{

		$this->grantAccess->visibility();

		$id_role = $this->post->param("id");
		$value = $this->post->param("value");

		if ($id_role == Login::getAdminData("id_role")) {
			$this->setError(1, Language::translate("Can't disable the role you belong to.","No puedes desactivar el rol al que perteneces."));
		}

		$db = $this->db();
		$tx = RolesTable::open($db)->update($id_role, ['active' => $value]);
		
		if ($tx === FALSE) {
			$this->setError(0, TEXT_WAS_AN_ERROR);
		}
		
		parent::output();
	}

} 