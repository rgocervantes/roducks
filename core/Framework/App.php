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

class App
{

	const COMPOSER_CLASS_MAP = 'vendor/composer/autoload_classmap.php';

	static $aliases = [],
		$composer = [];

	static function define($name, $value)
	{
		if (!defined($name)) {
			define($name, $value);
		}
	}

	static function root($dir)
	{
		$root = str_replace('/public', '', $dir) . DIRECTORY_SEPARATOR;
		self::define('RDKS_ROOT', $root);
	}

	static function text($name, $value)
	{
		self::define("TEXT_{$name}", $value);
	}

	static function getRealPath($dir)
	{
		$path = RDKS_ROOT . $dir;
	  $fileExists = file_exists($path);

		return [$path, $fileExists];
	}

	static function getRealFilePath($path)
	{
		list($realPath, $fileExists) = self::getRealPath($path);
		return $realPath;
	}

	static function fileExists($path)
	{
		list($realPath, $fileExists) = self::getRealPath($path);
		return $fileExists;
	}

	static function getComposerPath($path)
	{

		$fileExists = file_exists($path);

	    return [$path, $fileExists];
	}

	static function getComposerMap()
	{

		list($realPath, $fileExists) = self::getRealPath(self::COMPOSER_CLASS_MAP);

		if ($fileExists) {
			return include $realPath;
		}

		return [];
	}

	static function getText($var, $default = "")
	{
		$text = 'TEXT_' . strtoupper($var);

		if (!defined($text)) {
			return $default;
		}

		return constant($text);
	}

}

function handler($errno, $errstr, $errfile, $errline)
{

	if (php_sapi_name() == 'cli') {
		\Roducks\Framework\CLI::phpError($errno, $errstr, $errfile, $errline);
	} else {
		\Roducks\Framework\Error::phpError($errno, $errstr, $errfile, $errline);
	}

}

function _text($var, $default = "")
{
	return App::getText($var, $default);
}

/**
 *	Translate constants
 */
