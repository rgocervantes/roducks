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
use Roducks\Libs\Files\Image;
use Roducks\Libs\Files\File;
use Roducks\Libs\Files\Directory;
use Roducks\Libs\Utils\Date;

class FileAjax extends Service
{

	protected $_dispatchUrl = true;

	private function __makeCrops($fx, $dir, $f, array $cuts = [])
	{

		$w = $this->post->param("w");
		$h = $this->post->param("h");
		$x = $this->post->param("x");
		$y = $this->post->param("y");

		/* --- CROP ---- */
		$fn = explode(".", $f);
		$full_image = $fn[0] . '_'. $fx .'.' . $fn[1];
		$image = new Image;
		$image->crop($dir . $f, $dir . $full_image, $w, $h, $x, $y, 95);

		if(count($cuts) > 0):
			foreach($cuts as $c):
				$preset = $fn[0] . '_' . $c . '.' . $fn[1];
				if($w >= $c): // if crop is greather than resize
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
		$file = File::init();

		$copy = $this->post->param('copy');

		if(!empty($copy)):
			$file->delete($path, $copy);
			// delte all the crops
			if(count($cuts) > 0): foreach($cuts as $c):
				$file->delete($path, Path::getCropName($copy, $c));
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
		$this->__makeCrops($fx, $dir, $file, $cuts);
		$cuts = array_merge(array($fx), $cuts);
		$this->__deleteCrops($dir, $cuts);

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

		$file = File::init();
		if(count($types) > 0) $file->type($types);
		if(count($size) > 0){
			
			if(strtoupper($size[1]) == 'KB'){
				$file->kb($size[0]);	
			}
			
			if(strtoupper($size[1]) == 'MB'){
				$file->mb($size[0]);	
			}		
		
		}
		
		// If upload directory does not exist, let's create it!
		Directory::make($dir);

		$filename = $prefix . Date::getFlatDate(Date::getCurrentDateTime());
		$resp = $file->upload($dir, "uploader", $filename);

		$data = [
				'success' => $resp['success'],
				'message' => $resp['message'],
				'code' => $resp['code'],
				'data' => [
					'dir' => $dir2,
					'img' => $resp['file']
				]	
			];

		$this->data($data);
		
		// We don't send Http headers because of this is a post request inside of an iframe and it's parsed properly as text plain. 
		echo JSON::encode($this->getJsonData());	
	
	}
	
	private function _deleteFile($dir)
	{
		$file = File::init();
		$file->delete($dir, $this->post->param("file"));		

		parent::output();
	}

	public function module($class = "", $index = "", $action = "")
	{

		$this->params([
			'class' => [$class, 'PARAM', Dispatch::PARAM_STRING],
			'index' => [$index, 'PARAM', Dispatch::PARAM_STRING],
			'action' => [$action, 'PARAM', Dispatch::PARAM_STRING]
		]);

		$method = Helper::getCamelName($action, false);
		$module = Helper::getCamelName($class);
		$data = ($class == 'global') ? $this->getGlobalConfig() : $this->getModuleConfig($module);
		$config = [];

		if(isset($data[$index])){
			$config = $data[$index];
		} else {
			$method = "invalid";
		}
		
		switch ($method) {
			case 'cropSquared':
				if(isset($config['clipping']) && isset($config['dir_upload']) && isset($config['dir_uploaded']) && isset($config['squared_clippings'])){
					$this->_crop($config['clipping'], \App::getRealFilePath($config['dir_upload']), $config['dir_uploaded'], $config['squared_clippings']);
				} else {
					$this->setError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			case 'cropLandscape':
				if(isset($config['clipping']) && isset($config['dir_upload']) && isset($config['dir_uploaded']) && isset($config['landscape_clippings'])){
					$this->_crop($config['clipping'], \App::getRealFilePath($config['dir_upload']), $config['dir_uploaded'], $config['landscape_clippings']);
				} else {
					$this->setError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			case 'uploadFile':
				if(isset($config['prefix']) && isset($config['dir_upload']) && isset($config['dir_uploaded']) && isset($config['size']) && isset($config['types'])){
					$this->_upload($config['prefix'], \App::getRealFilePath($config['dir_upload']), $config['dir_uploaded'], $config['size'], $config['types']);
				} else {
					$this->setError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			case 'deleteFile':
				if(isset($config['dir_upload'])){
					$this->_deleteFile($config['dir_upload']);
				} else {
					$this->setError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			case 'deleteCrops':
				if(isset($config['dir_upload']) && isset($config['squared_clippings'])){
					$this->_deleteCrops(\App::getRealFilePath($config['dir_upload']), $config['squared_clippings']);
				} else {
					$this->setError(0, TEXT_SERVICE_UNAVAILABLE);
				}
				break;
			default:
				$this->setError(0, TEXT_SERVICE_UNAVAILABLE);
				parent::output();
				break;												
		}

	}

} 