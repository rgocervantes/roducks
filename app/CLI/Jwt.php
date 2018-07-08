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

namespace App\CLI;

use Roducks\Framework\CLI;
use Crypt\Hash;
use Lib\File;

class Jwt extends CLI
{
	public function secret()
	{

$file = 'jwt.local.inc';
$hash = Hash::getToken();
$config = <<< EOT
<?php

return [
	'secret' => '{$hash}'
];
EOT;

		File::create(DIR_APP_CONFIG, $file, $config);

		$this->success(DIR_APP_CONFIG . "{$file} was created successfully!");

		parent::output();
	}

}