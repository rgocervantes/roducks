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
use Roducks\Framework\Helper;
use Roducks\Framework\Role;
use App\Sites\Admin\Modules\Users\Helper\Users;
use App\Sites\Admin\Modules\Subscribers\Helper\Subscribers;
use App\Sites\Admin\Modules\Roles\Helper\Roles;
use App\Sites\Admin\Modules\Clients\Helper\Clients;

Router::init(function() {

	/*
	|---------------------------------
	|				DASHBOARD
	|---------------------------------
	*/
	Router::get('/', Dispatch::page("dashboard", "index"));

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
	|				ROLES
	|---------------------------------
	*/
	Router::prefix(Role::URL, function($path) {

		Router::get([$path => '/'], Dispatch::page("roles", "index"));
		Router::get([$path => '/list'], Dispatch::page("roles", "listing"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
				'type' => [Dispatch::PARAM_INTEGER => Dispatch::values(Roles::getIds())]
			];
		});
		Router::get([$path => '/add'], Dispatch::page("roles", "add"));
		Router::get([$path => '/edit/id/(?P<roleId>\d+)'], Dispatch::page("roles", "edit"));
	});

	/*
	|---------------------------------
	|				USERS
	|---------------------------------
	*/
	Router::prefix(Users::URL, function($path) {

		Router::get([$path => '/'], Dispatch::page("users", "index"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
				'tree' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])],
				'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
			];
		});
		Router::get_post([$path => '/search'], Dispatch::page("users", "index"), function() {
			return [
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
				],
				'POST' => [
					'email' => Dispatch::OPTIONAL_PARAM_STRING,
					'id_user' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'start_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':empty' => Dispatch::page("users", "reset-filter")
				]
			];
		});

		Router::get([$path => '/picture(/id/(?P<userId>\d+))?'], Dispatch::page("users", "picture"));
		Router::get([$path => '/add'], Dispatch::page("users", "add"));
		Router::get([$path => '/edit(/id/(?P<userId>\d+))?'], Dispatch::page("users", "edit"));
		Router::get([$path => '/change-password'], Dispatch::page("users", "change-password"));
		Router::get([$path => '/reset-password/id/(?P<userId>\d+)'], Dispatch::page("users", "reset-password"));
		Router::get([$path => '/reset-filter'], Dispatch::page("users", "reset-filter"));
		Router::get([$path => '/reset-logs/id/(?P<userId>\d+)'], Dispatch::page("users", "reset-logs"));

		Router::get([$path => '/logs'], Dispatch::page("users", "logs"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER
			];
		});

		Router::get([$path => '/logs/id/(?P<userId>\d+)'], Dispatch::page("users", "logs"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER
			];
		});

		Router::get_post([$path => '/logs/datepicker/id/(?P<userId>\d+)'], Dispatch::page("users", "logs"), function() {
			return [
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				],
				'POST' => [
					'start_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':empty' => Dispatch::page("users", "reset-filter")
				]
			];
		});

	});

	/*
	|---------------------------------
	|				SUBSCRIBERS
	|---------------------------------
	*/
	Router::prefix(Subscribers::URL, function($path) {

		Router::get([$path => '/'], Dispatch::page("subscribers", "index"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
				'tree' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])],
				'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
			];
		});
		Router::get_post([$path => '/search'], Dispatch::page("subscribers", "index"), function() {
			return [
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
				],
				'POST' => [
					'email' => Dispatch::OPTIONAL_PARAM_STRING,
					'id_user' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'start_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':empty' => Dispatch::page("subscribers", "reset-filter")
				]
			];
		});

		Router::get([$path => '/picture(/id/(?P<userId>\d+))?'], Dispatch::page("subscribers", "picture"));
		Router::get([$path => '/add'], Dispatch::page("subscribers", "add"));
		Router::get([$path => '/edit(/id/(?P<userId>\d+))?'], Dispatch::page("subscribers", "edit"));
		Router::get([$path => '/change-password'], Dispatch::page("subscribers", "change-password"));
		Router::get([$path => '/reset-password/id/(?P<userId>\d+)'], Dispatch::page("subscribers", "reset-password"));
		Router::get([$path => '/reset-filter'], Dispatch::page("subscribers", "reset-filter"));
		Router::get([$path => '/reset-logs/id/(?P<userId>\d+)'], Dispatch::page("subscribers", "reset-logs"));

		Router::get([$path => '/logs'], Dispatch::page("subscribers", "logs"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER
			];
		});

		Router::get([$path => '/logs/id/(?P<userId>\d+)'], Dispatch::page("subscribers", "logs"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER
			];
		});

		Router::get_post([$path => '/logs/datepicker/id/(?P<userId>\d+)'], Dispatch::page("subscribers", "logs"), function() {
			return [
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				],
				'POST' => [
					'start_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':empty' => Dispatch::page("subscribers", "reset-filter")
				]
			];
		});

	});

	/*
	|---------------------------------
	|				CLIENTS
	|---------------------------------
	*/
	Router::prefix(Clients::URL, function($path) {

		Router::get([$path => '/'], Dispatch::page("clients", "index"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
				'tree' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])],
				'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
			];
		});
		Router::get_post([$path => '/search'], Dispatch::page("clients", "index"), function() {
			return [
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'trash' => [Dispatch::OPTIONAL_PARAM_INTEGER => Dispatch::values([1])]
				],
				'POST' => [
					'email' => Dispatch::OPTIONAL_PARAM_STRING,
					'id_user' => Dispatch::OPTIONAL_PARAM_INTEGER,
					'start_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::OPTIONAL_PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':empty' => Dispatch::page("clients", "reset-filter")
				]
			];
		});

		Router::get([$path => '/picture(/id/(?P<userId>\d+))?'], Dispatch::page("clients", "picture"));
		Router::get([$path => '/add'], Dispatch::page("clients", "add"));
		Router::get([$path => '/edit(/id/(?P<userId>\d+))?'], Dispatch::page("clients", "edit"));
		Router::get([$path => '/change-password'], Dispatch::page("clients", "change-password"));
		Router::get([$path => '/reset-password/id/(?P<userId>\d+)'], Dispatch::page("clients", "reset-password"));
		Router::get([$path => '/reset-filter'], Dispatch::page("clients", "reset-filter"));
		Router::get([$path => '/reset-logs/id/(?P<userId>\d+)'], Dispatch::page("clients", "reset-logs"));

		Router::get([$path => '/logs'], Dispatch::page("clients", "logs"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER
			];
		});

		Router::get([$path => '/logs/id/(?P<userId>\d+)'], Dispatch::page("clients", "logs"), function() {
			return [
				'page' => Dispatch::OPTIONAL_PARAM_INTEGER
			];
		});

		Router::get_post([$path => '/logs/datepicker/id/(?P<userId>\d+)'], Dispatch::page("clients", "logs"), function() {
			return [
				'GET' => [
					'page' => Dispatch::OPTIONAL_PARAM_INTEGER
				],
				'POST' => [
					'start_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					'end_date' => [Dispatch::PARAM_STRING => Helper::VALID_DATE_YYYY_MM_DD],
					':empty' => Dispatch::page("clients", "reset-filter")
				]
			];
		});

	});

	/*
	|---------------------------------
	|				CONTENT
	|---------------------------------
	*/
	Router::get('/layout.html', Dispatch::page('content'), function () {
		return [
			'preview' => Dispatch::OPTIONAL_PARAM_INTEGER
		];
	});

	Router::get('/content/new/{name:str}', Dispatch::page("content", "add"));

});
