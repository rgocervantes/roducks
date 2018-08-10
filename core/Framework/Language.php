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

use Roducks\Libs\Data\Cookie;
use Roducks\Libs\Request\Http;

class Language
{

	const COOKIE_ID = "RDKS_LANG";
	const ID_ENGLISH = 1;
	const ID_ESPANOL = 2;

	static function getList()
	{

		$langs = [];

		$langs['en'] = [
					'id' => self::ID_ENGLISH,
					'title' => "English",
					'iso' => "en",
					'iso2' => "us",
					'img' => "lang/us.png",
					'icon' => Path::getIcon("lang/us.png")
		];

		$langs['es'] = [
					'id' => self::ID_ESPANOL,
					'title' => "Español",
					'iso' => "es",
					'iso2' => "mx",
					'img' => "lang/mx.png",
					'icon' => Path::getIcon("lang/mx.png")
		];

		return $langs;

	}

	static function getIso($id)
	{
		$langs = self::getList();
		$iso = "en";

		foreach ($langs as $key => $value) {
			if ($value['id'] == $id) {
				$iso = $key;
				break;
			}
		}

		return $iso;
	}

	static function isMultilanguage()
	{
		return MULTILANGUAGE;
	}

	static function getDefault()
	{

		if (MULTILANGUAGE && BROWSER_LANGUAGE) {
			return Http::getBrowserLanguage(DEFAULT_LANGUAGE);
		}

		return DEFAULT_LANGUAGE;

	}

	static function set($value)
	{

		$list = array_keys(self::getList());

		if (preg_match('/^\w{2}$/', $value) && in_array($value, $list)) {
			Cookie::set(self::COOKIE_ID, $value, DOMAIN_NAME);
			return true;
		}

		return false;
	}

	static function get()
	{
		return (Cookie::exists(self::COOKIE_ID)) ? Cookie::get(self::COOKIE_ID) : self::getDefault();
	}

	static function getId($iso = null)
	{
		$list = self::getList();
		$iso = (is_null($iso)) ? self::get() : $iso;

		return (isset($list[$iso])) ? $list[$iso]['id'] : $list[DEFAULT_LANGUAGE]['id'];
	}

	static function english()
	{
		return (self::get() == "en");
	}

	static function spanish()
	{
		return (self::get() == "es");
	}

	static function toEnglish($es, $en)
	{
		if (self::english()) {
			return $en;
		}

		return $es;
	}

	static function toSpanish($en, $es)
	{
		if (self::spanish()) {
			return $es;
		}

		return $en;
	}

	static function translate($en, $es)
	{
		return self::toSpanish($en, $es);
	}

}
