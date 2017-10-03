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

/*

=====================
POST Example
=====================
	$request = Request::init('POST',"http://www.google.com");
	$request
	->params(['key' => "cmsdeu3719384c"])
	->persistSession()
	->ssl(0)
	->execute();

=====================
GET Example
=====================
	$request = Request::init('GET',"http://www.google.com");
	$request
	->params(['key' => "cmsdeu3719384c"])
	->execute();

=====================
Result
=====================

	echo $request->getOutput();
	echo $request->getContentType();
	echo $request->getHttpCode();

	if($request->json()){
		$response = $request->getOutput(true);
	}
	
	if($request->success()){

	} else {
		
	}

*/

namespace rdks\core\libs\Data;

class Request{

	private $_ch;
	private $_url;
	private $_type;
	private $_result = null;
	private $_contentType = 'undefined';
	private $_httpCode = 404;
	private $_redirect = false; 
	private $_effectiveURL = '';

	static function getContent($url){
		if(empty($url)) return "";
		return file_get_contents($url);
	}

	static function init($type,$url){
		return new Request($type,$url);
	}

	/**
	*	$name = "XGET"
	*/
	private function _customRequest(){
		curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $this->_type);
	}	

	public function __construct($type,$url){
		$this->_ch = curl_init();
		$this->_url = $url;
		$this->_type = strtoupper($type);

		if($this->_type != 'POST' && $this->_type != 'GET'){
			$this->_customRequest();
		}

	}

	public function params(array $values = []){

		switch ($this->_type) {
			case 'GET':
				$this->_url .= (is_array($values) && count($values) > 0) ? '?' . http_build_query($values) : '';
				break;
			
			case 'POST':
				curl_setopt($this->_ch, CURLOPT_POST, count($values));
				curl_setopt($this->_ch, CURLOPT_POSTFIELDS, http_build_query($values));
				break;
		}

		return $this;

	}

	public function verbose(){
		curl_setopt($this->_ch, CURLOPT_VERBOSE, true);
		return $this;		
	}

	public function ssl($value = 1){
		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, $value);
		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, $value);
		return $this;
	}

	public function timeout($seconds){
		curl_setopt($this->_ch, CURLOPT_TIMEOUT, $seconds);
		return $this;
	}

	public function followRedirect($value = true){
		curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, $value);
		$this->redirect = true;
		return $this;
	}

	public function referer($url = ""){
		if(empty($url)) return $this;
		curl_setopt($this->_ch, CURLOPT_REFERER, $url);
		return $this;
	}

	public function userAgent($agent = ""){
		$_agent = (empty($agent)) ? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36' : $agent;
		curl_setopt($this->_ch, CURLOPT_USERAGENT,$_agent);
		return $this;
	}

	/**
	 *	$cookie = 'fb_cookie';
	 *
	 */
	public function cookieFile($cookie){
 		curl_setopt($this->_ch, CURLOPT_COOKIEJAR, $cookie);
    	curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $cookie);
    	return $this;
	}

	public function persistSession(){
		// if we want to request has an active session

		// current SESSION
		$strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
		// we close the current session to lock it out.
		session_write_close();

		curl_setopt($this->_ch, CURLOPT_COOKIE, $strCookie ); 

		return $this;

	}

	/*
		$h = [
			"Content-Type: application/json; charset=utf-8",
			"Content-Length: " . strlen(json_encode(array("data" => ".framework")))
		];
	*/
	public function headers(array $h = []){

		if(count($h) > 0){
			curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $h);
		}

		return $this;
	}

	public function execute(){

		curl_setopt($this->_ch, CURLOPT_URL, $this->_url);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($this->_ch, CURLINFO_HEADER_OUT, 1); 
		curl_setopt($this->_ch, CURLOPT_HEADER, 0);

		if($this->_redirect)
			$this->_effectiveURL = curl_getinfo($this->_ch, CURLINFO_EFFECTIVE_URL);	

		$this->_result = curl_exec($this->_ch); 
		$this->_contentType = curl_getinfo($this->_ch, CURLINFO_CONTENT_TYPE);	
		$this->_httpCode = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);

		curl_close($this->_ch);
	}

	public function success(){
		return ($this->_httpCode == 200);
	}

	public function json(){
		return ($this->_contentType == "application/json; charset=utf-8");
	}

	public function getOutput($inJSON = false){

		$result = ($this->json() && $inJSON) ? json_decode($this->_result, true) : $this->_result;

		return $result;
	} 

	public function getError(){
		return curl_error($this->_ch);
	}

	public function getContentType(){
		return $this->_contentType;
	}	

	public function getHttpCode(){
		return $this->_httpCode;
	}

	public function getEffectiveURL(){
		return $this->_effectiveURL;
	}

}