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

class Role {

	const URL = "/roles";

	const TYPE_USERS = 1;
	const TYPE_SUBSCRIBERS = 2;
	const TYPE_CLIENTS = 3;
	const TYPE_SUPPLIERS = 4;

	static function getAll($type = ""){

		$url = self::URL . "/list";
		
		$types = [
			self::TYPE_USERS => [
				'title' => TEXT_USERS,
				'url' => $url,
				'icon' => 'user',
				'access' => ["roles","view"]
			],
			self::TYPE_SUBSCRIBERS => [
				'title' => TEXT_SUBSCRIBERS,
				'url' => $url,
				'icon' => 'user',
				'access' => ["roles","view"]
			],
			self::TYPE_CLIENTS => [
				'title' => TEXT_CLIENTS,
				'url' => $url,
				'icon' => 'briefcase',
				'access' => ["roles","view"]
			],
			self::TYPE_SUPPLIERS => [
				'title' => TEXT_SUPPLIERS,
				'url' => $url,
				'icon' => 'briefcase',
				'access' => ["roles","view"]
			]
		];

		if(!empty($type)){
			return (isset($types[$type])) ? $types[$type] : [];
		}

		return $types;

	}

	static function getIds(){
		return array_keys(self::getAll());
	}

	static function getList(array $list = []){
		$roles = self::getAll();
		$ret = [];

		foreach ($list as $type => $role) {
			$ret[$type] = array_merge($roles[$type], $role);
		}

		return $ret;
	}

	static function getMenu(array $list = []){

		$roles = self::getAll();
		$menu = [];

		foreach ($list as $type => $role) {
			$menu[] = [
				'link' => URL::build($roles[$type]['url'], ['type' => $type]), 
				'text' => $roles[$type]['title'],
				'access' => $roles[$type]['access'],
			];
		}

		return $menu;

	}

	static function getIcon($type){
		$roles = self::getAll();
		return (isset($roles[$type]['icon'])) ? $roles[$type]['icon'] : 'user';
	}

}