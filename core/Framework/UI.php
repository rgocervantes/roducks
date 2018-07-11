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
use Roducks\Libs\Files\Image;

class UI
{

	const IMAGE_UNAVAILABLE = "unavailable.jpg";
	const BG_TRANSPARENT = "";
	const BG_WHITE = '#fff';
	const BG_BLACK = '#000';

	static function img($path, $size = "", array $attrs = [])
	{
		
		$div = [];
		$div['style'] = "";
		$conf = $attrs;
		$icon = [];
		$src = $path;

		if (is_array($path)) {
			$src = $path[0].$path[2];
		}

		if (!empty($size) && !isset($attrs['square']) && !isset($attrs['flex-w']) && !isset($attrs['flex-h'])) {
			$resize = Image::getResize(Path::get($src), $size);
			$attrs['width'] = $resize[0];
			$attrs['height'] = $resize[1];
		}	

		if (!\App::fileExists($src) || !preg_match('/^.+\.(jpg|jpeg|png)$/i', $src)) {
			$icon = Path::getAppIcon(self::IMAGE_UNAVAILABLE);
		}

		if (isset($attrs['square']) && $attrs['square']) {
			unset($attrs['square']);

			if (is_integer($size)) {
				$resize = Image::getResize(Path::get($src), $size);
				$attrs['width'] = $resize[0];
				$attrs['height'] = $resize[1];

				$div['style'] = "width:{$size}px; height:{$size}px;";

				if ($size > $attrs['width'] && $size > $attrs['height']) {
					if ($attrs['width'] > $attrs['height']) {
						$paddingTop = ceil(($size - $attrs['height']) / 2);
						if ($paddingTop > 0)
							$div['style'] .= " padding-top:{$paddingTop}px;";

					} else if ($attrs['height'] > $attrs['width']) {

					}
				} else {

					if ($attrs['width'] > $attrs['height']) {
						$paddingTop = ceil(($size - $resize[1]) / 2);
						if ($paddingTop > 0)
							$div['style'] .= " padding-top:{$paddingTop}px;";
					} else if ($attrs['height'] > $attrs['width']) {

					}
				}
			}

		} else if (isset($attrs['flex-w']) && $attrs['flex-w']) {
			unset($attrs['flex-w']);

			$resize = Image::getSize(Path::get($src));

			if ($size < $resize[1]) {

				$attrs['width'] = ceil(($resize[0] * $size) / $resize[1]);
				$attrs['height'] = $size;

				$div['style'] = "height:{$size}px;";

			} else {
				$paddingTop = ceil(($size - $resize[1]) / 2);
				$div['style'] = "height:{$size}px;";
				if ($paddingTop > 0)
					$div['style'] .= " padding-top:{$paddingTop}px;";
			}

		} else if (isset($attrs['flex-h']) && $attrs['flex-h']) {
			unset($attrs['flex-h']);

			$resize = Image::getSize(Path::get($src));

			if ($size < $resize[0]) {

				$attrs['width'] = $size;
				$attrs['height'] = ceil(($resize[1] * $size) / $resize[0]);

			}

		}

		if (isset($attrs['center']) && $attrs['center']) {
			unset($attrs['center']);

			$div['style'] .= " text-align:center;";
		}

		if (isset($attrs['bg'])) {
			if ($attrs['bg'] != self::BG_TRANSPARENT)
				$div['style'] .= " background: {$attrs['bg']};";
			unset($attrs['bg']);
		}

		$rz = (!isset($conf['square']) && !isset($conf['flex-w']) && !isset($conf['flex-h'])) ? $size : "auto";

		if (!empty($icon)) {
			$path = $icon[1].$icon[2];
		}

		$img = Html::img($path, $rz, $attrs);

		if (empty($div['style'])) {
			return $img;
		}

		return Html::div($img, $div);

	}

	static function getUploadedImage($path, $size = "", array $attrs = [])
	{
		return self::img(Path::getAppUploadedImage($path), $size, $attrs);
	}

	static function getImage($path, $size = "", array $attrs = [])
	{
		return self::img(Path::getAppImage($path), $size, $attrs);
	}

	static function getIcon($path, $size = "", array $attrs = [])
	{
		return self::img(Path::getAppIcon($path), $size, $attrs);
	}	

	static function getLogo($size = "", array $attrs = [])
	{
		return self::img(Path::getAppImage(LOGO_IMAGE), $size, $attrs);
	}

}