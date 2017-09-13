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

namespace rdks\core\libs\Protocol;

class Http{

	static function setHeader($name, $value){
		header($name . " " . $value);
	}

	static function httpHeader($code, $message){
		self::setHeader("HTTP/1.1","$code $message");
		die("<h1>{$message}</h1>");
	}

	static function sendHeaderAuthenticationFailed(){
		self::httpHeader(401, "Authentication failed");	
	}

	static function sendHeaderForbidden(){
		self::httpHeader(403, "Forbidden Request");	
	}	

	static function sendHeaderNotFound(){
		self::httpHeader(404, "Not Found");	
	}	

	static function setHeaderInvalidRequest(){
		self::httpHeader(501, "Invalid Request");	
	}

	static function setHeaderJSON(){
		self::setHeader("Content-type:", self::getHeaderJSON());
	}

	static function setHeaderXML(){
		self::setHeader("Content-type:", "text/xml; charset=utf-8");
	}	

	static function getHeaderJSON(){
		return "application/json; charset=utf-8";
	}	

	static function redirect($urlPath){
		self::setHeader("Location:", $urlPath);
	}

	static function getProtocol(){
		return (isset($_SERVER["HTTPS"])) ? "https://" : "http://";
	}

	static function getServerName(){
		return self::getProtocol() . $_SERVER['SERVER_NAME'];
	}

	static function getSplitURL($siteDomain){
		$domain = explode(".", $siteDomain);
		$serverName = self::getServerName();
		preg_match('/^(?P<PROTOCOL>https?):\/\/(?P<SUBDOMAIN>[a-z\.]+\.)?(?P<SERVER_NAME>'.$domain[0].')\.(?P<DOMAIN>[a-z\.]+)?$/', $serverName, $matches);

		return $matches;
	}

	static function getSubdomain($siteDomain, $defaultSubdomain){

		$url = self::getSplitURL($siteDomain);
		$subdomain = isset($url['SUBDOMAIN']) ? $url['SUBDOMAIN'] : "";

		if(!empty($subdomain)){
			return substr($subdomain, 0, -1);
		}

		return $defaultSubdomain;
	}

	static function getDomain(){
		$url = self::getSplitURL();

		return "." . $url['DOMAIN'];
	}

	static function getURI(){
		return filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
	}

	static function getIPClient(){
		return $_SERVER['REMOTE_ADDR'];
	}	

	static function getOrigin(){
		return (isset($_SERVER['HTTP_ORIGIN'])) ? $_SERVER['HTTP_ORIGIN'] : "";
	}

	static function getBrowserLanguage($defaultLanguage){

		if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			return $defaultLanguage;
		}

		return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}	

	static function getRequestMethod(){
		return $_SERVER['REQUEST_METHOD'];
	}

	static function getRequestHeader($name){
		return @$_SERVER['HTTP_X_'.strtoupper($name)];
	}

}
