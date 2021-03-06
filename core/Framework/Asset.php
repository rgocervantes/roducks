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

use Roducks\Libs\Output\Html;

class Asset
{

	private $_jsInline = [];
	private $_jsOnReady = [];
	private $_css = "";
	private $_js = "";
	private $_css_alt = "";
	private $_js_alt = "";

	static function load($scripts, $data)
	{

		if (is_array($scripts)) {

			extract($data);
			foreach ($scripts as $script) {
				$script_path = DIR_ASSETS_SCRIPTS . Helper::ext($script,"inc");
				list($realPath, $fileExists) = \App::getRealPath($script_path);
				if ($fileExists) {
					include $realPath;
					echo "\n";
				}
			}
		}
	}

	static function includeInLine(array $scripts = [], $data)
	{
		if (count($scripts) > 0) self::load($scripts, $data);
	}

	static function includeOnReady($scripts, $data)
	{
		if (count($scripts) > 0) {
			echo "\n$(document).ready(function() {\n\n";
			self::load($scripts, $data);
			echo "\n});\n\n";
		}
	}

	private function _getResource($dir, $script, $type)
	{

		$attrs = [];

		if (is_array($script)) {
			if (isset($script['src'])) {
				$attrs = $script;
				$script = $script['src'];
				unset($attrs['src']);
			} else {
				$load = false;
				$script = "unknown{$type}";
			}

		}

		$load = true;
		$resource = preg_replace('/^(.+)\?v=[0-9.]+$/', '$1', $script);

		if (Helper::isHttp($script)) {
			$file = $resource;
		}else{
			$file = $dir . $resource;
			$file_repo =  str_replace('/public/', 'public/assets/', $file);

			if (!\App::fileExists($file_repo)) $load = false;
		}

		return ['load' => $load, 'file' => $file, 'attrs' => $attrs];

	}

	private function _cssMeta($dir, $arr, $alt = false)
	{

		foreach($arr as $css) {
			$resource = $this->_getResource($dir, $css, "css");

			if ($resource['load']) {
				$file = $resource['file'];
				$p = (preg_match('/\?v=[0-9\.]+$/', $file)) ? "&t" : "?t";
				if (Environment::inDEV()) $file = $file .  $p . "=" . time();
				$attrs = (!empty($resource['attrs'])) ? " " . Html::getAttributes($resource['attrs']) : "";
				$tag = "<link type=\"text/css\" rel=\"stylesheet\" href=\"{$file}\"{$attrs} />\n";

				if ($alt) {
					$this->_css .= $tag;
				} else {
					$this->_css_alt .= $tag;
				}
			}
		}
	}

	private function _jsMeta($dir, $arr, $alt = false)
	{

		foreach($arr as $js) {
			$resource = $this->_getResource($dir, $js, "js");

			if ($resource['load']) {
				$file = $resource['file'];
				$p = (preg_match('/\?v=[0-9\.]+$/', $file)) ? "&t" : "?t";
				if (Environment::inDEV()) $file = $file .  $p . "=" . time();
				$attrs = (!empty($resource['attrs'])) ? " " . Html::getAttributes($resource['attrs']) : "";
				$tag = "<script type=\"text/javascript\" src=\"{$file}\"{$attrs}></script>\n";

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
	public function getJsInline()
	{
		return $this->_jsInline;
	}

	public function getJsOnReady()
	{
		return $this->_jsOnReady;
	}

	public function getCss()
	{
		return $this->_css . $this->_css_alt;
	}

	public function getJs()
	{
		return $this->_js . $this->_js_alt;
	}

	/**
	*	Set values
	*/
	public function jsInline($scripts, $overwrite = false)
	{

		if ($overwrite) {
			$this->_jsInline = $scripts;
		} else {
			$this->_jsInline = array_merge($this->_jsInline, $scripts);
		}

	}

	public function jsOnReady($scripts, $overwrite = false)
	{

		if ($overwrite) {
			$this->_jsOnReady = $scripts;
		} else {
			$this->_jsOnReady = array_merge($this->_jsOnReady, $scripts);
		}

	}

	public function css($arr, $overwrite = false)
	{

		if ($overwrite) {
			$this->_css = "";
		}

		$this->_cssMeta(DIR_ASSETS_CSS, $arr, true);
	}

	public function js($arr, $overwrite = false)
	{

		if ($overwrite) {
			$this->_js = "";
		}

		$this->_jsMeta(DIR_ASSETS_JS, $arr, true);
	}

	public function plugins(array $options = [], $alt = false)
	{

		if ($alt) {
			$this->_css_alt = "";
			$this->_js_alt = "";
		}

		$pluginsFile = Config::getPlugins()['data'];

		foreach ($options as $option) {
			if (isset($pluginsFile[$option])) {
				foreach ($pluginsFile[$option] as $src => $value) {

					if (is_array($value)) {
						$value['src'] = $src;
						$file = $value;
						$fileExt = $src;
					} else {
						$file = $value;
						$fileExt = $file;
					}

					$ext = Helper::getFileExtVersion($fileExt);
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
