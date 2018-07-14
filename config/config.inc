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

return [
//-------------------------------------------------------------------
//  Your Domain Name (WITHOUT www or any subdomain)
//-------------------------------------------------------------------
	'domain_name' 					=> '', // Example: yoursite.com
//-------------------------------------------------------------------
//  Timezone @url http://php.net/manual/es/timezones.america.php
//-------------------------------------------------------------------
	'default_timezone' 			=> 'America/New_York',
//-------------------------------------------------------------------
//  Default Title for all pages
//-------------------------------------------------------------------
	'page_title' 					=> 'Roducks',
//-------------------------------------------------------------------
//   Email reply
//-------------------------------------------------------------------
	'email_from'					=> 'noreply@yoursite.com',
//-------------------------------------------------------------------
//   Email sender
//-------------------------------------------------------------------
	'email_to' 						=> 'contact@yoursite.com',
//-------------------------------------------------------------------
//  Logo
//-------------------------------------------------------------------
	'logo_image' 					=> 'roducks_logo.png',
//-------------------------------------------------------------------
//  Find Request URL in Database
//-------------------------------------------------------------------
	'find_url_in_db' 				=> false,
//-------------------------------------------------------------------
//  Allow Subscribers to register
//-------------------------------------------------------------------
	'allow_subscribers_register' 	=> true,
//-------------------------------------------------------------------
//  Subscribers expires in ? days
//-------------------------------------------------------------------
	'subscribers_expire'			=> false,
	'subscribers_expire_time'		=> 'MONTHS', // DAYS | MONTHS
	'subscribers_expire_in' 		=> 2,
//-------------------------------------------------------------------
//  Is your site multilanguage?
//-------------------------------------------------------------------
	'multilanguage' 				=> true,
//-------------------------------------------------------------------
//  Allows user's browser language as default
//-------------------------------------------------------------------
	'browser_language' 				=> true,
//-------------------------------------------------------------------
//  Default language ISO
//-------------------------------------------------------------------
	'default_language' 				=> 'en' // ISO: en | es
];
