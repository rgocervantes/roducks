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

class App {

	const COMPOSER_CLASS_MAP = 'vendor/composer/autoload_classmap.php';

	static $aliases = [],
		$composer = [];

	static function define($name, $value){
		if(!defined($name)){
			define($name, $value);
		}
	}

	static function text($name, $value){
		self::define("TEXT_{$name}", $value);
	}

	static function getRealPath($path){

	    if(preg_match('/^core\/Framework/', $path)){
	        $path = str_replace('core/Framework/', '', $path);
	        $fileExists = file_exists(__DIR__  . "/" . $path);
	    } else {
	    	$dir = preg_replace('/^(.+)core\/Framework$/', '$1', __DIR__);
	        $path = $dir.$path;
	        $fileExists = file_exists($path);
	    }

	    return [$path, $fileExists];
	}

	static function getRealFilePath($path){
		list($realPath, $fileExists) = self::getRealPath($path);
		return $realPath;
	}

	static function fileExists($path){
		list($realPath, $fileExists) = self::getRealPath($path);
		return $fileExists;
	}

	static function getComposerPath($path){

		$fileExists = file_exists($path);

	    return [$path, $fileExists];		
	}

	static function getComposerMap(){

		list($realPath, $fileExists) = self::getRealPath(self::COMPOSER_CLASS_MAP);

		if($fileExists){
			return include $realPath;
		}

		return [];
	}

}