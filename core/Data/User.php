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
use Roducks\Framework\Login;
use Roducks\Framework\EAV;
use Roducks\Framework\Path;
use Roducks\Libs\Data\Session;
use DB\Models\Users\Users;

class User extends EAV
{

	static $_sessionName = null;
	static $_sessionData = [];

	public function __construct($id)
	{
		$this->_id = $id;
		$this->_entity = Users::CLASS;

		parent::__construct();
	}

	private static function _getSessionName()
  {
    $siteConfig = Core::getSiteConfigFile("config", false);
    return (isset($siteConfig['SESSION_NAME'])) ? $siteConfig['SESSION_NAME'] : null;
  }

	private static function _isLoggedIn()
  {

		if (!is_null(self::$_sessionName)) {
			$sessionName = self::$_sessionName;
		} else {
			self::$_sessionName = self::_getSessionName();
			$sessionName = self::$_sessionName;
		}

    if (!is_null($sessionName)) {
      return Session::exists($sessionName);
    }

    return false;
  }

  private static function _getSession()
  {

    if (self::_isLoggedIn()) {
      return Login::getSession(self::$_sessionName);
    }

    return [];
  }

	static function isLoggedIn()
	{
		return self::_isLoggedIn();
	}

  static function getData($index)
  {

		if (!empty(self::$_sessionData)) {
			return self::$_sessionData[$index];
		}

    self::$_sessionData = self::_getSession();

    if (empty(self::$_sessionData)) {
      return null;
    }

    if (isset(self::$_sessionData[$index])) {
      return self::$_sessionData[$index];
    }
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
    return implode(' ', [self::getData('first_name'), self::getData('last_name')]);
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

}
