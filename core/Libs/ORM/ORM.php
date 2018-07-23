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

abstract class ORM extends Query
{

/*
//----------------------
//		STATIC
//----------------------
*/

	static protected function _getTable($class)
	{
		return preg_replace('/^.+\\\([a-zA-Z_]+)$/', '$1', $class);
	}

	static function open(\mysqli $mysqli)
	{

		$class = get_called_class();
		$table = self::_getTable($class);
		$inst = new $class($mysqli, $table);

		return $inst;
	}

/*
//----------------------
//		PUBLIC
//----------------------
*/

	public function foundRow()
	{
		return parent::rows();
	}

	public function fetchAll($fields = "*")
	{
		return parent::filter([], $fields);
	}

	public function getData()
	{

		$ret = [];

		if ($this->rows()) : while ($row = $this->fetch()) :
		        $ret[] = $row;
		endwhile; endif;

		return $ret;
	}

	public function filteredBy($field)
	{

		$results = parent::distinct($field);
		$ret = [];
		if ($results->rows()) : while ($row = $results->fetch()) :
		        $ret[] = $row[$field];
		endwhile; endif;

		return $ret;

	}

}
