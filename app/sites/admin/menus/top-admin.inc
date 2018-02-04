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

use App\Sites\Admin\Modules\Users\Helper\Users;
use App\Sites\Admin\Modules\Subscribers\Helper\Subscribers;
use App\Sites\Admin\Modules\Clients\Helper\Clients;
use App\Sites\Admin\Modules\Roles\Helper\Roles as RolesHelper;
use Roducks\Framework\Role;

return [
	[
		'link' => Users::URL, 
		'text' => TEXT_USERS, 
		'access' => ["users","view"],
		'icon' => RolesHelper::getIcon(Role::TYPE_USERS)
	],
	[
		'link' => Subscribers::URL, 
		'text' => TEXT_SUBSCRIBERS, 
		'access' => "subscribers",
		'icon' => RolesHelper::getIcon(Role::TYPE_SUBSCRIBERS)
	],
	[
		'link' => Clients::URL, 
		'text' => TEXT_CLIENTS, 
		'access' => "clients",
		'icon' => RolesHelper::getIcon(Role::TYPE_CLIENTS)
	],	
	[
		'link' => Role::URL, 
		'text' => "Roles", 
		'access' => "roles",
		'icon' => "tags"		
	],	
	[
		'separator' => "divider"
	],
	[
		'link' => "/fake", 
		'text' => "Page Not Found", 
		'access' => "all",
		'icon' => "alert"
	],		
					
];

