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

use rdks\core\framework\Core;
use rdks\core\framework\Error;

/*
|--------------------------------|
|		   ENVIRONMENTS 	  	 |
|--------------------------------|
*/

// Only in devel mode errors should be displayed
Core::define('RDKS_ERRORS', $environment['errors']);
Core::define('RDKS_SITE', $environment['site']);
Core::define('RDKS_MODE', $environment['mode']); // LOCAL | QA | PRO
Core::define('RDKS_SUBDOMAIN', $environment['subdomain']);

// In case subdomain folder does not exist
if(!file_exists(Core::getSitePath()) || empty($environment['site'])){
	Error::siteFolderNotFound("Folder was not found.", __LINE__, __FILE__, Core::getAppConfigPath("environments.local"), DIR_APP_SITES);
}	

/*
|--------------------------------|
|		  APP DATABASE 			 |
|--------------------------------|
*/

$siteDbConfig = Core::getSiteConfigPath($environment['database']);
$appDbConfig = Core::getAppConfigPath($environment['database']);

// get site data base config if exists
if(file_exists($siteDbConfig)){
	$dbConfig = Core::getDbSiteConfigFile($environment['database'], false);
	$dbFile = $siteDbConfig;
}else{
	// get app data base config
	$dbConfig = Core::getDbAppConfigFile($environment['database'], true);
	$dbFile = $appDbConfig;
}

if(!isset($dbConfig['host'])){
	Error::missingDbConfig("Missing database config", __LINE__, __FILE__, $dbFile, 'host', 'localhost');
} else if(!isset($dbConfig['name'])){
	Error::missingDbConfig("Missing database config", __LINE__, __FILE__, $dbFile, 'name', 'roducks');
} else if(!isset($dbConfig['user'])){
	Error::missingDbConfig("Missing database config", __LINE__, __FILE__, $dbFile, 'user', 'xxxxxx');
} else if(!isset($dbConfig['password'])){
	Error::missingDbConfig("Missing database config", __LINE__, __FILE__, $dbFile, 'password', 'xxxxxx');
}

Core::define('DB_HOST', $dbConfig['host']); // localhost
Core::define('DB_NAME', $dbConfig['name']);
Core::define('DB_USER', $dbConfig['user']);
Core::define('DB_PASSWORD', $dbConfig['password']);

/*
|--------------------------------|
|			  ERRORS 	  		 |
|--------------------------------|
*/
// display errors in development mode
Error::display();

