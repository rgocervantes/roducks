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

use Roducks\Page\Frame;

abstract class Event extends Frame
{

	protected $_pageType = 'EVENT';

	static function dispatch($e, $settings = "")
	{

		if (!is_array($settings)) {
			$settings = [$settings];
		}

		$events = Core::getEventsFile();
		
		if (isset($events[$e])) {
			$dispatch = $events[$e];

			if (Helper::regexp('#::#', $dispatch)) {
				list($page,$method) = explode("::", $dispatch);
			
				$path = Core::getEventsPath();
				$class = Core::getClassNamespace($path) . $page;
				$file = $path . $page . FILE_EXT;

				if (Path::exists($file)) {
					include_once Path::get($file);
				}

				if (class_exists($class)) {
					Core::loadPage($path, $page, $method, array(), $settings);
				}
			} 
		}
	}
}