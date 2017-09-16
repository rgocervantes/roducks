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

namespace rdks\core\page;

use rdks\core\framework\URL;
use rdks\core\framework\Post;
use rdks\core\libs\Protocol\Http;
use rdks\core\libs\Protocol\CORS;

class JSON extends GenericPage {

	protected $post;

	private $_crossDomain = false;
	private $_allowRequest = false;
	private $_methods = [];
	private $_domains;

	/* ------------------------------------*/
	/* 		JSON OUTPUT
	/* ------------------------------------*/	
	private $_jsonCode = 200;
	private $_jsonMessage = "OK!";
	private $_jsonSuccess = true;	
	
	static function encode($str){
		return json_encode($str);
	}

	static function decode($str){
		return json_decode($str, true);
	}

	static function stOutput($obj){

		$json = [
			'code' => $obj['code'],
			'success' => $obj['success'],
			'message' => $obj['message'],
			'data' => $obj['data']
		];

		if(!$obj['format']) $json = $obj['data'];

		Http::setHeaderJSON();
		echo self::encode($json);
		exit;
	}

	static function error($message, array $data = []){

		$obj = [
			'code' => 0,
			'success' => false,
			'message' => $message,
			'data' => $data,
			'format' => true
		];

		self::stOutput($obj);
	}

	private function _jsonOutput($format = true){

		$obj = [
			'code' => $this->_jsonCode,
			'success' => $this->_jsonSuccess,
			'message' => $this->_jsonMessage,
			'data' => $this->_jsonData,
			'format' => $format
		];

		self::stOutput($obj);

	}	

	protected function setStatus($obj){
		if(isset($obj['code'])) $this->_jsonCode = $obj['code'];
		if(isset($obj['success'])) $this->_jsonSuccess = $obj['success'];
		if(isset($obj['message'])) $this->_jsonMessage = $obj['message'];
	}

	protected function setSuccess($value){
		$this->_jsonSuccess = $value;
	}

	protected function setError($code, $msg){
		$this->_jsonCode = $code;
		$this->_jsonMessage = $msg;
		$this->_jsonSuccess = false;
	}

	protected function setCode($code){
		$this->_jsonCode = $code;
	}

	protected function setMessage($message){
		$this->_jsonMessage = $message;
	}	

	protected function data($key, $value = ""){
		if(is_array($key)){
			$this->_jsonData = array_merge($this->_jsonData, $key);
		}else{
			$this->_jsonData[$key] = $value;
		}
	}

	protected function output($format = true){

		$url = URL::getURL();
		$origin = Http::getOrigin();

		if($this->_crossDomain){
			$cors = new CORS;
			$cors->allowDomains($this->_domains);
			$cors->methods($this->_methods);
			$cors->maxAge();
		} else if(!empty($origin) && $url != $origin && !$this->_allowRequest){
			Http::sendHeaderForbidden();
		}

		$this->_jsonOutput($format);
	}

	protected function allowRequest(){
		$this->_allowRequest = true;
	}

	protected function crossDomain(array $options = [], $domains = "*"){
		$this->_crossDomain = true;
		$this->_methods = (count($options) > 0) ? $options : ["POST","OPTIONS"];
		$this->_domains = $domains;
	}

/* ------------------------------------*/
/* 		PUBLIC METHODS
/* ------------------------------------*/
	public function __construct($settings){
		parent::__construct($settings);
		$this->post = Post::init();
	}

	public function getData(){
		$this->output();
	}

} 