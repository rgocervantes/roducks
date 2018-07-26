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

@usage:

	$db = $this->db();

	//-----------------
	// EXAMPLE # 1
	//-----------------

	$query = new Query($db);
	$query->query("SELECT * FROM Users");

	if ($query->rows()): while($row = $query->fetch()):
		// $row
	endwhile; endif;

	//-----------------
	// EXAMPLE # 2
	//-----------------
	$query = new Query($db, Users::CLASS);
	$query->filter([
		'name:date:year' => "2015", //first param must NOT have AND|OR
		'[OR]name:date:year' => "2014"),
		'[AND]id:<>' => 1000
	]);

	if ($query->rows()): while($row = $query->fetch()):
		// $row
	endwhile; endif;

	//-----------------
	// EXAMPLE # 3
	//-----------------
	// PARENTHESIS

		$filter['[BEGIN_COND]'] = "(";
			$filter['[NON]u.id_user_parent:>'] = Login::getAdminData('id_user_parent');
			$filter['[OR]u.id_role:>'] = Login::getAdminData('id_role');
		$filter['[END_COND]'] = ")";

*/

class Query
{

	const DATA_OBJECT = "object";
	const DATA_ARRAY = "array";
	const DATA_ASSOC = "assoc";

	private $_db,
		$_statment,
		$_connection = null,
		$_totalPages = 1,
		$_filter = [],
		$_queryString = "";

	protected $_condition = [],
			$_fields = [],
			$_table = "";

/*
//----------------------
//		STATIC
//----------------------
*/

	static function freeResult($statment)
	{
		\mysqli_free_result($statment);
	}

	static function numFields($statment)
	{
		return \mysqli_num_fields($statment);
	}

	static function numRows($statment)
	{
		return \mysqli_num_rows($statment);
	}

	static function fetchAssoc($statment)
	{
		return \mysqli_fetch_assoc($statment);
	}

	static function fetchObject($statment)
	{
		return \mysqli_fetch_object($statment);
	}

	static function fetchArray($statment)
	{
		return \mysqli_fetch_array($statment);
	}

	static function isInteger($value)
	{
		return preg_match('/^\d+$/', $value);
	}

