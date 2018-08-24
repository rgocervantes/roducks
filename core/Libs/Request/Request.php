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
	->body(['key' => "cmsdeu3719384c"])
	->persistSession()
	->ssl(false)
	->execute();

=====================
GET Example
=====================
	$request = Request::init('GET',"http://www.google.com");
	$request
	->body(['key' => "cmsdeu3719384c"])
	->execute();

=====================
Result
=====================

	echo $request->getOutput();
	echo $request->getContentType();
	echo $request->getHttpCode();

	if ($request->json()) {
		$response = $request->getOutput(true);
	}

	if ($request->success()) {

	} else {

	}

*/

namespace Roducks\Libs\Request;

use \stdClass;

class Request extends stdClass
{

	private $_ch;
	private $_url;
	private $_type;
	private $_result = null;
	private $_headers = [];
	private $_contentType = 'undefined';
	private $_httpCode = 404;
	private $_redirect = false;
	private $_effectiveURL = '';

	static function getContent($url = "")
	{
		if (empty($url)) return "";
		return file_get_contents($url);
	}

	static function init($type, $url)
	{
		return new Request($type, $url);
	}

	static function get($url)
	{
		return self::init(__FUNCTION__, $url);
	}

	static function post($url)
	{
		return self::init(__FUNCTION__, $url);
	}

	static function put($url)
	{
		return self::init(__FUNCTION__, $url);
	}

	static function delete($url)
	{
		return self::init(__FUNCTION__, $url);
	}

	static function patch($url)
	{
		return self::init(__FUNCTION__, $url);
	}

	static function obj()
	{
		return self::init('_OBJECT_', null);
	}

	public function __construct($type, $url)
	{
		if ($type != '_OBJECT_') {
			$this->_ch = curl_init();
			$this->_url = $url;
			$this->_type = strtoupper($type);

			if (!in_array($this->_type, ['GET','POST','PUT','DELETE','PATCH','OPTIONS'])) {
				curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $this->_type);
			}
		}
	}

	public function body($values)
	{

		switch ($this->_type) {
			case 'GET':
				$this->_url .= (is_array($values) && count($values) > 0) ? '?' . http_build_query($values) : '';
				break;

			case 'POST':
				$count = (is_array($values)) ? count($values) : 1;
				$body = (is_array($values)) ? http_build_query($values) : $values;
				curl_setopt($this->_ch, CURLOPT_POST, $count);
				curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $body);
				break;
		}

		return $this;

	}

	/*
		$headers = [
			'Content-Type' => "application/json; charset=utf-8",
			'Content-Length' => strlen(json_encode(['data' => "roducks.framework"]))
		];
	*/
	public function headers(array $values = [])
	{
		if (count($values) > 0) {
			foreach ($values as $key => $value) {
				array_push($this->_headers, "{$key}: {$value}");
			}
		}

		return $this;
	}

	/**
	 *	@param $user string
	 *	@param $pswd string
	 */
	public function basicAuth($user, $pswd)
	{
		curl_setopt($this->_ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($this->_ch, CURLOPT_USERPWD, "{$user}:{$pswd}");

		return $this;
	}

	/**
	 *	@param $accessToken string
	 */
	public function bearerToken($accessToken)
	{
		if (!empty($accessToken)) {
			$this->headers([
				'Authorization' => "Bearer {$accessToken}"
			]);
		}

		return $this;
	}

	/**
	 *	@param $proxy URL
	 *	@param $proxyauth user:password
	 */
	public function proxy($proxy = null, $proxyauth = null, $tunnel = false, $type = false)
	{
		if ($tunnel) {
			curl_setopt($this->_ch, CURLOPT_HTTPPROXYTUNNEL, 1);
		}

		curl_setopt($this->_ch, CURLOPT_PROXY, $proxy);     // PROXY details with port

		if (!is_null($proxyauth)) {
			curl_setopt($this->_ch, CURLOPT_PROXYUSERPWD, $proxyauth);   // Use if proxy have username and password
		}

		if ($type) {
			curl_setopt($this->_ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); // If expected to call with specific PROXY type
		}

		return $this;
	}

	public function verbose()
	{
		curl_setopt($this->_ch, CURLOPT_VERBOSE, true);
		return $this;
	}

	public function ssl($option = true)
	{
		$value = ($option) ? 1 : 0;
		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, $value);
		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, $value);
		return $this;
	}

	public function timeout($seconds)
	{
		curl_setopt($this->_ch, CURLOPT_TIMEOUT, $seconds);
		return $this;
	}

	public function followRedirect($value = true, $redirects = 1)
	{
		curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, $value);

		if ($redirects > 1) {
			curl_setopt($this->_ch, CURLOPT_MAXREDIRS, $redirects); //if http server gives redirection responce
		}

		$this->_redirect = true;

		return $this;
	}

	public function encoding($type = "gzip")
	{
		curl_setopt($this->_ch, CURLOPT_ENCODING, $type); // the page encoding
		return $this;
	}

	public function referer($url = "")
	{
		if (empty($url)) return $this;
		curl_setopt($this->_ch, CURLOPT_REFERER, $url);
		return $this;
	}

	public function userAgent($agent = "")
	{
		$_agent = (empty($agent)) ? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36' : $agent;
		curl_setopt($this->_ch, CURLOPT_USERAGENT,$_agent);
		return $this;
	}

	/**
	 *	@param $cookie string @example: 'fb_cookie';
	 */
	public function cookieFile($cookie)
	{
 		curl_setopt($this->_ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $cookie);

    return $this;
	}

	public function persistSession()
	{
		// current SESSION
		$strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
		// we close the current session to lock it out.
		session_write_close();

		curl_setopt($this->_ch, CURLOPT_COOKIE, $strCookie);

		return $this;
	}

	public function execute()
	{

		curl_setopt($this->_ch, CURLOPT_URL, $this->_url);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->_ch, CURLINFO_HEADER_OUT, 1);
		curl_setopt($this->_ch, CURLOPT_HEADER, 0);

		if (count($this->_headers) > 0) {
			curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $this->_headers);
		}

		if ($this->_redirect)
			$this->_effectiveURL = curl_getinfo($this->_ch, CURLINFO_EFFECTIVE_URL);

		$this->_result = curl_exec($this->_ch);
		$this->_contentType = curl_getinfo($this->_ch, CURLINFO_CONTENT_TYPE);
		$this->_httpCode = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);

		curl_close($this->_ch);
	}

	public function success()
	{
		return ($this->_httpCode == 200);
	}

	public function json()
	{
		return ($this->_contentType == "application/json; charset=utf-8");
	}

	public function getOutput($inJSON = false)
	{
		$result = ($this->json() && $inJSON) ? json_decode($this->_result, true) : $this->_result;

		return $result;
	}

	public function getError()
	{
		return curl_error($this->_ch);
	}

	public function getContentType()
	{
		return $this->_contentType;
	}

	public function getHttpCode()
	{
		return $this->_httpCode;
	}

	public function getEffectiveURL()
	{
		return $this->_effectiveURL;
	}

}