function __($var, $default = "true")
{

	if ($default == "true" && !preg_match('/\s/', $var)) {
		preg_match('/^([a-zA-Z]+)([\.!\?]+)?$/', $var, $char);
		$schar = (isset($char[2])) ? $char[2] : '';
		return _text($char[1]) . $schar;
	}

	if (!isset($_COOKIE['RDKS_LANG']) || $_COOKIE['RDKS_LANG'] == 'en') {
		return $var;
	}

	$var = preg_replace('/it\'s not/i', 'ITS_NOT', $var);
	$var = preg_replace('/it does(\snot|n\'t)/i', 'IT_DOES_NOT', $var);
	$var = preg_replace('/it did(\snot|n\'t)/i', 'IT_DID_NOT', $var);
	$var = preg_replace('/are you/i', 'ARE_YOU', $var);
	$var = preg_replace('/you are/i', 'YOU_ARE', $var);
	$var = preg_replace('/you are not/i', 'YOU_ARE_NOT', $var);
	$var = preg_replace('/sure you don\'t want/i', 'SURE_YOU_DONT_WANT', $var);
	$var = preg_replace('/sure you want/i', 'SURE_YOU_WANT', $var);
	$var = preg_replace('/you want/i', 'YOU_WANT', $var);
	$var = preg_replace('/is not available/i', 'IS_NOT_AVAILABLE', $var);
	$var = preg_replace('/is not/i', 'IS_NOT', $var);
	$var = preg_replace('/is available/i', 'IS_AVAILABLE', $var);
	$var = preg_replace('/are not/i', 'ARE_NOT', $var);
	$var = preg_replace('/was not/i', 'WAS_NOT', $var);
	$var = preg_replace('/does not/i', 'DOES_NOT', $var);
	$var = preg_replace('/they have been/i', 'THEY_HAVE_BEEN', $var);
	$var = preg_replace('/have been/i', 'HAVE_BEEN', $var);
	$var = preg_replace('/it has been/i', 'IT_HAS_BEEN', $var);
	$var = preg_replace('/has been/i', 'HAS_BEEN', $var);
	$var = preg_replace('/have not been/i', 'HAVE_NOT_BEEN', $var);
	$var = preg_replace('/has not been/i', 'HAS_NOT_BEEN', $var);
	$var = preg_replace('/(it )?was(\snot|n\'t)/i', 'IT_WAS_NOT', $var);
	$var = preg_replace('/it was/i', 'IT_WAS', $var);
	$var = preg_replace('/it will be/i', 'IT_WILL_BE', $var);
	$var = preg_replace('/will be/i', 'WILL_BE', $var);
	$var = preg_replace('/the option/i', 'THE_OPTION', $var);
	$var = preg_replace('/this item/i', 'THIS_ITEM', $var);
	$var = preg_replace('/these items/i', 'THESE_ITEMS', $var);
	$var = preg_replace('/at the moment/i', 'AT_THE_MOMENT', $var);
	$var = preg_replace('/development mode/i', 'DEVELOPMENT_MODE', $var);
	$var = preg_replace('/app is/i', 'APP_IS', $var);
	$var = preg_replace('/thank you/i', 'THANK_YOU', $var);
	$var = preg_replace('/to delete/i', 'TO_DELETE', $var);
	$var = preg_replace('/to create/i', 'TO_CREATE', $var);
	$var = preg_replace('/to active/i', 'TO_ACTIVE', $var);
	$spaces = explode(" ", $var);
	$words = [];

/*
	echo '<pre>';
	print_r($spaces);
	echo '</pre>';
*/
	foreach ($spaces as $key => $value) {

		$s = '';

		$char = preg_replace('/^[a-zA-Z_\s]+([,;\.!\?]+)?$/', '$1', $value);
		$value = str_replace([',',';','.','!','?'], '', $value);

		$lastLetter = substr($value, -1);
		if ($lastLetter == 's' && substr($value, -3) != 'ies' && !in_array($value, ['Is','is','This','this','Has','has'])) {
			$value = substr($value, 0 , -1);
			$s = 's';
		}

		$text = App::getText($value, $value);
		$lastLetterText = substr($text, -1);

		if ($key > 0) {

			if (preg_match('/^new$/i', $spaces[$key-1]) && substr($text, -1) == 'a') {
				$art = substr($spaces[$key-1], 0, 1);
				$words[$key-1] = ($art == mb_strtoupper($art)) ? 'Nueva' : 'nueva';
			}

			if (preg_match('/^the$/i', $spaces[$key-1]) && $lastLetterText != 'a' && $lastLetterText != 's' && $_COOKIE['RDKS_LANG'] == 'es') {
				$art = substr($spaces[$key-1], 0, 1);
				$words[$key-1] = ($art == mb_strtoupper($art)) ? 'El' : 'el';
			}

			if ((preg_match('/^the$/i', $spaces[$key-1]) && $_COOKIE['RDKS_LANG'] == 'es') && (substr($text, -1) == 's' || substr($text, -1) == 'a') && !empty($s)) {
				$art = substr($spaces[$key-1], 0, 1);
				$words[$key-1] = ($art == mb_strtoupper($art)) ? 'Las' : 'las';
			}

			if (preg_match('/^(is_|was_|it_was_)?not$/i', $spaces[$key-1]) || preg_match('/^(has|have)_(not_)?been$/i', $spaces[$key-1])) {
				$subject = substr($words[$key-2], -1);
				if ($subject == 'a') {
					$text = substr($text, 0, -1) .'a';
				}
			}

			if (preg_match('/^have_(not_)?been$/i', $spaces[$key-1])
			|| preg_match('/^were$/i', $spaces[$key-1])
			|| preg_match('/^they_have_been$/i', $spaces[$key-1])
			) {
				if ((
						preg_match('/^have_(not_)?been$/i', $spaces[$key-1]) ||
						preg_match('/^were$/i', $spaces[$key-1])
					)
						&& substr($spaces[$key-2], -1) == 's'
				) {

					if (substr($words[$key-2], -2) == 'as') {
						$text = substr($text, 0, -1).'as';
					} else {
						$text = $text.'s';
					}

				} else {
					$text = $text.'s';
				}
			}

			if (preg_match('/^(it|them)$/i', $value)) {
				$words[$key-1] = $words[$key-1].mb_strtolower($text);
				$text = '';
			}
		}

		$firstLetter = (!preg_match('#_#', $value)) ? substr($value, 0, 1) : 'yyyyy';
		$word = ($firstLetter == strtoupper($firstLetter)) ? $text : mb_strtolower($text);

		if ($key == 0 && preg_match('#_#', $value)) {
			$word = ucfirst($word);
		}

		if (!empty($s)) {
			if (!in_array(substr($word, -1), ['a','e','i','o','u'])) {
				$word = $word.'e';
			}

			if (!empty($s) && substr($text, -1) != 'a') {
				$words[$key-1] = 'los';
			}
		}

		if (preg_match('/^[\¿]/', $word)) {
			$word = str_replace('¿','',$word);
			$word = '¿'.ucfirst($word);
		}

		$words[] = $word.$s.$char;
	}

	$phrase = implode(" ", $words);
	$phrase = ucfirst($phrase);

	return preg_replace('/\s([\.,;\?])/', '$1', $phrase);
}
