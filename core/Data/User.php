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

namespace Roducks\Data;

use Roducks\Framework\Core;
use Roducks\Framework\EAV;
use Roducks\Framework\Path;
use Roducks\Framework\Hash;
use Roducks\Services\Auth as AuthService;
use Roducks\Libs\Data\Session;
use DB\Models\Users\Users;

class User extends EAV
{

	const SESSION_ADMIN = "RDKS_ADMIN";
	const SESSION_FRONT = "RDKS_FRONT";
	const SESSION_CLIENT = "RDKS_CLIENT";
	const SESSION_SUPPLIER = "RDKS_SUPPLIER";
	const SESSION_SECURITY = "RDKS_SECURITY";

	static $_sessionName = null;
	static $_sessionType = null;
	static $_sessionData = [];

	public function __construct($id)
	{
		$this->_id = $id;
		$this->_entity = Users::CLASS;

		parent::__construct();
	}

	static function generatePassword($pwd)
	{
   	return Hash::getSaltPassword($pwd);
	}

	static function paywall($db_password, $db_salt, $input_password)
	{
		return ($db_password == Hash::get($input_password . $db_salt));
	}

	static function security($return = true)
	{
		if ($return) {
			return Session::exists(self::SESSION_SECURITY);
		} else {
			Session::reset(self::SESSION_SECURITY);
		}
	}

	private static function _getConfigData()
	{
		$siteConfig = Core::getSiteConfigFile("config", false);
		$sessionName = (isset($siteConfig['SESSION_NAME'])) ? $siteConfig['SESSION_NAME'] : null;
		$roleType = (isset($siteConfig['ROLE_TYPE'])) ? $siteConfig['ROLE_TYPE'] : null;

		return [
			'session_name' => $sessionName,
			'role_type' => $roleType
		];

	}

	private static function _getRoleType()
  {

		if (is_null(self::$_roleType)) {
	    $siteConfig = self::_getConfigData();
	    $roleType = $siteConfig['role_type'];
			self::$_roleType = $roleType;
		}

		return self::$_roleType;
  }

	private static function _getSessionName()
  {

		if (is_null(self::$_sessionName)) {
	    $siteConfig = self::_getConfigData();
	    $sessionName = $siteConfig['session_name'];
			self::$_sessionName = $sessionName;
		}

		return self::$_sessionName;
  }

	private static function _isLoggedIn()
  {

		$sessionName = self::_getSessionName();

    if (!is_null($sessionName)) {
      return Session::exists($sessionName);
    }

    return false;
  }

  private static function _getStoredData()
  {

    if (self::_isLoggedIn()) {
      return Session::get(self::$_sessionName);
    }

    return [];
  }

	static function isLoggedIn()
	{
		return self::_isLoggedIn();
	}

	/**
	*	@param $obj array
	*/
	static function setSessionData($obj)
	{

		$data = self::_getStoredData();
		$sessionName = self::_getSessionName();

		// if there's data in session, we merge it
		if (count($data) > 0) {
			$obj = array_merge($data, $obj);
		}

		Session::set($sessionName, $obj);
	}

	/**
	*	@param $id integer USER ID
	*	@param $obj array Session data
	*/
	static function updateSessionData($id, $obj)
	{
		if ($id == self::getId()) {
			self::setSessionData($obj);
		}
	}

  static function getData($index)
  {

		if (empty(self::$_sessionData)) {
			self::$_sessionData = self::_getStoredData();
		}

    if (isset(self::$_sessionData[$index])) {
      return self::$_sessionData[$index];
    }

		return "";
  }

  static function getEmail()
  {
    return self::getData('email');
  }

  static function getId()
  {
    return self::getData('id_user');
  }

  static function getRoleId()
  {
    return self::getData('id_role');
  }

  static function getFirstName()
  {
    return self::getData('first_name');
  }

	static function getName()
  {
    return self::getFirstName();
  }

  static function getLastName()
  {
    return self::getData('last_name');
  }

  static function getFullName()
  {
    return implode(' ', [self::getFirstName(), self::getLastName()]);
  }

  static function getGender()
  {
    return self::getData('gender');
  }

  static function getConfigName()
  {
    return self::getData('config');
  }

  static function getPicture($absolute = false, $crop = 0)
  {
    return Path::getPublicUploadedUsers(self::getData('picture'), $crop, $absolute);
  }

	static function getSuperAdminId()
	{
		return 1;
	}

	static function isSuperAdmin()
	{
		return (self::getId() == self::getSuperAdminId());
	}

	static function getConfigData()
	{
		return self::_getConfigData();
	}

	static function logout()
	{

		if (self::_isLoggedIn()) {
			$sessionName = self::_getSessionName();
			$userId = self::getId();
			Session::reset(self::SESSION_SECURITY);
			Session::reset($sessionName);

			AuthService::init()->logout($userId);
		}

	}

	static function roleSuperAdminMaster()
	{
		return (self::getData('id_role') == 1);
	}

	static function roleSuperAdmin()
	{
		return (self::getData('id_role') == 2);
	}

	static function roleAdmin()
	{
		return (self::getData('id_role') == 6);
	}

}
