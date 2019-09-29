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

class Environment
{
	const DEV = 1;
	const QA = 2;
	const PRO = 3;
	const CLI = 4;

	private static function _getMode($key)
	{
		switch ($key) {
			case '@dev':
				$id = self::DEV;
				break;
			case '@qa':
				$id = self::QA;
				break;
			case '@cli':
				$id = self::CLI;
				break;
			case '@pro':
			default:
				$id = self::PRO;
				break;
		}

		return $id;
	}

	public static function getConfig()
	{
		$config = Config::getEnvs()['data'];
		$serverName = str_replace(['https://', 'http://'], '', Http::getServerName());

		if (isset($config[$serverName])) {
			$subdomain = $serverName;

			$args = explode(".", $serverName);

			if(count($args) >= 4) {
				unset($args[0]);
				unset($args[1]);
			} else if(count($args) == 3) {
				unset($args[0]);
			}

			$domainName = implode(".", $args);

			\App::define('DOMAIN_NAME', $domainName);
		} else {

			if (!defined('DOMAIN_NAME')) {
				Http::sendHeaderNotFound();
			}

			$subdomain = Http::getSubdomain(DOMAIN_NAME, Path::DEFAULT_SUBDOMAIN);
		}

		$site = (isset($config[$subdomain]['site'])) ? $config[$subdomain]['site'] : "Front";
		$database = (isset($config[$subdomain]['database'])) ? $config[$subdomain]['database'] : "database";
		$mode = (isset($config[$subdomain]['mode'])) ? self::_getMode($config[$subdomain]['mode']) : self::PRO;
		$errors = ($mode == self::DEV) ? true : false;

		return [
			'errors' => $errors,
			'subdomain' => $subdomain,
			'site' => $site,
			'database' => $database,
			'mode' => $mode
		];
	}

	public static function inDEV()
	{
		return (RDKS_MODE == self::DEV);
	}

	public static function inCLI()
	{
		return (RDKS_MODE == self::CLI);
	}

	public static function inQA()
	{
		return (RDKS_MODE == self::QA);
	}

	public static function inPRO()
	{
		return (RDKS_MODE == self::PRO);
	}

}