	static private function _dataType($field, $value)
	{

		$ret = "";

		// allows join fields as well ex. -> ab.publication:date
		if (preg_match('/^([a-zA-Z0-9_\.]+):(.+)$/i', $field, $match)) {

			switch ($match[2]) {
					case 'string':
						$ret .= $match[1] . " = '{$value}'";
						break;
					case 'concat':
						if (is_array($value)) {
							$values = [];
							foreach ($value[1] as $v) {
								$values[] = ($v == " ") ? self::_field($v) : $v;
							}
							$ret .= "CONCAT(".implode(",", $values) . ") = " . self::_field($value[0]);
						}
						break;
					case 'concat:like':
						if (is_array($value)) {
							$values = [];
							foreach ($value[1] as $v) {
								$values[] = ($v == " ") ? self::_field($v) : $v;
							}
							$ret .= "CONCAT(".implode(",", $values) . ") LIKE '%".$value[0]."%' ";
						}
						break;
					case 'is-null':
						$ret .= $match[1] . " IS NULL ";
						break;
					case 'is-not-null':
						$ret .= $match[1] . " IS NOT NULL ";
						break;
					case '%like%':
						$ret .= $match[1] . " LIKE '%".$value."%' ";
						break;
					case '%like':
						$ret .= $match[1] . " LIKE '%".$value."' ";
						break;
					case 'like%':
						$ret .= $match[1] . " LIKE '".$value."%' ";
						break;
					case 'ucase':
						$ret .= "UCASE(".$match[1].") = " . self::_field($value);
						break;
					case 'upper':
						$ret .= "UPPER(".$match[1].") = " . self::_field($value);
						break;
					case 'lower':
						$ret .= "LOWER(".$match[1].") = " . self::_field($value);
						break;
					case 'date':
						$ret .= "DATE(".$match[1].") = DATE(" . self::_field($value).")";
						break;
					case 'date:<>':
						$ret .= "DATE(".$match[1].") != DATE(" . self::_field($value).")";
						break;
					case 'date:<':
						$ret .= "DATE(".$match[1].") < DATE(" . self::_field($value).")";
						break;
					case 'date:>':
						$ret .= "DATE(".$match[1].") > DATE(" . self::_field($value).")";
						break;
					case 'date:<=':
						$ret .= "DATE(".$match[1].") <= DATE(" . self::_field($value).")";
						break;
					case 'date:>=':
						$ret .= "DATE(".$match[1].") >= DATE(" . self::_field($value).")";
						break;
					case 'date:between':
						$ret .= "DATE(".$match[1].") BETWEEN ".self::_field($value[0])." AND ".self::_field($value[1]);
						break;
					case 'date:year':
						$ret .= "EXTRACT( YEAR FROM ".$match[1].") = " . self::_field($value);
						break;
					case 'date:month':
						$ret .= "EXTRACT( MONTH FROM ".$match[1].") = " . self::_field($value);
						break;
					case 'date:day':
						$ret .= "EXTRACT( DAY FROM ".$match[1].") = " . self::_field($value);
						break;
					case 'date:year:<':
						$ret .= "EXTRACT( YEAR FROM ".$match[1].") < " . self::_field($value);
						break;
					case 'date:month:<':
						$ret .= "EXTRACT( MONTH FROM ".$match[1].") < " . self::_field($value);
						break;
					case 'date:day:<':
						$ret .= "EXTRACT( DAY FROM ".$match[1].") < " . self::_field($value);
						break;
					case 'date:year:<=':
						$ret .= "EXTRACT( YEAR FROM ".$match[1].") <= " . self::_field($value);
						break;
					case 'date:month:<=':
						$ret .= "EXTRACT( MONTH FROM ".$match[1].") <= " . self::_field($value);
						break;
					case 'date:day:<=':
						$ret .= "EXTRACT( DAY FROM ".$match[1].") <= " . self::_field($value);
						break;
					case 'date:year:>':
						$ret .= "EXTRACT( YEAR FROM ".$match[1].") > " . self::_field($value);
						break;
					case 'date:month:>':
						$ret .= "EXTRACT( MONTH FROM ".$match[1].") > " . self::_field($value);
						break;
					case 'date:day:>':
						$ret .= "EXTRACT( DAY FROM ".$match[1].") > " . self::_field($value);
						break;
					case 'date:year:>=':
						$ret .= "EXTRACT( YEAR FROM ".$match[1].") >= " . self::_field($value);
						break;
					case 'date:month:>=':
						$ret .= "EXTRACT( MONTH FROM ".$match[1].") >= " . self::_field($value);
						break;
					case 'date:day:>=':
						$ret .= "EXTRACT( DAY FROM ".$match[1].") >= " . self::_field($value);
						break;
					case 'date:year:<>':
						$ret .= "EXTRACT( YEAR FROM ".$match[1].") != " . self::_field($value);
						break;
					case 'date:month:<>':
						$ret .= "EXTRACT( MONTH FROM ".$match[1].") != " . self::_field($value);
						break;
					case 'date:day:<>':
						$ret .= "EXTRACT( DAY FROM ".$match[1].") != " . self::_field($value);
						break;
					case 'datetime':
						$ret .= "DATETIME(".$match[1].") = " . self::_field($value);
						break;
					case 'datetime:between':
						$ret .= "DATETIME(".$match[1].") BETWEEN ".self::_field($value[0])." AND ".self::_field($value[1]);
						break;
					case 'time':
						$ret .= "TIME(".$match[1].") = " . self::_field($value);
						break;
					case 'not':
						$ret .= $match[1]." NOT = " . self::_field($value);
						break;
					case 'not-in':
						$values = (is_array($value)) ? implode(",", $value) : self::_field($value);
						$ret .= $match[1] . " NOT IN(".$values.")";
						break;
					case 'in':
						$values = (is_array($value)) ? implode(",", $value) : self::_field($value);
						$ret .= $match[1] ." IN(".$values.")";
						break;
					case 'between':
						$ret .= $match[1] . " BETWEEN ".self::_field($value[0])." AND ".self::_field($value[1]);
						break;
					case '<>':
						$ret .= $match[1] . " != " . self::_field($value);
						break;
					case '<':
						$ret .= $match[1] . " < " . self::_field($value);
						break;
					case '>':
						$ret .= $match[1] . " > " . self::_field($value);
						break;
					case '>=':
						$ret .= $match[1] . " >= " . self::_field($value);
						break;
					case '<=':
						$ret .= $match[1] . " <= " . self::_field($value);
						break;
					default:
						$ret .= $field . " = " . self::_field($value);
						break;
				}
		} else {
			$ret = $field . " = " . self::_field($value);

			switch ($field) {
				case '[BEGIN_COND]':
					$ret = " {$value} ";
					break;
			}

			if (preg_match('/^\[COND_\d+\]$/', $field)) {
				$ret = " {$value} ";
			}

		}

		return $ret;

	}

