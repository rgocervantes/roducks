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

use Roducks\Libs\Request\Http;

class Post
{

	static function init()
	{
		$ins = new Post;
		return $ins;
	}

	static function value($name, $clean = false)
	{
		$v = $_POST[$name];

		if (!$clean) {
			return $v;
		}

		$v = trim($v);
		$v = strip_tags($v);

		return (is_integer($v)) ? intval($v) : $v;
	}

	static function stData()
	{
		return $_POST;
	}

	static function stSentData()
	{
		return (count(self::stData()) > 0);
	}

	static function stRequired()
	{
		if (Http::getRequestMethod() != 'POST' || !self::stSentData()) {
			Http::setHeaderInvalidRequest();
		}
	}

	static function stSent($name)
	{
		return isset($_POST[$name]);
	}

	static function stValue($name, $default = "", $clean = true, $returnDefault = false)
	{

		if (!self::stSent($name)) {
			return $default;
		}

		$value = (is_array($_POST[$name])) ? $_POST[$name] : self::value($name, $clean);

		if ($returnDefault && empty($value)) {
			return $default;
		}

		return $value;
	}

	public function sentData()
	{
		return self::stSentData();
	}

	public function data()
	{
		return self::stData();
	}

	public function required()
	{
		self::stRequired();
	}

	public function sent($name)
	{
		return self::stSent($name);
	}

	public function text($name, $default = "", $returnDefault = false)
	{
		return self::stValue($name, $default, true, $returnDefault);
	}

	public function param($name, $default = "", $returnDefault = false)
	{
		return $this->text($name, $default, true, $returnDefault);
	}

	public function hidden($name, $default = "", $returnDefault = false)
	{
		return $this->text($name, $default, true, $returnDefault);
	}

	public function password($name, $default = "", $returnDefault = false)
	{
		return $this->text($name, $default, true, $returnDefault);
	}

	public function checkbox($name, $default = "", $returnDefault = false)
	{
		return $this->text($name, $default, true, $returnDefault);
	}

	public function radio($name, $default = "", $returnDefault = false)
	{
		return $this->text($name, $default, true, $returnDefault);
	}

	public function textarea($name, $default = "", $returnDefault = false)
	{
		return $this->text($name, $default, true, $returnDefault);
	}

	public function textareaRich($name, $default = "")
	{
		return self::stValue($name, $default, false);
	}

	public function filter($name, $text = "")
	{

		$value = self::stValue($name, $text, false);

		if (preg_match('#[|]#', $value)) {
			list($value, $text) = explode("|", $value);
		}

		$ret = new \stdClass;
		$ret->value = Helper::dataType($value);
		$ret->text = $text;

		return $ret;
	}

	public function select($name, $text = "")
	{
		return $this->filter($name, $text)->value;
	}

}
