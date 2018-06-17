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
 *	php roducks cache:clean --pro
 */

namespace App\CLI;

use Roducks\Framework\CLI;

class Cache extends CLI
{

	public function clean($item = "all")
	{

		try {
			
			if ($item != "all") {
				$this->cache('init')->delete($iem);
			} else {
				$this->cache('clean');
			}

			$this->success("Cache memory was cleaned.");

		} catch (\Exception $e) {
			$this->error($e->getMessage());			
		}

		parent::output();
	}

}