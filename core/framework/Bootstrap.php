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

# Default file extentions
if(!defined('FILE_EXT')){
    define('FILE_EXT', ".php");
}

if(!defined('FILE_INC')){
    define('FILE_INC', ".inc");
}

if(!defined('FILE_TPL')){
    define('FILE_TPL', ".phtml");
}

# Paths
include_once "Directories" . FILE_EXT;

use rdks\core\framework\Core;
use rdks\core\framework\Error;
use rdks\core\framework\Cli;

// Autoload
spl_autoload_register(function($class){

	$className = $class;
    $class = str_replace("rdks\\","",$class);
    $path = str_replace("\\","/", $class) . FILE_EXT;
    $isEvent = preg_match('#/events/#', $path);

    if(!preg_match('/^rdks\\\.+/', $className)){
        $path = "app/libs/{$path}";
    }

    if(file_exists($path) || $isEvent){

        if(!$isEvent)
            include_once $path;

		if(!class_exists($className)) {
            if(php_sapi_name() != "cli"){
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
|		   CORE CONFIG    		 |
|--------------------------------|
*/
Core::loadConfig("config");


