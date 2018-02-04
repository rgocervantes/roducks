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

namespace rdks\app\sites\shop\modules\Cart\json;

use rdks\core\page\JSON;
use rdks\core\framework\Language;
use rdks\core\framework\Helper;
use rdks\core\framework\Post;
use rdks\core\libs\Protocol\Http;
use rdks\core\libs\Data\Cart as ShoppingCart;
use rdks\app\sites\shop\modules\Cart\helper\Cart as CartHelper;

class Cart extends JSON {

	var $id;

	protected $_dispatchUrl = true;
	private $_helper;

	private function _cart(){
		return $this->_helper->getData();		
	}	

	public function __construct(array $setttings){
		parent::__construct($setttings);

		$this->_helper = CartHelper::init();
	}

	private function _subtotal($id){

		$item = $this->_cart()->getItem($id);
		$currency = (Language::get() == "es") ? "MXN" : "USD";
		$subtotal = (isset($item['subtotal'])) ? $item['subtotal'] : 0;
		$subtotal = ShoppingCart::getPriceFormat($subtotal, $currency);

		$this->data("id", $id);
		$this->data("refresh", true);
		$this->data("subtotal", $subtotal);
	}

	private function _extra(){

		$charges = [
			/*'tua' => [
				'name' => [
					'es' => "TUA",
					'en' => "TUA"
				],
				'type' => ShoppingCart::TYPE_PERCENTAGE,
				'value' => [
					'es' => 15,
					'en' => 15
				]
			],*/
			'shipping' => [
				'name' => [
					'es' => "Envío",
					'en' => "Shipping"
				],
				'type' => ShoppingCart::TYPE_AMOUNT,
				'value' => [
					'es' => 184.72,
					'en' => 9.10
				]
			],
			/*
			'shipping_express' => [
				'name' => [
					'es' => "Envío Express",
					'en' => "Express Shipping"
				],
				'type' => ShoppingCart::TYPE_AMOUNT,
				'value' => [
					'es' => 51.00,
					'en' => 4.75
				]
			]*/
		];

		$discounts = [
			'coupon' => [
				'name' => [
					'es' => "Cupón Septiembre",
					'en' => "Coupon September"
				],
				'type' => ShoppingCart::TYPE_AMOUNT,
				'value' => [
					'es' => 43.00,
					'en' => 3.21
				]
			],/*
			'coupon_customer' => [
				'name' => [
					'es' => "Cupón Ganador",
					'en' => "Coupon Winner"
				],
				'type' => ShoppingCart::TYPE_PERCENTAGE,
				'value' => [
					'es' => 5,
					'en' => 4
				]
			]*/
		];

		$tax = [
			'es' => 16,
			'en' => 10
		];
		
		$this->_cart()->setTax($tax);		
		//$this->_cart()->setCharges($charges);
		//$this->_cart()->setDiscounts($discounts);	

	}

	public function charges(){
		$this->_extra();

		parent::output();
	}

