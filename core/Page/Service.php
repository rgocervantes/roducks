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

class Service extends JSON
{

	protected $_pageType = 'SERVICE';
	var $rdks = 1;

	static function init($site = "")
	{
		$page = get_called_class();
		return Core::loadService($page, $site);
	}

	public function _disableServiceUrl($method)
	{
		$error = "Methods that starts with \"<b style=\"color:#e69d97\">get</b>\" aren't allowed to be dispatched because they're supposed to \"<b style=\"color:#e69d97\">return</b>\" data.";
		Error::methodNotFound("Can't dispatch URL", __LINE__, __FILE__, $this->pageObj->fileName, $this->pageObj->className, $method, $this->getParentClassName(),$error);
	}

}