	static private function _field($value)
	{

		$ret = "";

		if (!is_array($value)) {
			$ret = (self::isInteger($value)) ? intval($value) : "'{$value}'";
		}

		return $ret;
	}

	static private function _operatorCond($field, $value)
	{

		if ($field == '[END_COND]' || preg_match('/^\[COND_\d+\]$/', $field)) {
			return [" {$value} ", ""];
		}

		$operator = " AND "; // default

		if (preg_match('/^\[([A-Z0-9_]+)\](.+)$/i', $field, $match)) {
			$op = strtoupper($match[1]);
			$field = $match[2];
			switch ($op) {
				case 'OR':
				case 'AND':
					$operator = " {$op} ";
					break;
			}

			if (preg_match('/^NON(_\d+)?$/', $op)) {
				$operator = "";
			}

			if (preg_match('/^AND(_\d+)?$/', $op)) {
				$operator = " AND ";
			}

			if (preg_match('/^OR(_\d+)?$/', $op)) {
				$operator = " OR ";
			}

		}

		return [self::_dataType($field,$value), $operator];
	}

	static private function _conditions(array $arguments = [], $db)
	{

		if (!isset($arguments['condition'])) {
			return "";
		}

		$i = 0;
		$ret = "";

		if (isset($arguments['condition']) && is_array($arguments['condition'])): foreach ($arguments['condition'] as $k => $v): $i++;
			if (!is_array($v) && !self::isInteger($v)) {
				$v = $db->real_escape_string($v);
			}

			list($field, $operator) = self::_operatorCond($k,$v);
			$ret .= ($i == 1) ? " WHERE " : $operator;
			$ret .= $field;
		endforeach; endif;

		return $ret;
	}

	static private function _value($v)
	{
		if ($v == 'NOW()' || $v == 'NULL') {
			return $v;
		}

		return "'{$v}'";
	}

	static private function _values($values, $db)
	{

		$i = 0;
		$ret = [];

		foreach ($values as $k => $v) { $i++;
			if (!self::isInteger($v)) {
				$v = $db->real_escape_string($v);
			}
			$v = self::_value($v);
			$ret[] = "{$k} = {$v}";
		}

		return implode(", ",$ret);
	}

	static private function _aditionalArguments($args)
	{

		$ret = "";

		if (isset($args['groupby'])) {
			$ret .= " GROUP BY ".$args['groupby'];
		}

		if (isset($args['having'])) {
			$having = array_keys($args['having'])[0];
			$ret .= " HAVING ". self::_dataType($having,$args['having'][$having]);
		}

		if (isset($args['orderby'])) {

			if (isset($args['orderby']['fields']) && isset($args['orderby']['sort'])) {
				$orderby = $args['orderby']['fields'];
				$sortby = $args['orderby']['sort'];
			} else {
				$orderby = array_keys($args['orderby'])[0];
				$sortby = $args['orderby'][$orderby];
			}

			$sortby = strtoupper($sortby);

			if (in_array($sortby, ['ASC','DESC'])) {
				$ret .= " ORDER BY ".self::_prepareFields($orderby)." ".$sortby;
			}

		}

		return $ret;

	}

	static private function _limit($start, $args)
	{

		if (isset($args['limit'])) {
			return " LIMIT {$start},".$args['limit'];
		}

		return "";
	}

	static private function _prepareFields($fields)
	{

		if (is_array($fields)) {
			return implode(", ", $fields);
		}

		return $fields;
	}

	static private function _concat(array $values = [], $field = "")
	{
		return self::field("CONCAT(". implode(",", $values) .")", $field);
	}

	static private function _concatBy(array $values, $char = " ")
	{
		$ret = [];

		foreach ($values as $key => $value) {
			if ($key > 0) array_push($ret, self::_field($char));
			array_push($ret, $value);
		}

		return $ret;
	}

