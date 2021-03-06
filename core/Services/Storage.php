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

namespace Roducks\Services;

use Roducks\Page\Service;
use Roducks\Page\JSON;
use Roducks\Framework\Dispatch;
use Roducks\Framework\Helper;
use Roducks\Framework\Path;
use Roducks\Data\User as UserData;
use Roducks\Data\Log as LogData;
use Roducks\Libs\Files\Image;
use Roducks\Libs\Files\File;
use Roducks\Libs\Files\Directory;
use Roducks\Libs\Files\Zip;
use Roducks\Libs\Utils\Date;
use Roducks\Libs\Request\Request;

class Storage extends Service
{

	protected $_dispatchUrl = true;

	private $_ajax = true,
			$_response = [],
			$_fileName = null,
			$_input = "uploader";

	static function user($id)
	{
		return UserData::init($id);
	}

	static function log($id, $date = "")
	{
		return LogData::init($id, $date);
	}

	static function moveFile($dir1, $dir2)
	{
		File::move(Path::getData(), $dir1, Path::getData(), $dir2);
	}

	static function removeFile($file)
	{
		File::remove(Path::getData($file));
	}

	static function makeDir($dir = "")
	{
		if (empty($dir)) {
			return false;
		}

		return Directory::make(Path::getData(), $dir);
	}

	static function removeDir($dir = "")
	{
		if (empty($dir)) {
			return false;
		}

		Directory::remove(Path::getData($dir));

	}

	static function openDir($dir)
	{
		return Directory::open(Path::getData($dir));
	}

	static function cleanDir($dir, array $options)
	{
		Directory::clean(Path::getData($dir), $options);
	}

	static function moveDir($dir1, $dir2)
	{
		Directory::move(Path::getData(), $dir1, Path::getData(), $dir2);
	}

	static function zipDir($dir_to_zip, $dir_destination, $name, array $exclude = [])
	{

		Directory::zip([
			'folder' => Path::getData($dir_to_zip),
	    'exclude' => $exclude,
	    'destination' => [Path::getData(), $dir_destination],
	    'filename' => $name // .zip
		]);

	}

	static function unzipDir($file, $remove = false)
	{
		Zip::extract(Path::getData($file));

		if ($remove) {
			self::removeFile($file);
		}
	}

	static function issetJSON($dir, $name)
	{
		$path = Path::getData($dir) . Helper::ext($name, 'json');
		return file_exists($path);
	}

	static function setJSON($dir, $name, $data)
	{
		Directory::make(Path::getData(), $dir);
		File::createJSON(Path::getData($dir), $name, $data);
	}

	static function setJSONString($dir, $name, $data)
	{
		Directory::make(Path::getData(), $dir);
		File::create(Path::getData($dir), Helper::ext($name, 'json'), $data);
	}

	static function getJSON($dir, $name)
	{
		$path = Path::getData($dir) . Helper::ext($name, 'json');

		if (file_exists($path)) {
			$content = Request::getContent($path);
			$json = \Roducks\Page\JSON::decode($content);
		} else {
			$json = [];
		}

		return $json;
	}

	static function getJSONString($dir, $name)
	{
		return Request::getContent(Path::getData($dir) . Helper::ext($name, 'json'));
	}

	static function updateJSON($dir, $name, $content)
	{
		$stored = self::getJSON($dir, $name);
		$data = array_merge($stored, $content);
		self::setJSON($dir, $name, $data);
	}

	static function readJSON($dir, $name)
	{
		$path = Path::getData($dir) . Helper::ext($name, 'json');
		header("Content-Type: application/json; charset=utf8");
		readfile($path);
	}

	static function removeJSON($dir, $name)
	{
		File::remove(Path::getData($dir) . Helper::ext($name, 'json'));
	}

	private function _serviceError($code, $msg)
	{
		if ($this->_ajax) {
			$this->setError($code, $msg);
		}
	}

	private function __makeCrops($fx, $dir, $f, array $cuts = [])
	{

		$w = intval($this->post->param("w"));
		$h = intval($this->post->param("h"));
		$x = intval($this->post->param("x"));
		$y = intval($this->post->param("y"));

		/* --- CROP ---- */
		$fn = explode(".", $f);
		$full_image = $fn[0] . '_'. $fx .'.' . $fn[1];
		$image = new Image;
		$image->crop($dir . $f, $dir . $full_image, $w, $h, $x, $y, 95);

		if (count($cuts) > 0) :
			foreach($cuts as $c) :
				$preset = $fn[0] . '_' . $c . '.' . $fn[1];
				if ($w >= $c) : // if crop is greather than resize
					$image->load($dir . $full_image);
					$image->resizeToWidth($c);
					$image->save($dir . $preset, 95);
				endif;
			endforeach;
		endif;

	}

	// delete old image when uploading a new one.
	private function __deleteCrops($path, array $cuts = [])
	{
		$copy = $this->post->param('copy');

		if (!empty($copy)) :
			File::remove($path.$copy);
			// delte all the crops
			if (count($cuts) > 0) : foreach($cuts as $c) :
				File::remove($path.Path::getCropName($copy, $c));
			endforeach; endif;
		endif;
	}

