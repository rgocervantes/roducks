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

abstract class Join extends ORM
{

	private $_joins = [];

  private function _link($table, $key, $type, array $join = [])
	{
		$table = self::_getTable($table);
		$this->_joins[$key] = ['table' => $table];
		if (count($join) > 0) {
			$this->_joins[$key][$type] = $join;
		}

		return $this;
	}

  protected function join($table, $key, array $join = [])
	{
		return $this->_link($table, $key, 'join', $join);
	}

	protected function leftJoin($table, $key, array $join = [])
	{
		return $this->_link($table, $key, 'left_join', $join);
	}

	protected function rightJoin($table, $key, array $join = [])
	{
		return $this->_link($table, $key, 'right_join', $join);
	}

	protected function table($table, $key)
	{
		return $this->join($table, $key);
	}

	public function last($limit = 1, array $orderBy = [])
	{
		if (!empty($orderBy)) {
			if (count($orderBy) > 1) {
				$this->orderBy($orderBy, "desc");
			} else {
				$this->orderBy([$orderBy[0] => "desc"]);
			}
		}
		
		return parent::first($limit);
	}

  public function __construct(\mysqli $mysqli)
	{
		parent::__construct($mysqli, $this->_joins);
	}

}
