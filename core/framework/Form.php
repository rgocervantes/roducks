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

use rdks\core\libs\Data\Session;
use rdks\core\libs\Protocol\Http;

class Form {

	const HASH_KEY = "RDKS_HASH_KEY";

	const FILTER_STRING = 1;
	const FILTER_INTEGER = 2;
	const FILTER_DATE_YYYY_MM_DD = 3;
	const FILTER_DATE_DD_MM_YYYY = 4;
	const FILTER_EMAIL = 5;
	const FILTER_URL = 6;
	const FILTER_WORD = 7;
	const FILTER_WORDS = 8;
	const FILTER_DATETIME = 9;

	static function getKey(){
		$token = Login::getToken();
		Session::set(self::HASH_KEY, $token);
		return $token;
	}

	static function hash($token = ""){
		if( (Session::exists(self::HASH_KEY) && $token != Session::get(self::HASH_KEY)) || empty($token) ){
			Http::setHeaderInvalidRequest();
		} else {
			Session::reset(self::HASH_KEY);
		}
	}

	/**
	*	@param $arr array	
	*	@return array|false
	*/
	static function validation($arr){

		$filters = [];
		$total = count($arr);
		$count = 0;
		$valid = true;

		foreach($arr as $key => $value):

			$k = array_keys($value);
			$v = array_values($value);
			
			switch ($k[0]):
				case self::FILTER_STRING:
					$rule = Helper::VALID_STRING; // allows *everything*
					break;

				case self::FILTER_WORD:
					$rule = Helper::VALID_WORD;
					break;					

				case self::FILTER_WORDS:
					$rule = Helper::VALID_WORDS;
					break;	

				case self::FILTER_INTEGER:
					$rule = Helper::VALID_INTEGER;
					break;

				case self::FILTER_DATETIME:
					$rule = Helper::VALID_DATETIME;
					break;

				case self::FILTER_DATE_YYYY_MM_DD:
					$rule = Helper::VALID_DATE_YYYY_MM_DD;
					break;

				case self::FILTER_DATE_DD_MM_YYYY:
					$rule = Helper::VALID_DATE_DD_MM_YYYY;
					break;

				case self::FILTER_EMAIL:
					$rule = Helper::VALID_EMAIL;
					break;

				case self::FILTER_URL:
					$rule = Helper::VALID_URL;
					break;									
			endswitch;

			if(Helper::regexp($rule, $v[0])){
				$count++;
			}

			$filters[] = $v[0];

		endforeach;		

		if($count < $total){
			$valid = false;
		}

		return ['valid' => $valid, 'filters' => $filters];
	}

	static function isValid($form){
		if($form['valid'] !== FALSE){
			return true;
		}

		return false;
	}

}