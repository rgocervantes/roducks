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

class Helper
{

	const REGEXP_HTTP = '/^https?.+/';
	const REGEXP_CONDITIONAL = '/^\((.+)\)$/';
	const REGEXP_INTEGER = '/^\d+$/';

	const REGEXP_OPTIONAL_PARAM = '/^optional_\w+$/';
	const REGEXP_SLASHES = '/\//';
	const REGEXP_RELATIVE_URL = '/^\/(.+)$/';
	const REGEXP_API = '(?P<API>[0-9\.]+)';
	const REGEXP_STRING_ALL = '/^.+$/';
	const REGEXP_FILE_EXT = '/\.([a-z]+)$/';
	const REGEXP_FILE_EXT_VERSION = '/\.([a-z]+)(\?v=[0-9.]+)?$/';
	const REGEXP_URL_DISPATCH = '[a-z\-\_]+(\/[a-z\-_]+(\/[a-zA-Z0-9\-\+\/_%]+)?)?';
	const REGEXP_GET_MODULE = '/^([a-zA-Z\-]+)\/.+$/';
	const REGEXP_IS_URL_DISPATCH = '/^_/';
	const REGEXP_IS_BLOCK_DISPATCHED = '/^_block/';
	const REGEXP_IS_SERVICE = '/Services\/[a-zA-Z_]+$/';
	const REGEXP_IS_API = '/^API\/[a-zA-Z_]+$/';
	const REGEXP_IS_MODULE = '#Modules#';
	const REGEXP_IS_JSON = '#JSON#';
	const REGEXP_IS_PAGE = '#Page#';
	const REGEXP_IS_BLOCK = '#Blocks#';
	const REGEXP_IS_XML = '#XML#';
	const REGEXP_IS_OBSERVER = '#Observers#';
	const REGEXP_IS_FACTORY = '#Factory#';
	const REGEXP_PATH = '/^([a-zA-Z_\/]+\/)(\w+)$/';
	const REGEXP_SITES = '[a-zA-Z0-9_]+';

	const VALID_DATETIME = '/^(\d{4}-\d{2}-\d{2})\s(\d{2}:\d{2}:\d{2})$/';
	const VALID_DATE_YYYY_MM_DD = '/^(\d{4})-(\d{2})-(\d{2})$/';
	const VALID_DATE_DD_MM_YYYY = '/^\(d{2})-(\d{2})-(\d{4})$/';
	const VALID_EMAIL = '/^([a-z0-9]+\-?[a-z0-9]+)+(\.([a-z0-9]+\-?[a-z0-9]+)+)?@([a-z0-9]+\-?[a-z0-9]+)+\.[a-z]{2,3}(\.[a-z]{2,3})?$/';
	const VALID_URL = '/^https?:\/\/([a-z0-9]+\-?[a-z0-9]+\.)?([a-z0-9]+\-?[a-z0-9]+\.)?([a-z0-9]+\-?[a-z0-9]+)+\.[a-z]{2,3}(\.[a-z]{2,3})?$/';
	const VALID_STRING = '/^.+$/';
	const VALID_PASSWORD = '/^[a-zA-Z0-9#$@\.,;!]+$/';
	const VALID_CLABE = '/^[a-zA-Z]+$/';
	const VALID_PARAM = '/^([a-zA-Z]+_)?[a-zA-Z]+$/';
	const VALID_WORD = '/^[a-zA-ZáéíóúñÁÉÍÓÚÑ]+$/';
	const VALID_WORDS = '/^[a-zA-Z\sáéíóúñÁÉÍÓÚÑ]+$/';
	const VALID_INTEGER = '/^\d+$/';
	const VALID_DECIMAL = '/^\d+\.\d{2}$/';
	const VALID_BOOL = '/^(0|1)$/';
	const VALID_USERNAME = '/^[a-z]+\.[a-z]+$/';
	const VALID_HTML = '/\.html$/';
	const VALID_IMAGE = '/\.(jpeg|jpg|png)$/';
	const VALID_JSON = '/\.json$/';
	const VALID_XML = '/\.xml$/';
	const VALID_TPL = '/\.tpl$/';

	const PAGE_NOT_FOUND = "Roducks\Page\Page";

	static function regexp($regexp, $param)
	{
		if (preg_match($regexp, $param)) {
			return true;
		}

		return false;
	}

	static function getMatches($regexp, $param)
	{
		if (preg_match($regexp, $param, $matches)) {
			return $matches[1];
		}

		return [];
	}

	static function isDispatch($dispatch)
	{
		return self::regexp(self::REGEXP_IS_URL_DISPATCH, $dispatch);
	}

