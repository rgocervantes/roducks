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

namespace Roducks\Page;

use Roducks\Framework\Core;
use Roducks\Framework\Config;
use Roducks\Framework\Dispatch;
use Roducks\Framework\URL;
use Roducks\Framework\Role;
use Roducks\Framework\Error;
use Roducks\Framework\Helper;
use Roducks\Framework\Language;
use Roducks\Framework\Environment;
use Roducks\Data\User;
use Roducks\Libs\Request\Http;
use Roducks\Libs\Data\Cache;

abstract class Frame
{

	/**
	*	Protected
	*	@var
	*/
	protected $pageObj;
	protected $view;
	protected $grantAccess;
	protected $_dispatchUrl = false;
	protected $_pageType = 'FRAME'; // PAGE|BLOCK|FACTORY

	private $_cache = null;

/*
//---------------------------------
//	PRIVATE METHODS
//---------------------------------
*/
	private function _autoLoad($params)
	{

		$class = $this->pageObj->className;
		$url = URL::getParams();

		// Avoid autoload for "Page Not Found"
		if ($class == Helper::PAGE_NOT_FOUND
			|| $this->getParentClassName() == '\Roducks\Page\HelperPage'
			|| $this->_pageType == 'DATA'
			|| $this->_pageType == 'EVENT'
			|| ($this->_pageType == 'BLOCK' && $url[0] == '_page')
		) {
			return;
		}

		if (Helper::isFactory($class)) {
			$class = Helper::pageByFactory($class);
		}

		foreach ($params as $key => $value) {

			if (!Helper::regexp(Helper::VALID_PARAM, $key)) {
				Error::debug("Invalid param", __LINE__, __FILE__, $this->pageObj->fileName, "Param <b style=\"color: #e69d97;\">{$key}</b> contains invalid chars. [\-0-9]");
			}

			if (property_exists($class, $key)) {

				if (!is_array($value)) {
					$value = (Helper::isInteger($value)) ? intval($value) : $value;
				}

				if (!empty($value) || $value == 0) {
					$this->$key = $value;
				}

			}else{
				Error::undefinedVariable("Undefined variable", $this->pageObj->className, __LINE__, __FILE__, $this->pageObj->fileName, $key, $this->getParentClassName());
			}
		}
	}

	private function _urlDispatcher()
	{

		$class = $this->pageObj->className;

		if ($this->_dispatchUrl || $class == Helper::PAGE_NOT_FOUND) {
			return;
		}

		$url = URL::getParams();
		$total = count($url) - 1;
		if (isset($url[0]) && isset($url[1]) && !empty($url[$total])) {
			if (preg_match('/^_(page|block|json|xml|service|factory)$/', $url[0])) {
				if (
					(!$this->_dispatchUrl && $this->_pageType == 'FRAME' && preg_match('/^_(json|xml)$/', $url[0])) ||
					(!$this->_dispatchUrl && $this->_pageType == 'PAGE' && $url[0] == '_page') ||
					(!$this->_dispatchUrl && $this->_pageType == 'SERVICE' && $url[0] == '_service') ||
					(!$this->_dispatchUrl && $this->_pageType == 'FACTORY' && $url[0] == '_factory') ||
					(!$this->_dispatchUrl && $this->_pageType == 'BLOCK' && $url[0] == '_block')
				) {
					Error::cantDispatchURL("Can't dispatch URL", $this->pageObj->className, __LINE__, __FILE__, $this->pageObj->fileName, $this->getParentClassName());
				}
			}
		}

	}

/*
//---------------------------------
//	PROTECTED METHODS
//---------------------------------
*/
	protected function cache($action = 'default')
	{

		if (is_null($this->_cache)) {
			$errorMessage1 = 'Unable to connect to Memcache';
			$errorMessage2 = 'Missing Cache config';
			$errorFile = Config::getMemcache()['path'];

			if (count($memcache) > 0) {

				$servers = implode(',', $memcache['servers']);
				$errorConn = "with -> Servers: {$servers}; Port: {$memcache['port']}";

				$this->_cache = Cache::init($memcache['servers'],$memcache['port']);
				if (is_null($this->_cache)) {
					if (Environment::inCLI()) {
						throw new \Exception("{$errorMessage1} {$errorConn}", 1);
					} else {
						Error::fatal($errorMessage1, __LINE__, __FILE__, $errorConn);
					}
				}
			} else {
				if (Environment::inCLI()) {
					throw new \Exception("{$errorMessage2}: {$errorFile}", 1);
				} else {
					Error::fatal($errorMessage2, __LINE__, __FILE__, $errorFile);
				}
			}
		}

		switch ($action) {
			case 'items':
				return Cache::items();
				break;
			case 'clean':
				Cache::clean();
				break;
			default:
				return $this->_cache;
				break;
		}

	}

