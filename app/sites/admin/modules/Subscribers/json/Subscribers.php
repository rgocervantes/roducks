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

namespace rdks\app\sites\admin\modules\Subscribers\json;

use rdks\core\page\View;
use rdks\core\framework\Role;
use rdks\core\modules\admin\Users\json\Users as UsersJSON;
use rdks\app\sites\admin\modules\Subscribers\helper\Subscribers as SubscribersHelper;

class Subscribers extends UsersJSON{
	
	protected $_dispatchUrl = true;
	
	public function __construct(array $settings){
		
		$this->_type = Role::TYPE_SUBSCRIBERS;
		$this->_url = SubscribersHelper::URL;

		parent::__construct($settings);

	}

} 