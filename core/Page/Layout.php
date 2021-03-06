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

namespace Roducks\Page;

use Roducks\Framework\Core;
use Roducks\Framework\Error;

class Layout
{

	static $data;
	static $path;

	static function view($name, $error = false)
	{

		if ($error) {
			return false;
		}

		$include = false;
		$view = (!is_null($name)) ? $name . FILE_PHTML : "";
		$dir_view = self::$path . $view;
		$dir_view_core = Core::getCoreModulesPathFrom($dir_view);

		$file = $dir_view;

		if (file_exists($dir_view)) {
			$include = true;
			$file = $dir_view;
		} else if (file_exists($dir_view_core)) {
			$include = true;
			$file = $dir_view_core;
		}

		if (preg_match('/\.tpl$/', $file)) {
			echo Duckling::parser($file, Template::$data);
		} else {
			if ($include) {
				extract(Template::$data);
				include $file;
			} else {
				Error::warning(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $file);
			}
		}

	}

	static private function _include($data)
	{

		if (isset($data[0]) && isset($data[1])) {
			switch ($data[0]) {
				case 'VIEW':
					self::view($data[1]);
					break;
				case 'TEMPLATE':
					Template::view($data[1],$data[2],$data[3]);
					break;
			}
		}

	}

	static function container($name)
	{

		if ($name == '') {
			return;
		}

		if (!isset(self::$data[$name])) {
			Error::warning("Undefined Layout container", __LINE__, __FILE__, '');
		} else {
			$data = self::$data[$name];

			if (empty($data)) {
				return;
			}

			if (is_array($data[0])) {
				foreach ($data as $key => $value) {
					self::_include($value);
				}
			} else {
				self::_include($data);
			}
		}

	}

	static function getColum($container, $index)
	{

		$width = 'col-md-12';

		switch ($container) {
			case 'col-2':
				$width = 'col-md-6';
				break;
			case 'col-3':
				$width = 'col-md-4';
				break;
			case 'col-4':
				$width = 'col-md-3';
				break;
		}

		return $width;
	}

}
