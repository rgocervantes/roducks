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

namespace Roducks\Libs\Files;

class Directory{

	const CLEAN_FILES = 1;
	const CLEAN_FOLDERS = 2;
	const CLEAN_FOLDERS_REMAIN = 3;

	static private function _getDir($dir){

		if(!preg_match('/\/$/', $dir)){
			return "{$dir}/";
		}

		return $dir;
	}

	/*
	Directory::folder("example");
	*/
	static private function _folder($folder, $chmod = 0755){
		if (!empty($folder)) {
			if (!file_exists($folder)) {
				@mkdir($folder, $chmod);
			}
		}
	}	

	// Directory::open("files/books/children/");
	static function open($dir){

		$folders = [];
		$files = [];
		$dir_handle = false;
		$dirname = self::_getDir($dir);

		if (is_dir($dirname))
		       $dir_handle = opendir($dirname);

		if ($dir_handle) {
	   		while($x = readdir($dir_handle)) {
	       		if ($x != "." && $x != "..") {
	          		if (is_dir($dirname . $x)){
	          			$folders[] = $x;
	          		}else{
	          			$files[] = $x;
	          		}
	       		}
	    	}

	   		closedir($dir_handle);
		}
		 
   		return ['folders' => $folders,
   				'files' => $files
   				];

	}
	
	/*
	Directory::make("app/data/json/");
	*/
	static function make($path, $chmod = 0755){

		if(preg_match('#\/#', $path)){

			$guide = "";
			$slashes = explode("/", $path);

			for($i=0; $i<count($slashes); $i++):
				if(!empty($slashes[$i])):
					$guide .= $slashes[$i]."/";
					self::_folder($guide, $chmod);
				endif;
			endfor;		

		} else {
			self::_folder($path, $chmod);
		}
	
	}
	
	/*
	*	Delete files and folders inside of another.
	*/
	static function remove($dirname){

		$dir_handle = false;

		if (is_dir($dirname))
       		$dir_handle = opendir($dirname);
    	if (!$dir_handle)
       		return false;
 
   		while($file = readdir($dir_handle)) {
       		if ($file != "." && $file != "..") {
          		if (!is_dir($dirname."/".$file))
             	unlink($dirname."/".$file);
         	else
             	self::remove($dirname.'/'.$file);    
       		}
    	}
   		closedir($dir_handle);
   		rmdir($dirname);
    	return true;
	}

	/**
	*	Directory::clean("app/tmp/cards/", [Directory::REMAIN_FOLDERS, Directory::REMOVE_FILES]);
	*/
	static function clean($dirname, array $options = []){
		$content = self::open($dirname);

		if($content !== false && is_array($options) && count($options) > 0){
			foreach ($options as $option) {
				switch (strtolower($option)) {
					case self::REMOVE_FILES:
						
						foreach ($content['files'] as $file) {
							unlink($dirname.$file);
						}

						break;
					case self::REMOVE_FOLDERS:
						
						foreach ($content['folders'] as $folder) {
							self::remove($dirname.$folder."/");
						}

						break;
					case self::REMAIN_FOLDERS:
						
						foreach ($content['folders'] as $folder) {
							self::clean($dirname.$folder."/", [self::REMAIN_FOLDERS, self::REMOVE_FILES]);
						}

						break;						
				}
			}
		}
	}

	/**
	*	Example: Directory::move(DIR_DATA_TMP . "new_package/other/", DIR_DATA_TMP . "new_package/example/other/");
	*
	*/
	static function move($origin, $destination){
		rename($origin, $destination);
	}

}
 
?>