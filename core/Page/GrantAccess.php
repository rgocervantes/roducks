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

namespace Roducks\Page;

use Roducks\Data\User;
use Roducks\Framework\Role;
use Roducks\Framework\Helper;
use Roducks\Framework\Error;
use Storage;
use Path;

class GrantAccess
{

	private $_page;
	private $_view;
	private $_json = false;

	private function _pageNotFound()
	{
		if ($this->_json) {
			JSON::error(TEXT_GRANT_ACCESS_REQUIRED);
		} else {
			Error::page();
		}
	}

	private function _load($name)
	{

		$path = Path::getRoles();
		$json = Storage::getJSON(DIR_ROLES, $name);
		$file = $path . $name;

		if (!empty($json)) {
			return $json;
		} else {
			if (!User::isSuperAdmin()) {
				Error::debug("Role config file was not found", __LINE__, __FILE__, $file, 'Make sure it exists in: <b>' . $path . "</b>");
			}
		}

		return ['data' => []];
	}

	private function _action($action)
	{
		if (!User::isSuperAdmin() && !$this->hasAccess($action)) {
			$this->_pageNotFound();
		}
	}

/*
//---------------------------------
//	PUBLIC METHODS
//---------------------------------
*/
	public function __construct($page)
	{
		$this->_page = $page;
	}

	public function json()
	{
		$this->_json = true;
	}

	public function getFileConfig($name)
	{
		return $this->_load($name);
	}

	public function getConfig()
	{

		$name = User::getData('config');
		$conf = $this->getFileConfig($name);

		if (empty($conf)) {
			return ['data' => []];
		}

		return $conf;
	}

	public function getData()
	{
		$config = $this->getConfig();
		return $config['data'];
	}

	public function getAccess()
	{
		$data = $this->getData();

		if ( User::isSuperAdmin() ) {
			return [];
		}
		return (isset($data[$this->_page])) ? $data[$this->_page] : [];
	}

	public function hasAccess($action)
	{
		$data = $this->getData();
		return isset($data[$this->_page][$action]);
	}

	public function superAdmin()
	{
		if ( !User::isSuperAdmin() ) {
			$this->_pageNotFound();
		}
	}

	public function view()
	{
		$this->_action(__FUNCTION__);
	}

	public function create()
	{
		$this->_action(__FUNCTION__);
	}

	public function reset()
	{
		$this->_action(__FUNCTION__);
	}

	public function picture()
	{
		$this->_action(__FUNCTION__);
	}

	public function visibility()
	{
		$this->_action(__FUNCTION__);
	}

	public function edit()
	{
		$this->_action(__FUNCTION__);
	}

	public function password()
	{
		$this->_action(__FUNCTION__);
	}

	public function action($action)
	{
		$this->_action($action);
	}

	public function editDescendent($id_user, $user, $isDescendent, $action)
	{

		$this->_action($action);

		// Nobody can edit *Super Admin Master* except himself!
		if ( !User::isSuperAdmin() && User::getSuperAdminId() == $id_user ) {
			$this->_pageNotFound();
		}

		// a Super Admin can't edit greater users
		if (!User::isSuperAdmin()
			&& User::roleSuperAdmin()
			&& $id_user != User::getId()
			&& $user['id_role'] == User::getData('id_role')
			&& $user['id_user_parent'] <= User::getData('id_user_parent')
			&& !Helper::regexp('#'.User::getId().'#', $user['id_user_tree'])
		) {
			$this->_pageNotFound();
		}

		// If admin session id is not super admin
		if ( !User::isSuperAdmin() && $id_user != User::getId() && User::getData('id_role') > 2) {
			if ($this->hasAccess("descendants") && !$isDescendent) {
				$this->_pageNotFound();
			}

			// Can't edit parent - OR - ascedent users - OR - Siblings
			if (!Helper::regexp('#'.User::getId().'#', $user['id_user_tree'])
				|| $id_user < User::getId()
				|| $user['id_user_parent'] == User::getData('id_user_parent')
			) {
				$this->_pageNotFound();
			}
		}

	}

}