	public function attrs(){

		$id_product = 91;
		$collection = [];
		$attrs = [];
		$res = [];
		$rel = [];

		$attrs[] = [
				'title' => [
					'es' => "Color",
					'en' => "Color"
				],
				'options' => [
					[
						'id' => 1,
						'title' => [
							'es' => "Blanco",
							'en' => "White"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 0					
					],
					[
						'id' => 2,
						'title' => [
							'es' => "Negro",
							'en' => "Black"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 0
					],
					[
						'id' => 3,
						'title' => [
							'es' => "Rojo",
							'en' => "Red"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 0
					],
					[
						'id' => 4,
						'title' => [
							'es' => "Verde",
							'en' => "Green"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 0
					],	
					[
						'id' => 5,
						'title' => [
							'es' => "Azul",
							'en' => "Blue"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 0
					],													
				]	
		];

/*
		$attrs[] = [
				'title' => [
					'es' => "Talla",
					'en' => "Size"
				],
				'options' => [
					[
						'id' => 1,
						'title' => [
							'es' => "CH",
							'en' => "S"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 1
					],
					[
						'id' => 2,
						'title' => [
							'es' => "M",
							'en' => "M"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 1
					],	
					[
						'id' => 3,
						'title' => [
							'es' => "G",
							'en' => "L"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 1
					],/*
					[
						'id' => 4,
						'title' => [
							'es' => "EG",
							'en' => "XL"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 1
					],
					[
						'id' => 5,
						'title' => [
							'es' => "EEG",
							'en' => "XXL"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 1
					],	
					* /			
									
				]	
		];	
/*
		$attrs[] = [
				'title' => [
					'es' => "Estilo",
					'en' => "Style"
				],
				'options' => [
					[
						'id' => 1,
						'title' => [
							'es' => "Formal",
							'en' => "Formal"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 2
					],
					[
						'id' => 2,
						'title' => [
							'es' => "Clásica",
							'en' => "Classic"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 2
					],
					[
						'id' => 3,
						'title' => [
							'es' => "Deportiva",
							'en' => "Sport"
						],
						'value' => 54.21,
						'stock' => 32,
						'index' => 2
					],					
				]	
		];
*/

		$totalAttrs = count($attrs);

		if($totalAttrs > 3){
			$this->setError(1, "Not allow");
			parent::output();
		} else {

			foreach ($attrs as $attr) {
				$options = $attr['options'];
				$collection[] = $options;
				$count[] = count($options); 
			}

			$max = max($collection);
			$min = min($collection);
			
			$maxIndex = $max[0]['index'];
			$minIndex = $min[0]['index'];

			$countMax = $count[$maxIndex];
			$countMin = $count[$minIndex];
			unset($count[$maxIndex]);
			unset($collection[$maxIndex]);

			$count = array_merge(array(), $count);
			$combine = array_product($count) / $countMin;
			unset($collection[$minIndex]);

			$middleIndex = array_keys($collection)[0];

			if($totalAttrs == 1) {
				$countMin = 1;
				$collection = [];
			}

			for ($i=0; $i < $countMax; $i++) { 
				
				for ($y=0; $y < $combine; $y++) { 
					
					for ($j=0; $j < $countMin; $j++) { 
						$titleES = $max[$i]['title']['es'];
						$titleEN = $max[$i]['title']['en'];
						$id = $max[$i]['id'];
						
						foreach ($collection as $k => $value) {
							if(isset($collection[$k][$y]['title']['es'])){
								$titleES .= " / " . $collection[$k][$y]['title']['es'];
								$titleEN .= " / " . $collection[$k][$y]['title']['en'];
								$id .= "_".$collection[$k][$y]['id'];
							}
						}

						if($countMin > 1) {
							$titleES .= " / " . $min[$j]['title']['es'];
							$titleEN .= " / " . $min[$j]['title']['en'];
							$id .= "_".$min[$j]['id'];
						}
						
						$res['item_'.$id_product.'_'.$id] = [
							'title' => [
								'es' => $titleES,
								'en' => $titleEN
							],
							'stock' => 1
						];
						

						$rel[] = $titleES;
					}
				}
			}
			
			$this->data("data", ['mix' => $rel, 'result' => $res]);
			parent::output(false);
		}

	}

	/**
	*	@param $id string: item_71_5_8
	*/
	public function isGrouped($id){

		$this->data("is", $this->_cart()->isGroupedItem($id));
		parent::output();
	}

	public function stock(){

		$items = $this->_cart()->getItemsStock();

		$this->data("items", $items);
		parent::output();
	}

	public function weight(){

		$this->data("weight", $this->_cart()->getItemsWeight());
		parent::output();
	}

	public function test($v = ""){

		//$this->setError(404, "Not Found.");

		$this->data("v", $v);
		$this->data("items", $this->_cart()->getData());
		$this->data("subtotal", $this->_cart()->getSubTotal());
		$this->data("total", $this->_cart()->getTotal());

		parent::output();
	}

	public function remove(){

		$id = $this->post->param("id");
		$level = (int) $this->post->param("level");
		$key = $this->post->param("key", 'item_0');

		if($level == 0){
			$this->_cart()->remove($id);
			$this->_extra();
			$this->data("refresh", false);
		} else {

			$removed = $this->_cart()->removeGrouped($id, $key);

			if($removed){

				$this->_extra();
				$this->_subtotal($id);

			} else {
				$this->setError(0, "Can't remove item.");
			}
		}

		$this->data("hasItems", $this->_cart()->hasItems());

		parent::output();
	}

	public function update(){

		$id = $this->post->param("id");
		$qty = $this->post->param("qty");

		$stock = $this->_cart()->getItemStock($id);

		$this->data("stock", $stock);

		if($qty < 5){
			if($this->_cart()->itemExists($id)){
				$this->_cart()->update($id, $qty);
				$this->_extra();
				$this->_subtotal($id);
			} else {
				$this->setError(2, "Can't update item.");
			}
		} else {
			$this->setError(1, "Insufficient stock.");
		}

		$this->data("hasItems", $this->_cart()->hasItems());

		parent::output();
	}

	public function add(){

		$this->_cart()->reset();

		$this->_cart()->add([
			'id' => 71,
			'qty' => 1,
			'price' => [
				'es' => 356.34,
				'en' => 156.34
			],
			'data' => [
				'title' => [
					'es' => "Samsung TV",
					'en' => "TV Samsung"
				],
				'description' => [
					'es' => "Lorem ipsum",
					'en' => "Lorem ipsum",					
				],
				'image' => "tv-samsung.jpg",
				'sku' => "TV83820",				
				'weight' => 3 // Kg
			],
			'attributes' => [
				[
					'id' => 5,
					'name' => [
						'es' => "Tipo",
						'en' => "Type"
					],
					'value' => [
						'es' => "TV Inteligente",
						'en' => "Smart TV"
					],
					'price' => [
						'es' => 70.31,
						'en' => 7.31
					]
				],
				[
					'id' => 9,
					'name' => [
						'es' => "Base",
						'en' => "Bottom"
					],
					'value' => [
						'es' => "Redonda",
						'en' => "Round"
					],
					'price' => [
						'es' => 10.90,
						'en' => 1.89
					]
				]
			],
			'grouped_products' => [
				24 => [
					'id' => 24,
					'qty' => 1,
					'price' => [
						'es' => 76.84,
						'en' => 6.74
					],
					'data' => [
						'title' => [
							'es' => "Soporte pared",
							'en' => "Wall support"
						],
						'description' => [
							'es' => "4 Tornillos",
							'en' => "4 Tourniques",					
						],
						'image' => "wall-support.jpg",
						'sku' => "SPT83820",							
						'weight' => 2.3 // Kg
					],
					'attributes' => [
						[
							'id' => 9,
							'name' => [
								'es' => "Color",
								'en' => "Color"
							],
							'value' => [
								'es' => "Negro",
								'en' => "Black"
							],
							'price' => [
								'es' => 0,
								'en' => 0
							]
						]
					]
				],			
				72 => [
					'remove' => true,
					'id' => 72,
					'qty' => 1,
					'price' => [
						'es' => 156.84,
						'en' => 56.74
					],
					'data' => [
						'title' => [
							'es' => "Blue Ray",
							'en' => "Blue Ray"
						],
						'description' => [
							'es' => "Lector DVD",
							'en' => "DVD Reader",					
						],
						'image' => "blue-ray.jpg",
						'sku' => "SPT892179",						
						'weight' => 1.5 // Kg
					],
					'attributes' => [
						[
							'id' => 5,
							'name' => [
								'es' => "Color",
								'en' => "Color"
							],
							'value' => [
								'es' => "Negro",
								'en' => "Black"
							],
							'price' => [
								'es' => 10.31,
								'en' => 2.31
							]
						]
					]
				]
			]
		]);

		$this->_cart()->add([
			'id' => 91,
			'qty' => 1,
			'price' => [
				'es' => 4630.21,
				'en' => 630.21
			],			
			'data' => [
				'title' => [
					'es' => "iPhone 6S",
					'en' => "iPhone 6S"
				],
				'description' => [
					'es' => "Apple",
					'en' => "Marca Apple",					
				],
				'image' => "iphone-6.jpg",
				'sku' => "IPH8765",
				'weight' => 0.123 // Kg
			],
			'attributes' => [
				[
					'id' => 1,
					'name' => [
						'es' => "Color",
						'en' => "Color"
					],
					'value' => [
						'es' => "Negro",
						'en' => "Black"
					],
					'price' => [
						'es' => 20.50,
						'en' => 2.41
					]
				]
			]
		]);	

		$this->_extra();

		parent::output();

	}

	public function getStates($id = ""){

		if(empty($id)){
			$id = $this->id;
		}

		$states = $this->_helper->getStates($id);

		$this->data("states", $states);

		if(empty($states)){
			$this->setError(0,"No available states");
		}		

		parent::output();
	}

	public function getCities($id = ""){

		if(empty($id)){
			$id = $this->id;
		}

		$cities = $this->_helper->getCities($id);

		$this->data("cities", $cities);
		
		if(empty($cities)){
			$this->setError(0,"No available cities");
		}

		parent::output();
	}	

	public function shipping(){

		$this->post->required();

		$option = $this->post->param("option");
		$charges = $this->_helper->setShippingOption($option);

		$data = [
			'shipping_option' => $option
		];
		$this->_helper->updateCheckoutData($data);
	
		$this->_cart()->setCharges($charges);
		$this->_cart()->refresh();

		parent::output();
	}

	public function payment(){

		$this->post->required();

		$option = $this->post->param("option");

		$data = [
			'payment_option' => $option
		];
		$this->_helper->updateCheckoutData($data);

		parent::output();
	}

	public function postCode(){

		$this->post->required();

		$code = $this->post->param("code", 0);

		$this->data("code", $code);

		parent::output();
	}

	public function coupon(){

		$this->post->required();

		$coupon = $this->post->param("coupon");
		$apply = $this->post->param("apply", 1);

		if($apply == 1){
			$coupons = $this->_helper->getCoupons();

			if(isset($coupons[$coupon])){
				$discounts = [];

				array_push($discounts, $coupons[$coupon]);

				$data = [
					'coupon' => $coupon
				];
				$this->_helper->updateCheckoutData($data);

				$this->_cart()->setDiscounts($discounts);
				$this->_cart()->refresh();
			} else {
				$this->setError(0, "Invalid Coupon!");
			}
		} else {
			$discounts = [];

			$data = [
				'coupon'
			];
			$this->_helper->removeCheckoutData($data);

			$this->_cart()->setDiscounts($discounts);
			$this->_cart()->refresh();
		}

		parent::output();
	}

	public function process(){

		if(!Post::stSentData()){
			Http::redirect("/");
		}

		$charges = $this->_cart()->getCharges();
		$lang = Language::get();
		$data = Post::stData();
		//$option = $data['shipping_option'];

		//\rdks\core\framework\Helper::pre($data);
		//\rdks\core\framework\Helper::pre(['shipping' => $shipping, 'subtotal' => $this->_cart()->getSubtotal()]);

		if(isset($data['checkout'])){

			$this->_helper->setOrderId(1701029736);

			Http::redirect("/cart/success");
		} else {
			$this->_helper->setCheckoutData($data);
			parent::output();
		}

	}

} 
