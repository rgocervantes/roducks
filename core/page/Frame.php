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

namespace rdks\core\page;

use rdks\core\framework\Core;
use rdks\core\framework\Dispatch;
use rdks\core\framework\URL;
use rdks\core\framework\Login;
use rdks\core\framework\Error;
use rdks\core\framework\Helper;
use rdks\core\libs\Protocol\Http;

abstract class Frame{

	/**
	*	Protected
	*	@var
	*/
	protected $pageObj;
	protected $view;
	protected $grantAccess;			
	protected $_dispatchUrl = false;
	protected $_pageType = 'FRAME'; // PAGE|BLOCK|FACTORY

/*
//---------------------------------
//	PRIVATE METHODS
//---------------------------------
*/
	private function _autoLoad($params){

		$class = $this->pageObj->className;

		// Avoid autoload for "Page Not Found"
		if($class == "rdks\core\page\Page"){
			return;
		}

		if(Helper::isFactory($class)){
			$class = Helper::pageByFactory($class);
		}

		foreach($params as $key => $value){

			if(!Helper::regexp(Helper::VALID_PARAM, $key)){
				Error::debug("Invalid param", __LINE__, __FILE__, $this->pageObj->fileName, "Param <b style=\"color: #c00;\">{$key}</b> contains invalid chars. [\-0-9]");
			}
		
			if(property_exists($class, $key)){

				if(!is_array($value)){
					$value = (Helper::isInteger($value)) ? intval($value) : $value;	
				}
				
				if(!empty($value)) {
					$this->$key = $value;
				}

			}else{
				$url = URL::getParams();
				$tag = (isset($url[0])) ? $url[0] : "";

				if(
					($this->_pageType == 'PAGE' && $tag == '_page') || 
					($this->_pageType == 'PAGE' && $tag == '_factory') || 
					($this->_pageType == 'BLOCK' && $tag == '_block') || 
					($this->_pageType == 'PAGE' && $tag != '_page') || 
					($this->_pageType == 'FRAME')
				) {
					Error::undefinedVariable("Undefined variable", $this->pageObj->className, __LINE__, __FILE__, $this->pageObj->fileName, $key, $this->getParentClassName());
				}
			}	
		}
	}

