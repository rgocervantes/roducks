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

class CLI extends Frame
{

	const SUCCESS = 1;
	const FAILURE = 2;
	const WARNING = 3;
	const NOTE = 4;
	const SPACE = "                                                                                                       ";
	const LN = "\n";

	private 
		$_args = [],
		$_flags = [],
		$_warnings = [],
		$_errors = [],
		$_result = [],
		$_success = [],
		$_correct = [],
		$_wrong = [],
		$_answer = "";

	protected $_params = [];	

	static function line($text, $px = 0, $break = true)
	{
		
		$space = self::SPACE;
		$long1 = strlen($space);
		$long2 = strlen($text);
		$diff = $long1 - $long2 - $px;
		$long = substr($space, 0, $diff);
		$ln = self::LN;

		if (!$break) {
			$ln = "";
		}

		return $text.$long.$ln;

	}

	static private function _colorize($text, $status)
	{
		$out = "";

		switch($status) {
			case self::SUCCESS:
			$out = "[42m"; //Green background
			break;
			case self::FAILURE:
			$out = "[41m"; //Red background
			break;
			case self::WARNING:
			$out = "[43m"; //Yellow background
			break;
			case self::NOTE:
			$out = "[44m"; //Blue background
			break;
		}

		return chr(27) . "{$out}{$text}" . chr(27);
	}

	static private function _dialog($title, $output, $color)
	{

		echo self::_colorize(self::SPACE, $color) . self::LN;
		if (!is_null($title)) echo self::_colorize(self::line("  {$title}: "), $color);
		if (!is_null($title)) echo self::_colorize(self::SPACE, $color) . self::LN;
		echo self::_colorize($output, $color);
		echo self::_colorize(self::SPACE, $color) . self::LN;

		echo "\033[0m".self::LN;		
	}

	static function println($text, $color = "NOTE", $px = 0)
	{
		echo self::LN;

		echo self::_colorize(self::SPACE, $color) . self::LN;
		echo self::_colorize(self::line("  {$text}", $px), $color);
		echo self::_colorize(self::SPACE, $color) . self::LN;
		echo "\033[0m".self::LN;

	}

	private function _getOutput(array $arr = [], $bullet = "")
	{
		return (count($arr) > 0) ? $bullet . implode($bullet, $arr) : self::LN;
	}

	private function _getList(array $arr = [])
	{
		return $this->_getOutput($arr, "  - ");
	}

	private function _getLines(array $arr = [])
	{
		return $this->_getOutput($arr, "  ");
	}

	private function _color($text, $code)
	{
		return "\033[{$code} {$text}\033[0m";
	}

	private function _getWarnings()
	{
		return $this->_getList($this->_warnings);
	}

	private function _getErrors() {
		return $this->_getList($this->_errors);
	}

	private function _getResult()
	{
		return $this->_getList($this->_result);
	}

	private function _getSuccess()
	{
		return $this->_getList($this->_success);
	}

	protected function result($message = "")
	{
		array_push($this->_result, self::line($message, 4));
	}

	protected function success($message = "")
	{
		array_push($this->_success, self::line($message, 4));
	}

	protected function warning($message = "")
	{
		array_push($this->_warnings, self::line($message, 4));
	}

	protected function error($message = "")
	{
		array_push($this->_errors, self::line($message, 4));
	}

	protected function correct($message = "")
	{
		array_push($this->_correct, self::line($message, 2));
	}

	protected function wrong($message = "")
	{
		array_push($this->_wrong, self::line($message, 2));
	}

	protected function getParam($key, $value = "")
	{
		if (isset($this->_args[$key])) {
			return $this->_args[$key];
		}

		return $value;
	}

	protected function getFlag($key)
	{
		return isset($this->_flags[$key]);
	}

	protected function getAnswer()
	{
		return $this->_answer;
	}

	protected function entered($option)
	{
		return ($this->getAnswer() == $option);
	}

	protected function yes()
	{
		return $this->entered("y");
	}

	protected function no()
	{
		return $this->entered("n");
	}

	protected function yesNo()
	{
		$answer = $this->getAnswer();

		if (!in_array($answer, ["y","n"])) {
			$this->wrong("Unknown option: " . $answer);
		}
	}

	protected function colorGreen($text)
	{
		return $this->_color($text, "0;32m");
	}

	protected function colorRed($text)
	{
		return $this->_color($text, "0;31m");
	}

	protected function bgGreenColorRed($text)
	{
		$text = self::line($text, 4, false);
		return "\033[0;31m\033[42m{$text}\033[0m";
	}

	protected function prompt($text)
	{
		$prompt = "{$text}: ";
		echo $prompt;
		$answer = rtrim( fgets( STDIN ));
		$this->_answer = $answer;
	}

	protected function promptConfirm($text)
	{
		$this->prompt("{$text} [y/n]");
	}

	protected function output()
	{
		
		if (count($this->_result) > 0) {
			echo self::LN;
			$result = $this->_getResult();
			self::_dialog("Message", $result, self::NOTE);
		}

		if (count($this->_success) > 0) {

			if (count($this->_result) == 0) echo self::LN; 

			$success = $this->_getSuccess();
			self::_dialog("Success", $success, self::SUCCESS);
		}

		if (count($this->_warnings) > 0) {

			if (count($this->_result) == 0 && count($this->_success) == 0) echo self::LN; 

			$warnings = $this->_getWarnings();
			self::_dialog("Warnings", $warnings, self::WARNING);
		}

		if (count($this->_errors) > 0) {

			if (count($this->_result) == 0 && count($this->_success) == 0 && count($this->_warnings) == 0) echo self::LN; 

			$errors = $this->_getErrors();
			self::_dialog("Errors", $errors, self::FAILURE);
		}

		if (count($this->_correct) > 0) {

			echo self::LN; 
			$output = $this->_getLines($this->_correct);
			self::_dialog(null, $output, self::SUCCESS);
		}

		if (count($this->_wrong) > 0) {

			echo self::LN;
			$output = $this->_getLines($this->_wrong);
			self::_dialog(null, $output, self::FAILURE);
		}

	}

	protected function reset()
	{
		$this->_warnings = [];
		$this->_errors = [];
		$this->_result = [];
		$this->_success = [];
		$this->_correct = [];
		$this->_wrong = [];
	}

	public function __construct(array $args = [])
	{

		$p = 0;
		$c = 1;

		$this->_flags = $args;

		foreach ($args as $key => $value) {

			if ($c > 2) {

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