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

namespace rdks\app\sites\shop\blocks\Cart;

use rdks\core\framework\Language;
use rdks\core\page\Block;
use rdks\core\page\View;
use rdks\core\libs\Data\Cart as ShoppingCart;

class Cart extends Block {

	protected $_dispatchUrl = true;
	private $_lang;

	private function _currency(){
		
		$currency = ($this->_lang == "es") ? "MXN" : "USD";

		$this->view->data("lang", $this->_lang);
		$this->view->data("currency", $currency);
	}

	public function __construct(array $settings, View $view){
		parent::__construct($settings, $view);

		$config = $this->getSiteConfig();
		$this->_lang = Language::get();
		$this->_cart = ShoppingCart::init($config['CART_NAME'], $this->_lang);
	}

	public function counter(){

		$this->_currency();

		$this->view->data("counter", $this->_cart->getTotalItems());
		$this->view->data("subtotal", $this->_cart->getSubtotal());
		$this->view->load("counter");
		
		return $this->view->output();
	}

	public function items(){

		$this->disableUrlDispatch();

		$this->_currency();

		$this->view->data("items", $this->_cart->getItems());			
		$this->view->load("items");
		
		return $this->view->output();
	}

	public function checkout(){

		$this->view->data("hasItems", $this->_cart->hasItems());
		$this->view->load("checkout");

		return $this->view->output();
	}

	public function totals(){

		$charges = $this->_cart->getCharges();

		$amount = ($this->_lang == "es") ? 5000 : 900;

		if($this->_cart->getSubtotal() > $amount){
			if(isset($charges['shipping']['value'][$this->_lang]) 
				&& $charges['shipping']['value'][$this->_lang] != 0 
			){
				$charges['shipping']['value'][$this->_lang] = 0;

				$this->_cart->setCharges($charges);
				$this->_cart->refresh();
			}
		}

		$this->_currency();
		
		$this->view->data("hasItems", $this->_cart->hasItems());
		$this->view->data("subtotal", $this->_cart->getSubtotal());
		$this->view->data("tax", $this->_cart->getTax());
		$this->view->data("charges", $charges);
		$this->view->data("discounts", $this->_cart->getDiscounts());
		$this->view->data("total", $this->_cart->getTotal());
		$this->view->load("totals");
		
		return $this->view->output();
	}

} 
