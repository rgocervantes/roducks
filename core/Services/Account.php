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

namespace Roducks\Services;

use Roducks\Page\Service;
use DB\Models\Users\Users as UsersTable;

class Account extends Service
{

	protected $_dispatchUrl = true;

	private function _emailExists()
	{
		$email = $this->post->text('email');

		$db = $this->db();
		$users = UsersTable::open($db);

		return $users->results(['email' => $email]);		
	}

	public function emailExists()
	{

		$rows = $this->_emailExists();

		if ($rows) {
			$this->setError(0,"Email already exists.");
		}

		parent::output();

	}

	public function accountExists()
	{
		$rows = $this->_emailExists();

		if (!$rows) {
			$this->setError(0,"Invalid email.");
		}

		parent::output();	
	}

} 