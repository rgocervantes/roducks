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

use Roducks\Framework\Router;
use Roducks\Framework\Dispatch;
use App\Sites\Front\Modules\Account\Helper\Account;

Router::init(function() {

	/*
	|---------------------------------
	|				FRONT
	|---------------------------------
	*/
	Router::get('/', Dispatch::page("home","index"));
	Router::get('/profile', Dispatch::page('home', 'profile'));

	Router::prefix('/contact-us', function($path) {

		Router::get([$path => '/'], Dispatch::page("home","contact-us"));
		Router::post([$path => '/submit'], Dispatch::json("home","contact-us-submit"));

	});
	/*
	|---------------------------------
	|				AUTH
	|---------------------------------
	*/
	Router::post('/auth', Dispatch::service("auth", "login"), function() {
		return [
			'email' => Dispatch::PARAM_EMAIL,
			'password' => Dispatch::PARAM_PASSWORD
		];
	});

	Router::get('/login', Dispatch::page("login", "form"));
	Router::get('/logout', Dispatch::page("login", "logout"));

	/*
	|---------------------------------
	|				ACOUNT
	|---------------------------------
	*/
	Router::prefix(Account::URL, function($path) {

		Router::get([$path => '/'], Dispatch::page("account","index"));
		Router::get([$path => '/edit'], Dispatch::page("account","edit"));
		Router::get([$path => '/picture'], Dispatch::page("account","picture"));
		Router::get([$path => '/change-password'], Dispatch::page("account","change-password"));
		Router::get([$path => '/create'], Dispatch::page("home","create-account"));
		Router::post([$path => '/insert'], Dispatch::json("home","create-account"));
		Router::post([$path => '/email'], Dispatch::service("account","email-exists"));
		Router::post([$path => '/check'], Dispatch::service("account","account-exists"));
		Router::get([$path => '/reset-password/token/[a-z0-9]+'], Dispatch::page("home","reset-password"));
		Router::get([$path => '/forgotten-password'], Dispatch::page("home","forgotten-password"));
		Router::post([$path => '/restore-password'], Dispatch::json("home","restore-password"));
		Router::post([$path => '/recover-password'], Dispatch::json("home","recover-password"));

	});

});
