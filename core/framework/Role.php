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

namespace Roducks\Framework;

class Role {

	const URL = "/roles";

	const TYPE_USERS = 1;
	const TYPE_SUBSCRIBERS = 2;
	const TYPE_CLIENTS = 3;
	const TYPE_SUPPLIERS = 4;

	static $types = [
		self::TYPE_USERS => [
			'session' => Login::SESSION_ADMIN,
			'title' => TEXT_USERS,
			'url' => "/roles/list",
			'icon' => 'user',
			'access' => ["roles","view"]
		],
		self::TYPE_SUBSCRIBERS => [
			'session' => Login::SESSION_FRONT,
			'title' => TEXT_SUBSCRIBERS,
			'url' => "/roles/list",
			'icon' => 'user',
			'access' => ["roles","view"]
		],
		self::TYPE_CLIENTS => [
			'session' => Login::SESSION_CLIENT,
			'title' => TEXT_CLIENTS,
			'url' => "/roles/list",
			'icon' => 'briefcase',
			'access' => ["roles","view"]
		],
		self::TYPE_SUPPLIERS => [
			'session' => Login::SESSION_SUPPLIER,
			'title' => TEXT_SUPPLIERS,
			'url' => "/roles/list",
			'icon' => 'briefcase',
			'access' => ["roles","view"]
		]
	];

	static function getRole($type){
		return (isset(self::$types[$type])) ? self::$types[$type] : [];
	}

	static function getData($type, $index){
		$role = self::getRole($type);
		return (isset($role[$index])) ? $role[$index] : '';
	}

	static function getIds(){
		return array_keys(self::$types);
	}

	static function getList(array $list = []){
		$ret = [];

		foreach ($list as $type => $role) {
			$ret[$type] = array_merge(self::$types[$type], $role);
		}

		return $ret;
	}

	static function getMenu(array $list = []){

		$menu = [];

		foreach ($list as $type => $role) {
			$menu[] = [
				'link' => URL::build(self::$types[$type]['url'], ['type' => $type], false), 
				'text' => self::$types[$type]['title'],
				'access' => self::$types[$type]['access'],
			];
		}

		return $menu;
	}

	static function getTitle($type){
		return self::getData($type, "title");
	}

	static function getSession($type){
		return self::getData($type, "session");
	}

	static function getIcon($type){
		return self::getData($type, "icon");
	}

}