	private function _deleteCrops($path, array $cuts = [])
	{
		$this->__deleteCrops($path, $cuts);
		parent::output();
	}

	private function _crop($fx, $dir, $dir2, $cuts)
	{

		$file = $this->post->param("cropper");
		$this->__makeCrops($fx, Path::get($dir), $file, $cuts);
		$cuts = array_merge(array($fx), $cuts);
		$this->__deleteCrops(Path::get($dir), $cuts);

		$data = [
			'dir' => $dir2,
			'img_full' => $file,
			'img_cropped' => Path::getCropName($file, 150),
			'name' => $file
		];

		$this->setMessage(TEXT_CHANGES_SAVED);
		$this->data($data);

		parent::output();
	}

	private function _upload($prefix, $dir, $dir2, array $size = [], array $types = [])
	{

		$file = File::system();
		if (count($types) > 0) $file->type($types);
		if (count($size) > 0) {

			if (strtoupper($size[1]) == 'KB') {
				$file->kb($size[0]);
			}

			if (strtoupper($size[1]) == 'MB') {
				$file->mb($size[0]);
			}

		}

		// If upload directory does not exist, let's create it!
		Directory::make(Path::get(), $dir);

		$filename = (!is_null($this->_fileName)) ? $this->_fileName : $prefix . Date::getCurrentDateTimeFlat();
		$file
		->path(Path::get($dir))
		->input($this->_input)
		->name($filename)
		->upload();

		$data = [
			'success' => $file->onSuccess(),
			'message' => $file->getMessage(),
			'code' => $file->getCode(),
			'data' => [
				'dir' => $dir2,
				'file' => $file->getName(),
				'path' => $dir2 . $file->getName()
			]
		];

		if ($this->_ajax) {
			$this->data($data);

			// We don't send Http headers because of this is a post request inside of an iframe and it's parsed properly as text plain.
			echo JSON::encode($this->getJsonData());
		} else {
			$this->_response = $data;
		}

	}

	private function _deleteFile($dir)
	{
		$file = ($this->_ajax) ? $this->post->param("file") : $this->_input;
		$response = File::remove($dir.$file);

		if ($this->_ajax) {
			parent::output();
		} else {
			$this->_response = $response;
		}
	}

	public function file($action = "", $class = "", $index = "")
	{

		$this->params([
			'action' => [$action, 'PARAM', Dispatch::PARAM_STRING],
			'class' => [$class, 'PARAM', Dispatch::PARAM_STRING],
			'index' => [$index, 'PARAM', Dispatch::PARAM_STRING]
		]);

		$method = Helper::getCamelName($action, false);
		$config = $this->getConfig($class, $index, null);

		if (is_null($config)) {
			$method = "invalid";
		}

		switch ($method) {
			case 'cropSquared':
				if (isset($config['clipping']) && isset($config['dir_upload']) && isset($config['dir_uploaded']) && isset($config['squared_clippings'])) {
					$this->_crop($config['clipping'], $config['dir_upload'], $config['dir_uploaded'], $config['squared_clippings']);
				} else {
					$this->_serviceError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			case 'cropLandscape':
				if (isset($config['clipping']) && isset($config['dir_upload']) && isset($config['dir_uploaded']) && isset($config['landscape_clippings'])) {
					$this->_crop($config['clipping'], $config['dir_upload'], $config['dir_uploaded'], $config['landscape_clippings']);
				} else {
					$this->_serviceError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			case 'upload':
				if (isset($config['prefix']) && isset($config['dir_upload']) && isset($config['dir_uploaded']) && isset($config['size']) && isset($config['types'])) {
					$this->_upload($config['prefix'], $config['dir_upload'], $config['dir_uploaded'], $config['size'], $config['types']);
				} else {
					$this->_serviceError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			case 'delete':
				if (isset($config['dir_upload'])) {
					$this->_deleteFile($config['dir_upload']);
				} else {
					$this->_serviceError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			case 'deleteCrops':
				if (isset($config['dir_upload']) && isset($config['squared_clippings'])) {
					$this->_deleteCrops($config['dir_upload'], $config['squared_clippings']);
				} else {
					$this->_serviceError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			default:
				$this->setServiceError(0, TEXT_SERVICE_UNAVAILABLE);
				if ($this->_ajax) parent::output();
				break;
		}

	}

	public function uploadFileWithName($name, $input, $class, $index)
	{
		$this->_fileName = $name;
		return $this->uploadFile($input, $class, $index);
	}

	public function uploadFile($input, $class, $index)
	{
		$this->_ajax = false;
		$this->_input = $input;
		$this->file('upload', $class, $index);

		return $this->_response;
	}

	public function deleteFile($input, $class, $index)
	{
		$this->_ajax = false;
		$this->_input = $input;
		$this->file('delete', $class, $index);

		return $this->_response;
	}

	public function getSize($name, $index)
	{
		$config = $this->getConfig($name, "{$index}:size", null);
		$json = (!is_null($config)) ? $config : [100,"KB"];

		return JSON::encode($json);

	}

}
