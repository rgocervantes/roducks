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
use Roducks\Framework\Dispatch;
use Roducks\Framework\URL;
use Roducks\Framework\Login;
use Roducks\Framework\Role;
use Roducks\Framework\Error;
use Roducks\Framework\Helper;
use Roducks\Framework\Environment;
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
	protected $_inLocal = true;

	private $_langIso = 'en';
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
			$memcache = Core::getCacheConfig($this->_inLocal);
			$config = (Environment::inDEV() || $this->_inLocal) ? '.local' : '';
			$errorMessage1 = 'Unable to connect to Memcache';
			$errorMessage2 = 'Missing Cache config';
			$errorFile = "config/memcache{$config}.inc";

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
		return $this->_langIso;
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
	protected function getGlobalConfig($name = "config")
	{
		return Core::getGlobalConfigFile($name);
	}

	protected function getSiteConfig($name = "")
	{

		if (!empty($name)) {
			return Core::getSiteByNameConfigFile($name, false);
		}

		return Core::getSiteConfigFile("config", false);
	}

	protected function getModuleConfig($name = "")
	{
		if (empty($name)) {
			$class = $this->pageObj->className;
			$name = Helper::getClassName($class);
		}

		return Core::getModuleConfigFile($name, false);
	}

	protected function getSiteModuleConfig($site, $module)
	{
		return Core::getSiteModuleConfigFile($site, $module);
	}

	/*

		$this->config('global',"user:prefix", 1);

		$this->config('site', "user:prefix"], 1);
		$this->config('site:test', "user:prefix"], 1);

		$this->config('module', "user:prefix"], 1);
		$this->config('module:blog', "user:prefix"], 1);

		$this->config('admin:users', "user:prefix"], 1);

	*/
	protected function config($tag, $var = "", $value = "")
	{

		$name = "";
		$type = null;
		$config = [];

		if (Helper::regexp("#:#", $tag)) {
			list($tag, $type) = explode(":", $tag);
			$name = Helper::getCamelName($type);
		}

		switch ($tag) {
			case 'global':
				$config = (!is_null($type)) ? $this->getGlobalConfig($type) : $this->getGlobalConfig();
				break;

			case 'site':
				$config = $this->getSiteConfig($name);
				break;

			case 'module':
				$config = $this->getModuleConfig($name);
				break;

			case 'file':
				$config = (!is_null($type)) ? Core::getLocalConfigFile($type, "config", false) : [];
				break;

			default:
				$tag = Helper::getCamelName($tag);
				$config = $this->getSiteModuleConfig($tag, $name);
				break;

		}

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

	protected function accessAdmin()
	{
		if (!Login::isAdminLoggedIn()) {
			Http::setHeaderInvalidRequest();
		}
	}

	protected function accessSubscriber()
	{
		if (!Login::isSubscriberLoggedIn()) {
			Http::setHeaderInvalidRequest();
		}
	}

	protected function role($type, $class = "")
	{

		if (empty($class)) {
			$class = $this->pageObj->className;
			$class = Helper::getClassName($class);
			$class = Helper::getConventionName($class,"_");
		} else {
			$class = Helper::removeSlash($class);
		}

		$session = Role::getSession($type);
		$session = (empty($session)) ? $type : $session;
		$this->grantAccess = new GrantAccess($class, $session);
	}

	protected function params(array $values = [])
	{

		$count = 0;
		$total = 0;
		$i = 0;
		$skip = false;
		$params = [];
		$relativeUrl = URL::getRelativeURL();
		$getGETParams = URL::getGETParams();
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
				'method'		=> "",
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
		$this->_urlDispatcher();

		/* ------------------------------------*/
		/* 		INITIALIZE VARS
		/* ------------------------------------*/
		$url = URL::getParams();
		$tag = (isset($url[0])) ? $url[0] : "";

		if ($this->_pageType == 'PAGE' || Helper::isDispatch($tag))
			$this->_autoLoad(URL::getGETParams());

	}

	public function setVars(array $params = [])
	{
		if (empty($params)) {
			return;
		}

		$this->_autoLoad($params);
	}

	public function setLang($iso)
	{
		$this->_langIso = $iso;
	}

	public function getParentClassName()
	{
		return '\\'.$this->_getParentClassName();
	}

	public function inLocal($bool)
	{
		$this->_inLocal = $bool;
	}

}
