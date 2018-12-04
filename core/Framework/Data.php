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
use Roducks\Libs\Output\XML as XMLDoc;
use Roducks\Libs\Files\Directory;

abstract class Data extends XML
{

	var $rdks = 1;

	protected $_pageType = 'DATA';

	static function init($id, $date = "")
	{
		$class = get_called_class();

		$settings = [];
		$settings['className'] = $class;
		$settings['filePath'] = '';
		$settings['fileName'] = '';
		$settings['urlParam'] = '';
		$settings['id'] = $id;
		$settings['method'] = 'write';
		$settings['url_dispatcher'] = false;

		if (!empty($date)) {
			$settings['date'] = $date;
		}

		$obj = new $class($settings);

		return $obj;
	}

	private function _node($key)
	{

		$field = $this->xml->createNode([
				'name' => $key
		]);

		return $field;
	}

	private function _field($key, $value)
	{

		$field = $this->xml->createNode([
				'name' => $key,
				'cdata' => $value
		]);

		return $field;
	}

	public function add($key, $value, $rewrite = false)
	{

		$field = $this->get($key);

		if (!empty($field) && !$rewrite) {
			return;
		}

		if (is_array($value)) {

			$field = $this->_node($key);

			foreach ($value as $k => $v) {
				$f = $this->_field($k, $v);
				$field->appendChild($f);
			}
		} else {
			$field = $this->_field($key, $value);
		}

		$this->xml->appendChild($field);
		$this->xml->save();

	}

	public function getAll()
	{

		if (!Path::exists($this->name)) {
			return [];
		}

		$this->xml->file($this->name);

		return $this->xml->content()->children();
	}

	public function get($field)
	{
		$content = $this->getAll();

		if (is_array($content) && count($content) == 0) {
			return "";
		}

		return $content->$field->__toString();
	}

	public function getContent()
	{
		return XMLDoc::parse($this->fileName)->content()->children();
	}

}
