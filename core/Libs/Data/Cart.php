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

namespace Roducks\Libs\Data;

class Cart
{

	const TYPE_AMOUNT = 'amount';
	const TYPE_PERCENTAGE = 'percentage';

	private $_cart; // Stores cart's name
	private $_subtotal = 0;
	private $_total = 0;
	private $_lang;
	private $_currency = 'USD';

//-------------------------------
//	STATIC
//-------------------------------
	static public function init($name, $lang = "en", $currency = 'USD')
	{
		return new static($name, $lang, $currency);
	}

	/*
	*	Return price format with symbol & currency
	*/
	static public function getPriceFormat($v, $c = 'USD', $s = '$')
	{
		return $s . " " . number_format($v,2,'.',',') . ' ' . $c;
	}

	/*
	*	Calculate subtotal by price, quantity and attributes
	*/
	static public function getItemSubtotal($lang, $price, $qty, $attrs, $groupedProducts)
	{
		$attrsValue = 0;
		$groupedValue = 0;

		foreach($attrs as $a):
			if ($a['price'][$lang] > 0):
				$attrsValue += ($a['price'][$lang] * $qty);
			endif;
		endforeach;

		foreach($groupedProducts as $g):
			if ($g['price'][$lang] > 0):
				$groupedValue += ($g['price'][$lang] * $qty);
			endif;

			if (isset($g['attributes'])):
				foreach ($g['attributes'] as $gpa):
					if ($gpa['price'][$lang] > 0):
						$groupedValue += ($gpa['price'][$lang] * $qty);
					endif;
				endforeach;
			endif;

		endforeach;

		return ($qty * $price) + $attrsValue + $groupedValue;
	}

	static public function getAttributes($lang, $currency, $attrs)
	{
		$ret = [];

		foreach ($attrs as $key => $attr) {
			$price = $attr['price'][$lang];
			unset($attr['price']);
			$attr['price'] = $price;
			$attr['price_format'] = self::getPriceFormat($price, $currency);
			$ret[] = $attr;
		}

		return $ret;
	}

	static public function getItemFormat($lang, $currency, $item)
	{

		$subtotal = self::getItemSubtotal($lang, $item['price'][$lang], $item['qty'], $item['attributes'], $item['grouped_products']);

		return [
			'index' => $item['index'],
			'qty' => $item['qty'],
			'price' => $item['price'][$lang],
			'price_format' => self::getPriceFormat($item['price'][$lang], $currency),
			'subtotal' => $subtotal,
			'subtotal_format' => self::getPriceFormat($subtotal, $currency),
			'data' => $item['data'],
			'attributes' => self::getAttributes($lang, $currency, $item['attributes']),
			'grouped_products' => $item['grouped_products']
		];
	}

	static public function getPercentageValue($subtotal, $per)
	{

		$div = ($per / 100);
		$value = ($subtotal * $div);

		return $value;
	}

//-------------------------------
//	PRIVATE
//-------------------------------
	private function _setData($index, $data)
	{
		if (!Session::exists($this->_cart)):
			Session::set($this->_cart, [$index => $data]);
		else:
			$session = Session::get($this->_cart);
			$session[$index] = $data;
			Session::set($this->_cart, $session);
		endif;
	}

	private function _getData($index = null)
	{

		$ret = [];

		if (Session::exists($this->_cart)):
			$data = Session::get($this->_cart);

			if (is_null($index)) {
				return $data;
			}

			if (isset($data[$index])):
				$ret = $data[$index];
			endif;
		endif;

		return $ret;
	}

	private function _format($index, $obj, $qty)
	{
		$data = [
			'index' 			=> $index,
			'id' 				=> $obj['id'],
			'qty' 				=> intval($qty),
			'price' 			=> $obj['price'],
			'data' 				=> $obj['data'],
			'attributes' 		=> [],
			'grouped_products' 	=> []
		];

		if (isset($obj['attributes'])){
			$data['attributes'] = $obj['attributes'];
		}

		if (isset($obj['grouped_products'])){

			$grouped_products = [];

			foreach ($obj['grouped_products'] as $key => $value) {
				$idx = $this->_setItemId($value['id'], $this->_setAttrsId($value['attributes']));
				$grouped_products[$idx] = $value;
			}

			$data['grouped_products'] = $grouped_products;
		}

		return $data;
	}

	private function _setAttrsId($attrs)
	{

		$values = [];
		foreach($attrs as $obj):
			$values[] = $obj['id'];
		endforeach;

		return $values;
	}

	private function _setItemId($id, $keys)
	{

		$ext = "";

		if (count($keys) > 0) $ext = "_" . implode("_", $keys);

		return 'item_' . $id . $ext;
	}

	private function _store($items)
	{
		$this->_setData('items', $items);
	}

	private function _percentage($per)
	{
		$subtotal = $this->getSubtotal(false);
		return self::getPercentageValue($subtotal, $per);
	}

