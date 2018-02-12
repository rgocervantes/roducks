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

namespace Roducks\Api;

use Roducks\Framework\Login;
use Roducks\Framework\Role;
use App\Models\Users\UsersRoles;

class Auth extends Api
{

	/**
	*	Authentication
	*/
	private function _authenticate($type, $email, $password)
	{

		$db = $this->db();
		$valid = false;
		$data = [];

		$users = UsersRoles::open($db);
		$users->auth($email, $type);

		// Is authentication Ok?
		if($users->rows()){

			$auth = $users->fetch();

			// Is user active and nor in trash?
			if($auth['active'] == 1 && $auth['trash'] == 0) {

				// Password matches and role is active
				$valid = (Login::paywall($auth['password'], $auth['salt'], $password) && $auth['ractive'] == 1);
				$data = $auth;
			}

		}

		$db->close();

		return ['valid' => $valid, 'data' => $data];
	}

	public function credentials()
	{
		$email = $this->post->param('email');
		$password = $this->post->param('password');
		$auth = $this->_authenticate(Role::TYPE_USERS, $email, $password);

		if($auth['valid']){

			$data = $auth['data'];

			$token = [
				'id_user' => $data['id_user'],
				'email' => $data['email'],
				'first_name' => $data['first_name'],
				'last_name' => $data['last_name'],
				'gender' => $data['gender'],
				'picture' => \Roducks\Framework\Path::getPublicUploadedUsers($data['picture']),
			];

			$jwt = $this->generateToken(static::JWT_EXPIRATION, $token); // 60 seconds
			$this->data("access_token", $jwt);
			$this->data("type", "Bearer");
			$this->data("expires_in", static::JWT_EXPIRATION);

		} else {
			$this->setError(401, "Authentication failed.");
		}

		$this->output();
	}

}