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

namespace rdks\core\page;

use rdks\core\framework\Core;
use rdks\core\framework\Helper;

class Block extends Frame {

	var $rdks = 0;

	protected $_pageType = 'BLOCK';
	
	public function __construct($settings, View $view){
		parent::__construct($settings);

		$this->view = $view;
		$this->view->parentPage($this->_getParentClassName());		

	}

	protected function getArray($values){

		if(empty($values)){
			return [];
		}

		if(!is_array($values)){
			$values = explode("_", $values);
			$values = Helper::getPairParams($values);
		}

		return $values;
	}

	static private function _getBlockPath($path){

		$params = [];
		$block = $path;
		$method = "output";

		if(Helper::hasSlashes($path)){

			$slashes = explode("/", $path);

			$block = $slashes[0];
			unset($slashes[0]);
			
			if(isset($slashes[1])){
				$method = $slashes[1];
				unset($slashes[1]);
			} 

			$params = Helper::resetArray($slashes);

		}

		return [$block, $method, $params];

	}

	/**
	*	Block::load("Home");
	*	Block::load("Home/output");
	*	Block::load("Home/output/bar/12345");
	*	Block::load("Home", ["foo" => "bar", 'id' => 12345] );	
	*/
	static function load($path, array $extraParams = [], array $queryString = []){

		list($block, $method, $params) = self::_getBlockPath($path);
		$block = Helper::getCamelName($block);
		$method = Helper::getCamelName($method, false);

		if(count($extraParams) > 0){
			$params = array_merge($params, $extraParams);
		}

		Core::loadPage(Core::getBlocksPath($block), $block, $method, $queryString, $params);

	}

	/**
	*	Block::getData("Home")->results();
	*/
	static function getData($path){

		list($block, $method, $params) = self::getBlockPath($path);

		return Core::loadPage(Core::getBlocksPath($block), $block, "output", array(), array(), true);
		
	}


} 