	private function _totals()
	{

		$this->_subtotal = 0;
		$this->_total = 0;

		if ($this->hasItems()):

			$lang = $this->_lang;

			$items = $this->getData();
			$charges = $this->_getData('charges');
			$discounts = $this->_getData('discounts');

			foreach ($items as $key => $stored):
				$attrs = 0;
				$grouped = 0;

				if (!empty($stored['attributes'])):
					foreach ($stored['attributes'] as $a):
						if ($a['price'][$lang] > 0):
							$attrs += ($a['price'][$lang] * $stored['qty']);
						endif;
					endforeach;
				endif;

				if (!empty($stored['grouped_products'])):
					foreach ($stored['grouped_products'] as $gp):
						if ($gp['price'][$lang] > 0):
							$grouped += (($gp['price'][$lang] * $gp['qty']) * $stored['qty']);
						endif;

						if (isset($gp['attributes'])):
							foreach ($gp['attributes'] as $gpa):
								if ($gpa['price'][$lang] > 0):
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

			if (count($charges) > 0):
				foreach ($charges as $charge):
					if ($charge['type'] == self::TYPE_AMOUNT):
						$this->_total += $charge['value'][$lang];
					elseif ($charge['type'] == self::TYPE_PERCENTAGE):
						$this->_total += $this->_percentage($charge['value'][$lang]);
					endif;
				endforeach;
			endif;

			if (count($discounts) > 0):
				foreach ($discounts as $discount):
					if ($this->_total > $discount['value'][$lang]):
						if ($discount['type'] == self::TYPE_AMOUNT):
							$this->_total -= $discount['value'][$lang];
						elseif ($discount['type'] == self::TYPE_PERCENTAGE):
							$this->_total -= $this->_percentage($discount['value'][$lang]);
						endif;
					endif;
				endforeach;
			endif;

		else:
			$this->removeCharges();
			$this->removeDiscounts();
		endif;
	}

//-------------------------------
//	PUBLIC
//-------------------------------
	public function __construct($name, $lang, $currency)
	{
		$this->_cart = $name;
		$this->_lang = $lang;
		$this->_currency = $currency;
		$this->refresh();
	}

	public function refresh()
	{
		$this->_totals();
	}

	public function getData()
	{
		return $this->_getData('items');
	}

	public function getCountItems()
	{
		$data = $this->getData();
		$count = count($data);

		foreach ($data as $item) {
			if (!empty($item['grouped_products'])){
				$count += count($item['grouped_products']);
			}
		}

		return $count;
	}

	public function hasItems()
	{
		if ($this->getCountItems() > 0) return true;

		return false;
	}

	public function itemExists($id)
	{
		$items = $this->getData();
		return (isset($items[$id]));
	}

	public function getItems()
	{

		$items = [];

		if ($this->hasItems()):
			foreach($this->getData() as $item):
				$items[] = self::getItemFormat($this->_lang, $this->_currency, $item);
			endforeach;
		endif;

		return $items;
	}

	public function getItem($id)
	{

		if ($this->itemExists($id)){
			$items = $this->getData();
			return self::getItemFormat($this->_lang, $this->_currency, $items[$id]);
		}

		return [];
	}

	public function isGroupedItem($id)
	{
		$item = $this->getItem($id);

		if (!empty($item)){
			return (!empty($item['grouped_products']));
		}

		return false;
	}

	public function remove($id)
	{

		if ($this->itemExists($id)){
			$items = $this->getData();
			unset($items[$id]);
			$this->_store($items);
		}
	}

	public function removeGrouped($id, $key, $force = false)
	{

		if ($this->itemExists($id)){
			$data = $this->getData();
			$item = $data[$id];

			if (isset($item['grouped_products'][$key])){
				if (
					(isset($item['grouped_products'][$key]["remove"]) && $item['grouped_products'][$key]["remove"] === true)
					|| $force
				){
					unset($item['grouped_products'][$key]);
					$this->update($id, null, $item);
					return true;
				}
			}
		}

		return false;
	}

	public function add($item)
	{

		// cast qty to integer
		$item['qty'] = intval($item['qty']);

		$items = $this->getData();

		$id = $this->_setItemId($item['id'], $this->_setAttrsId($item['attributes']));

		if (isset($items[$id])) {
			//let's update qty
			$qty = ($item['qty'] + $items[$id]['qty']);

			// store items before pushing in session again
			$items[$id] = $this->_format($id, $item, $qty);
		} else {
			$items[$id] = $this->_format($id, $item, $item['qty']);
		}

		$this->_store($items);

	}

	public function update($id, $qty, array $data = [])
	{

		if ($this->itemExists($id)){
			$items = $this->getData();
			if ($qty > 0 || is_null($qty)){
				$obj = $items[$id];

				if (!empty($data)){
					$obj = array_merge($obj, $data);
				}

				if (is_null($qty)){
					$qty = $items[$id]['qty'];
				}

				$items[$id] = $this->_format($obj['index'], $obj, $qty);

				$this->_store($items);
			}else{
				$this->remove($id);
			}
		}

	}

