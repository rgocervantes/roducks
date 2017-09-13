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
use rdks\core\libs\Protocol\Http;

/*
|--------------------------------|
|		      VERSION 	  		 |
|--------------------------------|
*/
Core::define('RDKS_VERSION', '1.0');

/*
|--------------------------------|
|		     APP TEXTS 	  		 |
|--------------------------------|
*/
Core::define('TEXT_CLASS', "Class");
Core::define('TEXT_METHOD', "Method");
Core::define('TEXT_FILE_NOT_FOUND', "File Not Found");
Core::define('TEXT_VIEW_FILE_NOT_FOUND', "View " . TEXT_FILE_NOT_FOUND);
Core::define('TEXT_CLASS_NOT_FOUND', TEXT_CLASS . " Not Found");
Core::define('TEXT_METHOD_NOT_FOUND', TEXT_METHOD . " Not Found");

/*
|--------------------------------|
|		    APP CONFIG  		 |
|--------------------------------|
*/
$appConfig = Core::getAppConfigFile();

if(!isset($appConfig['domain_name']) || empty($appConfig['domain_name'])){
    Http::sendHeaderNotFound();
}

Core::define('DOMAIN_NAME', $appConfig['domain_name']);
Core::define('PAGE_TITLE', $appConfig['page_title']);
Core::define('EMAIL_FROM', $appConfig['email_from']);
Core::define('EMAIL_TO', $appConfig['email_to']);
Core::define('LOGO_IMAGE', $appConfig['logo_image']);
Core::define('FIND_URL_IN_DB', $appConfig['find_url_in_db']);
Core::define('ALLOW_SUBSCRIBERS_REGISTER', $appConfig['allow_subscribers_register']);
Core::define('SUBSCRIBERS_EXPIRE', $appConfig['subscribers_expire']);
Core::define('SUBSCRIBERS_EXPIRE_TIME', $appConfig['subscribers_expire_time']);
Core::define('SUBSCRIBERS_EXPIRE_IN', $appConfig['subscribers_expire_in']);
Core::define('MULTILANGUAGE', $appConfig['multilanguage']);
Core::define('BROWSER_LANGUAGE', $appConfig['browser_language']);
Core::define('DEFAULT_LANGUAGE', $appConfig['default_language']);

