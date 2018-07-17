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

namespace App\Models\Users;

use Roducks\Framework\Login;
use Roducks\Libs\Utils\Date;
use Roducks\Libs\ORM\Model;

class Users extends Model
{

	var $id = "id_user";
	var $fields = [
		'id_user_parent'	 => Model::TYPE_INTEGER,
		'id_user_tree'	 	 => Model::TYPE_BLOB,
		'id_role' 			 => Model::TYPE_INTEGER,
		'email'				 => Model::TYPE_VARCHAR,
		'first_name'		 => Model::TYPE_VARCHAR,
		'last_name'			 => Model::TYPE_VARCHAR,
		'gender'			 => Model::TYPE_VARCHAR,
		'picture'			 => Model::TYPE_VARCHAR,
		'password'			 => Model::TYPE_VARCHAR,
		'salt'		 		 => Model::TYPE_VARCHAR,
		'active'			 => Model::TYPE_BOOL,
		'trash'				 => Model::TYPE_BOOL,
		'token'				 => Model::TYPE_VARCHAR,
		'loggedin'			 => Model::TYPE_BOOL,
		'location'			 => Model::TYPE_VARCHAR,
		'expires'			 => Model::TYPE_BOOL,
		'expiration_date'	 => Model::TYPE_DATE,
		'created_at'		 => Model::TYPE_DATETIME,
		'updated_at'		 => Model::TYPE_DATETIME
	];

	public function logInOut($id, $option)
	{
		return $this->update($id, ['loggedin' => $option]);
	}

	/*
	*	if User has descendents
	*/
	public function isDescendent($id, $id_user_parent)
	{
		return $this->results([$this->id => $id, 'id_user_parent' => $id_user_parent]);
	}

	/**
	*	@param $id integer USER ID
	*	@return bool
	*/
	public function paywall($id, $password)
	{
		$row = $this->row($id);

		if (!$this->rows()) {
			return false;
		}

		return Login::paywall($row['password'], $row['salt'], $password);
	}

	/**
	*	@param $id integer USER ID
	*	@param $data array fields
	*/
	public function changePassword($id, $password)
	{

		$secret = Login::generatePassword($password);

		$data = [];
		$data['updated_at'] = Date::getCurrentDateTime();
		$data['password'] = $secret['password'];
		$data['salt'] = $secret['salt'];

		return $this->update($id, $data);
	}

	/**
	*	@param $data array fields
	*/
	public function create($data)
	{

		$secret = Login::generatePassword($data['password']);

		$data['password'] = $secret['password'];
		$data['salt'] = $secret['salt'];

		return $this->insertOnce($data, ['email' => $data['email']]);

	}

	public function getFullName()
	{

		return self::concatValues([
			$this->getFirstName(),
			$this->getLastName()
		]);
	}

	public function exists($email)
	{
		return $this->results(['email' => $email]);
	}

}
