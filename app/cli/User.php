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
 *	time php roducks cmd=user:create env=pro email=dummy@yoursite.com password=duke017
 *	time php roducks cmd=user:reset env=pro email=dummy@yoursite.com password=duke017
 *	time php roducks cmd=user:who env=pro id=1
 */

namespace rdks\app\cli;

use rdks\core\framework\Cli;
use rdks\core\framework\Helper;
use rdks\core\libs\Utils\Date;
use rdks\app\models\Users\Users as UsersTable;

class User extends Cli {

	public function create(){
		
		$email = $this->getParam('email', "");
		$password = $this->getParam('password', "");
		$gender = $this->getParam('gender', "male");

		if(!empty($email) && !empty($password)){

			if(strlen($password) >= 7){

				$db = $this->db();
				$user = UsersTable::open($db);
				$total = $user->getTableTotalRows();

				if($total == 0){

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
						'created_date' => Date::getCurrentDateTime(),
						'updated_date' => Date::getCurrentDateTime()			
					];

					$tx = $user->create($data);

					if($tx === false){
						$this->setError("User could not be created.");
					} else {
						$this->setResult("User was created successfully!");
					}

				} else {
					$this->setWarning("Super Admin was already created.");
				}

			} else {
				$this->setError("Password length must be at least 7 chars.");
			}

		} else {
			$this->setWarning("Email and Password are required.");
		}

		parent::output();

	}

	public function reset(){

		$email = $this->getParam('email', "");
		$password = $this->getParam('password', "");

		if(!empty($email) && !empty($password)){

			$db = $this->db();
			$user = UsersTable::open($db);
			$user->filter(['email' => $email]);

			if($user->rows()){
				$row = $user->fetch();

				$user->changePassword($row['id_user'], $password);
				$this->setResult("User was reset successfully!");
			} else {
				$this->setError("User does not exist.");
			}

		} else {
			$this->setWarning("Email and Password are required.");
		}

		parent::output();

	}

	public function who(){
		$id = $this->getParam('id', 1);
		$db = $this->db();
		$user = UsersTable::open($db)->load($id);

		if($user->rows()){
			$this->setResult( $user->getFirstName() . " " . $user->getLastName() );
			$this->setResult( $user->getEmail() );
		} else {
			$this->setError("Invalid user ID.");
		}

		parent::output();

	}

}