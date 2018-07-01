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

class Directory
{

	const REMOVE_FILES = 1;
	const REMOVE_FOLDERS = 2;
	const REMAIN_FOLDERS = 3;

	static private function _getDir($dir)
	{

		if (!preg_match('/\/$/', $dir)) {
			return "{$dir}/";
		}

		return $dir;
	}

	/**
	 * @example Directory::folder("example");
	 */
	static private function _folder($folder, $chmod = 0755)
	{
		if (!empty($folder)) {
			if (!file_exists($folder)) {
				@mkdir($folder, $chmod);
			}
		}
	}	

	static private function _tree($path, $route, array $exclude = [], array $storage = [])
	{

		$name = $path . $route;

		if (count($exclude) > 0) {

			if (isset($exclude[$route])) {
				return [];
			}
		}

		$dir = self::open($name);

		if (!empty($dir['files'])) {
			$storage[$route] = $dir['files'];
		}

		if (!empty($dir['folders'])) {

			foreach ($dir['folders'] as $folder) {
				$sub = $route . $folder;
				$storage = array_merge($storage, self::_tree($path, $sub, $exclude, $storage));
			}

		}

		return $storage;
	}

	/**
	 * @example Directory::open(Path::getData("files/books/children/"));
	 */
	static function open($dir)
	{

		$folders = [];
		$files = [];
		$dir_handle = false;
		$dirname = self::_getDir($dir);

		if (is_dir($dirname))
		       $dir_handle = opendir($dirname);

		if ($dir_handle) {
	   		while($x = readdir($dir_handle)) {
	       		if ($x != "." && $x != "..") {
	          		if (is_dir($dirname . $x)) {
	          			$folders[] = $x.DIRECTORY_SEPARATOR;
	          		}else{
	          			$files[] = $x;
	          		}
	       		}
	    	}

	   		closedir($dir_handle);
		}
		 
   		return [
   			'folders' => $folders,
   			'files' => $files
   			];

	}
	
	/**
	 * @example Directory::make(Path::getData(), "foo/bar");
	 */
	static function make($base, $dir = "", $chmod = 0755)
	{

		$path = $base . $dir;

		if (preg_match('#\/#', $dir)) {

			$guide = "";
			$slashes = explode(DIRECTORY_SEPARATOR, $dir);

			foreach ($slashes as $key => $value) {
				$guide = $guide . $value . DIRECTORY_SEPARATOR;
				$folder = $base . $guide;
				self::_folder($folder, $chmod);
			}

		} else {
			self::_folder($path, $chmod);
		}
	
	}
	
	/**
	 * @example Directory::remove(Path::getData("tmp/"));
	 */
	static function remove($dir, $recursive = true)
	{

		$dir_handle = false;
		$dirname = self::_getDir($dir);

		if ($recursive) {

			if (is_dir($dirname))
	       		$dir_handle = opendir($dirname);
	    	if (!$dir_handle)
	       		return false;
	 
	   		while($file = readdir($dir_handle)) {
	       		if ($file != "." && $file != "..") {
	          		if (!is_dir($dirname.$file))
	             	unlink($dirname.$file);
	         	else
	             	self::remove($dirname.$file);    
	       		}
	    	}
	   		closedir($dir_handle);

		}

   		rmdir($dirname);
    	return true;
	}

	/**
	 * @example Directory::clean(Path::getData("tmp/cards/"), [Directory::REMAIN_FOLDERS, Directory::REMOVE_FILES]);
	 */
	static function clean($dirname, array $options = [])
	{
		$content = self::open($dirname);

		if ($content !== false && is_array($options) && count($options) > 0) {
			foreach ($options as $option) {
				switch (strtolower($option)) {
					case self::REMOVE_FILES:
						
						foreach ($content['files'] as $file) {
							unlink($dirname.$file);
						}

						break;
					case self::REMOVE_FOLDERS:
						
						foreach ($content['folders'] as $folder) {
							self::remove($dirname.$folder.DIRECTORY_SEPARATOR);
						}

						break;
					case self::REMAIN_FOLDERS:
						
						foreach ($content['folders'] as $folder) {
							self::clean($dirname.$folder.DIRECTORY_SEPARATOR, [self::REMAIN_FOLDERS, self::REMOVE_FILES]);
						}

						break;						
				}
			}
		}
	}

	/**
	 *	@example Directory::move(Path::getData(), "xml/", Path::getData(), "content/xml");
	*/
	static function move($path1, $origin, $path2, $destination = "")
	{
		self::make($path2, $destination);
		$tree = self::_tree($path1, $origin);

		foreach ($tree as $route => $files) {
			self::make($path2, $route);
			foreach ($files as $file) {
				File::move($path1.$route.$file, $path2.$destination.$file);
			}
		}

		if (!empty($origin) && !empty($destination)) {
			
			list($f1, $e1) = explode(DIRECTORY_SEPARATOR, $origin);
			list($f2, $e2) = explode(DIRECTORY_SEPARATOR, $destination);

			$recursive = ($f1 != $f2);
			self::remove($path1.$origin, $recursive);

		}
	}

	/**
	 * @example 
	 *
	 *	Directory::zip([
	 * 	 'path' => Path::getData(),
	 *	 'exlude' => [Path::getData('zip/') => 1],
	 *	 'destination' => Path::getData('zip/'),
	 *	 'filename' => 'rodrigo',
	 * ]);
	 */
	static function zip($obj)
	{
		$exclude = (isset($obj['exclude'])) ? $obj['exclude'] : [];
		$files = self::_tree($obj['path'], '', $exclude);

		self::make($obj['destination']);
		Zip::create($obj['path'], $files, "{$obj['destination']}{$obj['filename']}.zip");
	}

}