	static private function _alias($field, $alias)
	{
		return "{$field} AS {$alias}";
	}

	static function convert($field)
	{
		return self::_alias("CONVERT({$field} using utf8)", $field);
	}

	static function concat($field, array $values, $char = " ")
	{
		return self::_concat(self::_concatBy($values,$char), $field);
	}

	static function	concatMatch($value, array $fields, $char = " ")
	{
		return [$value, self::_concatBy($fields, $char)];
	}

	static function concatValues(array $fields, $char = " ")
	{
		return implode($char, $fields);
	}

	static function field($field, $alias = "")
	{

		if (!empty($alias)) {
			return self::_alias($field, $alias);
		}

		return $field;
	}

	static function now()
	{
		return "NOW()";
	}

	static function null()
	{
		return "NULL";
	}

	static function getPageFromOffset($offset, $limit)
	{

        $perPage = intval($limit);
        $offset = intval($offset);
        $page = 1;

        if ($offset > 0 && $offset >= $perPage) {
            $page = ($offset / $perPage) + 1;
        }

        return $page;
	}

/*
//----------------------
//		PRIVATE
//----------------------
*/

	private function _query($statment)
	{
		$this->_queryString = $statment;
		$this->_statment = $this->_db->query($this->_queryString, $this->_connection);
		return $this->_statment;
	}

	private function _join($table, $condition, $fields)
	{

		$wildcard = [];
		$text = "";
		$c = 0;

		foreach ($table as $key => $value) {

			$wildcard[] = $key . ".*";
			if (isset($value['left_join'])) {
				$join = "LEFT JOIN";
				$type = "left_join";
			} else if (isset($value['right_join'])) {
				$join = "RIGHT JOIN";
				$type = "right_join";
			} else {
				$join = "INNER JOIN";
				$type = "join";
			}

			if ($c == 0) {
				$text .= $value['table'] . " AS " . $key . " {$join} ";
			} else {
				if ($c > 1) $text .= " {$join} ";
				$text .= $value['table'] . " AS " . $key . " ";

				if (isset($value[$type])) {
					$skey = array_keys($value[$type])[0];
					$text .= "ON " . $skey . " = " . $value[$type][$skey];
				}
			}
			$c++;
		}

		$fields = ( (is_array($fields)) && (count($fields) > 0) ) ? $fields : $wildcard;

		return $this->_single($text, $condition, $fields);
	}

	private function _single($table, $condition, $fields)
	{
		$this->_queryString = "SELECT ".self::_prepareFields($fields)." FROM {$table}{$condition}";
		return $this->_queryString;
	}

	private function _select($table, $condition, $fields)
	{

		if (is_array($table)) {
			$statment = $this->_join($table, $condition, $fields);
		} else {
			$statment = $this->_single($table, $condition, $fields);
		}

		return $this->_query($statment);
	}

	private function _insert($table, $fields, $values)
	{
		$statment = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
		return $this->_query($statment);
	}

	private function _update($table, $values, $condition)
	{
		$statment = "UPDATE {$table} SET {$values}{$condition}";
		return $this->_query($statment);
	}

	private function _delete($table, $condition)
	{
		$statment = "DELETE FROM {$table}{$condition}";
		return $this->_query($statment);
	}

	private function _operator($operator, $table, $id, array $arguments = [])
	{
		$this->filter($arguments, ["{$operator}({$id}) AS total"]);
		$row = $this->fetch();
		return $row['total'];
	}

/*
//----------------------
//		PROTECTED
//----------------------
*/

	protected function _where(array $condition = [])
	{

		if (count($this->_condition) > 0) {
			return $this->_condition;
		}

		return $condition;
	}

/*
//----------------------
//		PUBLIC
//----------------------
*/

	public function __construct(\mysqli $mysqli, $table = "")
	{
		$this->_db = $mysqli;
		$this->_table = $table;
	}

	public function autocommit($option = false)
	{
		$this->_db->autocommit($option);
	}

	public function commit()
	{
		$this->_db->commit();
	}

	public function rollback()
	{
		$this->_db->rollback();
	}

	/**
	 *	Build query raw
	 */
	public function raw($statment)
	{
		return $this->_query($statment);
	}

	/**
	 *	@return string Query statment
	 */
	public function getQueryString()
	{
		return $this->_queryString;
	}

	public function getStatment()
	{
		return $this->_statment;
	}

