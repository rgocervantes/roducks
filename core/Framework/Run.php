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

use Roducks\Framework\Error;
use Roducks\Framework\Config;
use Roducks\Framework\Path;

/*
|--------------------------------|
|		   		ENVIRONMENTS
|--------------------------------|
*/

// Only in devel mode errors should be displayed
App::define('RDKS_ERRORS', $environment['errors']);
App::define('RDKS_SITE', $environment['site']);
App::define('RDKS_MODE', $environment['mode']); // LOCAL | QA | PRO
App::define('RDKS_SUBDOMAIN', $environment['subdomain']);

// In case subdomain folder does not exist
if (!file_exists(Path::getAppSite()) || empty($environment['site'])) {
	Error::siteFolderNotFound("Folder was not found.", __LINE__, __FILE__, Config::getEnvs()['path'], DIR_APP_SITES);
}

/*
|--------------------------------|
|		  		DATABASE CONFIG
|--------------------------------|
*/
$db = Config::getDb();

if (!isset($db['data']['host'])) {
	Error::missingDbConfig("Missing database config", __LINE__, __FILE__, $db['path'], 'host', 'localhost');
} else if (!isset($db['data']['name'])) {
	Error::missingDbConfig("Missing database config", __LINE__, __FILE__, $db['path'], 'name', 'roducks');
} else if (!isset($db['data']['user'])) {
	Error::missingDbConfig("Missing database config", __LINE__, __FILE__, $db['path'], 'user', 'xxxxxx');
} else if (!isset($db['data']['password'])) {
	Error::missingDbConfig("Missing database config", __LINE__, __FILE__, $db['path'], 'password', 'xxxxxx');
}

App::define('DB_FILE', $db['path']);
App::define('DB_HOST', $db['data']['host']); // localhost
App::define('DB_PORT', $db['data']['port']); // 3306
App::define('DB_NAME', $db['data']['name']);
App::define('DB_USER', $db['data']['user']);
App::define('DB_PASSWORD', $db['data']['password']);

/*
|--------------------------------|
|			  			ERRORS
|--------------------------------|
*/
// display errors in development mode
Error::display();

/*
|--------------------------------|
|         ERROR HANDLER
|--------------------------------|
*/
set_error_handler('handler');
