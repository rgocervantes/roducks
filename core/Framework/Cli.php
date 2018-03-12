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

	const SPACE = "                                                                                                       ";
	const LN = "\n";

	private 
		$_args = [],
		$_flags = [],
		$_warnings = [],
		$_errors = [],
		$_result = [],
		$_success = [],
		$_answer = "";

	protected $_params = [];	

	static function line($text, $px = 0){
		
		$space = self::SPACE;
		$long1 = strlen($space);
		$long2 = strlen($text);
		$diff = $long1 - $long2 - $px;
		$long = substr($space, 0, $diff);

		return $text.$long.self::LN;

	}

	static function println($text){
		echo self::line($text);
	}

	private function _getList(array $arr = []){
		$bullet = "  - ";
		return (count($arr) > 0) ? $bullet . implode($bullet, $arr) : self::LN;
	}

	private function _getWarnings(){
		return $this->_getList($this->_warnings);
	}

	private function _getErrors(){
		return $this->_getList($this->_errors);
	}

	private function _getResult(){
		return $this->_getList($this->_result);
	}

	private function _getSuccess(){
		return $this->_getList($this->_success);
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
		array_push($this->_result, self::line($message, 4));
	}

	protected function setSuccess($message = ""){
		array_push($this->_success, self::line($message, 4));
	}

	protected function setWarning($message = ""){
		array_push($this->_warnings, self::line($message, 4));
	}

	protected function setError($message = ""){
		array_push($this->_errors, self::line($message, 4));
	}	

	private function _colorize($text, $status) {
		$out = "";

		switch($status) {
			case "SUCCESS":
			$out = "[42m"; //Green background
			break;
			case "FAILURE":
			$out = "[41m"; //Red background
			break;
			case "WARNING":
			$out = "[43m"; //Yellow background
			break;
			case "NOTE":
			$out = "[44m"; //Blue background
			break;
		}

		return chr(27) . "{$out}{$text}" . chr(27);
	}

	private function _dialog($title, $output, $color){

		echo $this->_colorize(self::SPACE, $color) . self::LN;
		echo $this->_colorize(self::line("  {$title}: "), $color);
		echo $this->_colorize(self::SPACE, $color) . self::LN;
		echo $this->_colorize($output, $color);
		echo $this->_colorize(self::SPACE, $color) . self::LN;

		echo "\033[0m".self::LN;		
	}

	protected function prompt($text){
		$prompt = "{$text}: ";
		echo $prompt;
		$answer =  rtrim( fgets( STDIN ));
		$this->_answer = $answer;
		echo "\033[0;32m {$answer}\033[0m".self::LN;
	}

	protected function output(){
		
		echo self::LN;
		$result = $this->_getResult();
		$this->_dialog("Message", $result, "NOTE");

		if (count($this->_success) > 0) {

			if(count($this->_result) == 0) echo self::LN; 

			$success = $this->_getSuccess();
			$this->_dialog("Success", $success, "SUCCESS");
		}

		if (count($this->_warnings) > 0) {

			if(count($this->_result) == 0 && count($this->_success) == 0) echo self::LN; 

			$warnings = $this->_getWarnings();
			$this->_dialog("Warnings", $warnings, "WARNING");
		}

		if (count($this->_errors) > 0) {

			if(count($this->_result) == 0 && count($this->_success) == 0 && count($this->_warnings) == 0) echo self::LN; 

			$errors = $this->_getErrors();
			$this->_dialog("Errors", $errors, "FAILURE");
		}

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