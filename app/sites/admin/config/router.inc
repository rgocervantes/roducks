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

use rdks\core\framework\Dispatch;
use rdks\core\framework\Helper;
use rdks\core\framework\Login;
use rdks\core\framework\Role;
use rdks\app\sites\admin\modules\Users\helper\Users;
use rdks\app\sites\admin\modules\Subscribers\helper\Subscribers;
use rdks\app\sites\admin\modules\Roles\helper\Roles;
use rdks\app\sites\admin\modules\Clients\helper\Clients;

$router = [
	/*
	|---------------------------------
	|			  DASHBOARD
	|---------------------------------
	*/
	'/' => [
		'dispatch' => Dispatch::page("dashboard", "index")
	],	
	/*
	|---------------------------------
	|				AUTH
	|---------------------------------
	*/
	'/auth' => [
		'dispatch' => Dispatch::service("auth", "login-admin"),
		'POST' => [
			'email' => Dispatch::PARAM_EMAIL,
			'password' => Dispatch::PARAM_PASSWORD
		]
	],	
	'/login' => [
		'dispatch' => Dispatch::page("login", "login")
	],
	'/logout' => [
		'dispatch' => Dispatch::page("login", "logout")
	],	
	/*
	|---------------------------------
	|				ROLES
	|---------------------------------
	*/
	Role::URL => [
		'dispatch' => Dispatch::page("roles", "index"),
		'path' => [

			'/list' => [
				'dispatch' => Dispatch::page("roles", "listing"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'type' => [Dispatch::PARAM_INTEGER => Dispatch::values(Roles::getIds())]
				]
			],	
			'/add' => [
				'dispatch' => Dispatch::page("roles", "add")
			],
			'/edit/id/(?P<roleId>\d+)' => [
				'dispatch' => Dispatch::page("roles", "edit")
			]

		]
	],
	/*
	|---------------------------------
	|				USERS
	|---------------------------------
	*/
	Users::URL => [
		'dispatch' => Dispatch::page("users", "index"),
		'GET' => [
			'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
			'tree' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])],
			'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
		],		
		'path' => [

			'/search' => [
				'dispatch' => Dispatch::page("users", "index"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
				],		
				'POST' => [
					'email' => Dispatch::OPTIONAL_PARAM_STRING,
					'id_user' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'start_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':required' => Dispatch::page("users", "reset-filter")						
				]
			],	
			'/picture(/id/(?P<userId>\d+))?' => [
				'dispatch' => Dispatch::page("users", "picture")
			],
			'/add' => [
				'dispatch' => Dispatch::page("users", "add")
			],
			'/edit(/id/(?P<userId>\d+))?' => [
				'dispatch' => Dispatch::page("users", "edit")
			],
			'/change-password' => [
				'dispatch' => Dispatch::page("users", "change-password")
			],
			'/reset-password/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("users", "reset-password")
			],
			'/reset-filter' => [
				'dispatch' => Dispatch::page("users", "reset-filter")
			],	
			'/reset-logs/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("users", "reset-logs")
			],
			'/logs' => [
				'dispatch' => Dispatch::page("users", "logs"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				]
			],	
			'/logs/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("users", "logs"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				]
			],			
			'/logs/datepicker/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("users", "logs"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				],				
				'POST' => [
					'start_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':required' => Dispatch::page("users", "reset-filter")
				]
			]
		]
	],
	/*
	|---------------------------------
	|				SUBSCRIBERS
	|---------------------------------
	*/
	Subscribers::URL => [
		'dispatch' => Dispatch::page("subscribers", "index"),
		'GET' => [
			'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
			'tree' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])],
			'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
		],
		'path' => [

			'/search' => [
				'dispatch' => Dispatch::page("subscribers", "index"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
				],		
				'POST' => [
					'email' => Dispatch::OPTIONAL_PARAM_STRING,
					'id_user' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'start_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':required' => Dispatch::page("subscribers", "reset-filter")
				]
			],		
			'/picture/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("subscribers", "picture")
			],
			'/add' => [
				'dispatch' => Dispatch::page("subscribers", "add")
			],
			'/edit/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("subscribers", "edit")
			],
			'/change-password' => [
				'dispatch' => Dispatch::page("subscribers", "change-password")
			],
			'/reset-password/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("subscribers", "reset-password")
			],
			'/reset-filter' => [
				'dispatch' => Dispatch::page("subscribers", "reset-filter")
			],	
			'/reset-logs/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("subscribers", "reset-logs")
			],			
			'/logs' => [
				'dispatch' => Dispatch::page("subscribers", "logs"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				]
			],	
			'/logs/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("subscribers", "logs")
			],
			'/logs/datepicker/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("subscribers", "logs"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				],				
				'POST' => [
					'start_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':required' => Dispatch::page("subscribers", "reset-filter")
				]
			]
		]
	],
	/*
	|---------------------------------
	|				SUBSCRIBERS
	|---------------------------------
	*/
	Clients::URL => [
		'dispatch' => Dispatch::page("clients", "index"),
		'GET' => [
			'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
			'tree' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])],
			'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
		],
		'path' => [

			'/search' => [
				'dispatch' => Dispatch::page("clients", "index"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
				],		
				'POST' => [
					'email' => Dispatch::OPTIONAL_PARAM_STRING,
					'id_user' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'start_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':required' => Dispatch::page("clients", "reset-filter")		
				]
			],		
			'/picture/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("clients", "picture")
			],
			'/add' => [
				'dispatch' => Dispatch::page("clients", "add")
			],
			'/edit/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("clients", "edit")
			],
			'/change-password' => [
				'dispatch' => Dispatch::page("clients", "change-password")
			],
			'/reset-password/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("clients", "reset-password")
			],
			'/reset-filter' => [
				'dispatch' => Dispatch::page("clients", "reset-filter")
			],	
			'/reset-logs/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("clients", "reset-logs")
			],			
			'/logs' => [
				'dispatch' => Dispatch::page("clients", "logs"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				]
			],	
			'/logs/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("clients", "logs")
			],
			'/logs/datepicker/id/(?P<userId>\d+)' => [
				'dispatch' => Dispatch::page("clients", "logs"),
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				],				
				'POST' => [
					'start_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':required' => Dispatch::page("clients", "reset-filter")
				]
			]
		]
	],

];

