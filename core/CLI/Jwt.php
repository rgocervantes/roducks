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
 *	-----------------
 *	COMMAND LINE
 *	-----------------
 *	php roducks jwt:secret
 */

namespace Roducks\CLI;

use Roducks\Framework\CLI;
use Roducks\Framework\Config;
use Crypt\Hash;

class Jwt extends CLI
{
	public function secret()
	{

		$file = 'jwt.local';
		$ext = FILE_YML;
		$hash = Hash::getToken();
		$secret = substr($hash, 0, 32);

		Config::set($file, ['secret' => $secret]);

		$this->success("File: " . DIR_APP_CONFIG . "{$file}{$ext} was created successfully!");

		parent::output();
	}

}
