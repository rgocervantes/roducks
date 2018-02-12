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

class Router
{

	static 
		$_jwt = false,
		$_version = "",
		$_dispatch = [];

	static function init($routes)
	{
		$routes();
	}

	static function dispatch()
	{
		return self::$_dispatch;
	}

	static function _add($endpoint, $data)
	{

		if(is_array($endpoint)){
			if(isset($endpoint['uri']) && isset($endpoint['id'])) {
				$key = array_keys($endpoint['uri'])[0];
				$endpoint = array_values($endpoint['uri'])[0] . $endpoint['id'];
			} else {
				$key = array_keys($endpoint)[0];
				$endpoint = array_values($endpoint)[0];
			}

		} else {
			$key = $endpoint;
		}

		if(preg_match('/@/', $key)) {
			list($aux, $path) = explode("@", $key);

			if($endpoint == "/"){
				self::$_dispatch[self::$_version.$path] = $data;
			} else {
				self::$_dispatch[$path]['path'][self::$_version.$endpoint] = $data;
			}
			 

		} else {
			self::$_dispatch[self::$_version.$endpoint] = $data;
		}

	}

	static function _method($type, $token, $endpoint, $dispatch, $params = "")
	{

		$data = [
			'dispatch' => $dispatch
		];

		if (is_callable($params)) {
			if ($type == 'GET_POST') {
				$p = $params();
				$data['GET'] = $p['GET'];
				$data['POST'] = $p['POST'];
			} else {
				$data[$type] = $params();
			}
		}

		if ($token) {
			$data['jwt'] = true;
		}

		self::_add($endpoint, $data);		
	}

	static function auth($endpoint, $dispatch, $params)
	{
		self::_method('POST', false, $endpoint, $dispatch, $params);
	}

	static function post($endpoint, $dispatch, $params = "")
	{
		self::_method('POST', self::$_jwt, $endpoint, $dispatch, $params);
	}

	static function get($endpoint, $dispatch, $params = "")
	{
		self::_method('GET', self::$_jwt, $endpoint, $dispatch, $params);
	}

	static function get_post($endpoint, $dispatch, $params = "")
	{
		self::_method('GET_POST', self::$_jwt, $endpoint, $dispatch, $params);
	}

	static function put($endpoint, $dispatch, $params)
	{
		self::_method('PUT', self::$_jwt, $endpoint, $dispatch, $params);
	}

	static function delete($endpoint, $dispatch, $params = "")
	{
		self::_method('DELETE', self::$_jwt, $endpoint, $dispatch, $params);
	}

	static function api($endpoint, $dispatch, $params)
	{
		$path1 = [
			'dispatch' => $dispatch,
			'jwt' => true
		];

		$path2 = [
			'dispatch' => $dispatch,
			'jwt' => true
		];

		$methods = $params();
		$options = ['catalog', 'store', 'update', 'row', 'remove'];

		foreach ($options as $option) {
			if(!isset($methods[$option])){
				$methods[$option] = [];
			}
		}

		foreach ($methods as $key => $value) {
			switch ($key) {
				case 'catalog':
					$path1['GET'] = $value;
					break;
				case 'store':
					$path1['POST'] = $value;
					break;
				case 'update':
					$path2['PUT'] = $value;
					break;
				case 'row':
					$path2['GET'] = $value;
					break;
				case 'remove':
					$path2['DELETE'] = $value;
					break;
			}
		}

		self::_add($endpoint, $path1);
		self::_add(['uri' => $endpoint, 'id' => "/(?P<id>\d+)"], $path2);
	}

	static function path($uri, $callback, $version = "", $jwt = false)
	{
		if (!empty($version)) {
			self::$_version = "/{$version}";
		} else {
			self::$_version = "";
		}

		self::$_jwt = $jwt;

		$callback("path@{$uri}");
	}

}