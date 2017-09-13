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

namespace rdks\core\framework;

class Asset{

	private $_scriptsInline = [];
	private $_scriptsOnReady = [];
	private $_css = "";
	private $_js = "";
	private $_css_alt = "";
	private $_js_alt = "";

	private $_key = "";

	static function load($scripts, $data){

		if(is_array($scripts)){

			extract($data);
			foreach ($scripts as $script) {
				$script_path = DIR_ASSETS_SCRIPTS . Helper::ext($script,"inc");
				if(file_exists($script_path)){
					include $script_path;
					echo "\n";
				}
			}
		}
	}

	static function includeInLine($scripts = array(), $data){
		if(count($scripts) > 0) self::load($scripts, $data);
	}

	static function includeOnLoad($scripts, $data){
		echo "$(window).load(function(){\n\n";
		self::load($scripts, $data);	
		echo "\n});\n\n";
	}

	static function includeOnReady($scripts, $data){
		if(count($scripts) > 0){
			echo "$(document).ready(function(){\n\n";
			self::load($scripts, $data);	
			echo "\n});\n\n";			
		}
	}
	
	private function _getResource($dir, $scripts, $type){
		$load = true;
		$resource = Helper::ext($scripts,$type);
		
		if(Helper::isHttp($scripts)){
			$file = $resource;
		}else{
			$file = $dir . $resource;
			$file_repo = DIR_APP . preg_replace('/^\/(.+)$/', '$1', $file);
			if(!file_exists($file_repo)) $load = false;
		} 

		return ['load' => $load, 'file' => $file];

	}

	private function _cssMeta($dir, $arr, $alt = false){

		foreach($arr as $css){
			$resource = $this->_getResource($dir, $css, "css");
			
			if($resource['load']){
				$tag = "<link type=\"text/css\" rel=\"stylesheet\" href=\"". $resource['file'] . "\" />\n";
			
				if ($alt) {
					$this->_css .= $tag;
				} else {
					$this->_css_alt .= $tag;
				}
			}
		}
	}

	private function _jsMeta($dir, $arr, $alt = false){

		foreach($arr as $js){
			$resource = $this->_getResource($dir, $js, "js");
			
			if($resource['load']){

				$file = $resource['file'];

				$tag = "<script type=\"text/javascript\" src=\"{$file}\"></script>\n";

				if ($alt) {
					$this->_js .= $tag;
				} else {
					$this->_js_alt .= $tag;
				}
			}
		}
	}

	/**
	*	Get values
	*/
	public function getScriptsInline(){
		return $this->_scriptsInline;
	}

	public function getScriptsOnReady(){
		return $this->_scriptsOnReady;
	}

	public function getCss(){
		return $this->_css . $this->_css_alt;
	}

	public function getJs(){
		return $this->_js . $this->_js_alt;
	}

	/**
	*	Set values
	*/
	public function setKey($hash){
		$this->_key = $hash;
	}

	public function scriptsInline($scripts){
		$this->_scriptsInline = array_merge($this->_scriptsInline, $scripts);
	}	

	public function scriptsOnReady($scripts){
		$this->_scriptsOnReady = array_merge($this->_scriptsOnReady, $scripts);
	}

	public function css($arr){
		$this->_cssMeta(DIR_ASSETS_CSS, $arr, true);
	}

	public function js($arr){
		$this->_jsMeta(DIR_ASSETS_JS, $arr, true);
	}

	public function plugins(array $options = [], $alt = true){

		if($alt){
			$this->_css_alt = "";
			$this->_js_alt = "";
		}

		$pluginsFile = Core::getPluginsFile();

		foreach ($options as $option) {
			if(isset($pluginsFile[$option])) {
				foreach ($pluginsFile[$option] as $file) {
					$ext = Helper::getFileExt($file);
					$fx = "";
					switch ($ext) {
						case 'css':
							$fx = "_cssMeta";
							break;
						
						case 'js':
							$fx = "_jsMeta";
							break;
					}

					$this->$fx(DIR_ASSETS_PLUGINS.$option."/", array($file), $alt);
				}
			}
		}
	}

}
