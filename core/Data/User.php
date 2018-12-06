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

	public function __construct($id)
	{
		$this->_id = $id;
		$this->_entity = Users::CLASS;

		parent::__construct();
	}

	/*
    Array
    (
        [id_user] => 1
        [id_user_parent] => 0
        [id_user_tree] => 0
        [id_role] => 7
        [email] => rodrigocervantez@gmail.com
        [first_name] => Rodrigo
        [last_name] => Cervantes
        [gender] => male
        [picture] => user_20181204150942.jpeg
        [active] => 1
        [trash] => 0
        [token] =>
        [loggedin] => 0
        [location] => 127.0.0.1
        [expires] => 0
        [expiration_date] =>
        [created_at] => 2018-12-04 11:21:59
        [updated_at] => 2018-12-04 17:29:26
        [deleted_at] =>
        [role] => Subscribers
        [ractive] => 1
        [config] => subscribers.json
    )
  */
  private static function _getSession()
  {
    $siteConfig = Core::getSiteConfigFile("config", false);

    if (isset($siteConfig['SESSION_NAME'])) {
      $sessionName = $siteConfig['SESSION_NAME'];
      $isLoggedIn = Session::exists($sessionName);
      if ($isLoggedIn) {
        $session = Login::getSession($sessionName);
        return $session;
      }
    }

    return [];
  }

  private static function _getData($index)
  {
    $data = self::_getSession();

    if (empty($data)) {
      return null;
    }

    if (isset($data[$index])) {
      return $data[$index];
    }
  }

  static function getEmail()
  {
    return self::_getData('email');
  }

  static function getId()
  {
    return self::_getData('id_user');
  }

  static function getRoleId()
  {
    return self::_getData('id_role');
  }

  static function getFirstName()
  {
    return self::_getData('first_name');
  }

  static function getLastName()
  {
    return self::_getData('last_name');
  }

  static function getFullName()
  {
    return implode(' ', [self::_getData('first_name'), self::_getData('last_name')]);
  }

  static function getGender()
  {
    return self::_getData('gender');
  }

  static function getConfigName()
  {
    return self::_getData('config');
  }

  static function getPicture($absolute = false, $crop = 0)
  {
    return Path::getPublicUploadedUsers(self::_getData('picture'), $crop, $absolute);
  }

}
