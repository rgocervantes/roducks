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

namespace rdks\app\sites\_global\events;

use rdks\core\framework\Event;
use rdks\core\libs\Protocol\Http;
use rdks\core\libs\Utils\Date;
use rdks\app\sites\_global\data\LogData;
use rdks\app\sites\_global\data\UserData;

class Register extends Event{

	public function onLogin($id_user){
		UserData::init($id_user)->addOnce("log_date", Date::getCurrentDate());
		LogData::init($id_user)->add("logIn", ['time' => Date::getCurrentTime(), 'ip' => Http::getIPClient()], true);
	}

	public function onLogout($id_user){
		UserData::init($id_user)->addOnce("log_date", Date::getCurrentDate());
		LogData::init($id_user)->add("logOut", ['time' => Date::getCurrentTime(), 'ip' => Http::getIPClient()], true);
	}

	public function onCreateAccount($id_user, $type){
		// @TODO
	}	

}