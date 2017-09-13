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

namespace rdks\app\sites\admin\modules\Subscribers\page;

use rdks\core\page\View;
use rdks\core\framework\Role;
use rdks\core\modules\admin\Users\page\Users as UsersPage;
use rdks\app\sites\admin\modules\Subscribers\helper\Subscribers as SubscribersHelper;

class Subscribers extends UsersPage{

	public function __construct(array $settings, View $view){

		$this->_type = Role::TYPE_SUBSCRIBERS;
		$this->_url = SubscribersHelper::URL;
		$this->_title = TEXT_SUBSCRIBERS;

		parent::__construct($settings, $view);

	}

} 