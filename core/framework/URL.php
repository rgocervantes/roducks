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

namespace rdks\core\framework;

use rdks\core\libs\Protocol\Http;

class URL{

	const ROOT = "/";

	const CSRF_ATTACK_BASE_URL = '"\'\\(\){}<>\[\]=!@$%;'; // Forbidden chars
	const CSRF_ATTACK_GET_PARAMS = '\.\/\'\\(\){}<>\[\]!@$%'; // No dots nor slashes are allowed
	const CSRF_ATTACK_RULE_1 = '\.{2,}'; // More than 1 dot
	const CSRF_ATTACK_RULE_2 = '\.(exe|ini|inc|doc|php|phtml|sql)$'; // extensions
	const CSRF_ATTACK_END_URL = '[\?&=\.\-,;:\$\(\)%*@]$';

	const REGEXP_GET = '(\?[a-zA-Z0-9_\-=&+]+)?';

	static function preventCSRFAttack(){

		$baseURL = self::getBaseURL();
		$relativeURL = self::getRelativeURL();
		$GETParams = self::getBaseGETParams();
		
		if(preg_match('/['.self::CSRF_ATTACK_BASE_URL.']+/', $baseURL) 
		||	preg_match('/'.self::CSRF_ATTACK_RULE_1.'/', $baseURL)
		||	preg_match('/'.self::CSRF_ATTACK_RULE_2.'/', $baseURL)
		||	preg_match('/'.self::CSRF_ATTACK_END_URL.'/', $relativeURL)
		){
			Http::sendHeaderForbidden();
		}

		if(!is_null($GETParams)){

			if(preg_match('/['.self::CSRF_ATTACK_GET_PARAMS .']+/', $GETParams)){
				Http::sendHeaderForbidden();
			}

		}

	}
	
	static function getURLArguments(){

		$uri = self::getRelativeURL();
		$get = null;

		if(preg_match('#\?#', $uri)){
			list($url, $get) = explode("?", $uri);
			$uri = $url;
		}

		return array($uri, $get);

	}

	static function getBaseURL(){
		$baseURL = self::getURLArguments();

		return $baseURL[0];
	}

	static function getBaseGETParams(){
		$baseURL = self::getURLArguments();

		return $baseURL[1];
	}	

	static function serializeParams($p){

		$params = array();

		if(preg_match('#&#', $p)){
			$args = explode("&", $p);
			foreach ($args as $arg) {
				if(preg_match('#=#', $arg)){
					list($key,$value) = explode("=", $arg);
					$params[$key] = $value;	
				}else{
					$params[$arg] = "";	
				}

			}
		}else{
			if(preg_match('#=#', $p)){
				list($key,$value) = explode("=", $p);
				$params[$key] = $value;					
			}else{
				$params[$p] = "";
			}				
		}

		return $params;

	}

	static function serializeGETParams($url){

		$params = array();

		if(preg_match('#^'.self::REGEXP_GET.'$#', $url)){
			list($qm, $p) = explode("?", $url);
			
			$params = self::serializeParams($p);
			
		}

		return $params;

	}

	static function getGETParams(){

		$baseGETParams = self::getBaseGETParams();

		if(is_null($baseGETParams)) return array();

		return self::serializeGETParams('?'.$baseGETParams);
	}

	static function getParams(){
		
		$uri = self::getBaseURL();

		$slashes = explode(self::ROOT, $uri);
		unset($slashes[0]);
		$slashes = Helper::resetArray($slashes);

		return $slashes;

	}

	static function getRealParams(){
		$params = self::getParams();
		$ret = array();

		foreach($params as $param){
			if(!empty($param)){
				$ret[] = $param;
			}
		}

		return $ret;

	}

	static function getPairParams(){

		$params = self::getRealParams();
		$ret = Helper::getPairParams($params);

		return $ret;
	}

	static function getRelativeURL(){
		return Http::getURI();
	}

	static function getAbsoluteURL(){
		$relativeURL = (self::getRelativeURL() != self::ROOT) ? self::getRelativeURL() : '';
		return Http::getServerName() . $relativeURL;
	}

	static function getURL(){
		return Http::getServerName();
	}

	static function goToURL($inDEV, $inPro){
		$subdomain = (Environment::inDEV()) ? $inDEV : $inPro;
		return Http::getProtocol() . $subdomain . "." . DOMAIN_NAME;
	}

	static function goToFront(){
		return self::goToURL("local", Core::DEFAULT_SUBDOMAIN);
	}

	static function goToAdmin(){
		return self::goToURL("admin.local", Core::ADMIN_SUBDOMAIN);
	}

	static function getPublicURL($path = ""){
		return self::goToFront() . $path;
	}

	static function lang($iso, $rel = true){
		$relativeURL = self::getRelativeURL();
		$url = "/_lang/{$iso}";

		if($relativeURL != self::ROOT && $rel){
			$url .= $relativeURL;
		}

		return $url;
	}

	static function build($url = "/", array $params = [], $complete = true){

		if(count($params) == 0){
			return $url;
		}

		$getParams = ($complete) ? self::getGETParams() : [];

		$arr = array_merge($getParams, $params);
		$ret = [];

		foreach($arr as $key => $value){
			$ret[] = $key."=".$value;
		}

		return $url . "?" . implode("&", $ret);

	}

}