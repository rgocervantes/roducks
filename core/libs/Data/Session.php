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

class Session{

	public static function start(){
		session_start();
	}

	public static function destroy(){
		session_destroy();
	}

	public static function timeout($lifetime = 3600){
		session_set_cookie_params($lifetime); // 1 hr.
	}
	
	public static function set($name,$value){
		$_SESSION[$name] = $value; 
	}

	public static function delete($n){
		self::set($n,null);
	}	

	public static function exists($n){
		return isset($_SESSION[$n]);
	}
	
	public static function get($n){
		return  (self::exists($n)) ? $_SESSION[$n] : [];
	}

	public static function update($name, array $data = []){

		if(self::exists($name)){
			$stored = self::get($name);
			foreach ($data as $key => $value) {
				$stored[$key] = $value;
			}
			
			self::set($name, $stored);
		}
	}

	public static function remove($name, array $data = []){

		if(self::exists($name)){
			$stored = self::get($name);
			foreach ($data as $key) {
				if(isset($stored[$key])){
					unset($stored[$key]);
				}
			}
			
			self::set($name, $stored);
		}
	}	

	public static function reset($name){
		if(self::exists($name)){
			unset($_SESSION[$name]);		
		}
	}
		
}

?>