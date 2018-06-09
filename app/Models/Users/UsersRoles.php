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

use Roducks\Libs\ORM\Model;

class UsersRoles extends Model
{
	
	public function __construct(\mysqli $mysqli)
	{

		$this
		->join('u', Users::CLASS)
		->join('r', Roles::CLASS, ['u.id_role' => 'r.id_role']);	

		parent::__construct($mysqli);

	}

	public function totals($type, array $condition = [])
	{

		$filter = [
			'r.type' => $type,
		];

		$cond = (count($condition) > 0) ? array_merge($condition, $filter) : $filter;

		$this->select([self::field("u.id_user")])->filter($cond);

		return $this->getTotalRows();
	}

	/**
	*	@param $type integer					
	*	@return integer	
	*/
	public function inTrash($type, array $condition = [])
	{
		$condition['u.trash'] = 1;
		return $this->totals($type, $condition);
	}

	/**
	*	@param $email string
	*	@param $type integer				
	*	@return resource	
	*/
	public function auth($email, $type)
	{

		$fields = [
			self::field("u.*"),
			self::field("r.id_role"),
			self::alias("r.name","role"),
			self::alias("r.active","ractive"),
			self::field("r.config"),
		];
		
		return $this
		->select($fields)
		->filter([
				'u.email' => $email, 
				'r.type' => $type
		]);

	}

	/**
	*	@param $type integer
	*	@param $page integer
	*	@param $limit integer
	*	@param $orderby string
	*	@param $condition array					
	*	@return resource	
	*/
	public function getAll($type, $page = 1, $limit = 20, $sort = "asc", array $condition = [])
	{

		$filter = ['r.type' => $type];
		$condition = (count($condition) > 0) ? array_merge($condition, $filter) : $filter;

		$fields = [
			self::field("u.*"),
			self::field("r.id_role"),
			self::alias("r.name","role"),
			self::alias("r.type","rtype"),
			self::field("r.config"),
		];

        return $this
        ->select($fields)
        ->where($condition)
        ->orderBy(['u.id_user' => $sort])
        ->paginate($page, $limit);

	}

	/**
	*	@param $id_user integer				
	*	@return resource	
	*/
	public function getUser($id_user)
	{

		$fields = [
			self::field("u.*"),
			self::field("r.id_role"),
			self::alias("r.name","role"),
			self::alias("r.type","rtype"),
			self::field("r.config"),
		];

		return $this
		->select($fields)
		->filter([
			'u.id_user' => $id_user
		]);

	}

} 