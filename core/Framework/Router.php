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

	private $_version,
			$_dispatch = [];

	private function _method($type, $token, $endpoint, $dispatch, $params)
	{
		list($class, $method) = explode("::", $dispatch);

		$data = [
			'dispatch' => Dispatch::api($class, $method),
			$type => $params()
		];

		if ($token) {
			$data['jwt'] = true;
		}

		$this->_dispatch["/{$this->_version}{$endpoint}"] = $data;
	}

	public function __construct($version)
	{
		$this->_version = $version;
	}

	public function auth($endpoint, $dispatch, $params)
	{
		$this->_method('POST', false, $endpoint, $dispatch, $params);
	}

	public function post($endpoint, $dispatch, $params)
	{
		$this->_method('POST', true, $endpoint, $dispatch, $params);
	}

	public function get($endpoint, $dispatch, $params)
	{
		$this->_method('GET', true, $endpoint, $dispatch, $params);
	}

	public function put($endpoint, $dispatch, $params)
	{
		$this->_method('PUT', true, $endpoint, $dispatch, $params);
	}

	public function delete($endpoint, $dispatch, $params)
	{
		$this->_method('DELETE', true, $endpoint, $dispatch, $params);
	}

	public function api($endpoint, $dispatch, $params)
	{
		$path1 = [
			'dispatch' => Dispatch::api($dispatch),
			'jwt' => true
		];

		$path2 = [
			'dispatch' => Dispatch::api($dispatch),
			'jwt' => true
		];

		foreach ($params() as $key => $value) {
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

		$this->_dispatch["/{$this->_version}{$endpoint}"] = $path1;
		$this->_dispatch["/{$this->_version}{$endpoint}/(?P<id>\d+)"] = $path2;

	}

	public function dispatch()
	{
		return $this->_dispatch;
	}

}