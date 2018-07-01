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
/*
	USAGE:
	
	$file = File::init();
	$file->type(['image/jpg','image/jpeg','image/png']);
	$file->kb(150);
	$file->upload(DIR_UPLOAD_USERS, "profile_user", "my_custom_name");

	if ($file->onSuccess()) {
		echo $file->getFilename();
	} else {
		echo $file->getMessage();
	}

*/

namespace Roducks\Libs\Files;	

final class File
{

/* 
|-------------------------------|
|		PRIVATE
|-------------------------------|
*/
	private $_limit = 1024; // 1 MB by default
	private $_success = false;
	private $_message = "Ok.";
	private $_filename = "";
	private $_ext = [
			'text/plain',
			'image/gif',
			'image/jpeg',
			'image/jpg',
			'image/png',
			'audio/mpeg',
			'audio/mp3',			
			'video/quicktime',	
			'video/mpeg',
			'application/zip',
			'application/pdf',
			'application/octet-stream'
		];

	private function _getSize($f)
	{
		return ceil($this->_getAttribute($f,'size') / 1024);
	}	

	private function _setSize($n)
	{
		$this->_limit = $n;
	}

	private function _getAttribute($file, $attr)
	{
		return $_FILES[$file][$attr];			
	}	

/* 
|-------------------------------|
|		STATIC
|-------------------------------|
*/

	static function init()
	{
		$file = new File;
		return $file;
	}

	/**
	 * @example File::remove(Path::getData("xml/home.xml"));
	 */
	static function remove($filename)
	{
		return self::init()->delete($filename, null);
	}

	/**
	 * @example File::move(Path::get("xml/"), Path::getData("content/xml/"), "blog.xml");
	 */
	static function move($from, $to)
	{
		if (file_exists($from) && $from != $to) {
			rename($from, $to);
		}
	}

	static function create($path, $name, $content = '')
	{

		if (file_exists($path) && $path != '' && $name != '') {

			$file = fopen($path . $name, "w");
					fwrite($file, $content);
					fclose($file);

		}

	}

	static function createJSON($path, $name, $content = '', $encode = true)
	{
		$data = ($encode) ? json_encode($content) : $content;
		self::create($path, preg_replace('/^(.+)\.json$/', '$1', $name) . ".json", $data);
	}

/* 
|-------------------------------|
|		PUBLIC
|-------------------------------|
*/

	public function type($arr)
	{
		$this->_ext = $arr;
	}

	public function kb($n)
	{
		$this->_setSize($n);
	}

	public function mb($n)
	{
		$cal = ceil($n * 1024);
		$this->_setSize($cal);
	}

	public function onSuccess()
	{
		return $this->_success;
	}

	public function getMessage()
	{
		return $this->_message;
	}

	public function getFilename()
	{
		return $this->_filename;
	}

	public function upload($path, $file, $rename = null)
	{
		
		$this->_filename = $this->_getAttribute($file,'name');

		// if upload is successed
		if (!empty($this->_filename) && $this->_getAttribute($file,'error') == 0) {
			
			// Allowed size
			if ($this->_getSize($file) <= $this->_limit) {
				
				// Allowed type
				if (in_array($this->_getAttribute($file,'type'), $this->_ext)) {
					$this->_filename = (!is_null($rename)) ? $rename . preg_replace('/^.+(\.\w{3,4})$/', '$1', $this->_filename) : $this->_filename;
					
					if (move_uploaded_file($this->_getAttribute($file,'tmp_name'), $path . $this->_filename)) {
						$this->_success = true;
						$this->_message = "File was uploaded successfully.";
						$code = 1;
					} else {
						$this->_message = "It couln't be moved file to destination.";
						$code = 5;
					}

				} else {
					$this->_message = "Type" . $this->_getAttribute($file,'type') . " is not allowed.";
					$code = 2;					
				}
			} else {
				$this->_message = "File size is too heavy: " . $this->_getSize($file) . " KB.";
				$code = 3;
			}

		} else {
			$this->_message = "There was an error:  #" . $this->_getAttribute($file,'error');
			$code = 4;
		}

		return ['success' => $this->_success,
				'message' => $this->_message,
				'code' => $code,
				'file' => $this->_filename
				];

	}
	
	public function update($path, $file)
	{

		$filename = $this->_getAttribute($file,'name');
		$copy = $_POST[$file . '_copy'];

		if (!empty($filename)) {	
			if (empty($copy)) {
				$this->upload($path, $file);
			} else {
				if ($copy != $filename)
				{
					$this->upload($path, $file);
					$this->delete($path, $copy);
				}	
			}	
		}	
	}

	public function delete($path, $file)
	{

		$filename = (!is_null($file)) ? $path . $file : $path;
		$remove = (is_null($file)) ? false : empty($file);

		if (file_exists($filename) && !$remove) {
			return @unlink($filename);
		}
	}

	public function info($file)
	{

			$details = [
					'name' => $this->_getAttribute($file,'name'),
					'type' => $this->_getAttribute($file,'type'),
					'size' => $this->_getSize($file),
					'tmp_name' => $this->_getAttribute($file,'tmp_name'),
					'error' => $this->_getAttribute($file,'error')
			];

		return $details;
		
	}

}
