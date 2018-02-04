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

require "App.php";

# Default file extentions
App::define('FILE_EXT', ".php");
App::define('FILE_INC', ".inc");
App::define('FILE_TPL', ".phtml");

# Paths
include_once "Directories" . FILE_EXT;

use Roducks\Framework\Core;
use Roducks\Framework\Error;
use Roducks\Framework\Cli;

// Autoload
spl_autoload_register(function($class){

	$className = $class;

    if(preg_match('/^Roducks\\\/', $className)){

        $path = str_replace("\\","/", $class) . FILE_EXT;
        $path = str_replace("Roducks/","core/", $path);
        $isEvent = preg_match('#/Events/#', $path);

    } else if(preg_match('/^App\\\/', $className)){

        $class = str_replace("App\\","app\\",$class);
        $path = str_replace("\\","/", $class) . FILE_EXT;
        $isEvent = preg_match('#/Events/#', $path);

    } else if(preg_match('/^core\\\/', $className)){
        $className = str_replace("core\\","Roducks\\", $className);
        $path = str_replace("\\","/", $class) . FILE_EXT;
        $isEvent = preg_match('#/Events/#', $path);
    } else {
        
        $path = str_replace("\\","/", $class) . FILE_EXT;
        $isEvent = preg_match('#/Events/#', $path);
        $path = "app/Libs/{$path}";
    }

    list($realPath, $fileExists) = App::getRealPath($path);

    if($fileExists || $isEvent){

        if(!$isEvent)
            include_once $realPath;

		if(!class_exists($className)) {
            if(php_sapi_name() != "cli"){
                echo $className;
                if(!$isEvent)
                    Error::classNotFound(TEXT_CLASS_NOT_FOUND,__LINE__, __FILE__, $path, $className);
            } else {
                Cli::println("Class '{$className}' was not found.");
            }
		}
    }else{

        if(php_sapi_name() != "cli"){
            if(!$isEvent)
                Error::debug(TEXT_FILE_NOT_FOUND,__LINE__, __FILE__, $path);
        } else {
            Cli::println("Script file not found: {$path}");
        }  
    }
});

/*
|--------------------------------|
|             VERSION            |
|--------------------------------|
*/
App::define('RDKS_VERSION', '1.0');

/*
|--------------------------------|
|            APP TEXTS           |
|--------------------------------|
*/
App::define('TEXT_CLASS', "Class");
App::define('TEXT_METHOD', "Method");
App::define('TEXT_FILE_NOT_FOUND', "File Not Found");
App::define('TEXT_VIEW_FILE_NOT_FOUND', "View " . TEXT_FILE_NOT_FOUND);
App::define('TEXT_CLASS_NOT_FOUND', TEXT_CLASS . " Not Found");
App::define('TEXT_METHOD_NOT_FOUND', TEXT_METHOD . " Not Found");

/*
|--------------------------------|
|           APP CONFIG           |
|--------------------------------|
*/
$appConfig = Core::getAppConfigFile();

if(!isset($appConfig['domain_name']) || empty($appConfig['domain_name'])){
    Http::sendHeaderNotFound();
}

App::define('DOMAIN_NAME', $appConfig['domain_name']);
App::define('PAGE_TITLE', $appConfig['page_title']);
App::define('EMAIL_FROM', $appConfig['email_from']);
App::define('EMAIL_TO', $appConfig['email_to']);
App::define('LOGO_IMAGE', $appConfig['logo_image']);
App::define('FIND_URL_IN_DB', $appConfig['find_url_in_db']);
App::define('ALLOW_SUBSCRIBERS_REGISTER', $appConfig['allow_subscribers_register']);
App::define('SUBSCRIBERS_EXPIRE', $appConfig['subscribers_expire']);
App::define('SUBSCRIBERS_EXPIRE_TIME', $appConfig['subscribers_expire_time']);
App::define('SUBSCRIBERS_EXPIRE_IN', $appConfig['subscribers_expire_in']);
App::define('MULTILANGUAGE', $appConfig['multilanguage']);
App::define('BROWSER_LANGUAGE', $appConfig['browser_language']);
App::define('DEFAULT_LANGUAGE', $appConfig['default_language']);
