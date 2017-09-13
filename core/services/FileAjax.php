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

namespace rdks\core\services;

use rdks\core\page\Service;
use rdks\core\page\JSON;
use rdks\core\framework\Dispatch;
use rdks\core\framework\Helper;
use rdks\core\framework\Path;
use rdks\core\libs\Files\Image;
use rdks\core\libs\Files\File;
use rdks\core\libs\Files\Directory;
use rdks\core\libs\Utils\Date;

class FileAjax extends Service{

	protected $_dispatchUrl = true;

	private function __makeCrops($fx, $dir, $f, array $cuts = []){

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
	private function __deleteCrops($path, array $cuts = []){
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

	private function _deleteCrops($path, array $cuts = []){
		$this->__deleteCrops($path, $cuts);
		parent::output();
	}	
	
	private function _crop($fx, $dir, $dir2, $cuts){
		
		$file = $this->post->param("cropper");
		$this->__makeCrops($fx, $dir, $file, $cuts);
		$cuts = array_merge(array($fx), $cuts);
		$this->__deleteCrops($dir, $cuts);

		$data = [
			'dir' => $dir2,
			'img' => Path::getCropName($file, 150),
			'name' => $file
		];

		$this->setMessage(TEXT_CHANGES_SAVED);
		$this->data($data);

		parent::output();
	}
	
	private function _upload($prefix, $dir, $dir2, array $size = [], array $types = []){

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
	
	private function _deleteFile($dir){
		$file = File::init();
		$file->delete($dir, $this->post->param("file"));		

		parent::output();
	}

	public function module($class = "", $action = ""){

		$this->params([
			'class' => [$class, 'PARAM', Dispatch::PARAM_STRING],
			'action' => [$action, 'PARAM', Dispatch::PARAM_STRING]			
		]);

		$method = Helper::getCamelName($action, false);
		$module = Helper::getCamelName($class);
		$data = ($class == 'global') ? $this->getGlobalConfig() : $this->getModuleConfig($module);
		$config = $data['USER_CONFING'];

		switch ($method) {
			case 'cropSquared':
				$this->_crop($config['CLIPPING'], $config['DIR_UPLOAD'], $config['DIR_UPLOADED'], $config['SQUARED_CLIPPINGS']);	
				break;
			case 'cropLandscape':
				$this->_crop($config['CLIPPING'], $config['DIR_UPLOAD'], $config['DIR_UPLOADED'], $config['LANDSCAPE_CLIPPINGS']);
				break;
			case 'uploadFile':
				$this->_upload($config['PREFIX'], $config['DIR_UPLOAD'], $config['DIR_UPLOADED'], $config['SIZE'], $config['TYPES']);
				break;
			case 'deleteFile':
				$this->_deleteFile($config['PATH']);
				break;
			case 'deleteCrops':
				$this->_deleteCrops($config['DIR_UPLOAD'], $config['CLIPPINGS']);
				break;
			default:
				$this->setError(0, TEXT_SERVICE_UNAVAILABLE);
				parent::output();
				break;												
		}

	}

} 