	// @deprecated
	public function updateAll($ids, array $index = [])
	{

		$items = $this->getData();
		$insert = [];
		$qtys = [];

		foreach ($ids as $id => $qty):
			$qtys[$id] = $qty;
		endforeach;

		// if there's items already
		if (count($items) > 0):
			foreach ($items as $key => $saved):
				$q = $qtys[$key];
				if ($q > 0) $insert[$key] = $this->_format($saved['index'],$saved, $q);
			endforeach;
		endif;

		// if users checked boxes, rip them off.
		if (count($index) > 0):
			foreach ($index as $i):
				unset($insert[$i]);
			endforeach;
		endif;

		$this->_store($insert);

	}

	public function setTax($tx)
	{
		if (!$this->hasItems()) {
			$this->_setData('tax', $tx);
		}
	}

	public function getTax(){
		$data = $this->_getData('tax');
		$value = 0;

		if (empty($data)){
			$tax = $value;
			$format = $value;
		} else {
			$tax = $data[$this->_lang];
			$value = $this->_percentage($tax);
			$format = self::getPriceFormat($value, $this->_currency);
		}

		return ['percentage' => $tax, 'value' => $value, 'format' => $format];
	}

	public function setCharges(array $data, $overwrite = true)
	{

		if (!$overwrite){
			$stored = $this->getCharges();
			if (!empty($stored)){
				$data = array_merge($stored, $data);
			}
		}

		$this->_setData('charges', $data);
	}

	public function getCharges()
	{
		$charges = $this->_getData('charges');

		foreach ($charges as $i => $charge) {
			$charges[$i]['value'] = $charge['value'][$this->_lang];
			$charges[$i]['format'] = self::getPriceFormat($charge['value'][$this->_lang], $this->_currency);
		}

		return $charges;
	}

	public function removeCharges()
	{
		$this->setCharges([]);
	}

	public function setDiscounts(array $data, $overwrite = true)
	{

		if (!$overwrite){
			$stored = $this->getDiscounts();
			if (!empty($stored)){
				$data = array_merge($stored, $data);
			}
		}

		$this->_setData('discounts', $data);
	}

	public function getDiscounts()
	{
		$discounts = $this->_getData('discounts');

		foreach ($discounts as $i => $discount) {
			$discounts[$i]['value'] = $discount['value'][$this->_lang];
			$discounts[$i]['format'] = self::getPriceFormat($discount['value'][$this->_lang], $this->_currency);
		}

		return $discounts;
	}

	public function removeDiscounts()
	{
		$this->setDiscounts([]);
	}

	public function setShipping($title, $value)
	{
		$this->setCharges([
			'shipping' => [
				'type' => self::TYPE_AMOUNT,
				'title' => $title,
				'value' => $value
			]
		]);
	}

	public function getShipping()
	{
		$charges = $this->getCharges();
		return (isset($charges['shipping'])) ? $charges['shipping']['value'] : 0;
	}

	public function updateShipping($value, $title = null)
	{
		$charges = $this->_getData('charges');
		$charges['shipping']['value'] = $value;

		if (!is_null($title)) {
			$charges['shipping']['title'] = $title;
		}

		$this->setCharges($charges);
	}

	public function freeShipping($title = null)
	{
		$this->updateShipping([
			'es' => 0,
			'en' => 0,
			'fr' => 0
		], $title);
	}

	public function getSubtotal($format = true)
	{
		return ($format) ? self::getPriceFormat($this->_subtotal, $this->_currency) : $this->_subtotal;
	}

	public function getTotal($format = true)
	{
		return ($format) ? self::getPriceFormat($this->_total, $this->_currency) : $this->_total;
	}

	public function getCurrency()
	{
		return $this->_currency;
	}

	public function getItemsStock()
	{

		$items = [];
		$data = $this->getData();

		foreach ($data as $key => $item) {

			$id = $item['index'];
			$items[$id]['qty'] = $item['qty'];
			$items[$id]['grouped'] = [];

			foreach ($item['grouped_products'] as $k => $v) {
				$cids = "";
				$gp = [];

				if (count($v['attributes']) > 0){
					foreach ($v['attributes'] as $attr) {
						$cids .= "_".$attr['id'];
					}
				}

				$gp['item_'.$v['id'].$cids]['qty'] = $item['qty'];
				array_push($items[$id]['grouped'], $gp);
			}
		}

		return $items;
	}

	public function getItemStock($id)
	{
		$items = $this->getItemsStock();

		return (isset($items[$id])) ? $items[$id] : [];
	}

	public function getItemsWeight()
	{

		$total = 0;

		foreach ($this->getItems() as $key => $item) {
			if (isset($item['data']['weight'])):
				$total += $item['data']['weight'] * $item['qty'];
			endif;

			if (!empty($item['grouped_products'])):
				foreach ($item['grouped_products'] as $gp):
					if (isset($gp['data']['weight'])):
						$total += ($gp['data']['weight'] * $gp['qty']);
					endif;
				endforeach;
			endif;

		}

		return $total;
	}

	public function reset()
	{
		Session::reset($this->_cart);
	}

}
