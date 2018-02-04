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

namespace rdks\app\sites\shop\modules\Cart\helper;

use rdks\core\page\HelperPage;
use rdks\core\framework\Language;
use rdks\core\framework\Helper;
use rdks\core\framework\UI;
use rdks\core\libs\Data\Session;
use rdks\core\libs\Data\Cart as ShoppingCart;

class Cart extends HelperPage {

	const CHECKOUT_DATA = "RDKS_CHECKOUT";

	static function select($value, $index = "value"){

		$ret = [
			'id' => 0,
			'value' => $value
		];
		
		if(Helper::regexp('#[|]#', $value)){
			list($id, $value) = explode("|", $value);

			$ret = [
				'id' => $id,
				'value' => $value
			];

		}

		return $ret[$index];
	}

	static function getId($value){

		if(is_null($value)){
			return "";
		}

		if(Helper::regexp('/^\d+|.+$/', $value)){
			return intval(self::select($value, 'id'));
		}

		return null;
	}

	public function setCheckoutData($data){
		Session::set(self::CHECKOUT_DATA, $data);
	}

	public function getCheckoutData(){
		return Session::get(self::CHECKOUT_DATA);
	}

	public function updateCheckoutData($data){
		Session::update(self::CHECKOUT_DATA, $data);
	}	

	public function removeCheckoutData($data){
		Session::remove(self::CHECKOUT_DATA, $data);
	}

	public function resetCheckoutData(){
		Session::reset(self::CHECKOUT_DATA);
	}

	public function redirectCheckout(){

		if(!Session::exists(self::CHECKOUT_DATA)){
			return true;
		} else {
			$this->resetCheckoutData();
		}

		return false;
	}

	public function setOrderId($id){
		$this->updateCheckoutData(['orderId' => $id]);
	}

	public function getOrderId(){
		$data = $this->getCheckoutData();

		return (isset($data['orderId'])) ? $data['orderId'] : 0;
	}

	public function getAddressData(){

		if(Session::exists(self::CHECKOUT_DATA)) {
			$address = $this->getCheckoutData();
		} else {
			$address = [
				'same_address' => 1
			];
			$this->setCheckoutData($address);
		}

		return $address;
	}

	public function getData(){

		$config = $this->getSiteConfig();
		$lang = Language::get();
		$cart = ShoppingCart::init($config['CART_NAME'], $lang);

		return $cart;
	}

	public function getShippingOptions(){

		$icon = UI::getImage("rdks_logo_color_mini.png", 200, ['square' => true, 'center' => true, 'bg' => UI::BG_WHITE]);
		
		$shippingOptions = [
			'standar' => [
				'title' => [
					'es' => "Estándar (5 a 7 días hábiles)",
					'en' => "Standar (5 to 7 available days)"
				],
				'details' => true,
				'description' => [
					'es' => "Políticas de Envío",
					'en' => "Shipping Policy"
				]
			],
			'express' => [
				'title' => [
					'es' => "Express (3 días)",
					'en' => "Express (3 days)"
				],
				'details' => true,
				'description' => [
					'es' => "Restricciones {$icon}",
					'en' => "Restrictions {$icon}"
				]
			]
		];

		return $shippingOptions;
	}

	public function getPaymentOptions(){
		
		$paymentOptions = [
			'paypal' => [
				'title' => [
					'es' => "Paypal Express",
					'en' => "Paypal Express"
				],
				'details' => true,
				'description' => [
					'es' => "Paypal, Tu mejor opción.",
					'en' => "Paypal, Your best option."
				]
			],
			'transfer_hsbc' => [
				'title' => [
					'es' => "Transferencia Bancaria: (HSBC)",
					'en' => "IXE"
				],
				'details' => true,
				'description' => [
					'es' => "Cuenta: 284574520",
					'en' => "Account: 284574520"
				]
			],
			'cc_bancomer' => [
				'title' => [
					'es' => "Tarjeta Crédito Bancomer",
					'en' => "Credit Card (Master Card)"
				],
				'details' => true,
				'description' => [
					'es' => "BBVA",
					'en' => "VISA"
				]
			]
		];

		return $paymentOptions;
	}

	public function getCoupons(){

		$coupons = [
			'RODUCKS_07' => [
				'name' => [
					'es' => "Cupón Roducks",
					'en' => "Coupon Roducks"
				],
				'type' => ShoppingCart::TYPE_AMOUNT,
				'value' => [
					'es' => 43.00,
					'en' => 3.21
				]
			],
			'WEDDING' => [
				'name' => [
					'es' => "Cupón Ganador",
					'en' => "Coupon Winner"
				],
				'type' => ShoppingCart::TYPE_PERCENTAGE,
				'value' => [
					'es' => 5,
					'en' => 5
				]
			]
		];

		return $coupons;
	}

	public function setShippingOption($option = ""){

		switch ($option) {
			case 'standar':
				$value = 134.89;
				break;
			case 'express':
				$value = 150.34;
				break;
			default:
				$value = 100.34;
				break;	
		}

		$charges = [
			'shipping' => [
				'name' => [
					'es' => "Envío",
					'en' => "Shipping"
				],
				'type' => ShoppingCart::TYPE_AMOUNT,
				'value' => [
					'es' => $value,
					'en' => 9.10
				]
			]
		];

		return $charges;

	}

	public function getCountries(){

		$countries = [
			[
				'id' => 52,
				'name' => "México"
			],
		];

		return $countries;
	}

	public function getStates($idCountry = ""){

		if(is_null($idCountry)){
			return [];
		}

		$states = [
			[
				'id' => 1,
				'name' => "Cd. de México"
			],
			[
				'id' => 2,
				'name' => "Jalisco"
			],	
			[
				'id' => 3,
				'name' => "Nuevo León"
			],	
			[
				'id' => 4,
				'name' => "Guerrero"
			],		
			[
				'id' => 5,
				'name' => "Baja California"
			],
			[
				'id' => 6,
				'name' => "Yucatán"
			],
			[
				'id' => 7,
				'name' => "Oaxaca"
			],	
			[
				'id' => 8,
				'name' => "Zacatecas"
			],
			[
				'id' => 9,
				'name' => "Durango"
			],																	
		];

		if(empty($idCountry)){
			return $states;
		}

		if($idCountry == 52){
			return $states;
		}

		return [];

	}

	public function getCities($idState = ""){

		if(is_null($idState)){
			return [];
		}

		$cities = [
			[
				'id' => 1,
				'name' => "Monterrey"
			],
			[
				'id' => 2,
				'name' => "Guadalupe"
			],	
			[
				'id' => 3,
				'name' => "San Pedro"
			],
			[
				'id' => 4,
				'name' => "Apodaca"
			],												
		];

		if(empty($idState)){
			return $cities;
		}

		if($idState == 3){
			return $cities;
		}

		return [];
	}

} 
