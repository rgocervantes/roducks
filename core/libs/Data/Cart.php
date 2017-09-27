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

namespace rdks\core\libs\Data;

class Cart{

	const TYPE_AMOUNT = 'amount';
	const TYPE_PERCENTAGE = 'percentage';

	private $_cart; // Stores cart's name
	private $_subtotal = 0;
	private $_total = 0;
	private $_lang; 

//-------------------------------
//	STATIC 
//-------------------------------
	static public function init($name, $lang = "en"){
		$ins = new Cart($name, $lang);
		return $ins;
	}

	/*
	*	Return price format with symbol & currency
	*/
	static public function getPriceFormat($v, $c = 'USD', $s = '$'){
		return $s . number_format($v,2,'.',',') . ' ' . $c;
	}

	/*
	*	Calculate subtotal by price, quantity and attributes
	*/
	static public function getItemSubtotal($lang, $price, $qty, $attrs, $groupedProducts){
		$attrsValue = 0;
		$groupedValue = 0;
		
		foreach($attrs as $a):
			if($a['price'][$lang] > 0):
				$attrsValue += ($a['price'][$lang] * $qty);
			endif;
		endforeach;

		foreach($groupedProducts as $g):
			if($g['price'][$lang] > 0):
				$groupedValue += ($g['price'][$lang] * $qty);
			endif;

			if(isset($g['attributes'])):
				foreach ($g['attributes'] as $gpa):
					if($gpa['price'][$lang] > 0):
						$groupedValue += ($gpa['price'][$lang] * $qty);
					endif;	
				endforeach;
			endif;

		endforeach;

		return ($qty * $price) + $attrsValue + $groupedValue;
	}

	static public function getItemFormat($lang, $item){
		return [
			'index' => $item['index'],
			'qty' => $item['qty'],
			'price' => $item['price'][$lang],
			'subtotal' => self::getItemSubtotal($lang, $item['price'][$lang], $item['qty'], $item['attributes'], $item['grouped_products']),
			'data' => $item['data'],
			'attributes' => $item['attributes'],
			'grouped_products' => $item['grouped_products']
		];
	}

	static public function getPercentageValue($subtotal, $per){

		$div = ($per / 100);
		$value = ($subtotal * $div);

		return $value;
	}

//-------------------------------
//	PRIVATE 
//-------------------------------
	private function _setData($index, $data){
		if(!Session::exists($this->_cart)):
			Session::set($this->_cart, [$index => $data]);
		else:
			$session = Session::get($this->_cart);
			$session[$index] = $data;
			Session::set($this->_cart, $session);
		endif;	
	}

	private function _getData($index){

		$ret = [];

		if(Session::exists($this->_cart)):
			$data = Session::get($this->_cart);
			if(isset($data[$index])):
				$ret = $data[$index];
			endif;
		endif;

		return $ret;
	}	

	private function _format($index, $obj, $qty){
		$data = [
			'index' 			=> $index,
			'id' 				=> $obj['id'],
			'qty' 				=> $qty,
			'price' 			=> $obj['price'],
			'data' 				=> $obj['data'],
			'attributes' 		=> [],
			'grouped_products' 	=> []
		];

		if(isset($obj['attributes'])){
			$data['attributes'] = $obj['attributes'];
		}

		if(isset($obj['grouped_products'])){
			$data['grouped_products'] = $obj['grouped_products'];
		}

		return $data;
	}

	private function _attributes($attrs){

		$values = [];
		foreach($attrs as $obj):
			$values[] = $obj['id'];
		endforeach;	

		return $values;
	}

	private function _setId($id, $keys){
		
		$ext = "";

		if(count($keys) > 0) $ext = "_" . implode("_", $keys);

		return 'item_' . $id . $ext;
	}

	private function _refresh($items){
		$this->_setData('items', $items);
	}

	private function _percentage($per){
		$subtotal = $this->getSubtotal();
		return self::getPercentageValue($subtotal, $per);
	}

	private function _totals(){

		if($this->hasItems()):
			$this->_subtotal = 0;
			$this->_total = 0;
			$lang = $this->_lang;

			$items = $this->getData();
			$charges = $this->_getData('charges'); 
			$discounts = $this->_getData('discounts');
				
			foreach ($items as $key => $stored):
				$attrs = 0;
				$grouped = 0;

				if(!empty($stored['attributes'])):
					foreach ($stored['attributes'] as $a):
						if($a['price'][$lang] > 0):
							$attrs += ($a['price'][$lang] * $stored['qty']);
						endif;	
					endforeach;
				endif;	

				if(!empty($stored['grouped_products'])):
					foreach ($stored['grouped_products'] as $gp):
						if($gp['price'][$lang] > 0):
							$grouped += (($gp['price'][$lang] * $gp['qty']) * $stored['qty']);
						endif;	

						if(isset($gp['attributes'])):
							foreach ($gp['attributes'] as $gpa):
								if($gpa['price'][$lang] > 0):
									$grouped += (($gpa['price'][$lang] * $gp['qty']) * $stored['qty']);
								endif;	
							endforeach;
						endif;

					endforeach;
				endif;	

				$this->_subtotal += ($stored['price'][$lang] * $stored['qty']) + $attrs + $grouped;
			endforeach;

			$this->_total += $this->_subtotal;

			$tax = $this->getTax();	
			$this->_total += $tax['value'];

			if(count($charges) > 0):
				foreach ($charges as $charge):
					if($charge['type'] == self::TYPE_AMOUNT):
						$this->_total += $charge['value'][$lang];
					elseif($charge['type'] == self::TYPE_PERCENTAGE):
						$this->_total += $this->_percentage($charge['value'][$lang]);
					endif;
				endforeach;
			endif;

			if(count($discounts) > 0):
				foreach ($discounts as $discount):
					if($this->_total > $discount['value'][$lang]):
						if($discount['type'] == self::TYPE_AMOUNT):
							$this->_total -= $discount['value'][$lang];
						elseif($discount['type'] == self::TYPE_PERCENTAGE):
							$this->_total -= $this->_percentage($discount['value'][$lang]);
						endif;	
					endif;
				endforeach;
			endif;	

		endif;
	}

//-------------------------------
//	PUBLIC 
//-------------------------------
	public function __construct($name, $lang){
		$this->_cart = $name;
		$this->_lang = $lang;
		$this->refresh();
	}

