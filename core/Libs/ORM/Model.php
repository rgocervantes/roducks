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

namespace Roducks\Libs\ORM;

/*

//--------------------------------------------------------
//		EXAMPLES
//--------------------------------------------------------

		$db = $this->db();

	|-------------------------
	|	INSERT
	|-------------------------

		$user = UsersTable::open($db)->prepare();

		$user->setFirstName("Rod");
		$user->setEmail("rod@roducks.com");
		$user->insert();

	|-------------------------
	|	UPDATE
	|-------------------------

		$user = UsersTable::open($db)->getRow(1); // id

		if ($user->rows()) {
			$user->setFirstName("Rod");
			$user->setEmail("rod@roducks.com");
			$user->update();
		}

	|-------------------------
	|	FETCH OBJECT
	|-------------------------

		$user = UsersTable::open($db)->getRow(1); // id

		if ($user->rows()) {
			$name = $user->getFirstName();
			$email = $user->getEmail();
		} else {
			echo "No rows.";
		}

	|-------------------------
	|	FETCH ARRAY
	|-------------------------

		$user = UsersTable::open($db);
		$row = $user->row(1); // id

		if ($user->rows()) {
			$name = $row['first_name'];
			$email = $row['email'];
		} else {
			echo "No rows.";
		}

	|-------------------------
	|	JOIN
	|-------------------------

		$UsersRoles = UsersRoles::open($db);

		$condition = ['u.trash' => 0, 'r.type' => $type];

		$fields = [
			Model::field("u.*"),
			Model::field("r.id_role"),
			Model::field("r.name","role"),
			Model::field("r.type","rtype"),
			Model::field("r.config"),
		];

		$UsersRoles
		->groupby("u.id_user")
		->having(["u.id_user:>" => 1])
		->filter($condition, $fields); // For pagination use: ->pagination()

		if ($UsersRoles->rows()) : while ($row = $UsersRoles->fetch()) :
			print_r($row);
		endwhile; else:
			echo "No rows were found.";
		endif;

	|-------------------------------------
	|	DELETE BY CONDITION
	|-------------------------------------

		$db = $this->db();
		$tx = UsersTable::open($db)->deleteByCondition(['id_role' => 7]);

	|-------------------------------------
	|	UPDATE BY CONDITION
	|-------------------------------------

		$db = $this->db();
		$tx = UsersTable::open($db)->updateByCondition(['active' => 0], ['id_role' => 7]);

*/

abstract class Model extends ORM
{

	const TYPE_INTEGER = 1;
	const TYPE_DECIMAL = 2;
	const TYPE_BOOL = 3;
	const TYPE_VARCHAR = 4;
	const TYPE_TEXT = 5;
	const TYPE_BLOB = 6;
	const TYPE_DATETIME = 7;
	const TYPE_DATE = 8;
	const TYPE_TIME = 9;

	var
		$id,
		$table = null,
		$fields = [];

	private $_ORM = false;
	private $_data = [];
	private $_dataUpdate = [];
	private $_id;
	private $_action = 'none';

/*
//----------------------
//		STATIC
//----------------------
*/

	static function getConventionName($str, $sep = "-")
	{

		$abc = [
			"A" => 1,
			"B" => 1,
			"C" => 1,
			"D" => 1,
			"E" => 1,
			"F" => 1,
			"G" => 1,
			"H" => 1,
			"I" => 1,
			"J" => 1,
			"K" => 1,
			"L" => 1,
			"M" => 1,
			"N" => 1,
			"O" => 1,
			"P" => 1,
			"Q" => 1,
			"R" => 1,
			"S" => 1,
			"T" => 1,
			"U" => 1,
			"V" => 1,
			"W" => 1,
			"X" => 1,
			"Y" => 1,
			"Z" => 1
		];

		$ret = '';

		for ($i=0; $i < strlen($str); $i++) {
			$text = substr($str, $i, 1);
			$us = ($i>0) ? $sep : '';
			$ret .= (isset($abc[$text])) ? $us . strtolower($text) : $text;
		}

		return $ret;

	}

/*
//----------------------
//		PRIVATE
//----------------------
*/

	private function _autoload($data)
	{

		$this->_ORM = true;

	    foreach($data as $key => $value) {
	        if (isset($this->fields[$key]) || count($this->_fields) > 0) {
	           	$this->_data[$key] = $value;
	        }
	    }
	}

