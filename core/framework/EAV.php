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

namespace rdks\core\framework;

use rdks\core\framework\Helper;
use rdks\core\page\Frame;
use rdks\core\libs\Utils\Date;
use rdks\app\models\Data\EAV as EAVTable;

class EAV extends Frame{

	protected $_id;
	protected $_entity;
	protected $_pageType = 'DATA';

	static function init($settings = ""){
		$class = get_called_class();
		$obj = new $class($settings);

		return $obj;
	}	

	public function __construct($settings = ""){
		parent::__construct();
		$this->_entity = Helper::getTable($this->_entity);
	}

	private function _update($id, $value, $field){
		$set = "set" . ucfirst($field);
		$db = $this->db();
		$data = EAVTable::open($db)->load($id);
		$data->$set($value);
		$data->setUpdatedDate(Date::getCurrentDateTime());
		$data->where(['entity' => $this->_entity]);	
		
		return $data->update();
	}

	public function add($key, $value, $rewrite = false){
	
		$db = $this->db();
		$data = EAVTable::open($db)->prepare();
		$data->setIdRel($this->_id);
		$data->setEntity($this->_entity);
		$data->setField($key);
		$data->setText($value);
		$data->setCreatedDate(Date::getCurrentDateTime());
		$data->setUpdatedDate(Date::getCurrentDateTime());		

		if($rewrite){
			return $data->insert();
		} else {
			return $data->insertOnce([
				'id_rel' => $this->_id, 
				'entity' => $this->_entity, 
				'field' => $key
			]);
		}

	}

	public function addOnce($key, $value){
		$db = $this->db();
		$data = EAVTable::open($db);
		$data->filter([
			'id_rel' => $this->_id, 
			'entity' => $this->_entity, 
			'field' => $key,		
			'text' => $value
		]);

		if(!$data->rows()){
			return $this->add($key, $value, true);
		}

		return false;
	}	

	public function update($id, $value){
		return $this->_update($id, $value, "text");
	}

	public function active($id, $value){
		return $this->_update($id, $value, "active");
	}	

	public function remove($id){
		$db = $this->db();
		$data = EAVTable::open($db)->load($id);
		$data->delete();
	}	

	public function getRows($field = null, $page = 1, $limit = 15, array $cond = []){
		$ret = [];
		$output = ['pages' => 1, 'data' => $ret];

		$filters = [
			'id_rel'	 => $this->_id,
			'entity' 	 => $this->_entity,
			'field' 	 => $field,
			'active' 	 => 1
		];

		if(count($cond) > 0){
			$filters = array_merge($filters, $cond);
		}

		if(is_null($field)){
			unset($filters['field']);
		}

		$fields = [
			EAVTable::field("id_index"),
			EAVTable::field("field"),
			EAVTable::field("text")
		];

		$db = $this->db();
		$data = EAVTable::open($db);
		$data->pagination($filters, ['id_index' => "desc"], $page, $limit, $fields);

		if($data->rows()){

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

	public function get($field){
		$data = $this->getRows();

		return $data['data'];
	}

	public function getAll(){
		return $this->get(null);
	}


}