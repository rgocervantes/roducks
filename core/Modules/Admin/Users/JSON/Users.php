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

namespace Roducks\Modules\Admin\Users\JSON;

use Roducks\Data\User;
use Roducks\Framework\Role;
use Roducks\Framework\Form;
use Roducks\Framework\Helper;
use Roducks\Framework\Observer;
use Roducks\Page\JSON;
use Roducks\Page\View;
use Roducks\Libs\Utils\Date;

class Users extends JSON
{

	private $_fields;

	protected $_dispatchUrl = true;
	protected $_authentication = true;
	protected $_type;
	protected $_url;
	protected $_user;

	public function __construct(array $settings)
	{
		parent::__construct($settings);

		$this->post->required();
		$this->role($this->_url);
		$this->grantAccess->json();

		$this->_fields = [
			'first_name' 		=> $this->post->text('first_name'),
			'last_name' 		=> $this->post->text('last_name'),
			'id_role' 			=> $this->post->select('id_role',0),
			'updated_at' 		=> Date::getCurrentDateTime(),
			'active' 			=> $this->post->checkbox('active',0),
			'expires'			=> $this->post->checkbox('expires', 0),
			'expiration_date'	=> $this->post->hidden('expiration_date', 'NULL', true),
		];

		$this->_user = $this->model('users/users');

	}

	/**
	*	@type POST
	*/
	public function insert()
	{

		$this->grantAccess->create();
		$gender = $this->post->select('gender');
		$admin_id_user_tree = User::getData('id_user_tree');

		$fields = [
			'id_user_parent' => User::getId(),
			'email' 		 => $this->post->text('email'),
			'password' 		 => $this->post->password('password'),
			'gender' 		 => $gender,
			'picture' 		 => Helper::getUserIcon($gender),
		];

		$fields = array_merge($this->_fields, $fields);

		$tx = $this->_user->create($fields);

		if ($tx !== false) {
			$id = $this->_user->getId();
			$tree = (User::isSuperAdmin()) ? '0' : User::getId();
			$id_user_tree = ($admin_id_user_tree == '0') ? $tree : implode("_", [$admin_id_user_tree,$tree]);

			$this->_user->update($id, ['id_user_tree' => $id_user_tree]);

			Observer::on('CreateAccount', [$id, Role::TYPE_USERS]);
			$this->data('insert_id', $id);
		} else {
			$this->setError(0,"User already exists.");
		}

		parent::output();
	}

	/**
	*	@type POST
	*/
	public function update($id_user)
	{

		$row = $this->_user->row($id_user);
		$this->grantAccess->editDescendent($id_user, $row, $this->_user->isDescendent($id_user, User::getId()), "edit");

		if ($this->_fields['expires'] == 0) {
			$this->_fields['expiration_date'] = 'NULL';
		}

		if (User::getId() == $id_user) {
			unset($this->_fields['id_role']);
			unset($this->_fields['active']);
			unset($this->_fields['expires']);
			unset($this->_fields['expiration_date']);

			// Update admin session ONLY if $id equals adminId session
			User::updateSessionData($id_user, $this->_fields);
		}

		$this->data('url_redirect', $this->_url);
		$this->_user->update($id_user, $this->_fields);

		parent::output();
	}

	public function changePassword()
	{

		$id_user = User::getId();
		$this->grantAccess->reset();

		// Make sure user didn't skip his current password.
		if (!$this->_user->paywall($id_user, $this->post->password('password'))) {
			$this->setError(401,TEXT_AUTH_FAIL);
		} else {
			$this->_user->changePassword($id_user, $this->post->password('new_password'));
		}

		parent::output();
	}

	public function resetPassword($tag, $id_user)
	{

		$row = $this->_user->row($id_user);
		$this->grantAccess->editDescendent($id_user, $row, $this->_user->isDescendent($id_user, User::getId()), "reset");
		$this->_user->changePassword($id_user, $this->post->password('new_password'));

		parent::output();
	}

	public function picture($tag, $id_user)
	{

		$row = $this->_user->row($id_user);
		$this->grantAccess->editDescendent($id_user, $row, $this->_user->isDescendent($id_user, User::getId()), "picture");

		$data = ['picture' => $this->post->text('picture')];
		// Update admin session ONLY if $id equals adminId session
		$this->_user->update($id_user, $data);
		User::updateSessionData($id_user, $data);

		parent::output();
	}

	public function trash()
	{

		$id = $this->post->param('id');
		$value = $this->post->param('value');

		$row = $this->_user->row($id);
		$this->grantAccess->editDescendent($id, $row, $this->_user->isDescendent($id, User::getId()), "trash");

		$form = Form::validation([
			Form::filter(Form::FILTER_INTEGER, $id),
			Form::filter(Form::FILTER_INTEGER, $value)
		]);

		if ($form->success()) {

			if ($id != User::getId() || User::isSuperAdmin()) {
				$user = $this->_user->row($id);
				if ($this->_user->rows()) {
					$this->_user->update($id, ['loggedin' => 0, 'trash' => $value]);
				} else {
					$this->setError(2, TEXT_USER_NOT_EXIST);
				}
			} else {
				$this->setError(1, TEXT_INVALID_REQUEST);
			}
		} else {
			$this->setError(0, TEXT_INVALID_REQUEST);
		}

		parent::output();
	}

	/**
	*	@type GET
	*	@return json
	*/
	public function visibility()
	{

		$id = $this->post->param('id');
		$active = $this->post->param('value');

		$row = $this->_user->row($id);
		$this->grantAccess->editDescendent($id, $row, $this->_user->isDescendent($id, User::getId()), "visibility");

		$form = Form::validation([
			Form::filter(Form::FILTER_INTEGER, $id),
			Form::filter(Form::FILTER_INTEGER, $active)
		]);

		if ($form->success()) {

			// Make sure user ID is not the same as current adminId because you cannot disable yourself!
			if ($id != User::getId() || User::isSuperAdmin()) {
				$user = $this->_user->row($id);
				if ($this->_user->rows()) {
					$this->_user->update($id, ['loggedin' => 0,'active' => $active]);
				} else {
					$this->setError(2, TEXT_USER_NOT_EXIST);
				}

			} else {
				$this->setError(1, TEXT_INVALID_REQUEST);
			}

		} else {
			$this->setError(0, TEXT_INVALID_REQUEST);
		}

		parent::output();

	}

	public function expiration()
	{

		$id = $this->post->param('id');
		$date = $this->post->param('date');

		$row = $this->_user->row($id);
		$this->grantAccess->editDescendent($id, $row, $this->_user->isDescendent($id, User::getId()), "expiration");

		$form = Form::validation([
			Form::filter(Form::FILTER_INTEGER, $id),
			Form::filter(Form::FILTER_DATE_YYYY_MM_DD, $date)
		]);

		if ($form->success()) {

			if ($id != User::getId() || User::isSuperAdmin()) {
				$user = $this->_user->row($id);
				if ($this->_user->rows()) {
					$this->_user->update($id, ['expires' => 1,'expiration_date' => $date]);
				} else {
					$this->setError(2, TEXT_USER_NOT_EXIST);
				}
			} else {
				$this->setError(1, TEXT_INVALID_REQUEST);
			}
		} else {
			$this->setError(0, TEXT_INVALID_REQUEST);
		}

		parent::output();
	}

}
