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

namespace rdks\core\page;

use rdks\core\framework\Core;
use rdks\core\framework\Error;

class Template {
	
	static $path = null;
	static $data = [];
	
	static function view($file){

		if(!is_null(self::$path) && !empty($file)){
			$dir_template = self::$path.$file.FILE_TPL;
			if(file_exists($dir_template)){
				extract(self::$data);
				include $dir_template;
			}else{
				Error::warning(TEXT_FILE_NOT_FOUND,__LINE__, __FILE__, $dir_template);
			}
		}else{
			Error::warning("Invalid template name",__LINE__, __FILE__, $dir_template);
		}
	}

	static function menu($name){
		return Core::getMenuFile($name);
	}

	static function displayBlock($bool){
		return ($bool) ? 'display="block"; ' : ''; 
	}	

	static function displayNone($bool){
		return ($bool) ? 'display="none"; ' : ''; 
	}		

	static function checked($bool){
		return ($bool) ? ' checked="checked"' : ''; 
	}

	static function selected($bool){
		return ($bool) ? ' selected="selected"' : ''; 
	}

	static function conditional($bool, $onTrue = "", $onFalse = ""){
		return ($bool) ? $onTrue : $onFalse;
	}

	static function equals($a1, $a2, $onTrue = "", $onFalse = ""){
		return self::conditional(($a1 == $a2), $onTrue, $onFalse);
	}	

	static function notEmpty($value, $onTrue = "", $onFalse = ""){

		if(!empty($value)){
			return $onTrue;
		}

		return $onFalse;
	}

}