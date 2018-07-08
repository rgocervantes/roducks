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

namespace Roducks\API;

use Roducks\Libs\Request\Http;
use Roducks\Framework\Router;
use Roducks\Page\Service;
use Firebase\JWT\JWT;

class API extends Service 
{

	const JWT_SECRET_KEY = "R0duck5";
	const JWT_EXPIRATION = 3600;
	const JWT_ISS = "http://example.org";
	const JWT_AUD = "http://example.com";

	private $_jwt = [];

	static function router($uri, $version, $callback)
	{
		Router::prefix($uri, $callback, $version, true);
	}

	static function auth($endpoint, $dispatch, $params)
	{
		Router::auth($endpoint, $dispatch, $params);
	}

	static function endpoint($endpoint, $dispatch, $params)
	{
		Router::api($endpoint, $dispatch, $params);
	}

	static function get($endpoint, $dispatch, $params = "")
	{
		Router::get($endpoint, $dispatch, $params);
	}

	static function me($endpoint, $dispatch, $params = "")
	{
		Router::get($endpoint, $dispatch, $params);
	}

	static function post($endpoint, $dispatch, $params)
	{
		Router::post($endpoint, $dispatch, $params);
	}

	static function put($endpoint, $dispatch, $params)
	{
		Router::put($endpoint, $dispatch, $params);
	}

	static function delete($endpoint, $dispatch, $params)
	{
		Router::delete($endpoint, $dispatch, $params);
	}

	protected function setError($code, $msg)
	{
		Http::sendHeaderAuthenticationFailed(false);

		$this->data("code", $code);
		$this->data("message", $msg);
		$this->output();
	}

	private function _getSecretKey()
	{
		$secret = $this->config('file:jwt', 'secret');

		if (!is_array($secret)) {
			return $secret;
		}

		return static::JWT_SECRET_KEY;
	}

	protected function data($key, $value = "")
	{
		if (is_array($key)) {
			parent::data(['data' => $key]);
		}else{
			parent::data($key, $value);
		}
	}

	protected function output($format = true)
	{
		parent::output(false);
	}

	protected function getHeader($name)
	{
		return Http::getRequestHeader($name);
	}

	protected function generateToken($timeout = 3600, array $data = [], $leeway = 720000)
	{

		$time = time();

		$token = [
	        "iss" => static::JWT_ISS,
	        "aud" => static::JWT_AUD,
	        "iat" => $time,
	       	"exp" => $time + $timeout,
	        "nbf" => $time,
	        "data" => []
		];

		if (count($data) > 0) {
			$token['data'] = $data;
		}

		JWT::$leeway = $leeway; // $leeway in seconds
		return JWT::encode($token, $this->_getSecretKey());
	}

	protected function getToken($data = true)
	{
		$verifyToken = $this->verifyToken();

		if (!$verifyToken) {
			return [];
		}

		$token = $this->_jwt['decoded']; 

		return ($data) ? $token->data : $token;
	}

	public function verifyToken($leeway = 720000)
	{

		$this->_jwt['encoded'] = preg_replace('/^Bearer\s(.+)$/', '$1', Http::getAuthorizationHeader());

		try {
			$this->_jwt['decoded'] = JWT::decode($this->_jwt['encoded'], $this->_getSecretKey(), array('HS256'));
		} catch ( \Firebase\JWT\ExpiredException $e ) {
			$this->setError(401, $e->getMessage());
			return false;
		} catch( \Exception $e ) {
			$this->setError(401, $e->getMessage());
			return false;
		}

		return true;

	}

}