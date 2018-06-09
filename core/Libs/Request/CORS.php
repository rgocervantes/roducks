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

namespace Roducks\Libs\Request;

class CORS
{

	static function accessControl($key,$value)
	{
		Http::setHeader("Access-Control-{$key}", $value);
	}
	
	/**
	*	GET, POST, OPTIONS, PUT, DELETE
	*/
	public function methods(array $arr = [])
	{
		if (is_array($arr) && count($arr) > 0) {
			self::accessControl("Allow-Methods:",implode(", ",$arr));	
		}
	}

	public function allowDomains($domains = "*")
	{
		$domains = (is_array($domains)) ? implode(" ", $domains) : $domains;
		self::accessControl("Allow-Origin:", $domains);
	}

	public function headers(array $arr)
	{
		$headers = (is_array($arr)) ? implode(", ", $arr) : $arr;
		self::accessControl("Allow-Headers:", $headers);
	}

	public function exposeHeaders(array $arr)
	{
		$headers = (is_array($arr)) ? implode(" ", $arr) : $arr;
		self::accessControl("Expose-Headers:", $headers);
	}

	public function credentails()
	{
		self::accessControl("Allow-Credentials:", "true");
	}	

	public function maxAge($value = 1728000)
	{
		self::accessControl("Max-Age:", $value);
	}

}