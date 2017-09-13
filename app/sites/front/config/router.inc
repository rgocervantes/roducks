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
use rdks\app\sites\front\modules\Account\helper\Account;

$router = [
	/*
	|---------------------------------
	|				FRONT
	|---------------------------------
	*/
	'/' => [
		'dispatch' => Dispatch::page("home","index")
	],
	'/contact-us' => [
		'dispatch' => Dispatch::page("home","contact-us"),
		'path' => [
			'/submit' => [
				'dispatch' => Dispatch::json("home","contact-us-submit")
			]
		]
	],	
	/*
	|---------------------------------
	|				AUTH
	|---------------------------------
	*/
	'/auth' => [
		'dispatch' => Dispatch::service("auth", "login-subscriber"),
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
	|				ACOUNT
	|---------------------------------
	*/
	Account::URL => [
		'dispatch' => Dispatch::page("account","index"),
		'path' => [

			'/edit' => [
				'dispatch' => Dispatch::page("account","edit")
			],
			'/picture' => [
				'dispatch' => Dispatch::page("account","picture")
			],		
			'/change-password' => [
				'dispatch' => Dispatch::page("account","change-password")
			],
			'/create' => [
				'dispatch' => Dispatch::page("home","create-account")
			],				
			'/insert' => [
				'dispatch' => Dispatch::json("home","create-account")
			],	
			'/email' => [
				'dispatch' => Dispatch::service("account","email-exists")
			],	
			'/check' => [
				'dispatch' => Dispatch::service("account","account-exists")
			],		
			'/reset-password/token/[a-z0-9]+' => [
				'dispatch' => Dispatch::page("home","reset-password")
			],			
			'/forgotten-password' => [
				'dispatch' => Dispatch::page("home","forgotten-password")
			],	
			'/restore-password' => [
				'dispatch' => Dispatch::json("home","restore-password")
			],	
			'/recover-password' => [
				'dispatch' => Dispatch::json("home","recover-password")
			]

		]
	]

];

