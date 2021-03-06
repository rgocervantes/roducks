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
 *	php roducks user:create --pro dummy@yoursite.com duke017 gender=male firstname=Duke lastname=Cerva+Toledo
 *	php roducks user:reset --pro dummy@yoursite.com duke017
 *	php roducks user:who --pro id=1
 *	php roducks user:who --pro email=dummy@yoursite.com
 */

namespace Roducks\CLI;

use Roducks\Framework\CLI;
use Helper;
use Date;
use Query;

class User extends CLI
{

	public function create($email = "", $password = "")
	{

		if (!empty($email) && !empty($password)) {

			$id_role = 1; // super-admin
			$ids_roles = [];
			$user = $this->model('users/users');

			if ($this->getFlag('--super-admin') || $user->exists($email)) {
				array_push($ids_roles, $id_role);
			} else {

				$roles = $this->model('users/roles')->getCatalog();

				$this->dialogInfo('Roles');

				while ($role = $roles->fetch()) {
					array_push($ids_roles, $role['id_role']);
				 	$this->info("[x]( {$role['id_role']} ) {$role['name']}");
				}

				parent::output();

				$this->prompt("Type an option:");

				$id_role = $this->getAnswer();
			}

			if (in_array($id_role, $ids_roles)) {

				if (strlen($password) >= 7) {

					$total = $user->getTableTotalRows();
					$first_name = $this->getParam('firstname', "Super");
					$last_name = $this->getParam('lastname', "Admin+Master");

					if ($total > 0 && $this->getFlag('--super-admin')) {

						$flag = ($this->getFlag('--dev')) ? '--dev' : '--pro';
						$this->warning("[*]Super Admin was already created.");
						$this->warning("[x]");
						$this->warning("[*]If you want to reset password, run this command:");
						$this->warning("[x]");
						$this->warning("[x]php roducks user:reset {$flag} [EMAIL] [NEW_PASSWORD]");

					} else {

						$gender = $this->getParam('gender', "male");

						$data = [
							'id_user_tree' => '0',
							'id_role' => $id_role,
							'email' => $email,
							'password' => $password,
							'first_name' => $first_name,
							'last_name' => $last_name,
							'gender' => $gender,
							'picture' => Helper::getUserIcon($gender),
						];

						$tx = $user->create($data);

						if ($tx === false) {
							$this->error("User could not be created.");
							$this->error("[x]");
							$this->error("[*]It is very possible that it already exists.");
						} else {
							$this->success("User was created successfully!");
						}

					}

				} else {
					$this->error("Password length must be at least 7 chars.");
				}

			} else {
				$this->error("Invalid Role ID, Try again.");
			}

		} else {
			$this->warning("Email and Password are required.");
		}

		parent::output();

	}

	public function reset($email = "", $password = "")
	{

		if (!empty($email) && !empty($password)) {

			$user = $this->model('users/users');
			$user->filter(['email' => $email]);

			if ($user->foundRow()) {
				$row = $user->fetch();

				$tx = $user->changePassword($row['id_user'], $password);

				if ($tx) {
					$user->logInOut($row['id_user'], 0);
					$this->success("User was reset successfully!");
				} else {
					$this->error("It couldn't be reseted.");
				}

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

		$usersTable = $this->model('users/users');

		if (!is_null($email)) {
			$usersTable->filter(['email' => $email]);
			$user = $usersTable->fetch();
			$error = "Invalid email: '{$email}'";
		} else {
			$user = $usersTable->row($id);
			$error = "Invalid user ID -> {$id}";
		}

		if ($usersTable->foundRow()) {

			if ($user['id_user'] == 1) {
				$this->info("[*] Super Admin");
				$this->info("[x]");
			}

			$this->info( Query::concatValues([$user['first_name'],$user['last_name']]) );
			$this->info( $user['email'] );
		} else {
			$this->error($error);
		}

		parent::output();

	}

}
