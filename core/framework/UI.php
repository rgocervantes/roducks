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

use rdks\core\libs\Output\Html;
use rdks\core\libs\Files\Image;

class UI {

	const IMAGE_UNAVAILABLE = "unavailable.jpg";
	const BG_TRANSPARENT = "";
	const BG_WHITE = '#fff';
	const BG_BLACK = '#000';

	static function img($path, $size = "", array $attrs = []){
		
		$div = [];
		$conf = $attrs;
		$div['style'] = "";
		$src = $path;

		if(is_array($path)){
			$src = $path[0].$path[2];
		}

		if(!file_exists($src)){
			$icon = Path::getAppIcon(self::IMAGE_UNAVAILABLE);
			$src = $icon[0].$icon[2];
			$path = $src;
		}

		if(isset($attrs['square']) && $attrs['square']){
			unset($attrs['square']);

			if(is_integer($size)){
				$resize = Image::getResize($src, $size);
				$attrs['width'] = $resize[0];
				$attrs['height'] = $resize[1];

				$div['style'] = "width:{$size}px; height:{$size}px;";

				if($size > $attrs['width'] && $size > $attrs['height']){
					if($attrs['width'] > $attrs['height']){
						$paddingTop = ceil(($size - $attrs['height']) / 2);
						if($paddingTop > 0)
							$div['style'] .= " padding-top:{$paddingTop}px;";

					} else if($attrs['height'] > $attrs['width']){
						$div['style'] .= " text-align:center;";
					}
				} else {

					if($attrs['width'] > $attrs['height']){
						$paddingTop = ceil(($size - $resize[1]) / 2);
						if($paddingTop > 0)
							$div['style'] .= " padding-top:{$paddingTop}px;";
					} else if($attrs['height'] > $attrs['width']){
						$div['style'] .= " text-align:center;";
					}
				}
			}

		} else if(isset($attrs['flex-w']) && $attrs['flex-w']){
			unset($attrs['flex-w']);

			$resize = Image::getSize($src);

			if($size < $resize[1]){

				$attrs['width'] = ceil(($resize[0] * $size) / $resize[1]);
				$attrs['height'] = $size;

				$div['style'] = "width:{$attrs['width']}px; height:{$size}px;";

			} else {
				$paddingTop = ceil(($size - $resize[1]) / 2);
				$div['style'] = "width:{$resize[0]}px; height:{$size}px;";
				if($paddingTop > 0)
					$div['style'] .= " padding-top:{$paddingTop}px;";
			}

		} else if(isset($attrs['flex-h']) && $attrs['flex-h']){
			unset($attrs['flex-h']);

			$resize = Image::getSize($src);

			if($size < $resize[0]){

				$attrs['width'] = $size;
				$attrs['height'] = ceil(($resize[1] * $size) / $resize[0]);

			}

			$div['style'] = "width:{$size}px;";
		}

		if(isset($attrs['center']) && $attrs['center']){
			unset($attrs['center']);
			$div['style'] .= " margin: 0 auto;";
		}

		if(isset($attrs['bg'])){
			if($attrs['bg'] != self::BG_TRANSPARENT)
				$div['style'] .= " background: {$attrs['bg']};";
			unset($attrs['bg']);
		}

		$rz = (!isset($conf['square']) && !isset($conf['flex-w']) && !isset($conf['flex-h'])) ? $size : "auto";

		$img = Html::img($path, $rz, $attrs);

		if(empty($div['style'])){
			return $img;
		}

		return Html::div($img, $div);

	}

	static function getImage($path, $size = "", array $attrs = []){
		return self::img(Path::getAppImage($path), $size, $attrs);
	}

	static function getIcon($path, $size = "", array $attrs = []){
		return self::img(Path::getAppIcon($path), $size, $attrs);
	}	

	static function getLogo($size = "", array $attrs = []){
		return self::img(Path::getAppImage(LOGO_IMAGE), $size, $attrs);
	}

}