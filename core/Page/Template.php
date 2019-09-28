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

use Roducks\Framework\Config;
use Roducks\Framework\Helper;
use Roducks\Framework\Error;
use Roducks\Framework\Path;

class Template
{

	static $name = null;
	static $data = [];

	static function view($file, array $data = [], $merge = false)
	{

		$dir_template = Path::getTemplate(Template::$name, $file);

		if (!is_null(self::$name) && !empty($file)) {
			if (file_exists($dir_template)) {
				$content = self::$data;

				if (count($data) > 0) {
					if ($merge) {
						$content = self::$data + $data;
					} else {
						$content = $data;
					}
				}

				if (Helper::isTpl($dir_template)) {
					echo Duckling::parser($dir_template, $content);
				} else {
					extract($content);
					include $dir_template;
				}

			}
		} else {
			Error::warning("Invalid template name",__LINE__, __FILE__, $dir_template);
		}
	}

	static function menu($name)
	{
		return Config::getMenu($name)['data'];
	}

	static function displayBlock($bool)
	{
		return ($bool) ? 'display="block"; ' : '';
	}

	static function displayNone($bool)
	{
		return ($bool) ? 'display="none"; ' : '';
	}

	static function checked($bool)
	{
		return ($bool) ? ' checked="checked"' : '';
	}

	static function selected($bool)
	{
		return ($bool) ? ' selected="selected"' : '';
	}

	static function conditional($bool, $onTrue = "", $onFalse = "")
	{
		return ($bool) ? $onTrue : $onFalse;
	}

	static function equals($a1, $a2, $onTrue = "", $onFalse = "")
	{
		return self::conditional(($a1 == $a2), $onTrue, $onFalse);
	}

	static function notEmpty($value, $onTrue = "", $onFalse = "")
	{
		if (!empty($value)) {
			return $onTrue;
		}

		return $onFalse;
	}

}