	static function isUrlDispatch()
	{
		$url = URL::getSplittedURL();
		$dispatch = (isset($url[0])) ? $url[0] : "root";

		return self::isDispatch($dispatch);
	}

	static function onInstall()
	{
		return (!file_exists(Path::getData('install.lock')) && !self::isUrlDispatch());
	}

	static function isBlockDispatched()
	{
		$url = URL::getSplittedURL();
		$dispatch = (isset($url[0])) ? $url[0] : "root";
		return self::regexp(self::REGEXP_IS_BLOCK_DISPATCHED, $dispatch);
	}

	static function isService($str)
	{
		return self::regexp(self::REGEXP_IS_SERVICE, $str);
	}

	static function isObserver($str)
	{
		return self::regexp(self::REGEXP_IS_OBSERVER, $str);
	}

	static function isApi($str)
	{
		return self::regexp(self::REGEXP_IS_API, $str);
	}

	static function isJson($str)
	{
		return self::regexp(self::REGEXP_IS_JSON, $str);
	}

	static function isBlock($str)
	{
		return self::regexp(self::REGEXP_IS_BLOCK, $str);
	}

	static function isPage($str)
	{
		return self::regexp(self::REGEXP_IS_PAGE, $str);
	}

	static function isModule($str)
	{
		return self::regexp(self::REGEXP_IS_MODULE, $str);
	}

	static function isXml($str)
	{
		return self::regexp(self::REGEXP_IS_XML, $str);
	}

	static function isFactory($str)
	{
		return self::regexp(self::REGEXP_IS_FACTORY, $str);
	}

	static function isTpl($str)
	{
		return self::regexp(self::VALID_TPL, $str);
	}

	static function isInteger($str)
	{
		return self::regexp(self::REGEXP_INTEGER, $str);
	}

	static function isConditional($str)
	{
		return self::regexp(self::REGEXP_CONDITIONAL, $str);
	}

	static function isOptionalParam($str)
	{
		return self::regexp(self::REGEXP_OPTIONAL_PARAM, $str);
	}

	static function hasSlashes($str)
	{
		return self::regexp(self::REGEXP_SLASHES, $str);
	}

	static function isHttp($str)
	{
		return self::regexp(self::REGEXP_HTTP, $str);
	}

	static function isFile($str)
	{
		return self::regexp(self::REGEXP_FILE_EXT, $str);
	}

	static function getFileExt($str)
	{
		return self::getMatches(self::REGEXP_FILE_EXT, $str);
	}

	static function getFileExtVersion($str)
	{
		return self::getMatches(self::REGEXP_FILE_EXT_VERSION, $str);
	}

	static function getOptions($str)
	{
		return str_replace(["(",")"], "", implode(", ", explode("|", $str)));
	}

	static function removeSlash($str)
	{
		return preg_replace(self::REGEXP_RELATIVE_URL, '$1', $str);
	}

	static function removeUnderscore($str)
	{
		return str_replace('_', '', $str);
	}

	/**
	*	Utils
	*/
	static function getModule($page)
	{
		$page = preg_replace(self::REGEXP_GET_MODULE, '$1', $page);
		$page = self::getCamelName($page);

		return $page;
	}

	static function getBlockName($path)
	{
		return preg_replace('#('.DIR_BLOCKS.')_([a-zA-Z]+/)#', '$1$2', $path);
	}

	static function getBlockClassName($class)
	{

		if (preg_match('/^core\\\Blocks\\\/', $class)) {
			$class = CORE_NS . "\\" . preg_replace('/^core\\\(.+)$/', '$1', $class);
		} else if (preg_match('/^app\\\Blocks\\\/', $class)) {
			$class = APP_NS . "\\" . preg_replace('/^app\\\(.+)$/', '$1', $class);
		}

		return $class;
	}

	static function pageByFactory($str)
	{
		return preg_replace(self::REGEXP_IS_FACTORY, 'Page', $str);
	}

	static function getInvertedSlash($str)
	{
		return str_replace('/', '\\', $str);
	}

	static function getSlash($str)
	{
		return str_replace('\\','/', $str);
	}

	static function getTable($class)
	{
		return preg_replace('/^.+\\\([a-zA-Z_]+)$/', '$1', $class);
	}

