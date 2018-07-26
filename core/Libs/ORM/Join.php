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

  private function _link($key, $table, $type, array $join = [])
	{
		$table = self::_getTable($table);
		$this->_joins[$key] = ['table' => $table];
		if (count($join) > 0) {
			$this->_joins[$key][$type] = $join;
		}

		return $this;
	}

  protected function join($key, $table, array $join = [])
	{
		return $this->_link($key, $table, 'join', $join);
	}

	protected function leftJoin($key, $table, array $join = [])
	{
		return $this->_link($key, $table, 'left_join', $join);
	}

	protected function rightJoin($key, $table, array $join = [])
	{
		return $this->_link($key, $table, 'right_join', $join);
	}

	protected function table($key, $table)
	{
		return $this->join($key, $table);
	}

  public function __construct(\mysqli $mysqli)
	{
		parent::__construct($mysqli, $this->_joins);
	}

}
