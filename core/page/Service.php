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

namespace rdks\core\page;

use rdks\core\framework\Helper;
use rdks\core\framework\Core;

class Service extends JSON {

	static function init(){
		
		$page = get_called_class();
		$page = Helper::getClassName($page);
		$page = DIR_SERVICES . Helper::getCamelName($page);
				
		$servicePath = Core::getServicesPath($page);
		$pagePath = $servicePath['path'];

		return Core::loadPage($pagePath, $page, "", array(),array(), true);

	}

}