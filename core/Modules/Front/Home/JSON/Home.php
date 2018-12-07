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

namespace Roducks\Modules\Front\Home\JSON;

use Roducks\Data\User;
use Roducks\Framework\Hash;
use Roducks\Framework\Role;
use Roducks\Framework\Form;
use Roducks\Framework\Helper;
use Roducks\Framework\Event;
use Roducks\Page\JSON;
use Roducks\Libs\Request\Http;
use Roducks\Libs\Utils\Date;
use DB\Models\Users\Users as UsersTable;
use Roducks\Services\Auth as AuthService;

class Home extends JSON
{

	const REDIRECT_TO_URL = "/login";

	public function __construct(array $settings)
	{
		parent::__construct($settings);

		$this->post->required();
	}

	/**
	*	@type POST
	*/
	public function createAccount()
	{

		Form::setKey($this->post->hidden('form-key'));

		if (User::isLoggedIn()) {
			Http::sendHeaderForbidden();
		}

		$gender = $this->post->select('gender');
		$admin_id_user_tree = User::getData('id_user_tree');

		$fields = [
			'id_role' 		 => 7, // reserved for subscribers
			'email' 		 => $this->post->text('email'),
			'first_name' 	 => $this->post->text('first_name'),
			'last_name' 	 => $this->post->text('last_name'),
			'gender' 		 => $gender,
			'picture' 		 => Helper::getUserIcon($gender),
			'password' 		 => $this->post->password('password'),
		];

		if (SUBSCRIBERS_EXPIRE) {

			$expires = Date::addMonthsToCurrentDate(2);

			switch (SUBSCRIBERS_EXPIRE_TIME) {
				case 'DAYS':
					$expires = Date::addDaysToCurrentDate(SUBSCRIBERS_EXPIRE_IN);
					break;
				case 'MONTHS':
					$expires = Date::addMonthsToCurrentDate(SUBSCRIBERS_EXPIRE_IN);
					break;
			}

			$fields['expires'] = 1;
			$fields['expiration_date'] = $expires;

		}

		$form = Form::validation([
			Form::filter(Form::FILTER_EMAIL, $fields['email']),
			Form::filter(Form::FILTER_WORDS, $fields['first_name']),
			Form::filter(Form::FILTER_WORDS, $fields['last_name']),
			Form::filter(Form::FILTER_STRING, $fields['password'])
		]);

		if ($form->success()) {
			$db = $this->db();
			$user = UsersTable::open($db);
			$tx = $user->create($fields);

			if ($tx === false) {
				$this->setError(2, TEXT_ERROR_CREATING_USER);
			} else {

				$id = $user->getId();
				$user->update($id, ['id_user_tree' => '0']);

				Event::dispatch('onEventCreateAccount', [$id, Role::TYPE_SUBSCRIBERS]);
				$auth = AuthService::init()->success($fields['email'], $fields['password']);
				$this->data("url_redirect", "/");
			}

		} else {
			$this->setError(1, TEXT_FORM_ERRORS);
		}

		parent::output();
	}

	public function restorePassword()
	{

		if (User::isLoggedIn()) {
			Http::sendHeaderForbidden();
		}

		$email = $this->post->text('email');
		$token = $this->post->hidden('token');
		$password = $this->post->password('password');

		$form = Form::validation([
			Form::filter(Form::FILTER_STRING, $token),
			Form::filter(Form::FILTER_STRING, $password)
		]);

		if ($form->success()) {
			$db = $this->db();
			$user = UsersTable::open($db);
			$user->filter(['email' => $email, 'token' => $token], ['id_user']);

			if ($user->rows()) {
				$info = $user->fetch();

				$id_user = $info['id_user'];

				$tx1 = $user->changePassword($id_user, $password);
				$tx2 = $user->update($id_user, ['token' => '']);
				if ($tx1 === false) {
					$this->setError(2, TEXT_ERROR_RESETING_PASSWORD);
				} else {
					$this->data("url_redirect", self::REDIRECT_TO_URL);
				}
			}

		} else {
			$this->setError(1, TEXT_FORM_ERRORS);
		}

		parent::output();
	}

	public function recoverPassword()
	{

		$email = $this->post->text('email');

		$db = $this->db();
		$user = UsersTable::open($db);
		$user->filter(['email' => $email],['id_user']);

		if ($user->rows()) {
			$info = $user->fetch();

			$id_user = $info['id_user'];
			$token = Hash::getToken();

			$tx = $user->update($id_user, ['token' => $token]);

			if ($tx === false) {
				$this->setError(2, TEXT_WAS_AN_ERROR);
			} else {

		  		$this->data('token', $token);
		  		$sender = $this->sendEmail('recover-password', function ($sender) use ($email) {
		  			$sender->to = $email;
		  			$sender->from = EMAIL_FROM;
		  			$sender->company = PAGE_TITLE;
		  			$sender->subject = TEXT_RECOVER_PASSWORD;
		  		});

		  		if (!$sender) {
		  			$this->setError(1, TEXT_ERROR_SENDING_EMAIL);
		  		}
			}

		} else {
			$this->setError(3, TEXT_INVALID_EMAIL);
		}

  		parent::output();

	}

	public function contactUsSubmit()
	{

  		Form::setKey($this->post->hidden('form-key'));

  		$sender = $this->sendEmail('contact-us', function ($sender) {
  			$sender->to = EMAIL_TO;
  			$sender->from = EMAIL_FROM;
  			$sender->company = PAGE_TITLE;
  			$sender->subject = TEXT_CONTACT_US;
		});

  		if (!$sender) {
  			$this->setError(0, TEXT_ERROR_SENDING_EMAIL);
  		}

		parent::output();

	}

}