	public function records()
	{
		return $this->_statment->affected_rows;
	}

	public function insertId()
	{
		return $this->_db->insert_id;
	}

	// After calling a store procedure it's good to call free()
	public function free()
	{
		$this->_statment->free();
	}

	/**
	 *	@return integer
	 */
	public function getTotalRows()
	{
		return $this->_statment->num_rows;
	}

	/**
	 *	@return bool
	 */
	public function rows()
	{

		if ($this->getTotalRows() > 0) {
			return true;
		}

		return false;
	}

	/**
	 *	@return resource
	 */
	public function fetch($type = "default")
	{

		switch ($type) {
			case self::DATA_OBJECT:
				$ret = $this->_statment->fetch_object();
				break;
			case self::DATA_ARRAY:
				$ret = $this->_statment->fetch_array();
				break;
			case self::DATA_ASSOC:
			default:
				$ret = $this->_statment->fetch_assoc();
				break;
		}

		return $ret;
	}

	/**
	 *	@return integer
	 */
	public function getTotalPages()
	{
		return $this->_totalPages;
	}

	/**
	 *	@param $fields array
	 *	@param this
	 */
	public function select(array $fields)
	{
		$this->_fields = $fields;
		return $this;
	}

	/**
	 *	@param $table string
	 *	@param $arguments array
	 *	@param $fields string
	 *	@return resource
	 */
	public function filter(array $arguments = [], $fields = "*")
	{

		if (count($this->_fields) > 0) {
			$fields = $this->_fields;
		}

		if (count($this->_filter) > 0 || count($this->_condition) > 0) {

			if (count($arguments) > 0) {
				$this->_filter['condition'] = $arguments;
				$arguments = $this->_filter;
			} else {
				$arguments = $this->_filter;
				$arguments['condition'] = $this->_condition;
			}

		}

		$cond = (!isset($arguments['condition'])
			&& !isset($arguments['page'])
			&& !isset($arguments['limit'])
			&& !isset($arguments['orderby'])
			&& !isset($arguments['groupby'])
			&& !isset($arguments['having']))
		? ['condition' => $arguments]
		: $arguments;

		$condition = self::_conditions($cond, $this->_db);
		$page = 1;
		$start = 0;

		if (isset($arguments['page']) && isset($arguments['limit'])) {
			$result = $this->_select($this->_table, $condition, $fields );
			$totalRows = $this->getTotalRows();
			$page = $arguments['page'];

			// make sure total of rows is greater than limit
			if ($totalRows > $arguments['limit']) {
				$this->_totalPages = ceil($totalRows / $arguments['limit']);
				// if page is greater than total of pages reset to 1
				if ($page > $this->_totalPages) $page = 1;

				$start = ceil($arguments['limit'] * $page) - $arguments['limit'];
			}

		}

		$args = $condition . self::_aditionalArguments($arguments) . self::_limit($start, $arguments);

		$this->_select($this->_table, $args, $fields);

		return $this;
	}

	public function where(array $condition = [])
	{
		$this->_condition = $condition;
		return $this;
	}

	public function having(array $args)
	{
		$this->_filter['having'] = $args;
		return $this;
	}

	public function groupBy($field)
	{
		$this->_filter['groupby'] = $field;
		return $this;
	}

	public function orderBy($field, $sort = "")
	{

		if (is_array($field) && count($field) > 1 && !empty($sort)) {
			$field = ['fields' => $field, 'sort' => $sort];
		}

		$this->_filter['orderby'] = $field;
		return $this;
	}

	public function paginate($page = 1, $limit = 50)
	{

		$this->_filter['page'] = $page;
		$this->_filter['limit'] = $limit;

		$this->filter();

		return $this;
	}

	public function offset($offset, $limit = 50)
	{
		return $this->paginate(self::getPageFromOffset($offset, $limit), $limit);
	}

	/**
	 *	@param $table string
	 *	@param $condition array
	 *	@param $fields string
	 *	@return integer
	 */
	public function row($args, array $condition = [], $fields = "*")
	{

		if (is_array($condition) && count($condition) > 0) {
			$args = array_merge($args, $condition);
		}

		$this->filter($args, $fields);
		if ($this->rows()) {
			return $this->fetch();
		}

		return [];
	}

