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

use Roducks\Libs\Request\Http;

class URL
{

	const ROOT = "/";

	const CSRF_ATTACK_BASE_URL = '"\'\\(\) {}<>\[\]=!@$%;'; // Forbidden chars
	const CSRF_ATTACK_GET_PARAMS = '\/\'\\(\) {}<>\[\]!@$%'; // No dots nor slashes are allowed
	const CSRF_ATTACK_RULE_1 = '\.{2,}'; // More than 1 dot
	const CSRF_ATTACK_RULE_2 = '\.(exe|ini|inc|doc|php|phtml|sql)$'; // extensions
	const CSRF_ATTACK_RULE_3 = '-[\-]+'; // more than 1 dashes
	const CSRF_ATTACK_RULE_4 = '\/-'; // slash + dash
	const CSRF_ATTACK_END_URL = '[\?&=\.\-_,;:\$\(\)%*@]$';

	const REGEXP_GET = '(\?[a-zA-Z0-9_\-=&+]+)?';

	static function preventCSRFAttack()
	{

		$baseURL = self::getBaseURL();
		$relativeURL = self::getRelativeURL();
		$GETParams = self::getBaseGETParams();
		
		if (preg_match('/['.self::CSRF_ATTACK_BASE_URL.']+/', $baseURL) 
		||	preg_match('/'.self::CSRF_ATTACK_RULE_1.'/', $baseURL)
		||	preg_match('/'.self::CSRF_ATTACK_RULE_2.'/', $baseURL)
		||	preg_match('/'.self::CSRF_ATTACK_RULE_3.'/', $baseURL)
		||	preg_match('/'.self::CSRF_ATTACK_RULE_4.'/', $baseURL)
		||	preg_match('/'.self::CSRF_ATTACK_END_URL.'/', $relativeURL)
		) {
			Http::sendHeaderForbidden();
		}

		if (!is_null($GETParams)) {

			if (preg_match('/['.self::CSRF_ATTACK_GET_PARAMS .']+/', $GETParams)) {
				Http::sendHeaderForbidden();
			}
		}

	}

	static function lang($iso, $rel = true)
	{
		$relativeURL = self::getRelativeURL();
		$url = "/_lang/{$iso}";

		if ($relativeURL != self::ROOT && $rel) {
			$url .= $relativeURL;
		}

		return $url;
	}
	
	static function getURLArguments()
	{

		$uri = self::getRelativeURL();
		$get = null;

		if (preg_match('#\?#', $uri)) {
			list($url, $get) = explode("?", $uri);
			$uri = $url;
		}

		return array($uri, $get);

	}

	static function getBaseURL()
	{
		$baseURL = self::getURLArguments();

		return $baseURL[0];
	}

	static function getBaseGETParams()
	{
		$baseURL = self::getURLArguments();

		return $baseURL[1];
	}	

	static function serializeGETParams($url)
	{

		$params = [];

		if (preg_match('#^'.self::REGEXP_GET.'$#', $url)) {
			list($qm, $p) = explode("?", $url);
			
			$params = Http::serializeParams($p);
			
		}

		return $params;

	}

	static function getParams()
	{
		
		$uri = self::getBaseURL();

		$slashes = explode(self::ROOT, $uri);
		unset($slashes[0]);
		$slashes = Helper::resetArray($slashes);

		return $slashes;

	}

	static function getPairParams()
	{

		$params = self::getSplittedURL();
		$ret = Helper::getPairParams($params);

		return $ret;
	}

	static function goToURL($inDEV, $inPro)
	{
		$subdomain = (Environment::inDEV()) ? $inDEV : $inPro;
		return Http::getProtocol() . $subdomain . "." . DOMAIN_NAME;
	}

	/*
	|----------------------------------
	|	MOST COMMON
	|----------------------------------
	*/
	static function getDomainName()
	{
		return Http::getServerName();
	}

	static function getSplittedURL()
	{
		$params = self::getParams();
		$ret = [];

		foreach ($params as $param) {
			if (!empty($param)) {
				$ret[] = $param;
			}
		}

		return $ret;

	}

	static function getGETParams()
	{

		$baseGETParams = self::getBaseGETParams();

		if (is_null($baseGETParams)) return array();

		return self::serializeGETParams('?'.$baseGETParams);
	}

	static function getRelativeURL($withParams = true)
	{

		if (!$withParams) {
			return self::getBaseURL();
		}

		return Http::getURI();
	}

	static function getAbsoluteURL($withParams = true)
	{
		$relativeURL = (self::getRelativeURL($withParams) != self::ROOT) ? self::getRelativeURL($withParams) : '';
		return self::getDomainName() . $relativeURL;
	}

	static function getFrontURL($path = "")
	{
		return self::goToURL("local", Core::DEFAULT_SUBDOMAIN) . $path;
	}

	static function getAdminURL($path = "")
	{
		return self::goToURL("admin.local", Core::ADMIN_SUBDOMAIN) . $path;
	}

	static function setURL($url = "/", array $params = [], $complete = true)
	{

		if (count($params) == 0) {
			return $url;
		}

		$getParams = ($complete) ? self::getGETParams() : [];

		$arr = array_merge($getParams, $params);
		$ret = [];

		foreach ($arr as $key => $value) {
			$ret[] = $key."=".$value;
		}

		return $url . "?" . implode("&", $ret);

	}

	static function setAbsoluteURL($path = "")
	{
		return self::getDomainName() . $path;
	}

	static function getURL(array $params = [], $complete = true)
	{
		if (count($params) == 0) {
			return self::getRelativeURL();
		}

		return self::setURL(self::getBaseURL(), $params, $complete);
	}

	static function setQueryString(array $params = [], $complete = true)
	{
		return self::setURL("", $params, $complete);
	}

}