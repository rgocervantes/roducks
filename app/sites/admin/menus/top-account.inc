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

use rdks\app\sites\admin\modules\Users\helper\Users;

$menu = [
	[
		'link' => Users::URL . "/edit", 
		'text' => TEXT_EDIT_ACCOUNT, 
		'access' => "edit",
		'icon' => "pencil"
	],
	[
		'link' => Users::URL . "/picture", 
		'text' => TEXT_PROFILE_PICTURE, 
		'access' => "picture",
		'icon' => "picture"
	],
	[
		'link' => Users::URL . "/logs", 
		'text' => "Logs", 
		'access' => "logs",
		'icon' => "off"
	],
	[
		'link' => Users::URL . "/change-password", 
		'text' => TEXT_CHANGE_PASSWORD, 
		'access' => "password",
		'icon' => "lock"
	],		
	[
		'separator' => "divider"
	],	
	[
		'link' => "/logout", 
		'text' => TEXT_LOGOUT, 
		'access' => "all",
		'icon' => "log-out"
	],						
];

