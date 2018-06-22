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
		$_info = [],
		$_success = [],
		$_correct = [],
		$_wrong = [],
		$_answer = "",
		$_badAnswer = false;

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

		if ($text == '[x]') {
			return ["EMPTY", $text];
		}

		if (preg_match('/^\[x\].+$/', $text)) {
			return ['NO_BULLET', $text];
		}

		if (preg_match('/^\[\*\].+$/', $text)) {
			return ['BULLET', $text];
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

	private function _prompt($text, $yn = false)
	{
		$this->reset();

		echo $text;
		$this->_answer = rtrim( fgets( STDIN ));

		if (!in_array($this->_answer, ['y','n']) && $yn) {
			$this->_badAnswer = true;
		}
	}

	private function _getOutput(array $arr = [], $bullet = "")
	{

		$lines = "";

		foreach ($arr as $key => $value) {

			if (is_array($value)) {

				if ($value[0] == 'EMPTY') {
					$lines .= self::line("");
				} else if ($value[0] == 'NO_BULLET') {
					$lines .= self::line(preg_replace('/^\[x\](.+)$/', '  $1', $value[1]));
				} else if ($value[0] == 'BULLET') {
					$lines .= self::line(preg_replace('/^\[\*\](.+)$/', '  * $1', $value[1]));
				}

			} else {
				$lines .= $bullet.$value;
			}
		}
		return (count($arr) > 0) ? $lines : self::LN;
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

	private function _getInfo()
	{
		return $this->_getList($this->_info);
	}

	private function _getSuccess()
	{
		return $this->_getList($this->_success);
	}

	private function _entered($option)
	{
		return ($this->getAnswer() == $option);
	}

	protected function reset()
	{
		$this->_warnings = [];
		$this->_errors = [];
		$this->_info = [];
		$this->_success = [];
		$this->_correct = [];
		$this->_wrong = [];
		$this->_badAnswer = false;
	}

	protected function info($message = "")
	{
		array_push($this->_info, self::line($message, 4));
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
			if ($this->_args[$key] != 1) {
				$value = $this->_args[$key];
			}
		}

		return $value;
	}

	protected function getFlag($key)
	{
		return isset($this->_flags[$key]);
	}

	protected function getAnswer()
	{
		return Helper::dataType($this->_answer);
	}

	protected function yes()
	{
		return $this->_entered("y");
	}

	protected function no()
	{
		return $this->_entered("n");
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
		$this->_prompt("{$text} ");
	}

	protected function promptYN($text)
	{
		$this->_prompt("{$text} [y/n]: ", true);
	}

	protected function output()
	{

		if ($this->_badAnswer) {

			$this->reset();
			$this->wrong("Unreconized option: '".$this->getAnswer()."'");
			$this->wrong("Set 'y' (yes) or 'n' (no)");

		}

		if (count($this->_info) > 0) {
			echo self::LN;
			$result = $this->_getInfo();
			self::_dialog("Message", $result, self::NOTE);
		}

		if (count($this->_success) > 0) {

			if (count($this->_info) == 0) echo self::LN; 

			$success = $this->_getSuccess();
			self::_dialog("Success", $success, self::SUCCESS);
		}

		if (count($this->_warnings) > 0) {

			if (count($this->_info) == 0 && count($this->_success) == 0) echo self::LN; 

			$warnings = $this->_getWarnings();
			self::_dialog("Warnings", $warnings, self::WARNING);
		}

		if (count($this->_errors) > 0) {

			if (count($this->_info) == 0 && count($this->_success) == 0 && count($this->_warnings) == 0) echo self::LN; 

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

	public function __construct(array $flags, array $args = [])
	{
		$c = 1;
		$this->_flags = $flags;

		foreach ($args as $key => $value) {

			if ($c > 1) {
				$this->_args[$key] = $value;
			}

			$c++;
		}
	}

} 