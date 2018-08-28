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

use Roducks\Page\Frame;
use DB\Models\SEO\EAV as EAVTable;

class EAV extends Frame
{

	protected $_id;
	protected $_entity;
	protected $_pageType = 'DATA';

	static function init($settings = "")
	{
		$class = get_called_class();
		$obj = new $class($settings);

		return $obj;
	}

	public function __construct($settings = "")
	{
		parent::__construct();
		$this->_entity = Helper::getTable($this->_entity);
	}

	private function _update($id, $value, $field)
	{
		$set = "set" . ucfirst($field);
		$db = $this->db();
		$data = EAVTable::open($db)->getRow($id);
		$data->$set($value);
		$data->setUpdatedAt(EAVTable::now());
		$data->where(['entity' => $this->_entity]);

		return $data->update();
	}

	private function _getRows($paginate = true, $field = null, $orderBy = "desc", $page = 1, $limit = 15, array $cond = [])
	{
		$ret = [];
		$output = ['pages' => 1, 'data' => $ret];

		$filters = [
			'id_rel'	 => $this->_id,
			'entity' 	 => $this->_entity,
			'field' 	 => $field,
			'active' 	 => 1
		];

		if (count($cond) > 0) {
			$filters = array_merge($filters, $cond);
		}

		$db = $this->db();
		$data = EAVTable::open($db);

		$fields = [
			EAVTable::field("id_index"),
			EAVTable::field("field"),
			EAVTable::field("text")
		];

		if (is_null($field)) {
			unset($filters['field']);
		}

		$data
		->select($fields)
		->where($filters)
		->orderBy(['id_index' => $orderBy]);

		if ($paginate) {

			$data->paginate($page, $limit);

		} else {

			$data->filter();

		}

		if ($data->rows()) {

			while ($row = $data->fetch()) {
				$ret[] = [
					'id' => $row['id_index'],
					$row['field'] => $row['text']
				];
			}

			return ['pages' => $data->getTotalPages(), 'data' => $ret];
		}

		return $output;
	}

	public function add($key, $value, $rewrite = true)
	{

		$db = $this->db();
		$data = EAVTable::open($db)->prepare();
		$data->setIdRel($this->_id);
		$data->setEntity($this->_entity);
		$data->setField($key);
		$data->setText($value);
		$data->setCreatedAt(EAVTable::now());
		$data->setUpdatedAt(EAVTable::now());

		if ($rewrite) {
			return $data->insert();
		} else {
			return $data->insertOnce([
				'id_rel' => $this->_id,
				'entity' => $this->_entity,
				'field' => $key
			]);
		}

	}

	public function unique($key, $value)
	{
		$db = $this->db();
		$data = EAVTable::open($db);
		$data->filter([
			'id_rel' => $this->_id,
			'entity' => $this->_entity,
			'field' => $key,
			'text' => $value
		]);

		if (!$data->rows()) {
			return $this->add($key, $value);
		}

		return false;
	}

	public function update($id, $value)
	{
		return $this->_update($id, $value, "text");
	}

	public function active($id, $value)
	{
		return $this->_update($id, $value, "active");
	}

	public function remove($id)
	{
		$db = $this->db();
		$data = EAVTable::open($db)->getRow($id);
		$data->delete();
	}

	public function getRows($field = null, $orderBy = "desc", $page = 1, $limit = 15, array $cond = [])
	{
		return $this->_getRows(true, $field, $orderBy, $page, $limit, $cond);
	}

	public function get($field, $orderBy = "desc")
	{
		$data = $this->_getRows(false, $field, $orderBy);

		return $data['data'];
	}

	public function getAll($orderBy = "desc")
	{
		return $this->get(null, $orderBy);
	}

}