	protected function getLang()
	{
		return Language::get();
	}

	protected function disableUrlDispatcher()
	{
		$this->_dispatchUrl = false;
		$this->_urlDispatcher();
	}

	protected function _getParentClassName()
	{
		return get_parent_class($this);
	}

	protected function db()
	{
		return Core::db();
	}

	protected function openDb(array $conn = [])
	{
		return Core::openDb($conn);
	}

	protected function model($className)
	{
		if (Helper::regexp('/\//', $className)) {
			$slash = explode('/', $className);
			$ret = [];

			foreach ($slash as $class) {
				$ret[] = Helper::getCamelName($class);
			}

			$path = implode('\\', $ret);
		} else {
			$path = $className;
		}

		$model = "DB\\Models\\{$path}";

		return $model::open($this->db());
	}

	/*
	//---------------------------------
	//	Get configs
	//---------------------------------
	*/
	/*

		$this->getConfig('global',"user:prefix", 1);
		$this->getConfig('global:config',"user:prefix", 1);
		$this->getConfig('global:foo',"user:prefix", 1);

		$this->getConfig('site', "user:prefix", 1);
		$this->getConfig('site:rod', "user:prefix"], 1);
		$this->getConfig('site:rod:foo', "user:prefix"], 1);

		$this->getConfig('module', "user:prefix", 1);
		$this->getConfig('module:user', "user:prefix", 1);
		$this->getConfig('module:user:config', "user:prefix", 1);
		$this->getConfig('module:user:foo', "user:prefix", 1);

		$this->getConfig('admin:users', "user:prefix", 1);
		$this->getConfig('admin:users:config', "user:prefix", 1);
		$this->getConfig('admin:users:foo', "user:prefix", 1);

	*/
	protected function getConfig($tag, $var = "", $value = "")
	{

		$name = "";
		$type = null;
		$key = null;
		$default = 'config';
		$config = [];

		if (Helper::regexp("#:#", $tag)) {
			$terms = explode(":", $tag);
			$total = count($terms);
			switch ($total) {
				case 3:
					list($tag, $type, $key) = $terms;
					break;
				
				default:
				list($tag, $type) = $terms;
					break;
			}

			$name = Helper::getCamelName($type);
		}

		switch ($tag) {
			case 'global':
				$data = (!is_null($type)) ? Config::fromSite($type, 'All') : Config::fromSite('config', 'All');
				break;

			case 'site':

				if (!is_null($type) && !is_null($key)) {
					$data = Config::fromSite($key, Helper::getCamelName($type));
				} else if (!is_null($type) && is_null($key)) {
					$data = Config::fromSite($default, Helper::getCamelName($type));
				} else {
					$data = Config::fromSite($default);
				}
				break;

			case 'module':
				$class = Helper::getClassName($this->pageObj->className);
				$module = (!is_null($type)) ? Helper::getCamelName($type) : $class;
				$index = (is_null($key)) ? $default : $key;
				$data = Config::fromModule($module, $index);
				break;

			case 'file':
				$data = (!is_null($type)) ? Config::get($type) : [];
				break;

			default:
				$class = Helper::getClassName($this->pageObj->className);
				$module = (!is_null($type)) ? Helper::getCamelName($type) : $class;
				$index = (is_null($key)) ? $default : $key;
				$data = Config::fromModule($module, $index, Helper::getCamelName($tag));
				break;

		}

		$config = $data['data'];

		if (empty($var)) {
			return $config;
		}

		if (Helper::regexp("#:#", $var)) {
			$var = explode(":", $var);
		}

		if (empty($config) && empty($value)) {
			if (!is_null($value)) {
				$value = [];
			}
		}

		return Helper::getArrayValue($config, $var, $value);

	}

