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

use Roducks\Framework\Event;
use Roducks\Page\Service;
use Roducks\Data\User;
use Roducks\Libs\Request\Http;
use Roducks\Libs\Data\Session;
use Roducks\Libs\Utils\Date;
use DB\Models\Users\Users as UsersTable;
use DB\Models\Users\UsersRoles;

class Auth extends Service
{

	protected $_dispatchUrl = true;

	/**
	*
	*/
	private function _login($session, $type, $email, $password)
	{

		$db = $this->db();

		$success = false;
		$message = TEXT_AUTH_FAIL;
		$code = 401;

		$users = UsersRoles::open($db);
		$users->auth($email, $type);

		// Is authentication Ok?
		if ($users->rows()) {

			$auth = $users->fetch();

			// Is user active and nor in trash?
			if ($auth['active'] == 1 && $auth['trash'] == 0) {

				// User account expires?
				if ($auth['expires'] == 1 && Date::getCurrentDate() >= Date::parseDate($auth['expiration_date'])) {

					$user = UsersTable::open($db);
					$user->update($auth['id_user'],['loggedin' => 0, 'active' => 0]);

					$success = false;
					$message = TEXT_AUTH_DISABLED;
					$code = 201;

				} else {

					// Password matches and role is active
					if (User::paywall($auth['password'], $auth['salt'], $password) && $auth['ractive'] == 1) {

						$success = true;
						$message = TEXT_AUTH_OK;
						$code = 200;

						// Unset these values for security
						unset($auth['password']);
						unset($auth['salt']);

						$ip = Http::getIPClient();
						$logInOut = 1;

						// Log out all current users
						if (!empty($auth['location']) && $auth['location'] != $ip && $auth['loggedin'] == 1) {
							$logInOut = 0;
							Session::set(User::SESSION_SECURITY, 1);
						}

						// Store user data in session to recover later
						Session::set($session,$auth);

						Event::dispatch('onEventLogin', [$auth['id_user']]);

						$user = UsersTable::open($db);
						$user->update($auth['id_user'],['loggedin' => $logInOut, 'location' => $ip]);

					} else {
						$success = false;
						$message = TEXT_AUTH_FAIL;
						$code = 202;
					}
				}

			} else {
				$success = false;
				$message = TEXT_AUTH_DISABLED;
				$code = 201;
			}
		}

		$db->close();

		$response =	[
				'success' => $success,
				'message' => $message,
				'code' => $code
		];

		return $response;
	}

	/**
	*	@return json
	*/
	private function _response($session, $type, $return = false)
	{

		$this->post->required();
		$email = $this->post->text('email');
		$password = $this->post->password('password');

		$auth = $this->_login($session, $type, $email, $password);

		if ($return) {
			return $auth;
		} else {
			$this->setStatus($auth);
			parent::output();
		}

	}

	private function _paywall($id)
	{

		$this->post->required();
		$password = $this->post->param('password');
		$db = $this->db();
		$users = UsersTable::open($db);

		if (!$users->paywall($id, $password)) {
			$this->setError(401, TEXT_INCORRECT_PASSWORD);
		}

		parent::output();
	}

	private function _emailExists()
	{
		$email = $this->post->text('email');

		$db = $this->db();
		$users = UsersTable::open($db);

		return $users->results(['email' => $email]);
	}

	/**
	 * @return bool
	 */
	public function valid($type, $email, $password)
 	{
 		$valid = false;
 		$data = [];

 		$users = $this->model('users/users-roles');
 		$users->auth($email, $type);

 		// Is authentication Ok?
 		if ($users->rows()) {

 			$auth = $users->fetch();

 			// Is user active and nor in trash?
 			if ($auth['active'] == 1 && $auth['trash'] == 0) {

 				// Password matches and role is active
 				$valid = (User::paywall($auth['password'], $auth['salt'], $password) && $auth['ractive'] == 1);
 				$data = $auth;
 			}

 		}

 		return ['valid' => $valid, 'data' => $data];
 	}

	public function login($return = false)
	{

		$config = User::login();

		if ($return) {
			return $this->_response($config['session_name'], $config['role_type'], $return);
		}

		$this->_response($config['session_name'], $config['role_type'], $return);
	}

	public function logout($userId)
	{
		Event::dispatch('onEventLogout', [$userId]);
		return $this->model('users/users')->logInOut($userId, 0);
	}

	public function forceToLogout()
	{
		if (User::isLoggedIn()) {
			$id = $this->post->param("id");
			$tx = $this->logout($id);

			if ($tx === false) {
				$this->setError(0, "Something went wrong!");
			}
		}

		parent::output();
	}

}
