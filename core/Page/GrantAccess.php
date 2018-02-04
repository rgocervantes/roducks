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

use Roducks\Framework\Login;
use Roducks\Framework\Role;
use Roducks\Framework\Helper;
use Roducks\Framework\Error;
use Roducks\Libs\Data\Request;

class GrantAccess{

	private $_page;
	private $_session;
	private $_view;
	private $_json = false;

	private function _pageNotFound(){
		if($this->_json){
			JSON::error(TEXT_GRANT_ACCESS_REQUIRED);
		} else {
			Error::pageNotFound();
		}
	}

	private function _load($name){

		$file = DIR_ROLES . $name;

		if(file_exists($file) && !empty($name)){
			$config = Request::getContent($file);
			$json = JSON::decode($config);
			return $json;
		} else {
			if(!Login::isSuperAdmin()){
				Error::debug("Role config file was not found", __LINE__, __FILE__, $file, 'Make sure it exists in: <b>' . DIR_ROLES . "</b>");
			}
		}

		return ['data' => []];
	}

	private function _action($action){
		if(!Login::isSuperAdmin() && !$this->hasAccess($action)){
			$this->_pageNotFound();
		}
	}

/*
//---------------------------------
//	PUBLIC METHODS
//---------------------------------
*/
	public function __construct($page, $session){
		$this->_page = $page;
		$this->_session = $session;
	}

	public function json(){
		$this->_json = true;
	}

	public function getFileConfig($name){
		return $this->_load($name);
	}

	public function getConfig(){

		$name = Login::getData($this->_session, "config");
		$conf = $this->getFileConfig($name);

		if(empty($conf)){
			return ['data' => []];
		}

		return $conf;
	}		

	public function getData(){
		$config = $this->getConfig();
		return $config['data'];
	}

	public function getAccess(){
		$data = $this->getData();

		if( Login::isSuperAdmin() ) {
			return [];
		}
		return (isset($data[$this->_page])) ? $data[$this->_page] : [];
	}

	public function hasAccess($action){
		$data = $this->getData();
		return isset($data[$this->_page][$action]);
	}	

	public function superAdmin(){
		if( !Login::isSuperAdmin() ) {
			$this->_pageNotFound();
		}
	}	

	public function view(){
		$this->_action(__FUNCTION__);
	}

	public function create(){
		$this->_action(__FUNCTION__);
	}	

	public function reset(){
		$this->_action(__FUNCTION__);
	}

	public function picture(){
		$this->_action(__FUNCTION__);
	}	

	public function visibility(){
		$this->_action(__FUNCTION__);
	}	

	public function edit(){
		$this->_action(__FUNCTION__);
	}

	public function password(){
		$this->_action(__FUNCTION__);
	}

	public function action($action){
		$this->_action($action);
	}		

	public function editDescendent($id_user, $user, $isDescendent, $action){

		$this->_action($action);

		// Nobody can edit *Super Admin Master* except himself!
		if( !Login::isSuperAdmin() && Login::getSuperAdminId() == $id_user ){
			$this->_pageNotFound();	
		}

		// a Super Admin can't edit greater users
		if(!Login::isSuperAdmin() 
			&& Login::roleSuperAdmin() 
			&& $id_user != Login::getAdminId() 
			&& $user['id_role'] == Login::getAdminData('id_role') 
			&& $user['id_user_parent'] <= Login::getAdminData('id_user_parent')
			&& !Helper::regexp('#'.Login::getAdminId().'#', $user['id_user_tree'])
		){
			$this->_pageNotFound();
		}

		// If admin session id is not super admin
		if( !Login::isSuperAdmin() && $id_user != Login::getAdminId() && Login::getAdminData('id_role') > 2) {
			if($this->hasAccess("descendants") && !$isDescendent){
				$this->_pageNotFound();	
			}

			// Can't edit parent - OR - ascedent users - OR - Siblings
			if(!Helper::regexp('#'.Login::getAdminId().'#', $user['id_user_tree']) 
				|| $id_user < Login::getAdminId() 
				|| $user['id_user_parent'] == Login::getAdminData('id_user_parent')
			){
				$this->_pageNotFound();	
			}
		}

	}

}