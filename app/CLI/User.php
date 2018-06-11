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
 *	php roducks user:create --pro dummy@yoursite.com duke017
 *	php roducks user:create --pro dummy@yoursite.com duke017 gender=female
 *	php roducks user:reset --pro dummy@yoursite.com duke017
 *	php roducks user:who --pro id=1
 */

namespace App\CLI;

use Roducks\Framework\CLI;
use Roducks\Framework\Helper;
use Roducks\Libs\Utils\Date;
use App\Models\Users\Users as UsersTable;

class User extends CLI
{

	protected $_params = [
		'email',
		'password'
	];

	public function create()
	{
		
		$email = $this->getParam('email', "");
		$password = $this->getParam('password', "");
		$gender = $this->getParam('gender', "male");

		if (!empty($email) && !empty($password)) {

			if (strlen($password) >= 7) {

				$db = $this->db();
				$user = UsersTable::open($db);
				$total = $user->getTableTotalRows();

				if ($total == 0) {

					$data = [
						'id_user_tree' => '0',
						'id_role' => 1,
						'active' => 1,
						'email' => $email,
						'password' => $password,
						'first_name' => "Super",
						'last_name' => "Admin Master",
						'gender' => $gender,
						'picture' => Helper::getUserIcon($gender),
						'created_at' => UsersTable::now(),
						'updated_at' => UsersTable::now()		
					];

					$tx = $user->create($data);

					if ($tx === false) {
						$this->error("User could not be created.");
					} else {
						$this->success("User was created successfully!");
					}

				} else {
					$this->warning("Super Admin was already created.");
				}

			} else {
				$this->error("Password length must be at least 7 chars.");
			}

		} else {
			$this->warning("Email and Password are required.");
		}

		parent::output();

	}

	public function reset()
	{

		$email = $this->getParam('email', "");
		$password = $this->getParam('password', "");

		if (!empty($email) && !empty($password)) {

			$db = $this->db();
			$user = UsersTable::open($db);
			$user->filter(['email' => $email]);

			if ($user->rows()) {
				$row = $user->fetch();

				$user->changePassword($row['id_user'], $password);
				$this->success("User was reset successfully!");
			} else {
				$this->error("User does not exist.");
			}

		} else {
			$this->warning("Email and Password are required.");
		}

		parent::output();

	}

	public function who()
	{

		$id = $this->getParam('id', 1);
		$db = $this->db();
		$user = UsersTable::open($db)->getRow($id);

		if ($user->foundRow()) {
			$this->result( $user->getFirstName() . " " . $user->getLastName() );
			$this->result( $user->getEmail() );
		} else {
			$this->error("Invalid user ID.");
		}

		parent::output();

	}

}