	private function _unexcepted(array $condition = [])
	{

		$error = 0;

		if (count($condition) > 0) {
			foreach ($condition as $key => $value) {
				$key = preg_replace('/^([a-z]+\.)?([a-zA-Z_]+) :?.*$/', '$2', $key);
				if (!isset($this->fields[$key])) {
					$error++;
					break;
				}

				if (empty($value) || $value == "NULL") {
					continue;
				}

				$fieldType = $this->fields[$key];

				switch ($fieldType) {
					case self::TYPE_INTEGER:
					case self::TYPE_DECIMAL:
						$regexp = '/^(\-)?\d+(\.\d+)?$/';
						break;
					case self::TYPE_BOOL:
						$regexp = '/^(1|0)$/';
						break;
					case self::TYPE_VARCHAR:
					case self::TYPE_TEXT:
					case self::TYPE_BLOB:
						$regexp = '/^.+$/';
						break;
					case self::TYPE_DATETIME:
						if ($value == 'NOW()') {
							$regexp = '/^(NOW)\(\)$/';
						} else {
							$regexp = '/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/';
						}

						break;
					case self::TYPE_DATE:
						$regexp = '/^\d{4}-\d{2}-\d{2}$/';
						break;
					case self::TYPE_TIME:
						$regexp = '/^\d{2}:\d{2}:\d{2}$/';
						break;
				}

				if (!preg_match($regexp, $value)) {
					$error++;
					break;
				}
			}

			if ($error > 0) {
				return true;
			}

		}

		return false;
	}

  protected function getAll(array $conditions = [])
  {
    $filter = [
      'deleted_at:null' => TRUE,
    ];

    return $this->filter($filter + $conditions);
  }


/*
//----------------------
//		PUBLIC
//----------------------
*/
	public function __call($method, $args)
	{

	    if (preg_match('/^(get|set|inc)(\w+)$/', $method, $fx)) {

	        $first = strtolower(substr($fx[2],0,1));
	        $property = $first . substr($fx[2],1);

	        $name = self::getConventionName($property, "_");

	        if (isset($this->fields[$name]) || count($this->_fields) > 0) {
						$value = (isset($this->_data[$name])) ? $this->_data[$name] : "";

						switch ($fx[1]) {
							case 'get':
								return $value;
								break;
							case 'set':
								if ($this->_action == 'insert') {
									$this->_data[$name] = $args[0];
								} else {
									$this->_dataUpdate[$name] = $args[0];
								}
								break;
							case 'inc':
								$value = (empty($value) || !preg_match('/^\d+$/', $value)) ? 0 : $value;
								$inc = (empty($args[0])) ? 1 : $args[0];
								$i = intval($value) + intval($inc);
								if ($this->_action == 'insert') {
									$this->_date[$name] = $i;
								} else {
									$this->_dataUpdate[$name] = $i;
								}
								break;
						}
	        }
	    }

	    return "";
	}

	public function __construct(\mysqli $mysqli, $tbl)
	{
		$table = (!is_null($this->table)) ? $this->table : $tbl;
		parent::__construct($mysqli, $table);
	}

	public function row($id)
	{
		$args = [$this->id => $id];

		return parent::row($args);
	}

	public function getRow($id)
	{
		$this->_action = 'update';
		$this->_id = $id;
		$args = [$this->id => $id];

		$row = parent::row($args);

		$this->_autoload($row);

		return $this;
	}

	public function prepare()
	{
		$this->_ORM = true;
		$this->_action = 'insert';

		return $this;
	}

	public function update($id = "", array $data = [], array $condition = [])
	{

		if ($this->_ORM) {
			$id = $this->_id;
			$data = $this->_dataUpdate;
		}

		if ($this->_unexcepted($data) || $this->_unexcepted($condition)) {
			return false;
		}

		$where = $this->_where($condition);

		$args = [$this->id => $id];

		return parent::update($args, $data, $where);
	}

	public function updateByCondition(array $data, array $condition)
	{
		return parent::update($condition, $data);
	}

	public function delete($id = "", array $condition = [])
	{

		if ($this->_ORM) {

			if (!parent::rows()) {
				return false;
			}

			if (is_array($id)) {
				$condition = $id;
			}
			$id = $this->_id;
		}

		if ($this->_unexcepted($condition) || !self::isInteger($id)) {
			return false;
		}

		$where = $this->_where($condition);

		$args = [$this->id => $id];
		return parent::delete($args, $where);
	}

	public function deleteByCondition(array $condition)
	{
		return parent::delete($condition);
	}

	public function insert(array $data = [])
	{

		if ($this->_ORM) {
			$data = $this->_data;
		}

		if ($this->_unexcepted($data)) {
			return false;
		}

		return parent::insert($data);
	}

	public function insertOnce(array $data = [], array $condition = [])
	{

		if ($this->_ORM) {
			$condition = $data;
			$data = $this->_data;
		}

		if ($this->_unexcepted($data) || $this->_unexcepted($condition)) {
			return false;
		}

		$where = $this->_where($condition);

		return parent::insertOnce($data, $where);
	}

	public function lastId($data = "", array $condition = [])
	{

		if ($this->_ORM) {
			if (is_array($data)) {
				$condition = $data;
			}
		}

		if ($this->_unexcepted($condition)) {
			return false;
		}

		$where = $this->_where($condition);

		return parent::lastId($this->id, $where);
	}

	public function save()
	{
		switch ($this->_action) {
			case 'insert':
				$this->insert();
				break;
			case 'update':
				$this->update();
				break;
		}
	}

	public function getTableTotalRows()
	{
		return $this->count($this->id);
	}

	public function last($limit = 1)
	{
		$this->_orderByFilter = [];

		if ($this->_orderByOn) {
			$this->_orderByFilter = [$this->id => 'desc'];
		}

		return $this->first($limit);
	}

}
