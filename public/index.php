<?php
/**
 *
 *	@package Roducks Framework
 *	@version 1.0
 *	@copyright Possible Development
 *	@author Rod	<rodrigo@possible-development.com>
 *
 *
 * Copyright (C) 2017  Rod
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

namespace Roducks;

if(isset($_GET['uri']) && $_GET['uri'] == 'security'){
	header("HTTP/1.1 403 Forbidden Request");
	die("<h1>Forbidden Request.</h1>");
}

# Run App
$app = __DIR__ . "/../core/Framework/Web.php";

if(file_exists($app)){
	require_once $app;
} else {
	header("HTTP/1.1 404 Not Found");
	die("<h1>Page Not Found.</h1>");
}