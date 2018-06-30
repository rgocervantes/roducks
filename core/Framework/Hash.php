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

class Hash
{

	const ENCRYPT = "sha512";

	static function get($str)
	{
		return hash(self::ENCRYPT, $str);
	}

	static function getSaltPassword($pwd)
	{
   		$salt = self::get(uniqid(mt_rand(1, mt_getrandmax()), true));
    	$password = self::get($pwd . $salt);

		return ['salt' => $salt, 'password' => $password];
	}

	static function getToken()
	{
		$password = self::getSaltPassword('token');
		return $password['salt'];
	}

}