	static function replaceJsonAcents($str, $inverse = false, $esc = true)
	{
		$slash = ($esc) ? '\\' : '';
	    $chars = ['á','é','í','ó','ú','ñ','Á','É','í','Ó','Ú','Ñ'];
	    $replacement = [$slash.'u00e1',$slash.'u00e9',$slash.'u00ed',$slash.'u00f3',$slash.'u00fa',$slash.'u00f1',$slash.'u00c1',$slash.'u00c9',$slash.'u00cd',$slash.'u00d3',$slash.'u00da',$slash.'u00d1'];

	    if ($inverse) {
	    	return str_replace($replacement, $chars, $str);
	    }

		return str_replace($chars, $replacement, $str);
	}

	static function replaceAcents($str, $inverse = false)
	{
	    $chars = ['á','é','í','ó','ú','ñ','Á','É','í','Ó','Ú','Ñ'];
	    $replacement = ['a','e','i','o','u','n','A','E','I','O','U','N'];

	    if ($inverse) {
	    	return str_replace($replacement, $chars, $str);
	    }

		return str_replace($chars, $replacement, $str);
	}

	static function removeSpecialChars($str)
	{
		$str = strtolower($str);
		$str = self::replaceAcents($str);
		$str = str_replace(array("#","@","$","%","&","/","(",")","=","?","¿","¡","!","'",'"',"*","+",",",".","-"), "", $str);
		$str = str_replace(" ", "-", $str);

		return $str;
	}

	static function addZero($n)
	{

		if (strlen((string)$n) < 2) return "0" . $n;

		return $n;

	}

	static function getGender($gender)
	{
		$types = [
			'male' => TEXT_MALE,
			'female' => TEXT_FEMALE
		];

		return (isset($types[$gender])) ? $types[$gender] : $types['male'];
	}

	static function getUserIcon($gender)
	{
		$rand = rand(1,4);
		return "user_{$gender}_{$rand}.jpg";
	}

	static function integerLength($value, $n)
	{
		return self::regexp("/^\d{$n}$/",$value);
	}

	static function stringLength($value, $n)
	{
		return self::regexp("/^\w{$n}$/",$value);
	}

	static function floatNumber($n)
	{

		if (preg_match('/^\d+\.\d{1,2}$/', $n)) {
			return $n;
		}

		return "$n.00";
	}

	static function fixDecimal($value, $decimals = 2)
	{
		return number_format($value,$decimals,'.','');
	}

	static function dataType($value)
	{
		return (self::isInteger($value)) ? intval($value) : $value;
	}

	static function truncate($str, $chars = 50, $points = "...")
	{
		$len = strlen($str);

		if ($len >= $chars) {
			return substr($str, 0, $chars) . $points;
		}

		return $str;
	}

	/**
	*	@var $params array
	*	@var $message string
	*/
	/*
		-----------------------------------------------------------
		EXAMPLE:
		-----------------------------------------------------------
			$email_settings = [
							'to' => "example@domain.com",
							'from' => EMAIL_FROM,
							'company' => PAGE_TITLE,
							'subject' => "Type your subject here!"
						];

			Helper::mailHTML($email_settings, $msg);
		-----------------------------------------------------------
	*/
	static function mailHTML($params, $message)
	{

		if (strtoupper(substr(PHP_OS,0,3)=='WIN')) {
		  $eol="\r\n";
		} elseif (strtoupper(substr(PHP_OS,0,3)=='MAC')) {
		  $eol="\r";
		} else {
		  $eol="\n";
		}

		$headers = 'From: '.$params['company'].'<'.$params['from'].'>'.$eol;
		$headers .= 'Reply-To: '.$params['company'].'<'.$params['from'].'>'.$eol;
		$headers .= "Content-Type: text/html; charset=utf-8".$eol;
		$headers .= "X-Mailer: PHP v".phpversion().$eol;           // These two to help avoid spam-filters

		return mail($params['to'], $params['subject'], $message, $headers);

	}

	static function getHelperPath($str)
	{
		return str_replace(['Page','JSON','XML'], 'Helper', $str);
	}

	static function getHelperFileName($file)
	{
		return preg_replace('#^app/Sites/([a-zA-Z]+)/Modules/(.+)$#', 'core/Modules/$1/$2', $file);
	}

	static function getExtendClass($class)
	{

		$type = preg_replace('/.+\/([a-zA-Z]+)$/', '$1', $class);
		$ns = "\\Roducks\\Page\\Page";

		switch ($type) {
			case 'Helper':
				$ns = "\\Roducks\\Page\\Helper";
				break;
			case 'JSON':
				$ns = "\\Roducks\\Page\\JSON";
				break;
			case 'XML':
				$ns = "\\Roducks\\Page\\XML";
				break;
		}

		return $ns;

	}

