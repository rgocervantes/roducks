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

namespace Roducks\Framework;

use Roducks\Libs\Data\Session;
use Roducks\Libs\Request\Http;

class Form
{

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
	const FILTER_DECIMAL = 10;

	private $_valid = true;
	private $_message = 'Form is Ok!';
	private $_field = '';

	static function getKey()
	{
		if (!empty(Http::getUserAgent())) {
			$token = Hash::getToken();
			Session::set(self::HASH_KEY, $token);
			return $token;
		}

		return null;

	}

	static function setKey($token = "")
	{
		if ( (Session::exists(self::HASH_KEY) && $token != Session::get(self::HASH_KEY)) || empty($token) ) {
			Error::message('Bad Request.');
		} else {
			Session::reset(self::HASH_KEY);
		}
	}

	static function filter($filter, $data, $message = "Error", $field = "")
	{
		return [
			'field' => $field,
			'filter' => $filter,
			'data' => $data,
			'message' => $message
		];
	}

	static function values(array $values)
	{
		return $values;
	}

	static function match($text)
	{
		return self::values([$text]);
	}

	static function regexp($rule)
	{
		return self::values(['regexp' => $rule]);
	}

	static function greaterThan($n)
	{
		return self::values(['greater_than' => $n]);
	}

	static function lessThan($n)
	{
		return self::values(['less_than' => $n]);
	}

	/**
	*	@param $filters array
	*	@return bool
	*/
	static function validation(array $filters)
	{
		return new Form($filters);
	}

	public function __construct(array $filters)
	{

		$alert = [];
		$error = 0;

		foreach($filters as $value):

			if (is_array($value['filter'])) {

				if (isset($value['filter']['regexp'])) {

					if (!Helper::regexp($value['filter']['regexp'], $value['data'])) {
						$error++;
						array_push($alert, ['message' => $value['message'], 'field' => $value['field']]);
					}

				} else if (isset($value['filter']['greater_than'])) {

					if (intval($value['data']) < $value['filter']['greater_than']) {
						$error++;
						array_push($alert, ['message' => $value['message'], 'field' => $value['field']]);
					}

				} else if (isset($value['filter']['less_than'])) {

					if (intval($value['data']) > $value['filter']['less_than']) {
						$error++;
						array_push($alert, ['message' => $value['message'], 'field' => $value['field']]);
					}

				} else {

					if (!in_array($value['data'], $value['filter'])) {
						$error++;
						array_push($alert, ['message' => $value['message'], 'field' => $value['field']]);
					}
				}

			} else {

				switch ($value['filter']):
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

					case self::FILTER_DECIMAL:
						$rule = Helper::VALID_DECIMAL;
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

				if (!Helper::regexp($rule, $value['data'])) {
					$error++;
					array_push($alert, ['message' => $value['message'], 'field' => $value['field']]);
				}

			}

		endforeach;

		if ($error > 0) {
			$this->_valid = false;
			$this->_message = $alert[0]['message'];
			$this->_field = $alert[0]['field'];
		}

	}

	public function success()
	{
		return $this->_valid;
	}

	public function getMessage()
	{
		return $this->_message;
	}

	public function getField()
	{
		return $this->_field;
	}

}
