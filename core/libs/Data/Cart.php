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

	var $discounts;
	var $subtotalBeforeDiscount;

	private $_cart; // stores cart's name

//-------------------------------
//	PRIVATE 
//-------------------------------
	private function _tax($tx = 0){
		$subtotal = $this->getSubtotal();
		$tax = $this->getTax($tx);

		return ($subtotal + $tax);
	}	

	private function _format($index, $obj, $qty){
		return [
			'index' => $index,
			'id' => $obj['id'],
			'qty' => $qty,
			'price' => $obj['price'],
			'details' => $obj['details'],
			'attributes' => $obj['attributes']
		];
	}

	private function _attributes($arr){

		$values = [];
		foreach($arr as $obj):
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
		Session::set($this->_cart, $items);
	}
	
//-------------------------------
//	PUBLIC 
//-------------------------------
	public function __construct($name){
		$this->_cart = $name;
	}

	public function getItems(){

		$items = [];

		if(Session::exists($this->_cart)):
			$items = Session::get($this->_cart);
		endif;

		return $items;
	}

	public function getCart(){

		$items = [];

		foreach($this->getItems() as $item):
			$items[] = [
				'index' => $item['index'],
				'qty' => $item['qty'],	
				'price' => self::getPriceFormat($item['price']),
				'subtotal' => self::getPriceFormat( self::getSubtotalFormat($item['price'], $item['qty'], $item['attributes']) ),
				'details' => $item['details'],
				'attributes' => self::getAttributes($item['attributes'])
			];
		endforeach;	

		return $items;

	}	

	public function getTotalItems(){
		return count($this->getItems());
	}

	public function hasItems(){
		if($this->getTotalItems() > 0) return true;

		return false;
	}	

	public function getItem($id){
		
		$items = $this->getItems();
		$obj = [];
		
		foreach($items as $key => $item):
			if($item['id'] == $id):
				$obj = $item;
				break;
			endif;
		endforeach;	

		return $obj;
	}

	public function itemExists($id){
		$item = $this->getItem($id);
		return (count($item) > 0) ? true : false;
	}

	public function deleteItem($id){
		$items = $this->getItems();

		if(isset($items[$id])){
			unset($items[$id]);
			$this->_refresh($items);
		}	
	}

	public function updateItem($id, $qty){
		$items = $this->getItems();

		if(isset($items[$id])){
			if($qty > 0){
				$obj = $items[$id];
				$items[$id] = $this->_format($obj['index'], $obj, $qty);
				$this->_refresh($items);
			}else{
				$this->deleteItem($id);
			}
		}

	}

	public function add($item){

		$items = $this->getItems();
		$insert = [];

		$clave = $this->_setId($item['id'], $this->_attributes($item['attributes']));

		// if there's items already
		if(count($items) > 0):
			
			foreach ($items as $key => $saved):
				
				// item already exists in cart?
				if($clave == $key):

					//let's update qty
					$qty = ($item['qty'] + $saved['qty']);

					// store items before pushing in session again
					$insert[$clave] = $this->_format($clave, $item, $qty);					
				
				else:
					// retrieve stored quantity
					$qty = $saved['qty'];
					
					// store items before pushing in session again
					$insert[$key] = $this->_format($saved['index'], $saved, $qty);

				endif;	

			endforeach;

			// if it's a new one
			if(!isset($insert[$clave])):
				$insert[$clave] = $this->_format($clave, $item, $item['qty']);
			endif;	

		else: // cart is empty, so let's add the first item
			$insert[$clave] = $this->_format($clave, $item, $item['qty']);
		endif;	

		$this->_refresh($insert);

	}

	public function update($ids, array $index = []){

		$items = $this->getItems();
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

	public function setDiscount(array $amount = []){
		$this->discounts = $amount;
	}

	public function getSubtotalBeforeDiscount(){
		return $this->subtotalBeforeDiscount;
	}

	public function getSubtotal(){
		$items = $this->getItems();
		$subtotal = 0;

		if(count($items) > 0):
			foreach ($items as $key => $saved):
				$attrs = 0;
				if(isset($saved['attributes'])):
					foreach ($saved['attributes'] as $a):
						if($a['sign'] == '+'):
							$attrs+= ($a['value'] * $saved['qty']);
						endif;	
					endforeach;
				endif;	
				$subtotal += ($saved['price'] * $saved['qty']) + $attrs;
				$this->subtotalBeforeDiscount = $subtotal;
			endforeach;

			if(count($this->discounts) > 0):
				foreach ($this->discounts as $discounts):
					$subtotal -= $discounts;
				endforeach;
			endif;	

		endif;	

		return $subtotal;
	}

	public function getTotal($tx = 0, array $add = [], array $less = []){

		if(!$this->hasItems()) return 0;

		$total = $this->_tax($tx);

		if(count($add) > 0):
			foreach ($add as $value):
				$total += $value;
			endforeach;
		endif;	

		if(count($less) > 0):
			foreach ($less as $value):
				$total -= $value;
			endforeach;
		endif;	

		return $total;

	}

	public function getTax($tx = 0){

		$subtotal = $this->getSubtotal();
		$tax = ($tx / 100);
		$res = ($subtotal * $tax);

		return $res;

	}

	public function reset(){
		Session::reset($this->_cart);
	}

//-------------------------------
//	STATIC 
//-------------------------------
	/*
	*	Return price format with symbol & currency
	*/
	static public function getPriceFormat($v,$c = 'MXN', $s = '$'){
		return $s . number_format($v,2,'.',',') . ' ' . $c;
	}

	/*
	*	Calculate subtotal by price, quantity and attributes
	*/
	static public function getSubtotalFormat($price, $qty, $attr){
		$attr_value = 0;
		
		foreach($attr as $a):
			if($a['sign'] == '+'):
				$attr_value += $a['value'];
			endif;
		endforeach;
			
		return ($qty * $price) + ($attr_value * $qty);

	}

	/*
	*	Format attributes
	*/
	static public function getAttributes($attributes){

		$output = '';

		if(isset($attributes[0]) && $attributes[0]['value'] > 0):
			foreach($attributes as $a):
				$output .= $a['name'] . ' ('. $a['sign'] .' '. Cart::getPriceFormat($a['value']) .')<br />';
			endforeach;	
		endif;

		return $output;

	}		

}

?>