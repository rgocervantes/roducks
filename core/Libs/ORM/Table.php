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

	private $_raw = [],
			$_foreign = [],
			$_column = [],
			$_columns = [],
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
		$attrs->after = "";
		
		if (is_callable($callback)) {
			$callback($attrs);
		}

		$attrs->append = (!empty($attrs->after)) ? " AFTER `{$attrs->after}`" : "";
		$attrs->empty = ($attrs->nullable) ? 'NULL' : 'NOT NULL';

		return $attrs;
	}

	private function _setField($type, $field, $callback = "")
	{
		$attrs = self::_getAttrs($callback);
		$this->_raw[] = self::_format($field, $type, $attrs->empty, $attrs->comment);
	}

	private function _execute($statment)
	{
		$tx = $this->query($statment);
		DB::transaction($tx);
	}

	private function _alter($cmd, $field, $type, $callback)
	{
		$cmd = strtoupper($cmd);
		$attrs = self::_getAttrs($callback);
		$value = $attrs->value;

		if (!is_null($value)) {

			if (is_array($value)) {
				$values = array_map(function($v){
					return "'{$v}'";
				}, $value);
				$value = implode(",", $values);
			}

			$type = "{$type}({$value})";
		}

		if (!is_null($attrs->default)) {
			$type = "{$type} DEFAULT '{$attrs->default}'";
		}

		$this->_columns[] = "ALTER TABLE `{$this->_table}` {$cmd} `{$field}` {$type} {$attrs->empty}{$attrs->append}";
	}

	public function create()
	{
		$statment = "CREATE TABLE IF NOT EXISTS `{$this->_table}` (";
		$this->_raw[] = "PRIMARY KEY (`{$this->_pk}`)";
		$this->_raw = array_merge($this->_raw, $this->_foreign);
		$statment .= implode(', ', $this->_raw);
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
		$statment = "";
		$this->_execute("SET FOREIGN_KEY_CHECKS = 0");

		foreach ($tables as $table) {
			if (!empty($table)) {
				$statment .= "TRUNCATE TABLE `{$table}`";
			}
		}

		$this->_execute($statment);
		$this->_execute("SET FOREIGN_KEY_CHECKS = 1");
	}

	public function column($key, $value)
	{
		$this->_column[$key] = $value;
	}

	public function values(callable $callback)
	{
		$callback();

		$this->_columns[] = $this->_column;
		$this->_column = [];

	}

	public function getColumns()
	{

		if (count($this->_columns) == 0 && count($this->_column) > 0) {
			$this->_columns[] = $this->_column;
		}

		return $this->_columns;
	}

	public function alter()
	{
		foreach ($this->_columns as $statment) {
			if (DB::success()) {
				$this->_execute($statment);
			}
		}
	}

	public function modify($field, $type, $callback = "")
	{
		$this->_alter(__FUNCTION__, $field, $type, $callback);
	}

	public function add($field, $type, $callback = "")
	{
		$this->_alter(__FUNCTION__, $field, $type, $callback);
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

		$this->_raw[] = self::_format($field, "enum(".implode(",", $value).")", $attrs->empty, $attrs->comment);
	}

	public function decimal($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 255);
		$this->_raw[] = self::_format($field, "decimal(".implode(",", $attrs->value).")", $attrs->empty, $attrs->comment);
	}

	public function bigint8($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback);
		$this->_raw[] = self::_format($field, 'bigint(8)', $attrs->empty, $attrs->comment);
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
		$this->_raw[] = self::_format($field, "{$dataType} AUTO_INCREMENT", 'NOT NULL');
	}

	public function varchar($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 255);
		$this->_raw[] = self::_format($field, "varchar({$attrs->value})", $attrs->empty, $attrs->comment);
	}

	public function int($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 11);
		$this->_raw[] = self::_format($field, "int({$attrs->value})", $attrs->empty, $attrs->comment);
	}

	public function float($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 2);
		$this->_raw[] = self::_format($field, "float({$attrs->value})", $attrs->empty, $attrs->comment);
	}

	public function tinyint($field, $callback = "")
	{
		$attrs = self::_getAttrs($callback, 1, 0);
		$this->_raw[] = self::_format($field, "tinyint({$attrs->value})", "DEFAULT '{$attrs->default}'", $attrs->comment);
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