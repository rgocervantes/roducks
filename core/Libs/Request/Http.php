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

namespace Roducks\Libs\Request;

class Http
{

	static function setHeader($name, $value)
	{
		header($name . " " . $value);
	}

	static function httpHeader($code, $message, $die = true)
	{
		self::setHeader("HTTP/1.1","$code $message");
		if ($die) die("<h1>{$message}</h1>");
	}

	static function sendHeaderMovedPermanently($die = true)
	{
		self::httpHeader(301, "Moved Permanently", $die);
	}

	static function sendHeaderAuthenticationFailed($die = true)
	{
		self::httpHeader(401, "Authentication failed", $die);
	}

	static function sendHeaderForbidden($die = true)
	{
		self::httpHeader(403, "Forbidden Request", $die);
	}

	static function sendHeaderNotFound($die = true)
	{
		self::httpHeader(404, "Not Found", $die);
	}

	static function sendMethodNotAllowed($die = true)
	{
		self::httpHeader(405, "Method Not Allowed", $die);
	}

	static function setHeaderInvalidRequest($die = true)
	{
		self::httpHeader(501, "Invalid Request", $die);
	}

	static function setHeaderJSON()
	{
		self::setHeader("Content-type:", self::getHeaderJSON());
	}

	static function setHeaderXML()
	{
		self::setHeader("Content-type:", "text/xml; charset=utf-8");
	}

	static function getHeaderJSON()
	{
		return "application/json; charset=utf-8";
	}

	static function redirect($urlPath)
	{
		self::setHeader("Location:", $urlPath);
	}

	static function getProtocol()
	{
		return (isset($_SERVER["HTTPS"])) ? "https://" : "http://";
	}

	static function getServerName()
	{
		return self::getProtocol() . $_SERVER['SERVER_NAME'];
	}

	static function getSplittedURL($siteDomain)
	{
		$domain = explode(".", $siteDomain);
		$serverName = self::getServerName();
		preg_match('/^(?P<PROTOCOL>https?):\/\/(?P<SUBDOMAIN>[a-z\.]+\.)?(?P<SERVER_NAME>'.$domain[0].')\.(?P<DOMAIN>[a-z\.]+)?$/', $serverName, $matches);

		return $matches;
	}

	static function getSubdomain($siteDomain, $defaultSubdomain)
	{
		if (!empty($siteDomain)) {
			$url = self::getSplittedURL($siteDomain);
			$subdomain = isset($url['SUBDOMAIN']) ? $url['SUBDOMAIN'] : "";

			if (!empty($subdomain)) {
				return substr($subdomain, 0, -1);
			}
		}

		return $defaultSubdomain;
	}

	static function getDomain()
	{
		$url = self::getSplittedURL();

		return "." . $url['DOMAIN'];
	}

	static function getURI()
	{
		$uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
		$uri = str_replace(self::getServerName(), '', $uri);

		return $uri;
	}

	static function getPort()
	{
		return $_SERVER['SERVER_PORT'];
	}

	static function getIPClient()
	{
		return $_SERVER['REMOTE_ADDR'];
	}

	static function getOrigin()
	{
		return (isset($_SERVER['HTTP_ORIGIN'])) ? $_SERVER['HTTP_ORIGIN'] : "";
	}

	static function getBrowserLanguage($defaultLanguage)
	{

		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return $defaultLanguage;
		}

		return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}

	static function getRequestMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	static function getRequestHeader($name)
	{
		$name = str_replace('-', '_', $name);

		return @$_SERVER['HTTP_X_'.strtoupper($name)];
	}

	static function getAuthorizationHeader()
	{
		return @$_SERVER['HTTP_AUTHORIZATION'];
	}

	static function serializeParams($p)
	{

		$params = [];

		if (preg_match('#&#', $p)) {
			$args = explode("&", $p);
			foreach ($args as $arg) {
				if (preg_match('#=#', $arg)) {
					list($key,$value) = explode("=", $arg);
					$params[$key] = $value;
				} else {
					$params[$arg] = "";
				}

			}
		} else {
			if (preg_match('#=#', $p)) {
				list($key,$value) = explode("=", $p);
				$params[$key] = $value;
			} else {
				$params[$p] = "";
			}
		}

		return $params;

	}

	static function getBody()
	{
		$params = file_get_contents("php://input");

		if (is_array($params)) {
			$values = $params[0];
		} else {
			$values = $params;
		}

		return self::serializeParams($values);

	}

}
