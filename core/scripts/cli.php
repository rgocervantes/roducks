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

if(!isset($argv) || php_sapi_name() != "cli") die("Unauthorized to access"); 

$params = [];

if(is_array($argv) && count($argv) > 0):
unset($argv[0]);		
    foreach ($argv as $arg):
        list($k, $v) = explode("=",$arg);
        $params[$k] = $v;
    endforeach;  
endif;

/*
|--------------------------------|
|		 LOAD BOOTSTRAP			 |
|--------------------------------|
*/
require "./core/framework/Bootstrap.php";

use rdks\core\framework\Core;
use rdks\core\framework\Cli;
use rdks\core\framework\Environment;
use rdks\core\framework\Helper;

Core::loadFile(DIR_APP_LANGUAGES, 'en' . FILE_INC);

/*
|--------------------------------|
|		 CHECK ENVIRONMENT  	 |
|--------------------------------|
*/

$mode = (isset($params['env']) && $params['env'] == "dev") ? true : false;
$database = (isset($params['database'])) ? $params['database'] : "database";

if($mode) {
	$database = 'database.local';
}

$environment = [
	'errors' => false,
	'subdomain' => "www",
	'site' => "front",
	'mode' => Environment::CLI,
	'database' => $database
];

/*
|--------------------------------|
|			RUN SCRIPT  		 |
|--------------------------------|
*/
require "./core/framework/Run" . FILE_EXT;

if(isset($params['cmd'])){

	$method = "output";
	$name = $params['cmd'];

	if(preg_match('#::#', $params['cmd'])){
		list($name, $method) = explode("::", $params['cmd']);
	}

	$cls = $name;
	$name = Helper::getCamelName($name);
	$script = "rdks\app\cli\\" . $name;

	$class = new $script($params);
	if(method_exists($class, $method)){
		$class->$method();
	} else {
		Cli::println("Unknown command: {$cls}::{$method}");
	}
	
}else{
	Cli::println("Please set a script");
}

