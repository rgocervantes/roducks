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

return [
	[
		'text' => TEXT_USERS, 
		'link' => Users::URL,
		'access' => ["users", "view"],
		'children' => [
			[
				'link' => Users::URL . "/add",
				'text' => TEXT_CREATE . " " .TEXT_NEW,
				'access' => ["users", "create"]
			]		
		]
	],
	[
		'text' => TEXT_SUBSCRIBERS, 
		'link' => Subscribers::URL,
		'access' => ["subscribers", "view"],
		'children' => [
			[
				'link' => Subscribers::URL . "/add",
				'text' => TEXT_CREATE . " " .TEXT_NEW,
				'access' => ["subscribers", "create"]
			]
		]
	],
	[
		'text' => TEXT_CLIENTS, 
		'link' => Clients::URL, 
		'access' => ["clients", "view"],
		'children' => [
			[
				'link' => Clients::URL . "/add", 
				'text' => TEXT_CREATE . " " .TEXT_NEW,
				'access' => ["clients", "create"]
			]
		]
	],	
];

