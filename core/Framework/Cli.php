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

use Roducks\Libs\Utils\Date;
use Roducks\Page\Frame;

class Cli extends Frame{

	const LINE_DIVISOR = "-------------------------------------------------------------------------------------------------\n";

	private 
		$_args = [],
		$_flags = [],
		$_warnings = [],
		$_errors = [],
		$_result = [];

	protected $_params = [];	

	static function println($message){
		echo self::LINE_DIVISOR;
		echo "{$message}\n";
		echo self::LINE_DIVISOR;
	}

	private function _getList(array $arr = []){
		return (count($arr) > 0) ? "\n- " . implode("- ", $arr) : "\n";
	}

	private function _getAlerts($title, array $arr = []){
		$alerts = $this->_getList($arr);

		echo "{$title}: " . $alerts;
		echo self::LINE_DIVISOR;
	}

	private function _getWarnings(){
		$this->_getAlerts("Warnings", $this->_warnings);
	}

	private function _getErrors(){
		$this->_getAlerts("Errors", $this->_errors);
	}

	private function _getStatus(){

		if(count($this->_warnings) > 0){
			$this->_getWarnings();
		}

		if(count($this->_errors) > 0){
			$this->_getErrors();
		}	

		if(count($this->_warnings) == 0 && count($this->_errors) == 0){
			echo "Status: OK!\n";
			echo self::LINE_DIVISOR;
		}	

	}

	protected function getParam($key, $value = ""){
		if(isset($this->_args[$key])){
			return $this->_args[$key];
		}

		return $value;
	}

	protected function getFlag($key){
		return isset($this->_flags[$key]);
	}

	protected function setResult($message = ""){
		array_push($this->_result, $message."\n");
	}

	protected function setWarning($message = ""){
		array_push($this->_warnings, $message."\n");
	}

	protected function setError($message = ""){
		array_push($this->_errors, $message."\n");
	}	

	protected function output(){
		 
		$output = $this->_getList($this->_result);

		echo self::LINE_DIVISOR;
		echo "Executed @: " . Date::getCurrentDateTime() . "\n";
		echo self::LINE_DIVISOR;
		echo "Message: {$output}";
		echo self::LINE_DIVISOR;

		$this->_getStatus();

		echo self::LINE_DIVISOR;
	
	}

	public function __construct(array $args = []){

		$p = 0;
		$c = 1;

		$this->_flags = $args;

		foreach ($args as $key => $value) {

			if($c > 2) {

				if ($value == 1) {
					if (isset($this->_params[$p])) {
						$k = $this->_params[$p];
						$this->_args[$k] = $key;
						$p++;
					}

				} else {
					$this->_args[$key] = $value;
				}

			}

			$c++;
		}
		
	}

} 