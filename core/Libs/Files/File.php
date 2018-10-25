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

	$file = File::manager();
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
	const TYPE_PLAIN = 'text/plain';
	const TYPE_CSV = 'text/csv';
	const TYPE_GIF = 'image/gif';
	const TYPE_JPEG = 'image/jpeg';
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
	private $_filename = "";
	private $_rename = null;
	private $_ext = [];

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

	static function manager($file = null)
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
		return self::manager()->delete($filename, null);
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

	public function getFilename()
	{
		return $this->_filename;
	}

	public function getTmp($file)
	{
		$this->_getAttribute($file,'tmp_name');
	}

	public function getContent($name = null)
	{
		$file = (is_null($name)) ? $this->_name : $name;
		return file_get_contents($this->getTmp($file));
	}

	public function upload($path, $file = null, $rename = null)
	{

		$rename = (is_null($rename)) ? $this->_rename : $rename;
		$file = (is_null($file)) ? $this->_name : $file;

		if (is_null($file)) {
			$file = 'file_'.time();
		}

		$this->_filename = $this->_getAttribute($file,'name');

		// if upload is successed
		if (!empty($this->_filename) && $this->_getAttribute($file,'error') == 0) {

			// Allowed size
			if ($this->_getSize($file) <= $this->_limit) {

				// Allowed type
				if (in_array($this->_getAttribute($file,'type'), $this->_ext) || empty($this->_ext)) {
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
					$this->_message = "Type: " . $this->_getAttribute($file,'type') . " is not allowed.";
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

		return [
			'success' => $this->_success,
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

	public function info($file, $index = null)
	{

			$details = [
					'name' => $this->_getAttribute($file,'name'),
					'type' => $this->_getAttribute($file,'type'),
					'size' => $this->_getSize($file),
					'tmp_name' => $this->_getAttribute($file,'tmp_name'),
					'error' => $this->_getAttribute($file,'error')
			];

			if (!is_null($index)) {
				return (isset($details[$index])) ? $details[$index] : null;
			}

		return $details;

	}

}
