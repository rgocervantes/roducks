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

namespace Roducks\Page;

use Roducks\Framework\URL;
use Roducks\Framework\Post;
use Roducks\Libs\Request\Http;
use Roducks\Libs\Request\CORS;

class JSON extends GenericPage
{

	protected $_pageType = 'JSON';

	protected $post;
	protected $_authentication = false;
	protected $_format = true;

	private $_crossDomain = false;
	private $_methods = [];
	private $_domains;

	/* ------------------------------------*/
	/* 		JSON OUTPUT
	/* ------------------------------------*/
	private $_jsonCode = 200;
	private $_jsonMessage = "OK!";
	private $_jsonSuccess = true;

	static function encode($str)
	{
		return json_encode($str);
	}

	static function decode($str)
	{
		return json_decode($str, true);
	}

	static private function _httpCode($code)
	{
		switch ($code) {
			case 401:
				Http::sendHeaderAuthenticationFailed(false);
				break;
			case 404:
				Http::sendHeaderNotFound(false);
				break;
			case 501:
				Http::setHeaderInvalidRequest(false);
				break;
			default:
				# code...
				break;
		}
	}

	static function stOutput($obj)
	{

		$json = [
			'code' => $obj['code'],
			'success' => $obj['success'],
			'message' => $obj['message'],
			'data' => $obj['data']
		];

		if (!$obj['format']) $json = $obj['data'];

		Http::setHeaderJSON();
		echo self::encode($json);
		exit;
	}

	static function error($message, array $data = [])
	{

		$obj = [
			'code' => 0,
			'success' => false,
			'message' => $message,
			'data' => $data,
			'format' => true
		];

		self::stOutput($obj);
	}

	static function response($message, $code = 200)
	{
		self::_httpCode($code);
		self::stOutput(['format' => false, 'data' => ['message' => $message]]);
	}

	private function _jsonOutput($format = true)
	{

		$obj = [
			'code' => $this->_jsonCode,
			'success' => $this->_jsonSuccess,
			'message' => $this->_jsonMessage,
			'data' => $this->_jsonData,
			'format' => $format
		];

		self::stOutput($obj);

	}

	protected function setStatus($obj)
	{
		if (isset($obj['code'])) $this->_jsonCode = $obj['code'];
		if (isset($obj['success'])) $this->_jsonSuccess = $obj['success'];
		if (isset($obj['message'])) $this->_jsonMessage = $obj['message'];
	}

	protected function setSuccess($value)
	{
		$this->_jsonSuccess = $value;
	}

	protected function setCode($code)
	{
		$this->_jsonCode = $code;
	}

	protected function setMessage($message)
	{
		$this->_jsonMessage = $message;
	}

	protected function setError($code, $msg = "Error", $httpCode = 0)
	{
		$this->setCode($code);
		$this->setMessage($msg);
		$this->setSuccess(false);
		self::_httpCode($httpCode);
	}

	protected function data($key, $value = "")
	{
		if (is_array($key)) {
			$this->_jsonData = array_merge($this->_jsonData, $key);
		} else {
			$this->_jsonData[$key] = $value;
		}
	}

	protected function output($format = true)
	{

		$mode = $format;

		if ($mode && !$this->_format) {
			$mode = $this->_format;
		}

		if ($this->_crossDomain) {
			CORS::allowDomains($this->_domains);
			CORS::methods($this->_methods);
			CORS::maxAge();
		}

		$this->_jsonOutput($mode);
	}

	protected function crossDomain(array $options = [], $domains = "*")
	{
		$this->_crossDomain = true;
		$this->_methods = (count($options) > 0) ? $options : ["POST","OPTIONS"];
		$this->_domains = $domains;

		if (in_array('POST', $options)) {
			$this->post->required();
		}
	}

/* ------------------------------------*/
/* 		PUBLIC METHODS
/* ------------------------------------*/
	public function __construct(array $settings)
	{
		parent::__construct($settings);

		if ($this->_authentication) {
			$this->accessDenied();
		}

		$this->post = Post::init();
	}

}