	protected function getViewData()
	{
		if ($this->view instanceof View) {
			return $this->view->getData();
		}

		return [];
	}

	protected function getAccess($name = "")
	{

		$class = (!empty($name)) ? $name : $this->pageObj->className;
		$class = Helper::getClassName($class);
		$class = Helper::getConventionName($class);
		$access = $this->grantAccess->getData();

		return (isset($access[$class])) ? $access[$class] : [];
	}

	protected function getUrlParam($index, $value = "")
	{
		return (isset($this->pageObj->urlParam[$index])) ? $this->pageObj->urlParam[$index] : $value;
	}

	protected function getPairParam($index)
	{
		$params = URL::getPairParams();
		return (isset($params[$index])) ? $params[$index] : "";
	}

	protected function accessDenied()
	{
		if (!User::isLoggedIn()) {
			Http::setHeaderInvalidRequest();
		}
	}

	protected function role($class = "")
	{

		if (empty($class)) {
			$class = $this->pageObj->className;
			$class = Helper::getClassName($class);
			$class = Helper::getConventionName($class,"_");
		} else {
			$class = Helper::removeSlash($class);
		}

		$this->grantAccess = new GrantAccess($class);
	}

	protected function params(array $values = [])
	{

		$count = 0;
		$total = 0;
		$i = 0;
		$skip = false;
		$params = [];
		$relativeUrl = URL::getRelativeURL();
		$getGETParams = URL::getQueryString();
		$alert = "debug";
		$title = "Unexpected value";

		if ($this->_pageType == 'BLOCK') {
			$alert = "warning";
		} else {

			$params = URL::getSplittedURL();

			if (!isset($params[0])) {
				return;
			}

			if (Helper::regexp('/^_/',$params[0])) {
				$params = Helper::getUrlParams($params);
				$total = count($params);
			} else {
				$total = count($params) - 1;
				if ($total == 0) {
					$skip = true;
				}
			}
		}

		foreach ($values as $p => $value) {

			if (isset($value[0]) && isset($value[1]) && isset($value[2])) {

				if ($value[1] == 'PARAM') {

					if ($value[2] == Dispatch::PARAM_ARRAY) {
						if (is_array($value[0])) {
							continue;
						} else {
							$error = "Param <b>{$p}</b> must be <b>{$value[2]}</b>";
							Error::$alert("Missing value",__LINE__, __FILE__, $this->pageObj->fileName, $error);
							$this->view->setError();
							$value[0] = Dispatch::PARAM_ARRAY;
							$value[2] = Dispatch::PARAM_STRING;
						}
					}

					if ($value[2] == Dispatch::PARAM_NOT_EMPTY_ARRAY) {

						if (is_array($value[0]) && !empty($value[0])) {
							continue;
						}

						$error = "Param <b>{$p}</b> must not be an <b style=\"color:#e69d97;\">EMPTY</b> <b>Array</b>";
						Error::$alert("Missing value",__LINE__, __FILE__, $this->pageObj->fileName, $error);
						$this->view->setError();
						$value[0] = Dispatch::PARAM_ARRAY;
						$value[2] = Dispatch::PARAM_STRING;

					}

					if ($skip) {
						continue;
					}

					$i = $count;
					$count++;

					if ($this->_pageType == 'BLOCK') {
						$total++;
					}
				}

				switch ($value[2]) {

					case Dispatch::PARAM_STRING:
						$regexp = Helper::VALID_STRING;
					break;

					case Dispatch::PARAM_WORD:
						$regexp = Helper::VALID_WORD;
					break;

					case Dispatch::PARAM_INTEGER:
						$regexp = Helper::VALID_INTEGER;
					break;

				}

				if ($value[1] == 'PARAM') {
					$key = (isset($params[$i])) ? $params[$i] : $value[0];

					if (empty($key)) {
						$ruleRegExp = (isset($value[3])) ? " that must match this regular expression {$value[3]}" : "";
						$error = "Param <b>{$p}</b> must be <b>{$value[2]}</b>{$ruleRegExp}";
						$err = $error;
						$title = "Missing value";
					} else {
						$error = "Unreconignized param <b>{$key}</b>, it must be <b>{$value[2]}</b>";
						$err = "Unreconignized param <b>{$key}</b>";
					}

				} else {
					$key = $p;
					$error = "Param <b>{$key}</b> must be <b>{$value[2]}</b>";
					if (isset($getGETParams[$key])) {
						$err = "Invalid value for GET param: <b>{$key}</b>";
					} else {
						$err = "Missing GET param: <b>{$key}</b>";
					}

				}

				if (isset($value[3]) && !empty($value[0])) {
					$regexp = $value[3];
					$error = "Value <b>{$key}</b> does not match with this regular expression: {$regexp}";

					if (Helper::isConditional($value[3])) {
						$error = "{$err}, It <b style=\"color: #e69d97;\">ONLY</b> allows the next values: " . Helper::getOptions($value[3]);
						$regexp = '/^'.$value[3].'$/';
					}

				}

				if (!Helper::regexp($regexp, $value[0])) {
					Error::$alert($title,__LINE__, __FILE__, $this->pageObj->fileName, $error);
					$this->view->setError();
				}
			}

		}

		if ($count != $total && $this->_pageType != 'BLOCK') {
			$error = "";
			for ($x=$count; $x < $total; $x++) {
				$error .= "Unreconignized param <b>" . $params[$x]."</b><br>";
			}

			Error::debug("Unexpected params",__LINE__, __FILE__, $this->pageObj->fileName, "Expected {$count} params, {$total} given instead:<br><br>{$error}");
			$this->view->setError();
		}

	}

