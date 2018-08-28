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

namespace Roducks\Modules\Front\Account\JSON;

use Roducks\Page\JSON;
use Roducks\Page\View;
use Roducks\Framework\Login;
use Roducks\Framework\Role;
use Roducks\Libs\Utils\Date;
use DB\Models\Users\Users as UsersTable;

class Account extends JSON
{

	private $_fields;

	protected $_dispatchUrl = true;
	protected $_user;
	protected $_id;

	public function __construct(array $settings)
	{
		parent::__construct($settings);

		$this->post->required();
		$this->accessSubscriber();
		$this->role(Role::TYPE_SUBSCRIBERS);
		$this->grantAccess->json();

		$this->_fields = [
			'first_name' => $this->post->text('first_name'),
			'last_name' => $this->post->text('last_name'),
			'updated_at' => Date::getCurrentDateTime()
		];

		$db = $this->db();
		$this->_user = UsersTable::open($db);
		$this->_id = Login::getSubscriberId();

	}

	/**
	*	@type POST
	*/
	public function update()
	{

		$this->grantAccess->edit();

		// Update admin session ONLY if $id equals adminId session
		Login::updateSubscriber($this->_id, $this->_fields);
		
		$this->_user->update($this->_id, $this->_fields);

		parent::output();
	}

	public function changePassword()
	{

		$this->grantAccess->password();

		// Make sure user didn't skip his current password.
		if (!$this->_user->paywall($this->_id, $this->post->password('password'))) {
			$this->setError(401,TEXT_AUTH_FAIL);
		} else {
			$this->_user->changePassword($this->_id, $this->post->password('new_password'));
		}	

		parent::output();
	}

	public function picture()
	{

		$this->grantAccess->picture();

		$data = ['picture' => $this->post->text('picture')];
		// Update admin session ONLY if $id equals adminId session
		$this->_user->update($this->_id, $data);
		Login::updateSubscriber($this->_id, $data);
		
		parent::output();
	}

} 