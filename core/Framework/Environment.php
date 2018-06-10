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

	static function getConfig()
	{

		$subdomain = Http::getSubdomain(DOMAIN_NAME, Core::DEFAULT_SUBDOMAIN);
		$config = Core::getEnvConfigFile();

		$site = (isset($config[$subdomain]['site'])) ? $config[$subdomain]['site'] : "front";
		$database = (isset($config[$subdomain]['database'])) ? $config[$subdomain]['database'] : "database";
		$mode = (isset($config[$subdomain]['mode'])) ? $config[$subdomain]['mode'] : self::PRO;
		$errors = ($mode == self::DEV) ? true : false;

		if ($errors && !isset($config[$subdomain]['database'])) {
			$database .= ".local";
		}

		return [
			'errors' => $errors, 
			'subdomain' => $subdomain,
			'site' => $site, 
			'database' => $database,
			'mode' => $mode
		];
	}

	static function inDEV()
	{
		return (RDKS_MODE == self::DEV);
	}

	static function inCLI()
	{
		return (RDKS_MODE == self::CLI);
	}	

	static function inQA()
	{
		return (RDKS_MODE == self::QA);
	}

	static function inPRO()
	{
		return (RDKS_MODE == self::PRO);
	}	

}