	private function _urlDispatch(){

		$class = $this->pageObj->className;

		if($this->_dispatchUrl || $class == "rdks\core\page\Page"){
			return;
		}

		$url = URL::getParams();
		$total = count($url) - 1;
		if(isset($url[0]) && isset($url[1]) && !empty($url[$total])){
			if(preg_match('/^_(page|block|json|xml|service|factory)$/', $url[0])){
				if(
					(!$this->_dispatchUrl && $this->_pageType == 'FRAME' && preg_match('/^_(json|service|xml)$/', $url[0])) || 
					(!$this->_dispatchUrl && $this->_pageType == 'PAGE' && $url[0] == '_page') || 
					(!$this->_dispatchUrl && $this->_pageType == 'FACTORY' && $url[0] == '_factory') || 				
					(!$this->_dispatchUrl && $this->_pageType == 'BLOCK' && $url[0] == '_block')
				){
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
	protected function disableUrlDispatch(){
		$this->_dispatchUrl = false;
		$this->_urlDispatch();
	}

	protected function _getParentClassName(){
		return get_parent_class($this);
	}	

	protected function db(){
		return Core::db(RDKS_ERRORS);
	}	

	protected function openDb(array $conn = []){
		return Core::openDb(RDKS_ERRORS, $conn);
	}

	/*
	//---------------------------------
	//	Get configs
	//---------------------------------
	*/
	protected function getGlobalConfig(){
		return Core::getGlobalConfigFile();
	}

	protected function getSiteConfig($name = ""){

		if(!empty($name)){
			return Core::getSiteByNameConfigFile($name, false);
		}

		return Core::getSiteConfigFile("config", false);
	}

	protected function getModuleConfig($name = ""){
		if(empty($name)){
			$class = $this->pageObj->className;
			$name = Helper::getClassName($class);
		}

		return Core::getModuleConfigFile($name, false);
	}

	protected function getViewData(){
		if($this->view instanceof View){
			return $this->view->getData();
		}

		return [];
	}

	protected function getAccess($name = ""){

		$class = (!empty($name)) ? $name : $this->pageObj->className;
		$class = Helper::getClassName($class);
		$class = Helper::getConventionName($class);
		$access = $this->grantAccess->getData();

		return (isset($access[$class])) ? $access[$class] : [];
	}

	protected function getUrlParam($index, $value = ""){
		return (isset($this->pageObj->urlParam[$index])) ? $this->pageObj->urlParam[$index] : $value;
	}

	protected function getPairParam($index){
		$params = URL::getPairParams();
		return (isset($params[$index])) ? $params[$index] : "";
	}	

	protected function accessAdmin(){
		if(!Login::isAdminLoggedIn()){
			Http::setHeaderInvalidRequest();
		}
	}

	protected function accessSubscriber(){
		if(!Login::isSubscriberLoggedIn()){
			Http::setHeaderInvalidRequest();
		}
	}	

	protected function role($type, $class = ""){

		if(empty($class)){
			$class = $this->pageObj->className;
			$class = Helper::getClassName($class);
			$class = Helper::getConventionName($class,"_");
		} else {
			$class = Helper::removeSlash($class);
		}

		$this->grantAccess = new GrantAccess($class, $type);
	}	

	protected function initCache(){

		/* ------------------------------------*/
		/* 		MEMCACHED
		/* ------------------------------------*/
		$memcache = Core::getCacheConfig();
		if(count($memcache) > 0){
			$cache = Cache::init($memcache['servers'],$memcache['port']);
			if($cache !== false){
				return $cache;
			}	
		}

		return false;

	}

	protected function params(array $values = []){

		$count = 0;
		$total = 0;
		$i = 0;
		$skip = false;
		$params = [];
		$relativeUrl = URL::getRelativeURL();
		$getGETParams = URL::getGETParams();
		$alert = "debug";

		if($this->_pageType == 'BLOCK'){
			$alert = "warning";
		} else {	

			$params = URL::getRealParams();
			
			if(!isset($params[0])){
				return;
			}

			if(Helper::regexp('/^_/',$params[0])){
				$params = Helper::getUrlParams($params);
				$total = count($params);
			} else {
				$total = count($params) - 1;
				if($total == 0){
					$skip = true;
				}
			}
		}

		foreach ($values as $p => $value) {

			if(isset($value[0]) && isset($value[1]) && isset($value[2])){
				
				if($value[1] == 'PARAM'){
					if($skip){
						continue;
					}

					$i = $count;
					$count++;

					if(empty($value[0])){
						$autocomplete = (isset($value[3])) ? $value[3] : "[{$value[2]}]";
						$error = "Complete URL params:<br><br><span style=\"color:#c00;\">{$relativeUrl}/</span><b>{$autocomplete}</b>";
						Error::$alert("Missing value",__LINE__, __FILE__, $this->pageObj->fileName, $error);
					}

					if($this->_pageType == 'BLOCK'){
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

				if($value[1] == 'PARAM'){
					$key = (isset($params[$i])) ? $params[$i] : $value[0];
					$error = "Unreconignized param <b>{$key}</b>, it must be <b>{$value[2]}</b>";
					$err = "Unreconignized param <b>{$key}</b>";
				} else {
					$key = $p;
					$error = "Param '{$key}' must be <b>{$value[2]}</b>";
					if(isset($getGETParams[$key])){
						$err = "Invalid value for GET param: <b>{$key}</b>";
					} else {
						$err = "Missing GET param: <b>{$key}</b>";
					}
					
				}	
				
				if(isset($value[3])){
					$regexp = $value[3];
					$error = "Param <b>{$key}</b> does not match with this regular expression: {$regexp}";
	
					if(Helper::isConditional($value[3])){
						$error = "{$err}, It <b style=\"color: #c00;\">ONLY</b> allows the next values: " . Helper::getOptions($value[3]);
						$regexp = '/^'.$value[3].'$/';
					}

				}

				if(!Helper::regexp($regexp, $value[0])){
					Error::$alert("Unexpected value",__LINE__, __FILE__, $this->pageObj->fileName, $error);
				}
			}

		}

		if($count != $total && $this->_pageType != 'BLOCK'){
			$error = "";
			for ($x=$count; $x < $total; $x++) { 
				$error .= "Unreconignized param <b>" . $params[$x]."</b><br>";
			}
			
			Error::debug("Unexpected params",__LINE__, __FILE__, $this->pageObj->fileName, "Expected {$count} params, {$total} given instead:<br><br>{$error}");
		}

	}

/*
//---------------------------------
//	PUBLIC METHODS
//---------------------------------
*/
	public function __construct(array $settings = []){

		// Avoid warnings 
		if(count($settings) == 0){
			$settings = [
				'className' 	=> "",
				'filePath'		=> "",
				'fileName' 		=> "", 
				'urlParam'		=> "",
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

		/* ------------------------------------*/
		/* 		DISPATCH URL
		/* ------------------------------------*/		
		$this->_urlDispatch();

		/* ------------------------------------*/
		/* 		INITIALIZE VARS
		/* ------------------------------------*/
		$this->_autoLoad(URL::getGETParams());

	}	

	public function getParentClassName(){
		return '\\'.$this->_getParentClassName();
	}	

} 