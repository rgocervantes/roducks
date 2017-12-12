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

namespace rdks\core\libs\ORM;

/*

@usage:

	$db = $this->db();

	//-----------------
	// EXAMPLE # 1
	//-----------------

	$query = new Query($db);
	$query->statment("SELECT * FROM Users");

	if($query->rows()): while($row = $query->fetch()):
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

	if($query->rows()): while($row = $query->fetch()):
		// $row
	endwhile; endif;	

	//-----------------
	// EXAMPLE # 3
	//-----------------
	// PARENTHESIS 

		$filter['[BEGIN_COND]'] = "(";
			$filter['[COND_AND]u.id_user_parent:>'] = Login::getAdminData('id_user_parent');
			$filter['[COND_OR]u.id_role:>'] = Login::getAdminData('id_role');
		$filter['[END_COND]'] = ")";	

*/

class Query {

	const DATA_OBJECT = "object";
	const DATA_ARRAY = "array";
	const DATA_ASSOC = "assoc";
	const NOW = "NOW()";

	private $_db,	
		$_statment, 
		$_connection = null, 
		$_totalPages = 1,
		$_filter = [],
		$_queryString = "",
		$_table = "";	

/*
//----------------------
//		STATIC
//----------------------	
*/

	static function freeResult($statment){
		\mysqli_free_result($statment);
	}

	static function numFields($statment){
		return \mysqli_num_fields($statment);
	}

	static function numRows($statment){
		return \mysqli_num_rows($statment);
	}

	static function fetchAssoc($statment){
		return \mysqli_fetch_assoc($statment);
	}

	static function fetchObject($statment){
		return \mysqli_fetch_object($statment);
	}

	static function fetchArray($statment){
		return \mysqli_fetch_array($statment);
	}		

	static function isInteger($value){
		return preg_match('/^\d+$/', $value);
	}

