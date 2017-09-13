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

namespace rdks\core\libs\Output;

use rdks\core\libs\Files\Image;

class Html {

	static private function _getAttributes($params, $attrs){

		if(empty($attrs)) {
			return $params;
		}

		$params = array_merge($params, $attrs);

		return $params;

	}

	static function getAttributes(array $attrs){
		$ret = [];

		foreach ($attrs as $key => $value) {
			$ret[] = $key.'="'.$value.'"';
		}

		return implode(' ', $ret);
	}

	static function tag($name, $content = "", array $attrs = [], $closed = true){
		$params = self::getAttributes($attrs);
		$tag = "<{$name} {$params}";

		if($closed) {
			$tag .= ">{$content}</{$name}>";
		} else {
			$tag .= "/>";
		}

		return $tag;
	}

	static function div($content = "", array $attrs = []){
		return self::tag(__FUNCTION__, $content, $attrs);
	}

	static function img($path, $size = "", array $attrs = []){

		$http = "";

		if(is_array($path) && count($path) == 3){
			$img = $path[0] . $path[2];
			$src = $path[1] . $path[2];
		} else {
			$img = $path;
			$src = $path;
		}

		if(isset($attrs['http'])){
			$http = $attrs['http'];
			unset($attrs['http']);
		}
		
		$params = ['src' => $http.$src];

		if(!empty($size) && $size != "auto") {

			if(is_array($size) && count($size) == 2) {
				$xy = $size;
			} else {
				$xy = Image::getResize($img, $size);
			}

			list($w, $h) = $xy;

			$params['width'] = $w;
			$params['height'] = $h;			

		}

		$params = self::_getAttributes($params, $attrs);

		return self::tag(__FUNCTION__, "", $params, false);
	}

}
 
?>