	static function getCoreHelperclassName($className)
	{
		$classPath = str_replace("\\","/", $className);
		$coreClassName = preg_replace('/^App\/Sites\/([a-zA-Z]+)\/Modules\/(.+)$/', 'Roducks\\Modules\\\$1\\\$2', $classPath);
		$coreClassName = str_replace("/", "\\", $coreClassName);

		return $coreClassName;
	}

	static function getCamelName($url, $ret = true, $sep = "-")
	{
		$pts = explode($sep, $url);

		if (count($pts) == 1 && !$ret) {
			return $url;
		}

		$urlCamel = '';
		$i = 0;
		foreach ($pts as $key => $value) {
			$i++;
			$v = ($i == 1 && !$ret) ? $value : ucfirst($value);
			$urlCamel = $urlCamel . $v;
		}

		if (Helper::regexp(self::REGEXP_IS_URL_DISPATCH, $urlCamel)) {
			$urlCamel = '_'. ucfirst(substr($urlCamel, 1));
		}

		return $urlCamel;
	}

	static function getClassName($class, $index = '$2')
	{
		$class = str_replace("\\","/", $class);
		$class = preg_replace('/^(.+)\/(.+)$/', $index, $class);

		return $class;
	}

	static function getConventionName($str, $sep = "-")
	{

		$abc = [
			"A" => 1,
			"B" => 1,
			"C" => 1,
			"D" => 1,
			"E" => 1,
			"F" => 1,
			"G" => 1,
			"H" => 1,
			"I" => 1,
			"J" => 1,
			"K" => 1,
			"L" => 1,
			"M" => 1,
			"N" => 1,
			"O" => 1,
			"P" => 1,
			"Q" => 1,
			"R" => 1,
			"S" => 1,
			"T" => 1,
			"U" => 1,
			"V" => 1,
			"W" => 1,
			"X" => 1,
			"Y" => 1,
			"Z" => 1
		];

		$ret = '';

		for ($i=0; $i < strlen($str); $i++) {
			$text = substr($str, $i, 1);
			$us = ($i>0) ? $sep : '';
			$ret .= (isset($abc[$text])) ? $us . strtolower($text) : $text;
		}

		return $ret;

	}

	static function resetArray(array $arr = [])
	{
		return array_merge(array(), $arr);
	}

	static function getArrayValue($data, $index, $value)
	{

		if (empty($value)) {
			if (!is_null($value)) {
				$value = [];
			}
		}

		if (is_array($index)) {

			$ret = $data;
			foreach ($index as $i) {
				$ret = self::getArrayValue($ret, $i, $value);
			}

			return $ret;
		} else {
			return (isset($data[$index])) ? $data[$index] : $value;
		}

	}

	static function getUrlParams(array $params = [])
	{

		unset($params[0]);
		unset($params[1]);
		unset($params[2]);

		return self::resetArray($params);
	}

	static function getPairParams(array $params = [])
	{
		$ret = [];
		$i = 0;

		if (count($params) % 2 == 1) {
			return $params;
		}

		foreach ($params as $key => $param) {
			$pair = ($i % 2);
			if ($pair == 0) {
				$param = self::replaceJsonAcents($param, true, false);
				$value = (isset($params[$i+1])) ? $params[$i+1] : "";
				$value = self::replaceJsonAcents($value, true, false);
				$value = str_replace("+", " ", $value);
				$value = urldecode($value);
				$ret[$param] = self::dataType($value);
			}

			$i++;
		}

		return $ret;
	}

	static function removeFileExt($str)
	{
		return preg_replace('/^(.+)\.[a-z]+$/', '$1', $str);
	}

	static function getCliParams($args)
	{
		$params = [];
		$c = 1;

		foreach ($args as $key => $value) {

			if ($c > 1) {
				if (!preg_match('/^--/', $key)) {
					$params[] = ($value == 1) ? $key : $value;
				}
			}

			$c++;
		}

		return $params;

	}

	static function pre($arr, $die = false)
	{

		echo '<pre>';
		print_r($arr);
		echo '</pre>';

		if ($die) {
			exit;
		}
	}

	public static function extractHtml($tpl)
	{
		ob_start();
		include $tpl;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	static function ext($file, $ext)
	{
		if (!self::regexp('/(\.'.$ext.')$/', $file)) {
			return $file . "." . $ext;
		}

		return $file;
	}

	/*
	*	Clean POST data
	*/
	static function cleanData($arr)
	{
		$clean = [];

		foreach ($arr as $key => $value):
			$clean[$key] = strip_tags(trim($value));
		endforeach;

		return $clean;
	}

}
