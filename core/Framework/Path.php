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

namespace Roducks\Framework;

class Path{

	static function getCropName($src, $s){

		$img = explode(".", $src);
		$size = count($img);

		if($size > 2):
			$res = $size - 1;
			$ext = $img[ $res ];
			unset($img[ $res ]);
			$name = implode(".", $img);
		else:
			$name = $img[0];
			$ext = $img[1];	
		endif;	

		return $name . '_' . $s . '.' . $ext;
	}	

	static function getUploadedImage($file = ""){
		return DIR_DATA_UPLOADED_IMAGES . $file;
	}

	static function getUploadsUsers($file = ""){
		return DIR_DATA_UPLOADS_USERS . $file;
	}	

	static function getUploadedUsers($file = ""){
		return DIR_DATA_UPLOADED_USERS . $file;
	}	

	static function getAppUploadedImage($file){
		return [DIR_DATA_UPLOADS_IMAGES, DIR_DATA_UPLOADED_IMAGES, $file];
	}

	static function getAppImage($file){
		return [DIR_APP_IMAGES, DIR_ASSETS_IMAGES, $file];
	}

	static function getAppIcon($file){
		return [DIR_APP_ICONS, DIR_ASSETS_ICONS, $file];
	}	

	static function getImage($file = ""){
		return DIR_ASSETS_IMAGES . $file;
	}

	static function getIcon($file = "") {
		return DIR_ASSETS_ICONS . $file;
	}	

	static function getLogo(){
		return self::getImage(LOGO_IMAGE);
	}

	static function getImageAbsolute($file){
		return URL::getURL() . self::getImage($file);
	}

	static function getPublicUploadedUsers($file){
		return URL::getURL() . self::getUploadedUsers($file);
	}


}