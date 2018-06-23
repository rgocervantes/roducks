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
 *	php roducks user:create --pro dummy@yoursite.com duke017 --super-admin
 *	php roducks user:create --pro dummy@yoursite.com duke017 gender=female firstname=Rod lastname=Cervantes+TC
 *	php roducks user:reset --pro dummy@yoursite.com duke017
 *	php roducks user:who --pro id=1
 *	php roducks user:who --pro email=dummy@yoursite.com
 */

namespace App\CLI;

use Roducks\Framework\CLI;
use Roducks\Framework\Helper;
use Roducks\Libs\Utils\Date;
use App\Models\Users\Users as UsersTable;

class User extends CLI
{

	public function create($email = "", $password = "")
	{

		$gender = $this->getParam('gender', "male");

		if (!empty($email) && !empty($password)) {

			if (strlen($password) >= 7) {

				$db = $this->db();
				$user = UsersTable::open($db);
				$total = $user->getTableTotalRows();
				$first_name = $this->getParam('firstname', "Super");
				$last_name = $this->getParam('lastname', "Admin+Master");

				if ($total > 0 && $this->getFlag('--super-admin')) {

					$flag = ($this->getFlag('--dev')) ? '--dev' : '--pro';
					$this->warning("[*]Super Admin was already created.");
					$this->warning("[x]");
					$this->warning("[*]If you want to reset password, run this command:");
					$this->warning("[x]");
					$this->warning("[x]php roducks user:reset {$flag} <EMAIL> <NEW_PASSWORD>");

				} else {

					$data = [
						'id_user_tree' => '0',
						'id_role' => 1,
						'active' => 1,
						'email' => $email,
						'password' => $password,
						'first_name' => $first_name,
						'last_name' => $last_name,
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

			if ($user->foundRow()) {
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
		$email = $this->getParam('email', null);
		$db = $this->db();
		$usersTable = UsersTable::open($db);

		if (!is_null($email)) {
			$usersTable->filter(['email' => $email]);
			$user = $usersTable->fetch();
			$error = "Invalid email: '{$email}'";
		} else {
			$user = $usersTable->row($id);
			$error = "Invalid user ID -> {$id}";
		}

		if ($usersTable->foundRow()) {
			$this->info( $user['first_name'] . " " . $user['last_name'] );
			$this->info( $user['email'] );
		} else {
			$this->error($error);
		}

		parent::output();

	}

}