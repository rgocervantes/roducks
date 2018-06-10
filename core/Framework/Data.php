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

namespace Roducks\Framework;

use Roducks\Page\XML;
use Roducks\Libs\Files\Directory;

abstract class Data extends XML
{

	private $_xmlFile;
	protected $_fileName;
	protected $_filePath;
	protected $_dirStorage;
	protected $_pageType = 'DATA';
	protected $_makeDir = true;

	static function init($settings)
	{
		$class = get_called_class();
		$obj = new $class($settings);

		return $obj;
	}	

	private function _node($key)
	{

		$field = $this->doc->createNode([
				'name' => $key
		]);		

		return $field;
	}	

	private function _field($key, $value)
	{
		
		$field = $this->doc->createNode([
				'name' => $key,
				'cdata' => $value
		]);				

		return $field;
	}

	public function __construct()
	{
			
		$this->_dirStorage = DIR_DATA_STORAGE_XML;	
		$dir = $this->_dirStorage . $this->_filePath;
		if ($this->_makeDir) Directory::make(Path::get(), $dir);
		$this->_xmlFile = $dir . Helper::ext($this->_fileName, "xml");

		parent::__construct();
	}

	public function add($key, $value, $rewrite = false)
	{

		$field = $this->get($key);

		if (!empty($field) && !$rewrite) {
			return;
		}

		$this->doc->file($this->_xmlFile);
		$this->doc->namespaceRootAtom();
		$this->doc->root("data");

		if (is_array($value)) {

			$field = $this->_node($key);

			foreach ($value as $k => $v) {
				$f = $this->_field($k, $v);
				$field->appendChild($f);
			}
		} else {
			$field = $this->_field($key, $value);
		}

		$this->doc->appendChild($field);
		$this->doc->save();

	}

	public function getAll()
	{

		if (!file_exists($this->_xmlFile)) {
			return [];
		}
		
		$this->doc->file($this->_xmlFile);

		return $this->doc->content()->children();
	}

	public function get($field)
	{
		$content = $this->getAll();

		if (is_array($content) && count($content) == 0) {
			return "";
		}

		return $content->$field->__toString();
	}

}