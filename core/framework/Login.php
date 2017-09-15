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

namespace rdks\core\framework;

use rdks\core\libs\Data\Session;
use rdks\core\libs\Protocol\Http;

class Login {

	const SESSION_ADMIN = "RDKS_ADMIN";
	const SESSION_FRONT = "RDKS_FRONT";
	const SESSION_SECURITY = "RDKS_SECURITY";
	const ENCRYPT = "sha512";

	private $_type;
	private $_url;

/*
| -----------------------------
|	STATIC
| -----------------------------
|*/

	static function generatePassword($pwd){

   		$salt = hash(self::ENCRYPT, uniqid(mt_rand(1, mt_getrandmax()), true));
    	$password = hash(self::ENCRYPT, $pwd . $salt);

		return ['salt' => $salt, 'password' => $password];
	}

	static function getToken($word = "token"){
		$password = self::generatePassword($word);

		return $password['salt'];
	}

	static function paywall($db_password, $db_salt, $input_password){
		return ($db_password == hash(self::ENCRYPT, $input_password . $db_salt));
	}

	static function getData($session, $index){
		switch ($session) {
			case Role::TYPE_USERS:
				$data = self::getAdmin();
				break;
			case Role::TYPE_SUBSCRIBERS:
				$data = self::getSubscriber();
				break;
			default:
				return "";
				break;
		}

		if(is_array($data) && count($data) > 0){
			if(isset($data[$index])){
				return $data[$index];
			}
			
		}

		return "";
	}

	/*
	| --------------------------------------------------------
	|		Admin
	| --------------------------------------------------------
	*/

	/**
	*	@return bool
	*/
	static function isAdminLoggedIn(){
		return Session::exists(self::SESSION_ADMIN);
	}

	/**
	*	@return array
	*/
	static function getAdmin(){

		if(self::isAdminLoggedIn()){
			return Session::get(self::SESSION_ADMIN);
		}

		return [];
	}	

	static function getAdminData($index){
		return self::getData(Role::TYPE_USERS,$index);
	}

	static function getAdminId(){
		return self::getAdminData('id_user');
	}
	 
	static function getAdminName(){
		return self::getAdminData('first_name');
	} 

	static function getAdminLastName(){
		return self::getAdminData('last_name');
	} 	

	static function getAdminFullName(){
		return self::getAdminData('first_name') . " " . self::getAdminData('last_name');
	} 		

	static function getAdminPicture(){
		return self::getAdminData('picture');
	} 

	static function getAdminEmail(){
		return self::getAdminData('email');
	} 		

	static function getSuperAdminId(){
		return 1;
	}

	static function isSuperAdmin(){
		return (self::getAdminId() == self::getSuperAdminId());
	}

	/**
	*	@param $obj array
	*/
	static function setAdmin($obj){

		$data = self::getAdmin();

		// if there's data in session, we merge it
		if(count($data) > 0){
			$obj = array_merge($data, $obj);
		}

		Session::set(self::SESSION_ADMIN, $obj);
	}

	/**
	*	@param $id integer USER ID
	*	@param $obj array Session data
	*/	
	static function updateAdmin($id, $obj){
		if($id == self::getAdminId()){
			self::setAdmin($obj);
		}
	}

	static function logoutAdmin(){
		Session::reset(self::SESSION_SECURITY);
		Session::reset(self::SESSION_ADMIN);
	}	

	static function roleSuperAdminMaster(){
		return (self::getAdminData('id_role') == 1);
	}

	static function roleSuperAdmin(){
		return (self::getAdminData('id_role') == 2);
	}

	static function roleAdmin(){
		return (self::getAdminData('id_role') == 6);
	}

	/*
	| --------------------------------------------------------
	|		Subscriber
	| --------------------------------------------------------
	*/

	/**
	*	@return bool
	*/
	static function isSubscriberLoggedIn(){
		return Session::exists(self::SESSION_FRONT);
	}

	/**
	*	@return array
	*/
	static function getSubscriber(){

		if(self::isSubscriberLoggedIn()){
			return Session::get(self::SESSION_FRONT);
		}

		return [];
	}		

	static function getSubscriberData($index){
		return self::getData(Role::TYPE_SUBSCRIBERS,$index);
	}

	static function getSubscriberId(){
		return self::getSubscriberData('id_user');
	}	

	static function getSubscriberName(){
		return self::getSubscriberData('first_name');
	} 

	static function getSubscriberLastName(){
		return self::getSubscriberData('last_name');
	} 	

	static function getSubscriberFullName(){
		return self::getSubscriberData('first_name') . " " . self::getSubscriberData('last_name');
	} 

	static function getSubscriberPicture(){
		return self::getSubscriberData('picture');
	} 

	static function getSubscriberEmail(){
		return self::getSubscriberData('email');
	} 	

	/**
	*	@param $obj array
	*/
	static function setSubscriber($obj){

		$data = self::getSubscriber();

		// if there's data in session, we merge it
		if(count($data) > 0){
			$obj = array_merge($data, $obj);
		}
		
		Session::set(self::SESSION_FRONT, $obj);
	}	

	/**
	*	@param $id integer USER ID
	*	@param $obj array Session data
	*/	
	static function updateSubscriber($id, $obj){
		if($id == self::getSubscriberId()){
			self::setSubscriber($obj);
		}
	}	

	static function logoutSubscriber(){
		Session::reset(self::SESSION_SECURITY);
		Session::reset(self::SESSION_FRONT);
	}

	static function security($option = true){
		if($option){
			return Session::exists(self::SESSION_SECURITY);
		} else {
			Session::reset(self::SESSION_SECURITY);
		}
	}

/*
|-----------------------------
|	PRIVATE
|-----------------------------
|*/

	private function _getSession(){
		switch ($this->_type) {
			case Role::TYPE_USERS:
				$session = self::isAdminLoggedIn();
				break;

			case Role::TYPE_SUBSCRIBERS:
				$session = self::isSubscriberLoggedIn();
				break;
		}

		return $session;
	}

/*
|-----------------------------
|	PUBLIC
|-----------------------------
|*/

	public function __construct($type, $url){
		$this->_type = $type;
		$this->_url = $url;
	}

	public function required(){

		$session = $this->_getSession();

		if(!$session){
			Http::redirect($this->_url);
		}
	}

	/**
	*	@var $url string URL
	*/	
	public function redirect($url = "/"){
		$session = $this->_getSession();

		if($session){
			Http::redirect($url);
		}
	}

	/**
	*
	*/
	public function access(){

		$session = $this->_getSession();

		if(!$session){
			Http::setHeaderInvalidRequest();
		}
	}

}

?>