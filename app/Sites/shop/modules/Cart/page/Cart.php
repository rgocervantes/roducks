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

namespace rdks\app\sites\shop\modules\Cart\page;

use rdks\core\page\FrontPage;
use rdks\core\page\View;
use rdks\app\sites\shop\modules\Cart\helper\Cart as CartHelper;
use Firebase\JWT\JWT;

class Cart extends FrontPage {

	private $_helper;

	private function _cart(){
		return $this->_helper->getData();		
	}

	public function __construct(array $settings, View $view){
		parent::__construct($settings, $view);

		$this->_helper = CartHelper::init();

		$key = "example_key";
		$token = array(
		    "iss" => "http://example.org",
		    "aud" => "http://example.com",
		    "iat" => 1356999524,
		    "nbf" => 1357000000
		);

		/**
		 * IMPORTANT:
		 * You must specify supported algorithms for your application. See
		 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
		 * for a list of spec-compliant algorithms.
		 */
		$jwt = JWT::encode($token, $key);
		$decoded = [];
		try {
			$decoded = JWT::decode($jwt, $key, array('HS256'));
		} catch(\Exception $e){
			echo $e->getMessage();
		}

		//print_r($decoded);
		//exit;

	}

	public function index(){

		//$this->view->assets->js(["jquery-libs/jquery-v3.1.1.min.js"]);

		$this->view->load("index");

		return $this->view->output();
	}

	public function basket(){

		$this->view->title(TEXT_WELCOME);
		$this->view->assets->plugins([
			'roducks-cart'
		], false);
		$this->view->assets->scriptsInline(["grid","popover"]);
		$this->view->assets->scriptsOnReady(["cart.ready"]);
		$this->view->data("hasItems", $this->_cart()->hasItems());		
		$this->view->load("basket");

		return $this->view->output();
	}

	public function clean(){
		$this->_helper->resetCheckoutData();
		$this->_cart()->reset();
		$this->redirect("/");
	}

	public function checkout(){

		if(!$this->_cart()->hasItems()){
			$this->redirect("/");
		}

		$shippingOptions = $this->_helper->getShippingOptions();
		$paymentsOptions = $this->_helper->getPaymentOptions();

		$address = $this->_helper->getAddressData();
		$checkout = $this->_helper->getCheckoutData();

		$step = (isset($checkout['shipping_option']) && count($address) == 3) ? 2 : 3;
		$startOnStep = (count($address) > 1) ? $step : 1;

		$shipping = [];
		$shipping['idCountry'] = (isset($address['shipping_country'])) ? $address['shipping_country'] : null;
		$shipping['idState'] = (isset($address['shipping_state'])) ? $address['shipping_state'] : null;

		$shipping['countries'] = $this->_helper->getCountries();
		$shipping['states'] = $this->_helper->getStates(CartHelper::getId($shipping['idCountry']));
		$shipping['cities'] = $this->_helper->getCities(CartHelper::getId($shipping['idState']));

		$billing = [];
		$billing['idCountry'] = (isset($address['billing_country'])) ? $address['billing_country'] : null;
		$billing['idState'] = (isset($address['billing_state'])) ? $address['billing_state'] : null;

		$billing['countries'] = $this->_helper->getCountries();
		$billing['states'] = $this->_helper->getStates(CartHelper::getId($billing['idCountry']));
		$billing['cities'] = $this->_helper->getCities(CartHelper::getId($billing['idState']));		

		$this->view->assets->plugins([
			'roducks-cart',
			'roducks-checkout'
		], false);

		$this->view->assets->scriptsInline(["checkout","form"]);
		$this->view->assets->scriptsOnReady(["checkout.ready"]);

		$this->view->data("address", $address);
		$this->view->data("shipping", $shipping);
		$this->view->data("billing", $billing);
		$this->view->data("startOnStep", $startOnStep);
		$this->view->data("shippingOptions", $shippingOptions);
		$this->view->data("paymentsOptions", $paymentsOptions);
		$this->view->load("checkout");

		return $this->view->output();
	}

	public function success(){

		$orderId = $this->_helper->getOrderId();
		$order = $this->_helper->getCheckoutData();

		if($this->_helper->redirectCheckout()){
			$this->redirect("/");
		}

		$this->_cart()->reset();

		$this->view->data("orderId", $orderId);
		$this->view->data("order", $order);
		$this->view->load("success");

		return $this->view->output();
	}	

} 