	public function refresh(){
		$this->_totals();
	}

	public function getData(){
		return $this->_getData('items');
	}

	public function getTotalItems(){
		return count($this->getData());
	}

	public function hasItems(){
		if($this->getTotalItems() > 0) return true;

		return false;
	}

	public function itemExists($id){
		$items = $this->getData();
		return (isset($items[$id]));
	}

	public function getItems(){

		$items = [];

		if($this->hasItems()):
			foreach($this->getData() as $item):
				$items[] = self::getItemFormat($this->_lang, $item);
			endforeach;
		endif;

		return $items;
	}

	public function getItem($id){
		
		if($this->itemExists($id)){
			$items = $this->getData();
			return self::getItemFormat($this->_lang, $items[$id]);
		}

		return [];
	}

	public function isGroupedItem($id){
		$item = $this->getItem($id);

		if(!empty($item)){
			return (!empty($item['grouped_products']));
		}

		return false;
	}

	public function update($id, $qty, array $data = []){

		if($this->itemExists($id)){
			$items = $this->getData();
			if($qty > 0 || is_null($qty)){
				$obj = $items[$id];

				if(!empty($data)){
					$obj = array_merge($obj, $data);
				}

				if(is_null($qty)){
					$qty = $items[$id]['qty'];
				}
				
				$items[$id] = $this->_format($obj['index'], $obj, $qty);
				
				$this->_refresh($items);
			}else{
				$this->remove($id);
			}
		}

	}

	public function remove($id){
		
		if($this->itemExists($id)){
			$items = $this->getData();
			unset($items[$id]);
			$this->_refresh($items);
		}	
	}

	public function add($item){

		$items = $this->getData();
		$insert = [];

		$id = $this->_setId($item['id'], $this->_attributes($item['attributes']));

		// if there's items already
		if(count($items) > 0):
			
			foreach ($items as $key => $saved):
				
				// item already exists in cart?
				if($id == $key):

					//let's update qty
					$qty = ($item['qty'] + $saved['qty']);

					// store items before pushing in session again
					$insert[$id] = $this->_format($id, $item, $qty);					
				
				else:
					// retrieve stored quantity
					$qty = $saved['qty'];
					
					// store items before pushing in session again
					$insert[$key] = $this->_format($saved['index'], $saved, $qty);

				endif;	

			endforeach;

			// if it's a new one
			if(!isset($insert[$id])):
				$insert[$id] = $this->_format($id, $item, $item['qty']);
			endif;	

		else: // cart is empty, so let's add the first item
			$insert[$id] = $this->_format($id, $item, $item['qty']);
		endif;	

		$this->_refresh($insert);

	}

	public function updateAll($ids, array $index = []){

		$items = $this->getData();
		$insert = [];
		$qtys = [];

		foreach ($ids as $id => $qty):
			$qtys[$id] = $qty;
		endforeach;

		// if there's items already
		if(count($items) > 0):
			foreach ($items as $key => $saved):
				$q = $qtys[$key];
				if($q > 0) $insert[$key] = $this->_format($saved['index'],$saved, $q);
			endforeach;
		endif;	

		// if users checked boxes, rip them off.
		if(count($index) > 0):
			foreach ($index as $i):
				unset($insert[$i]);
			endforeach;
		endif;

		$this->_refresh($insert);

	}

	public function setTax($tx){
		$this->_setData('tax', $tx);
	}

	public function getTax(){
		$data = $this->_getData('tax');
		$value = 0;

		if(empty($data)){
			$tax = $value;
		} else {
			$tax = $data[$this->_lang];
			$value = $this->_percentage($tax);
		}

		return ['percentage' => $tax, 'value' => $value];
	}	

	public function setCharges(array $data, $overwrite = true){

		if(!$overwrite){
			$stored = $this->getCharges();
			if(!empty($stored)){
				$data = array_merge($stored, $data);
			}
		}

		$this->_setData('charges', $data);
	}

	public function getCharges(){
		return $this->_getData('charges');
	}	

	public function setDiscounts(array $data, $overwrite = true){

		if(!$overwrite){
			$stored = $this->getDiscounts();
			if(!empty($stored)){
				$data = array_merge($stored, $data);
			}
		}

		$this->_setData('discounts', $data);
	}	

	public function getDiscounts(){
		return $this->_getData('discounts');
	}

	public function getSubtotal(){
		return $this->_subtotal;
	}

	public function getTotal(){
		return $this->_total;
	}

	public function getItemsWeight(){

		$total = 0;

		foreach ($this->getItems() as $key => $item) {
			if(isset($item['data']['weight'])):
				$total += $item['data']['weight'];
			endif;

			if(!empty($item['grouped_products'])):
				foreach ($item['grouped_products'] as $gp):
					if(isset($gp['data']['weight'])):
						$total += ($gp['data']['weight'] * $gp['qty']);
					endif;	
				endforeach;
			endif;

		}

		return $total;
	}

	public function reset(){
		Session::reset($this->_cart);
	}

}