	protected function getArray($values)
	{

		if (empty($values)) {
			return [];
		}

		if (!is_array($values)) {
			$values = explode("_", $values);
			$values = Helper::getPairParams($values);
		}

		return $values;
	}

/*
//---------------------------------
//	PUBLIC METHODS
//---------------------------------
*/
	public function __construct(array $settings = [])
	{

		// Avoid warnings
		if (count($settings) == 0) {
			$settings = [
				'className' 	=> "",
				'filePath'		=> "",
				'fileName' 		=> "",
				'urlParam'		=> "",
				'method'			=> "",
				'url_dispatcher' => true
			];
		}

		/* ------------------------------------*/
		/* 		PAGE OBJ
		/* ------------------------------------*/
		$this->pageObj = new \stdClass;
		$this->pageObj->className = $settings['className'];
		$this->pageObj->filePath = $settings['filePath'];
		$this->pageObj->fileName = $settings['fileName'];
		$this->pageObj->urlParam = $settings['urlParam'];
		$this->pageObj->method = $settings['method'];

		/* ------------------------------------*/
		/* 		DISPATCH URL
		/* ------------------------------------*/
		if (!Environment::inCLI() && $settings['url_dispatcher']) {
			$this->_urlDispatcher();
		}

		/* ------------------------------------*/
		/* 		INITIALIZE VARS
		/* ------------------------------------*/
		$url = (!Environment::inCLI() && $settings['url_dispatcher']) ? URL::getParams() : [];
		$tag = (isset($url[0])) ? $url[0] : "";

		if ($this->_pageType == 'PAGE' || Helper::isDispatch($tag))
			$this->_autoLoad(URL::getQueryString());

	}

	public function setVars(array $params = [])
	{
		if (empty($params)) {
			return;
		}

		$this->_autoLoad($params);
	}

	public function getParentClassName()
	{
		return '\\'.$this->_getParentClassName();
	}

}
