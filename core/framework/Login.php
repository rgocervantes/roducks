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
	const SESSION_CLIENT = "RDKS_CLIENT";
	const SESSION_SUPPLIER = "RDKS_SUPPLIER";
	const SESSION_SECURITY = "RDKS_SECURITY";
	const ENCRYPT = "sha512";

	private $_url;
	private $_session = false;

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

	static function security($return = true){
		if($return){
			return Session::exists(self::SESSION_SECURITY);
		} else {
			Session::reset(self::SESSION_SECURITY);
		}
	}

	/**
	*	@return array
	*/
	static function getSession($name){

		if(Session::exists($name)){
			return Session::get($name);
		}

		return [];
	}	

	static function getData($session, $index){

		$data = self::getSession($session);

		if(is_array($data) && count($data) > 0){
			if(isset($data[$index])){
				return $data[$index];
			}
		}

		return "";
	}

	static function getId($session){
		return self::getData($session, "id_user");
	}

	static function logout($session){
		Session::reset(self::SESSION_SECURITY);
		Session::reset($session);
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
		return self::getSession(self::SESSION_ADMIN);
	}	

	static function getAdminData($index){
		return self::getData(self::SESSION_ADMIN,$index);
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
		return self::getSession(self::SESSION_FRONT);
	}		

	static function getSubscriberData($index){
		return self::getData(self::SESSION_FRONT,$index);
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

	/*
	| --------------------------------------------------------
	|		Client
	| --------------------------------------------------------
	*/

	/**
	*	@return bool
	*/
	static function isClientLoggedIn(){
		return Session::exists(self::SESSION_CLIENT);
	}

	/**
	*	@return array
	*/
	static function getClient(){
		return self::getSession(self::SESSION_CLIENT);
	}

	static function getClientData($index){
		return self::getData(self::SESSION_CLIENT,$index);
	}

	static function getClientId(){
		return self::getClientData('id_user');
	}

	static function getClientName(){
		return self::getClientData('first_name');
	} 

	static function getClientLastName(){
		return self::getClientData('last_name');
	} 	

	static function getClientFullName(){
		return self::getClientData('first_name') . " " . self::getClientData('last_name');
	} 

	static function getClientPicture(){
		return self::getClientData('picture');
	} 

	static function getClientEmail(){
		return self::getClientData('email');
	} 	

	/**
	*	@param $obj array
	*/
	static function setClient($obj){

		$data = self::getClient();

		// if there's data in session, we merge it
		if(count($data) > 0){
			$obj = array_merge($data, $obj);
		}
		
		Session::set(self::SESSION_CLIENT, $obj);
	}	

	/**
	*	@param $id integer USER ID
	*	@param $obj array Session data
	*/	
	static function updateClient($id, $obj){
		if($id == self::getClientId()){
			self::setClient($obj);
		}
	}	

	static function logoutClient(){
		Session::reset(self::SESSION_SECURITY);
		Session::reset(self::SESSION_CLIENT);
	}	

/*
|-----------------------------
|	PUBLIC
|-----------------------------
|*/
	/**
	 *	@example 
	 *	@var $session = Login::SESSION_ADMIN
	 */
	public function __construct($session, $url){
		$this->_session = Session::exists($session);
		$this->_url = $url;
	}

	public function required(){

		if(!$this->_session){
			Http::redirect($this->_url);
		}
	}

	/**
	*	@var $url string URL
	*/	
	public function redirect($url = "/"){

		if($this->_session){
			Http::redirect($url);
		}
	}

}

?>