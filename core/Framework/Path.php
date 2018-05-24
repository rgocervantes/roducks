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

	static function get($path = ""){
		return \App::getRealFilePath($path);
	}

	static function getData($path = ""){
		return self::get(DIR_APP_DATA . $path);
	}

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

	static function getUploads($file = "")
	{
		return DIR_DATA_UPLOADS . $file;
	}

	static function getUploaded($file = "")
	{
		return DIR_DATA_UPLOADED . $file;
	}

	static function getUploadsImages($file = "")
	{
		return DIR_DATA_UPLOADS_IMAGES . $file;
	}

	static function getUploadedImages($file = "")
	{
		return DIR_DATA_UPLOADED_IMAGES . $file;
	}

	static function getUploadsCsv($file = "")
	{
		return DIR_DATA_UPLOADS_CSV . $file;
	}

	static function getUploadedCsv($file = "")
	{
		return DIR_DATA_UPLOADED_CSV . $file;
	}

	static function getUploadsPdf($file = "")
	{
		return DIR_DATA_UPLOADS_PDF . $file;
	}

	static function getUploadedPdf($file = "")
	{
		return DIR_DATA_UPLOADED_PDF . $file;
	}

	static function getUploadsZip($file = "")
	{
		return DIR_DATA_UPLOADS_ZIP . $file;
	}

	static function getUploadedZip($file = "")
	{
		return DIR_DATA_UPLOADED_ZIP . $file;
	}

	static function getUploadsJson($file = "")
	{
		return DIR_DATA_UPLOADS_JSON . $file;
	}

	static function getUploadedJson($file = "")
	{
		return DIR_DATA_UPLOADED_JSON . $file;
	}

	static function getUploadsXml($file = "")
	{
		return DIR_DATA_UPLOADS_XML . $file;
	}

	static function getUploadedXml($file = "")
	{
		return DIR_DATA_UPLOADED_XML . $file;
	}

	/**
	*
	*/
	static function getUploadsUsers($file = ""){
		return DIR_DATA_UPLOADS_USERS . $file;
	}	

	static function getUploadedUsers($file = ""){
		return DIR_DATA_UPLOADED_USERS . $file;
	}

	/**
	*
	*/
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