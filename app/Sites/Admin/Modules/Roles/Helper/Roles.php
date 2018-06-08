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

namespace App\Sites\Admin\Modules\Roles\Helper;

use Roducks\Framework\Role;
use Roducks\Page\HelperPage;

class Roles extends HelperPage {

	static $icon = "tags";
	static $list = [
		Role::TYPE_USERS 		=> [],
		Role::TYPE_SUBSCRIBERS 	=> [],
		Role::TYPE_CLIENTS 		=> []		
	];

	static function getIds(){
		return array_keys(self::$list);
	}

	static function getIcon($type = ""){
		
		if(empty($type)){
			return self::$icon;
		}

		$roles = Role::getList(self::$list);

		return (isset($roles[$type]['icon'])) ? $roles[$type]['icon'] : self::$icon;
	}

}