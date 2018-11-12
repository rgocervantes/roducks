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

	$file = File::system();
	->type([File::TYPE_JPG',File::TYPE_PNG]);
	->kb(150);
	->path(Path::getData())
	->input($input)
	->name('profile')
	->upload();

	if ($file->onSuccess()) {
		echo $file->getName();
	} else {
		echo $file->getMessage();
	}

*/

namespace Roducks\Libs\Files;

final class File
{
	const TYPE_PLAIN = 'text/plain';
	const TYPE_CSV = 'text/csv';
	const TYPE_GIF = 'image/gif';
	const TYPE_JPG = 'image/jpeg';
	const TYPE_PNG = 'image/png';
	const TYPE_MP3 = 'audio/mp3';
	const TYPE_MPEG = 'video/mpeg';
	const TYPE_QUICKTIME = 'video/quicktime';
	const TYPE_ZIP = 'application/zip';
	const TYPE_PDF = 'application/pdf';
	const TYPE_XML = 'application/xml';
	const TYPE_JSON = 'application/json';
	const TYPE_OTHER = 'application/octet-stream';
	const TYPE_XLS = 'application/vnd.ms-excel';
	const TYPE_PPT = 'application/vnd.ms-powerpoint';
	const TYPE_WORD = 'application/msword';
	const TYPE_RAR = 'application/x-rar-compressed';
	const TYPE_TAR = 'application/x-tar';

	/*
	|-------------------------------|
	|		PRIVATE
	|-------------------------------|
	*/

	private $_limit = 1024; // 1 MB by default
	private $_success = false;
	private $_message = "Ok.";
	private $_name = "";
	private $_rename = null;
	private $_path = null;
	private $_input = null;
	private $_ext = [];

	private function _getAttribute($file, $attr)
	{
		return $_FILES[$file][$attr];
	}

	private function _getSize($f)
	{
		return ceil($this->_getAttribute($f, 'size') / 1024);
	}

	private function _setSize($n)
	{
		$this->_limit = $n;
	}

	/*
	|-------------------------------|
	|		STATIC
	|-------------------------------|
	*/

	static function system($file = null)
	{
		$file = new File($file);
		return $file;
	}

	static function exists($filename)
	{
		return file_exists($filename);
	}

	/**
	 * @example File::remove(Path::getData("xml/home.xml"));
	 */
	static function remove($filename)
	{
		if (self::exists($filename)) {
			return @unlink($filename);
		}
	}

	/**
	 * @example File::move(Path::getData(), "web/hello/cervantes.zip", Path::getData(), "web/zip/profile/rod.zip");
	 */
	static function move($path1, $origin, $path2, $destination)
	{

		$from = $path1.$origin;
		$to = $path2.$destination;

		if (file_exists($from) && $origin != $destination) {

			// From path
			$fromPath = explode(DIRECTORY_SEPARATOR, $origin);
			$total1 = count($fromPath);

			if ($total1 > 1) {
				$index1 =  $total1 - 1;
				unset($fromPath[$index1]);
			}

			$folder1 = implode(DIRECTORY_SEPARATOR, $fromPath);

			// To path
			$toPath = explode(DIRECTORY_SEPARATOR, $destination);
			$total2 = count($toPath);

			if ($total2 > 1) {
				$index2 =  $total2 - 1;
				unset($toPath[$index2]);
			}

			$folder2 = implode(DIRECTORY_SEPARATOR, $toPath);

			if (!file_exists($path2.$folder2)) {
				Directory::make($path2, $folder2);
			}

			$removePath = $path1.$folder1;
			$dir = Directory::open($removePath);
			$canRemove = (empty($dir['folders']) && count($dir['files']) == 1);

			rename($from, $to);

			if (!empty($origin) && !empty($destination)) {

				if ($total1 > 2 && $canRemove) {
					Directory::remove($removePath);
				}

				$rootFolder = $path1.$fromPath[0];
				if ($fromPath[0] != $toPath[0] && Directory::isEmpty($rootFolder)) {
					Directory::remove($rootFolder);
				}

			}
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
	public function __construct($file = null)
	{
		$this->_name = $file;
	}

	public function type($arr)
	{
		$this->_ext = $arr;
		return $this;
	}

	public function kb($n)
	{
		$this->_setSize($n);
		return $this;
	}

	public function mb($n)
	{
		$cal = ceil($n * 1024);
		$this->_setSize($cal);
		return $this;
	}

	public function onSuccess()
	{
		return $this->_success;
	}

	public function onError()
	{
		return !$this->_success;
	}

	public function getMessage()
	{
		return $this->_message;
	}

	public function getCode()
	{
		return $this->_code;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function getTmp()
	{
		return $this->_getAttribute($this->_input, 'tmp_name');
	}

	public function getContent($name = null)
	{
		return file_get_contents($this->getTmp());
	}

	public function getDetails($index = null)
	{

		$input = $this->_input;
		$details = [
			'name' => $this->_getAttribute($input, 'name'),
			'type' => $this->_getAttribute($input, 'type'),
			'size' => $this->_getSize($input),
			'tmp_name' => $this->_getAttribute($input, 'tmp_name'),
			'error' => $this->_getAttribute($input, 'error')
		];

		if (!is_null($index)) {
			return (isset($details[$index])) ? $details[$index] : null;
		}

		return $details;

	}

	public function path($dir)
	{
		$this->_path = $dir;
		return $this;
	}

	public function input($name)
	{
		$this->_input = $name;
		return $this;
	}

	public function name($name)
	{
		$this->_rename = $name;
		return $this;
	}

	public function upload()
	{

	  $path = $this->_path;
	  $input = $this->_input;
		$rename = $this->_rename;

	  if (is_null($rename)) {
	    $rename = 'file_'.time();
	  }

	  $this->_name = $this->_getAttribute($input, 'name');

	  // if upload is successed
	  if (!empty($this->_name) && $this->_getAttribute($input, 'error') == 0) {

	    // Allowed size
	    if ($this->_getSize($input) <= $this->_limit) {

	      // Allowed type
	      if (in_array($this->_getAttribute($input, 'type'), $this->_ext) || empty($this->_ext)) {
	        $this->_name = $rename . preg_replace('/^.+(\.\w{3,4})$/', '$1', $this->_name);

	        if (move_uploaded_file($this->_getAttribute($input, 'tmp_name'), $path . $this->_name)) {
	          $this->_success = true;
	          $this->_message = "File was uploaded successfully.";
	          $this->_code = 1;
	        } else {
	          $this->_message = "It couln't be moved file to destination.";
	          $this->_code = 5;
	        }

	      } else {
	        $this->_message = "Type: " . $this->_getAttribute($input, 'type') . " is not allowed.";
	        $this->_code = 2;
	      }
	    } else {
	      $this->_message = "File size is too heavy: " . $this->_getSize($input) . " KB.";
	      $this->_code = 3;
	    }

	  } else {
	    $this->_message = "There was an error:  #" . $this->_getAttribute($input, 'error');
	    $this->_code = 4;
	  }

	  return $this;

	}

	public function update()
	{

		$filename = $this->_getAttribute($this->_input, 'name');
		$copy = $_POST[$this->_input . '_copy'];

		if (!empty($filename)) {
			if (empty($copy)) {
				$this->upload();
			} else {
				if ($copy != $filename) {
					$this->upload();
					self::remove($this->_path.$copy);
				}
			}
		}
	}

	public function delete()
	{
		return self::remove($this->_path.$this->_input);
	}

}