	/**
	 *	@param $table string
	 *	@param $data array
	 *	@return bool
	 */
	public function insert(array $data = [])
	{

		if (is_array($data) && count($data) > 0) {

			$fields = [];
			$values = [];

			foreach ($data as $k => $v) {
				if (!self::isInteger($v)) {
					$v = $this->_db->real_escape_string($v);
				}
				$fields[] = $k;
				$values[] = self::_value($v);
			}

			return $this->_insert($this->_table, implode(",",$fields), implode(",",$values) );
		}

		return false;
	}

	/**
	 *	@param $table string
	 *	@param $data array
	 *	@param $arguments array
	 *	@return bool
	 */
	public function insertOnce(array $data = [], array $arguments = [])
	{

		if (is_array($arguments)
		&& count($arguments) > 0
		&& is_array($data)
		&& count($data) > 0
		) {

			$this->filter(['condition' => $arguments]);
			if (!$this->rows()) {
				return $this->insert($data);
			}

		}

		return false;
	}

	/**
	 *	@param $table string
	 *	@param $data array
	 *	@param $arguments array
	 *	@return bool
	 */
	public function update($args, array $data = [], array $condition = [])
	{

		if (is_array($condition) && count($condition) > 0) {
			$args = array_merge($args, $condition);
		}

		if (is_array($args)
		&& count($args) > 0
		&& is_array($data)
		&& count($data) > 0
		) {
			return $this->_update($this->_table, self::_values($data, $this->_db), self::_conditions(['condition' => $args], $this->_db) );
		}

		return false;

	}

	/**
	 *	@param $table string
	 *	@param $arguments array
	 *	@return bool
	 */
	public function delete($args, array $condition = [])
	{

		if (is_array($condition) && count($condition) > 0) {
			$args = array_merge($args, $condition);
		}

		return $this->_delete($this->_table, self::_conditions(['condition' => $args], $this->_db) );
	}

	/**
	 *	@param $table string
	 *	@param $id string
	 *	@param $condition array
	 *	@return integer
	 */
	public function avg($id, array $condition = [])
	{
		$where = $this->_where($condition);
		return $this->_operator("AVG", $this->_table, $id, $where);
	}

	/**
	 *	@param $table string
	 *	@param $id string
	 *	@param $condition array
	 *	@return integer
	 */
	public function max($id, array $condition = [])
	{
		$where = $this->_where($condition);
		return $this->_operator("MAX", $this->_table, $id, $where);
	}

	/**
	 *	@param $table string
	 *	@param $id string
	 *	@param $condition array
	 *	@return integer
	 */
	public function min($id, array $condition = [])
	{
		$where = $this->_where($condition);
		return $this->_operator("MIN", $this->_table, $id, $where);
	}

	/**
	 *	@param $field string
	 *	@param $condition array
	 *	@return integer
	 */
	public function lastId($field, array $condition = [])
	{
		$where = $this->_where($condition);
		return $this->max($field, $where);
	}

	/**
	 *	@param $table string
	 *	@param $id string
	 *	@param $condition array
	 *	@return integer
	 */
	public function sum($id, array $condition = [])
	{
		$where = $this->_where($condition);
		return $this->_operator("SUM", $this->_table, $id, $where);
	}

	/**
	 *	@param $table string
	 *	@param $id string
	 *	@param $condition array
	 *	@return integer
	 */
	public function count($id = "*", array $condition = [])
	{
		$where = $this->_where($condition);
		return $this->_operator("COUNT", $this->_table, $id, $where);
	}

	/**
	 *	@param $table string
	 *	@param $id string
	 *	@param $condition array
	 *	@return resource
	 */
	public function distinct($id, array $condition = [])
	{
		$where = $this->_where($condition);
		return $this->select(["DISTINCT($id)"])->filter($where);
	}

	/**
	 *	@param $table string
	 *	@param $condition array
	 *	@return bool
	 */
	public function results(array $condition = [])
	{
		$where = $this->_where($condition);
		$this->filter($condition);
		return $this->rows();
	}

	/**
	 *	@param $storedProcedureName string
	 *	@param $params array
	 *	@return resource|bool
	 */
	public function storedProcedure($name, array $params = [])
	{

		$values = [];

		foreach ($params as $param) {
			$values[] = self::_field($param);
		}

		$statment = "CALL {$name}(".implode(", ", $values).")";

		return $this->_query($statment);
	}

}
