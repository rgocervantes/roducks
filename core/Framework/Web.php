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

require "Bootstrap.php";

use Roducks\Framework\Core;
use Roducks\Framework\Dispatch;

/*
|--------------------------------|
|		       LANGUAGE FILE
|--------------------------------|
*/
Core::loadAppLanguages();

/*
|--------------------------------|
|		      GET ENVIRONMENT
|--------------------------------|
*/
$environment = Core::getEnvironment($appConfigData);

/*
|--------------------------------|
|			       RUN APP
|--------------------------------|
*/
require "Run" . FILE_EXT;

/*
|--------------------------------|
|			       CHECK APP
|--------------------------------|
*/
Core::requireConfig($appConfig);
Core::checkApp($environment);

/*
|--------------------------------|
|		          INSTALL
|--------------------------------|
*/
Core::install();

/*
|--------------------------------|
|		    SYSTEM REQUIREMENT
|--------------------------------|
*/
Core::requirements();

/*
|--------------------------------|
|		         DISPATCH
|--------------------------------|
*/
Dispatch::init();
