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

class Table extends Query
{

	private $_columns = [],
			$_foreign = [],
			$pk = "";

	static function _format($field, $dataType, $value = "NULL", $comment = "")
	{
		$value = strtoupper($value);
		$notes = (!empty($comment)) ? " COMMENT '{$comment}'" : "";
		return "`{$field}` {$dataType} {$value}{$notes}";
	}

	static private function _getAttrs($callback, $value = null, $default = null)
	{

		$attrs = new \StdClass;
		$attrs->value = $value;
		$attrs->default = $default;
		$attrs->comment = "";
		$attrs->nullable = true;
		
		if (is_callable($callback)) {
			$callback($attrs);
		}

		$attrs->empty = ($attrs->nullable) ? 'NULL' : 'NOT NULL';

		return $attrs;
	}

	private function _setField($type, $field, $callback = "")
	{
		$attrs = self::_getAttrs($callback);
		$this->_columns[] = self::_format($field, $type, $attrs->empty, $attrs->comment);
	}

	private function _execute($statment)
	{
		$tx = $this->query($statment);
		DB::transaction($tx);
	}

	public function create()
	{
		$statment = "CREATE TABLE IF NOT EXISTS `{$this->_table}` (";
		$this->_columns[] = "PRIMARY KEY (`{$this->_pk}`)";
		$this->_columns = array_merge($this->_columns, $this->_foreign);
		$statment .= implode(', ', $this->_columns);
		$statment .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8";

		$this->_execute($statment);
	}

	public function drop()
	{
		$statment = "DROP TABLE IF EXISTS `{$this->_table}`";
		$this->_execute($statment);
	}

	public function truncate(array $tables = [])
	{
		$statment = "SET FOREIGN_KEY_CHECKS=0";

		foreach ($tables as $table) {
			if (!empty($table)) {
				$statment .= "TRUNCATE TABLE `{$table}`";
			}
		}

		$statment .= "SET FOREIGN_KEY_CHECKS=1";
		$this->_execute($statment);
	}

	public function datetime($field, $callback = "")
	{
		$this->_setField(__FUNCTION__, $field, $callback);
	}

	public function date($field, $callback = "")
	{
		$this->_setField(__FUNCTION__, $field, $callback);
	}

	public function blob($field, $callback = "")
	{
		$this->_setField(__FUNCTION__, $field, $callback);
	}

	public function text($field, $callback = "")
	{
		$this->_setField(__FUNCTION__, $field, $callback);
	}

	public function enum($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 255);
		$value = array_map(function($v){
			return "'{$v}'";
		}, $attrs->value);

		$this->_columns[] = self::_format($field, "enum(".implode(",", $value).")", $attrs->empty, $attrs->comment);
	}

	public function decimal($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 255);
		$this->_columns[] = self::_format($field, "decimal(".implode(",", $attrs->value).")", $attrs->empty, $attrs->comment);
	}

	public function bigint8($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback);
		$this->_columns[] = self::_format($field, 'bigint(8)', $attrs->empty, $attrs->comment);
	}

	public function timestamps()
	{
		$this->datetime('created_at', function ($attrs) {
			$attrs->nullable = false;
		});
		$this->datetime('updated_at', function ($attrs) {
			$attrs->nullable = false;
		});
	}

	public function id($field = "id", $dataType = "bigint(8)")
	{
		$this->_pk = $field;
		$this->_columns[] = self::_format($field, "{$dataType} AUTO_INCREMENT", 'NOT NULL');
	}

	public function varchar($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 255);
		$this->_columns[] = self::_format($field, "varchar({$attrs->value})", $attrs->empty, $attrs->comment);
	}

	public function int($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 11);
		$this->_columns[] = self::_format($field, "int({$attrs->value})", $attrs->empty, $attrs->comment);
	}

	public function float($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 2);
		$this->_columns[] = self::_format($field, "float({$attrs->value})", $attrs->empty, $attrs->comment);
	}

	public function tinyint($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 1, 0);
		$this->_columns[] = self::_format($field, "tinyint({$attrs->value})", "DEFAULT '{$attrs->default}'", $attrs->comment);
	}

	public function foreignKey($fk, $references, $key)
	{
		$this->_foreign[] = "CONSTRAINT `{$fk}` FOREIGN KEY (`{$fk}`) REFERENCES `{$references}` (`{$key}`) ON DELETE CASCADE ON UPDATE CASCADE";
	}

	public function uniqueIndex($field)
	{
		$this->_foreign[] = "UNIQUE INDEX `idx_{$field}` (`{$field}`)";
	}

	public function index($field, $index = "")
	{
		$idx = (!empty($index)) ? $index : "idx_{$field}";
		$this->_foreign[] = "INDEX `{$idx}` (`{$field}`)";
	}

}