	static private function _dataType($field, $value){

		$ret = "";

		// allows join fields as well ex. -> ab.publication:date
		if(preg_match('/^([a-zA-Z0-9_\.]+):(.+)$/i', $field, $match)){

			switch ($match[2]) {
					case 'string':
						$ret .= $match[1] . " = '{$value}'";
						break;
					case 'concat':
						if(is_array($value)){
							$values = [];
							foreach ($value as $v) {
								$values[] = ($v == " ") ? self::_field($v) : $v;
							}
							$ret .= "CONCAT(".$match[1] . "," . implode(",", $values) . ")";
						}
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
		}else{
			$ret = $field . " = " . self::_field($value);


			switch ($field) {
				case '[BEGIN_COND_AND]':
					$ret = " AND $value ";
					break;
				case '[BEGIN_COND_OR]':
					$ret = " OR $value ";
					break;					
				case '[BEGIN_COND]':
					$ret = " $value ";
					break;
			}

		}

		return $ret;

	}

	static private function _field($value){

		$ret = "";

		if(!is_array($value)){
			$ret = (self::isInteger($value)) ? intval($value) : "'{$value}'";
		}

		return $ret;
	}

	static private function _operatorCond($field, $value){

		if($field == '[END_COND]'){
			return [" {$value} ", ""];
		}
		
		$operator = " AND "; // default

		if($field == '[BEGIN_COND_AND]' || $field == '[BEGIN_COND_OR]'){
			$operator = "";
		}

		if(preg_match('/^\[(\w+)\](.+)$/i', $field, $match)){
			$op = strtoupper($match[1]);
			$field = $match[2];
			switch ($op) {
				case 'OR':
				case 'AND':
					$operator = " {$op} ";
					break;
				case 'COND_OR':
					$operator = " OR ";
					break;					
				case 'COND_AND':
					$operator = "";
					break;	

			}
		}

		return array(self::_dataType($field,$value), $operator);
	}

	static private function _conditions(array $arguments = [], $db){

		if(!isset($arguments['condition'])){
			return "";
		}

		$i = 0;
		$ret = "";

		if(isset($arguments['condition']) && is_array($arguments['condition'])): foreach($arguments['condition'] as $k => $v): $i++;
			if(!is_array($v) && !self::isInteger($v)){
				$v = $db->real_escape_string($v);
			} 

			list($field, $operator) = self::_operatorCond($k,$v);
			$ret .= ($i == 1) ? " WHERE " : $operator;
			$ret .= $field;
		endforeach; endif;

		return $ret;
	}

	static private function _value($v){
		if($v == 'NOW()'){
			return $v;
		}

		return "'{$v}'";
	}

	static private function _values($values, $db){

		$i = 0;
		$ret = [];

		foreach($values as $k => $v){ $i++;
			if(!self::isInteger($v)){
				$v = $db->real_escape_string($v);
			}
			$v = self::_value($v);
			$ret[] = "{$k} = {$v}";
		}

		return implode(", ",$ret);
	}	

	static private function _aditionalArguments($args){

		$ret = "";

		if(isset($args['groupby'])){
			$ret .= " GROUP BY ".$args['groupby'];
		}

		if(isset($args['having'])){
			$having = array_keys($args['having'])[0];
			$ret .= " HAVING ". self::_dataType($having,$args['having'][$having]);
		}			

		if(isset($args['orderby'])){
			$orderby = array_keys($args['orderby'])[0];
			$ret .= " ORDER BY ".$orderby." ".strtoupper($args['orderby'][$orderby]);
		}

		return $ret;

	}

	static private function _limit($start, $args){

		if(isset($args['limit'])){
			return " LIMIT {$start},".$args['limit'];
		}	

		return "";
	}

	static private function _prepareFields($fields){
		
		if(is_array($fields)){
			return implode(", ", $fields);
		}

		return $fields;
	}

	static function alias($field, $alias){
		return "{$field} AS {$alias}";
	}

	static function blob($field){
		return self::alias("CONVERT({$field} using utf8)", $field);
	}

	static function concat($field, array $fields = []){
		$values = [];
		foreach ($fields as $value) {
			$values[] = (preg_match('/^[\s\-_\.+,;:]+$/', $value)) ? self::_field($value) : $value;
		}
		return self::alias("CONCAT(". implode(",", $values) .")", $field);
	}

	static function field($field, $alias = ""){

		if(!empty($alias)){
			return self::alias($field, $alias);
		}

		return $field;
	}

/*
//----------------------
//		PRIVATE
//----------------------	
*/

	private function _query($statment){
		$this->_queryString = $statment;
		$this->_statment = $this->_db->query($this->_queryString, $this->_connection);
		return $this->_statment;
	}

	private function _join($table, $condition, $fields){

		$wildcard = [];
		$text = "";
		$c = 0;

		foreach ($table as $key => $value) {

			$wildcard[] = $key . ".*";
			if(isset($value['left_join'])){
				$join = "LEFT JOIN";
				$type = "left_join";
			} else if(isset($value['right_join'])){
				$join = "RIGHT JOIN";
				$type = "right_join";
			} else {
				$join = "INNER JOIN";
				$type = "join";
			}

			if($c == 0){
				$text .= $value['table'] . " AS " . $key . " {$join} ";
			}else{
				if($c > 1) $text .= " {$join} ";
				$text .= $value['table'] . " AS " . $key . " ";
				
				if(isset($value[$type])){
					$skey = array_keys($value[$type])[0];
					$text .= "ON " . $skey . " = " . $value[$type][$skey];
				} 			
			}		
			$c++;
		}

		$fields = ( (is_array($fields)) && (count($fields) > 0) ) ? $fields : $wildcard;

		return $this->_single($text, $condition, $fields);
	}

	private function _single($table, $condition, $fields){
		$this->_queryString = "SELECT ".self::_prepareFields($fields)." FROM {$table}{$condition}";
		return $this->_queryString;
	}

	private function _select($table, $condition, $fields){

		if(is_array($table)){
			$statment = $this->_join($table, $condition, $fields);
		}else{
			$statment = $this->_single($table, $condition, $fields);
		}

		return $this->_query($statment);
	}

	private function _insert($table, $fields, $values){
		$statment = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
		return $this->_query($statment);
	}

	private function _update($table, $values, $condition){
		$statment = "UPDATE {$table} SET {$values}{$condition}";
		return $this->_query($statment);
	}	

	private function _delete($table, $condition){
		$statment = "DELETE FROM {$table}{$condition}";
		return $this->_query($statment);
	}

	private function _operator($operator, $table, $id, array $arguments = []){
		$this->filter($arguments, ["{$operator}($id) AS total"]);
		$row = $this->fetch();
		return $row['total'];
	}	

/*
//----------------------
//		PUBLIC
//----------------------	
*/

	public function __construct(\mysqli $mysqli, $table = ""){
		$this->_db = $mysqli;
		$this->_table = $table;
	}

	/**
	 *
	 */
	public function statment($statment){
		return $this->_query($statment);
	}

	/**
	 *	@return string Query statment
	 */
	public function getQueryString(){
		return $this->_queryString;
	}

	public function getStatment(){
		return $this->_statment;
	}
	
	public function records(){
		return $this->_statment->affected_rows;
	}

	public function insertId(){
		return $this->_db->insert_id;
	}	

	// After calling a store procedure it's good to call free()
	public function free(){
		$this->_statment->free();
	}	

	/**	
	 *	@return integer
	 */
	public function getTotalRows(){
		return $this->_statment->num_rows;
	}

	/**	
	 *	@return bool
	 */
	public function rows(){

		if($this->getTotalRows() > 0){
			return true;
		}

		return false;
	}

	/**	
	 *	@return resource
	 */
	public function fetch($type = "default"){
		
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
	public function getTotalPages(){
		return $this->_totalPages;
	}

	/**
	 *	@param $table string
	 *	@param $arguments array	
	 *	@param $fields string		
	 *	@return resource
	 */
	public function filter(array $arguments = [], $fields = "*"){

		if(count($this->_filter) > 0) {
			if(!isset($arguments['condition'])) {
				$arguments['condition'] = $arguments;
				$arguments = array_merge($arguments, $this->_filter);
			} else {
				$arguments = $this->_filter;
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

		if(isset($arguments['page']) && isset($arguments['limit'])){
			$result = $this->_select($this->_table, $condition, $fields );
			$totalRows = $this->getTotalRows();
			$page = $arguments['page'];

			// make sure total of rows is greater than limit
			if($totalRows > $arguments['limit']){
				$this->_totalPages = ceil($totalRows / $arguments['limit']);
				// if page is greater than total of pages reset to 1
				if($page > $this->_totalPages) $page = 1;

				$start = ceil($arguments['limit'] * $page) - $arguments['limit'];	
			}

		}

		$args = $condition . self::_aditionalArguments($arguments) . self::_limit($start, $arguments);	

		$this->_select($this->_table, $args, $fields);

		return $this;
	}

	public function having(array $args){
		$this->_filter['having'] = $args;
		return $this;
	}

	public function groupby($field){
		$this->_filter['groupby'] = $field;
		return $this;		
	}

	public function pagination(array $condition, array $orderby, $page, $limit, $fields = "*"){

		$this->_filter['condition'] = $condition;
		$this->_filter['orderby'] = $orderby;
		$this->_filter['page'] = $page;
		$this->_filter['limit'] = $limit;									

		$this->filter($this->_filter, $fields);

		return $this;
	}

	/**
	 *	@param $table string
	 *	@param $condition array
	 *	@param $fields string
	 *	@return integer
	 */
	public function row($args, array $condition = [], $fields = "*"){

		if(is_array($condition) && count($condition) > 0){
			$args = array_merge($args, $condition);
		}

		$this->filter($args, $fields);
		if($this->rows()){
			return $this->fetch();
		}	

		return [];
	}

	/**
	 *	@param $table string
	 *	@param $data array
	 *	@return bool
	 */
	public function insert(array $data = []){

		if(is_array($data) && count($data) > 0){

			$fields = [];
			$values = [];

			foreach($data as $k => $v){
				if(!self::isInteger($v)){
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
	public function insertOnce(array $data = [], array $arguments = []){

		if(is_array($arguments) 
		&& count($arguments) > 0 
		&& is_array($data) 
		&& count($data) > 0
		){
			
			$this->filter(['condition' => $arguments]);
			if(!$this->rows()){
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
	public function update($args, array $data = [], array $condition = []){

		if(is_array($condition) && count($condition) > 0){
			$args = array_merge($args, $condition);
		}

		if(is_array($args) 
		&& count($args) > 0 
		&& is_array($data) 
		&& count($data) > 0
		){
			return $this->_update($this->_table, self::_values($data, $this->_db), self::_conditions(['condition' => $args], $this->_db) );
		}
		
		return false;

	}

	/**
	 *	@param $table string
	 *	@param $arguments array	
	 *	@return bool
	 */
	public function delete($args, array $condition = []){

		if(is_array($condition) && count($condition) > 0){
			$args = array_merge($args, $condition);
		}

		return $this->_delete($this->_table, self::_conditions(['condition' => $args], $this->_db) );
	}

	/**
	 *	@param $table string
	 *	@param $id string	
	 *	@param $arguments array	
	 *	@return integer
	 */
	public function avg($id, array $condition = []){
		return $this->_operator("AVG", $this->_table, $id, $condition);
	}

	/**
	 *	@param $table string
	 *	@param $id string	
	 *	@param $arguments array	
	 *	@return integer
	 */
	public function max($id, array $condition = []){
		return $this->_operator("MAX", $this->_table, $id, $condition);
	}

	/**
	 *	@param $table string
	 *	@param $id string	
	 *	@param $arguments array	
	 *	@return integer
	 */
	public function min($id, array $condition = []){
		return $this->_operator("MIN", $this->_table, $id, $condition);
	}	

	/**
	 *	@param $field string
	 *	@param $arguments array	
	 *	@return integer
	 */
	public function lastId($field, array $condition = []){
		return $this->max($field, ['condition' => $condition]);
	} 

	/**
	 *	@param $table string
	 *	@param $id string	
	 *	@param $arguments array	
	 *	@return integer
	 */
	public function sum($id, array $condition = []){
		return $this->_operator("SUM", $this->_table, $id, $condition);
	}

	/**
	 *	@param $table string
	 *	@param $id string	
	 *	@param $arguments array	
	 *	@return integer
	 */
	public function total($id, array $condition = []){
		return $this->_operator("TOTAL", $this->_table, $id, $condition);
	}	

	/**
	 *	@param $table string
	 *	@param $id string	
	 *	@param $arguments array	
	 *	@return integer
	 */
	public function count($id, array $condition = []){
		return $this->_operator("COUNT", $this->_table, $id, $condition);
	}			

	/**
	 *	@param $table string
	 *	@param $id string	
	 *	@param $arguments array	
	 *	@return resource
	 */
	public function distinct($id, array $condition = []){
		return $this->filter($condition, ["DISTINCT($id)"]);
	}

	/**
	 *	@param $table string
	 *	@param $arguments array
	 *	@return bool
	 */
	public function results(array $arguments = []){	
		$this->filter($arguments);
		return $this->rows();	
	}

	/**
	 *	@param $storedProcedureName string
	 *	@param $params array
	 *	@return resource|bool
	 */
	public function callSP($name, array $params = []){

		$values = [];

		foreach($params as $param){
			$values[] = self::_field($param);	
		}

		$statment = "CALL {$name}(".implode(", ", $values).")";

		return $this->_query($statment);
	}

}

