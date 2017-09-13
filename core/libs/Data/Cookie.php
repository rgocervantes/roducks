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

namespace rdks\core\libs\Data;

class Cookie{

	// this cookie will be visible in any subdomain
	static function set($name, $value, $serverName = ""){

		$host = $serverName;

		if((empty($serverName))){
			// www.domain.com
			$serverName = explode(".",$_SERVER['SERVER_NAME']);
			unset($serverName[0]); // remove www or subdomain
			$host = implode(".", $serverName);
		}
		
		//$expire = time()+60*60*24*30; // a month
		$expire = time() + (10 * 365 * 24 * 60 * 60); // a year
		setcookie($name, $value, $expire, '/', $host);

	}

	static function create($name, $value, $serverName = ""){
		if(!self::exists($name)){
			self::set($name, $value, $serverName);
		}
	}

	static function delete($name){
		self::set($name, NULL);
	}

	static function get($name){
		return $_COOKIE[$name];
	}

	static function exists($name){
		if(isset($_COOKIE[$name]) && self::get($name) != NULL){
			return true;
		}

		return false;
	}

}

?>