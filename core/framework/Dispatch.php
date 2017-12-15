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

namespace rdks\core\framework;

use rdks\core\page\JSON;
use rdks\core\libs\Data\Session;
use rdks\core\libs\Utils\Date;
use rdks\core\libs\Protocol\Http;
use rdks\app\models\Data\UrlsUrlsLang;
use rdks\app\models\Users\Users as UsersTable;

class Dispatch{

	const PARAM_STRING = 'string';
	const PARAM_WORD = 'word';
	const PARAM_WORDS = 'words';
	const PARAM_INTEGER = 'integer';
	const PARAM_BOOL = 'bool';
	const PARAM_CLABE = 'clabe';
	const PARAM_PASSWORD = 'password';
	const PARAM_EMAIL = 'email';
	const PARAM_USERNAME = 'username';

	const OPTIONAL_PARAM_STRING = 'optional_string';
	const OPTIONAL_PARAM_WORD = 'optional_word';
	const OPTIONAL_PARAM_WORDS = 'optional_words';
	const OPTIONAL_PARAM_INTEGER = 'optional_integer';
	const OPTIONAL_PARAM_BOOL = 'optional_bool';
	const OPTIONAL_PARAM_CLABE = 'optional_clabe';
	const OPTIONAL_PARAM_PASSWORD = 'optional_password';
	const OPTIONAL_PARAM_EMAIL = 'optional_email';
	const OPTIONAL_PARAM_USERNAME = 'optional_username';

	const PARAM_HTML = 'html';
	const PARAM_IMAGE = 'image';
	const PARAM_JSON = 'json';
	const PARAM_XML = 'xml';
	const PARAM_ARRAY = 'array';
	const PARAM_NOT_EMPTY_ARRAY = 'not_empty_array';

	static function page($class, $method){
		$class = Helper::getCamelName($class);
		return "{$class}/page/{$class}::{$method}";
	}

	static function _page($class, $method){
		$class = Helper::getCamelName($class);
		return "/_page/{$class}/{$method}";
	}	

	static function factory($class, $method){
		$class = Helper::getCamelName($class);
		return "{$class}/factory/{$class}::{$method}";
	}	

	static function _factory($class, $method){
		$class = Helper::getCamelName($class);
		return "/_factory/{$class}/{$method}";
	}

	static function json($class, $method){
		$class = Helper::getCamelName($class);
		return "{$class}/json/{$class}::{$method}";
	}

	static function _json($class, $method){
		$class = Helper::getCamelName($class);
		return "/_json/{$class}/{$method}";
	}	

	static function xml($class, $method){
		$class = Helper::getCamelName($class);
		return "{$class}/xml/{$class}::{$method}";
	}

	static function _xml($class, $method){
		$class = Helper::getCamelName($class);
		return "/_xml/{$class}/{$method}";
	}	

	static function service($class, $method){
		$class = Helper::getCamelName($class);
		return "services/{$class}::{$method}";
	}		

	static function _service($class, $method){
		$class = Helper::getCamelName($class);
		return "/_service/{$class}/{$method}";
	}	

	static function values($arr){
		return "(".implode("|", $arr).")";
	}

	static function getLastParams($params){
		
		// Avoid empty params
		$total = count($params) - 1;

		if($total > 2){
			$last = (isset($params[$total]) && empty($params[$total]) && $params[$total] != '0');
		} else {
			$last = (isset($params[2]) && ($params[2] == '0' || $params[2] == ""));
		}

		return $last;
	}

	static function init(){
		
		$URI = URL::getRelativeURL();

		/* ------------------------------------*/
		/* 		PREVENT POSSIBLE CSRF ATTACK
		/* ------------------------------------*/
		URL::preventCSRFAttack();
		/* ------------------------------------*/			


		/* ------------------------------------*/
		/* 		START SESSION
		/* ------------------------------------*/
		Session::start();
		/* ------------------------------------*/


		/* ------------------------------------*/
		/* 		LOG OUT
		/* ------------------------------------*/
		$secure = true;

		$siteConfig = Core::getSiteConfigFile("config", false);

		if(isset($siteConfig['SESSION_NAME'])){
			$sessionName = $siteConfig['SESSION_NAME'];
			$isLoggedIn = Session::exists($sessionName);

			if($isLoggedIn){
				$id_user = Login::getId($sessionName);	

				$ip = Http::getIPClient();
				$db = Core::db(RDKS_ERRORS);

				$user = UsersTable::open($db);
				$row = $user->row($id_user);
				$location = (!empty($row['location'])) ? $row['location'] : $ip;

				// Expiration account
				if($row['expires'] == 1 && Date::getCurrentDate() >= Date::parseDate($row['expiration_date'])){
					$user->update(['id_user' => $id_user],['loggedin' => 0,'active' => 0]);
					$row['active'] = 0;
					$secure = false;
				}

				// Log Out when User is disabled - or - Admin forces User to log out
				if( $row['active'] == 0 || $row['loggedin'] == 0 ){
					
					if($location == $ip && Login::security(true) && $secure === TRUE){
						$user->update(['id_user' => $id_user],['loggedin' => 1]);
					} else {
						Login::logout($sessionName);
					}

				}
			}
		}

		/* ------------------------------------*/
		/* 		INITIAL VARS
		/* ------------------------------------*/
		$getGETParams = URL::getGETParams();
		$routerPath = Core::getSiteConfigPath("router");
		$params = URL::getParams();
		$dispatcher = ['dispatch' => 'Page::pageNotFound'];
		
		$found = false;
		$rowUrl = [];
		$mainPath = "";

		$router = [];
		$routers = [];
		$subRouter = [];
		$urlPattern = [];
		$missingGETParams = [];
		$missingPOSTParams = [];
		$unknownGETParams = [];		
		/* ------------------------------------*/


		/* ------------------------------------*/
		/* 		ROUTER URLS
		/* ------------------------------------*/
		$routers = Core::getRouterFile();
		$router['/_email/(?P<TEMPLATE>[a-z\-]+)'] = ['dispatch' => 'Page::_email'];
		$router['/_lang/(?P<LANG>\w{2}).*'] = ['dispatch' => 'Page::_lang'];
		$router['/_(page|json|xml|factory)/' . Helper::REGEXP_URL_DISPATCH] = ['dispatch' => 'Output::_data_'];
		$router['/_block/' . Helper::REGEXP_URL_DISPATCH] = ['dispatch' => 'Output::_block_'];
		$router['/_service/' . Helper::REGEXP_URL_DISPATCH] = ['dispatch' => 'Output::_service_'];

		/* ------------------------------------*/
		/* 		DISPATCHER
		/* ------------------------------------*/

		// We are in root
		if($URI == URL::ROOT){

			// Default view controller
			if(isset($routers[$URI])){
				$dispatcher = $routers[$URI];
				$found = true;				
			}else{
				Error::defaultPageIsMissing("Undefined default page",__LINE__, __FILE__, $routerPath);
			}

		}else{
			
			if(isset($params[0]) && !Helper::isDispatch($params[0])){
				
				$subRouter = $params;
				$mainPath = URL::ROOT.$params[0];
				$altMainPath = $mainPath . URL::ROOT;

				if($altMainPath == $URI){
					$mainPath = $altMainPath;
					$params = [];
				}

				if(isset($routers[$mainPath]) || isset($routers[$altMainPath])){

					if(!isset($routers[$mainPath])){
						$mainPath = $altMainPath;
					}

					if(count($params) > 1){
						unset($subRouter[0]);
						$subRouter = Helper::resetArray($subRouter);
						$subURI = URL::ROOT . implode(URL::ROOT, $subRouter);

						if(isset($routers[$mainPath]['path'])){
							$router = $routers[$mainPath]['path'];
						}
						
					} else {
						$router[$mainPath] = $routers[$mainPath];
						$mainPath = "";
					}
				} else {
					$router = $routers;
					$mainPath = "";
				}

			}

			// Let's find a URL
			foreach($router as $key => $value) {

				// Let's look if a URL matches
				if(preg_match('#^'. $mainPath . $key . URL::REGEXP_GET .'$#', $URI, $urlPattern)){
					$dispatcher = $value;
					$found = true;
					break;
				}
			}
		}

		// Let's search URL in database
		if(!$found && FIND_URL_IN_DB){

			$db = Core::db(RDKS_ERRORS);

			$queryUrl = UrlsUrlsLang::open($db);
			$queryUrl->filter(['ul.url' => URL::getBaseURL(), 'u.active' => 1]);

			// It was found it
			if($queryUrl->rows()){
				$rowUrl = $queryUrl->fetch();
				$dispatcher = ['dispatch' => $rowUrl['dispatch']];
			}

		}

		// Make sure we have a valid dispatcher 
		if(isset($dispatcher['dispatch'])) {
			if(Helper::regexp('#::#', $dispatcher['dispatch'])) {
				list($page,$method) = explode("::", $dispatcher['dispatch']);
			} else {
				Error::debug("Bad dispatcher syntax",__LINE__, __FILE__, $routerPath);
			}
		} else {
			Error::debug('Missing index "dispatch" on $router var',__LINE__, __FILE__, $routerPath);
		}

		// Modules Map
		$modulesMap = Core::getModulesFile();
		$module = Helper::getModule($page);

		// Let's see if module is enabled
		if( 
			(!isset($modulesMap[$module]) || $modulesMap[$module] === FALSE) 
		&& ($page != 'Output' && !Helper::isService($page) && $page != "Page") 
		){
			Error::moduleDisabled("Module is disabled or undefined", __LINE__, __FILE__, Core::getSiteConfigPath("modules"), $module);	
		} else {

			// Let's take a look if there's POST Params
			if(isset($dispatcher['POST']) && is_array($dispatcher['POST'])) {

				if(isset($dispatcher['POST'][':required'])){
					if(!Post::stSentData()){
						if(Helper::regexp('#::#', $dispatcher['POST'][':required'])) {
							list($page,$method) = explode("::", $dispatcher['POST'][':required']);
						} else {
							Error::debug("Bad dispatcher syntax",__LINE__, __FILE__, $routerPath);
						}
					}
					unset($dispatcher['POST'][':required']);
				} else {
					Post::stRequired();
				}
				
				foreach ($dispatcher['POST'] as $key => $value) {

					$val = (is_array($value)) ? array_keys($value)[0] : $value;
					
					if(!Post::stSent($key) && !Helper::isOptionalParam($val)) {
						$missingPOSTParams[] = "Post param <b style=\"color: #c00;\">{$key}</b> is <b>required.</b>";
					} else {

						if(Post::stSent($key)){

							// Let's validate expecting match value
							if(is_array($value)){

								$k = array_keys($value);
								$v = array_values($value);

								$match = $v[0];
								$value = $k[0];

								$err = "Param <b>{$key}</b> does not match with this regular expression: {$match}";
								$regexp_rule = $match;

								if(Helper::isConditional($match)){
									$err = "Param <b>{$key}</b> <b style=\"color: #c00;\">ONLY</b> allows the next values: " . Helper::getOptions($match);
									$regexp_rule = '/^'.$match.'$/';
								}
								
							}

							switch ($value) {

								case self::PARAM_STRING:
								case self::OPTIONAL_PARAM_STRING:
									$regexp = Helper::VALID_STRING;
								break;

								case self::PARAM_WORD:
								case self::OPTIONAL_PARAM_WORD:
									$regexp = Helper::VALID_WORD;
								break;

								case self::PARAM_WORDS:
								case self::OPTIONAL_PARAM_WORDS:
									$regexp = Helper::VALID_WORDS;
								break;

								case self::PARAM_INTEGER:
								case self::OPTIONAL_PARAM_INTEGER:
									$regexp = Helper::VALID_INTEGER;
								break;	

								case self::PARAM_BOOL:
								case self::OPTIONAL_PARAM_BOOL:
									$regexp = Helper::VALID_BOOL;
								break;

								case self::PARAM_CLABE:
								case self::OPTIONAL_PARAM_CLABE:
									$regexp = Helper::VALID_CLABE;
								break;
								
								case self::PARAM_PASSWORD:
								case self::OPTIONAL_PARAM_PASSWORD:
									$regexp = Helper::VALID_PASSWORD;
								break;

								case self::PARAM_EMAIL:
								case self::OPTIONAL_PARAM_EMAIL:
									$regexp = Helper::VALID_EMAIL;
								break;

								case self::PARAM_USERNAME:
								case self::OPTIONAL_PARAM_USERNAME:
									$regexp = Helper::VALID_USERNAME;
								break;

							}

							if(!Helper::regexp($regexp, Post::stValue($key))){
								$missingPOSTParams[] = "Param <b style=\"color: #c00;\">{$key}</b> must be <b>". str_replace('optional_', '', $value) ."</b>.";
							}
						}
					}
				}

				// Send Error
				if(count($missingPOSTParams) > 0){
					if($params[0] == '_json' || preg_match('#json#', $page)) {
						
						$jsonData = (Environment::inDEV()) ? $missingPOSTParams : [];
						
						JSON::stOutput([
							'code' => 501,
							'success' => false,
							'message' => TEXT_INVALID_REQUEST,
							'format' => true,
							'data' => $jsonData
						]);
					} else {
						Error::missingParams('Missing POST params',__LINE__, __FILE__, $routerPath, $missingPOSTParams);
					}
				}
			}

			// Let's take a look if there's GET Params
			if(isset($dispatcher['GET']) && is_array($dispatcher['GET'])){

				foreach($dispatcher['GET'] as $key => $value){
				
					// Let's validate expecting match value
					if(is_array($value)){

						$k = array_keys($value);
						$v = array_values($value);

						$match = $v[0];
						$value = $k[0];

						$err = "Param <b>{$key}</b> does not match with this regular expression: {$match}";
						$regexp_rule = $match;

						if(Helper::isConditional($match)){
							$err = "Param <b>{$key}</b> <b style=\"color: #c00;\">ONLY</b> allows the next values: " . Helper::getOptions($match);
							$regexp_rule = '/^'.$match.'$/';
						}
						
						if(isset($getGETParams[$key])){

							if(!Helper::regexp($regexp_rule, $getGETParams[$key])){
								Error::debug("Unexpected value",__LINE__, __FILE__, $routerPath,$err);
							}
						}
					}
					
					/* ----------------------------------------------*/
					// OPTIONAL GET PARAMS
					/* ----------------------------------------------*/	
					if(Helper::isOptionalParam($value)){	
					
						if(isset($getGETParams[$key])){

							if(!empty($getGETParams[$key])){
								
								switch ($value) {

									case self::OPTIONAL_PARAM_STRING:
										$regexp = Helper::VALID_STRING;
									break;

									case self::OPTIONAL_PARAM_WORD:
										$regexp = Helper::VALID_WORD;
									break;

									case self::OPTIONAL_PARAM_WORDS:
										$regexp = Helper::VALID_WORDS;
									break;

									case self::OPTIONAL_PARAM_INTEGER:
										$regexp = Helper::VALID_INTEGER;
									break;	

									case self::OPTIONAL_PARAM_BOOL:
										$regexp = Helper::VALID_BOOL;
									break;

									case self::OPTIONAL_PARAM_CLABE:
										$regexp = Helper::VALID_CLABE;
									break;
									
									case self::OPTIONAL_PARAM_PASSWORD:
										$regexp = Helper::VALID_PASSWORD;
									break;

									case self::OPTIONAL_PARAM_EMAIL:
										$regexp = Helper::VALID_EMAIL;
									break;

									case self::OPTIONAL_PARAM_USERNAME:
										$regexp = Helper::VALID_USERNAME;
									break;

								}	

								if(!Helper::regexp($regexp, $getGETParams[$key])){
									$missingGETParams[] = "Param <b>{$key}</b> must be <b>" . str_replace('optional_', '', $value) . "</b>.";
								}


							}else{
								if(!Helper::isInteger($getGETParams[$key]))
									$missingGETParams[] = "Param <b>{$key}</b> is empty.";
							}											

						}						
			
					}else{
					
					/* ----------------------------------------------*/
					// OBLIGATORY GET PARAMS
					/* ----------------------------------------------*/
						
						if(!isset($getGETParams[$key])){
							$missingGETParams[] = "GET Param <b>{$key}</b> is required.";
						}else{
							if(!empty($getGETParams[$key])){

								switch ($value) {

									case self::PARAM_STRING:
										$regexp = Helper::VALID_STRING;
									break;

									case self::PARAM_WORD:
										$regexp = Helper::VALID_WORD;
									break;

									case self::PARAM_WORDS:
										$regexp = Helper::VALID_WORDS;
									break;

									case self::PARAM_INTEGER:
										$regexp = Helper::VALID_INTEGER;
									break;	

									case self::PARAM_BOOL:
										$regexp = Helper::VALID_BOOL;
									break;

									case self::PARAM_CLABE:
										$regexp = Helper::VALID_CLABE;
									break;
									
									case self::PARAM_PASSWORD:
										$regexp = Helper::VALID_PASSWORD;
									break;

									case self::PARAM_EMAIL:
										$regexp = Helper::VALID_EMAIL;
									break;	

									case self::PARAM_USERNAME:
										$regexp = Helper::VALID_USERNAME;
									break;

								}

								if(!Helper::regexp($regexp, $getGETParams[$key])){
									$missingGETParams[] = "Param <b>{$key}</b> must be <b>$value</b>.";
								}

							}else{
								if(!Helper::isInteger($getGETParams[$key]))
								$missingGETParams[] = "Param <b>{$key}</b> is empty.";
							}

						}	


					} // end else

				} // end foreach


				// Unknown GET Params are not allowed
				foreach ($getGETParams as $key => $value) {
					if(!isset($dispatcher['GET'][$key])){
						$unknownGETParams[] = "Unknown param <b>{$key}</b>.";
					}
				}

				// Send Error
				if(count($unknownGETParams) > 0){
					Error::missingParams('Unknown GET param',__LINE__, __FILE__, $routerPath, $unknownGETParams);
				}

				// Send Error
				if(count($missingGETParams) > 0){
					Error::missingParams('Missing GET param',__LINE__, __FILE__, $routerPath, $missingGETParams);
				}

			}
			
			/* ----------------------------------------------*/
			// Dispatch page
			/* ----------------------------------------------*/
			if(Helper::isService($page)) {
				$servicePath = Core::getServicesPath($page);
				$pagePath = $servicePath['path'];
			} else {
				$pagePath = ($page == 'Page') ? DIR_CORE_PAGE : Core::getModulesPath();
			}
			
			if($method == "_data_" || $method == "_block_") {

				$invalidParam = self::getLastParams($params);

				if($invalidParam){

					$page = "Page";
					$method = "pageNotFound";
					$pagePath = DIR_CORE_PAGE;

				} else {

					list($type, $page) = $params;
					$type = Helper::removeUnderscore($type);
					$page = Helper::getCamelName($page);

					switch ($method) {
						case '_data_':
							$pagePath = Core::getModulesPath() . $page . "/" . $type . "/";
							$action = ($type == 'page' || $type == 'factory') ? 'index' : 'getData';
							break;
						case '_block_':
							$pagePath = Core::getBlocksPath($page);
							$action = 'output';
							$urlPattern = [];
							break;
					}

					$method = (!isset($params[2])) ? $action : $params[2];
					$params = Helper::getUrlParams($params);

					if((!isset($modulesMap[$page]) || $modulesMap[$page] === FALSE) && ($method == '_data_')){
						Error::moduleDisabled("Module is disabled or undefined", __LINE__, __FILE__, Core::getSiteConfigPath("modules"), $page);
					}
				}

			} else if($method == "_service_") {

				$invalidParam = self::getLastParams($params);
				
				if($invalidParam){
					
					$page = "Page";
					$method = "pageNotFound";
					$pagePath = DIR_CORE_PAGE;

				} else {

					list($type, $page) = $params;
					$method = (!isset($params[2])) ? 'getData' : $params[2];
					$page = DIR_SERVICES . Helper::getCamelName($page);

					$servicePath = Core::getServicesPath($page);
					$pagePath = $servicePath['path'];
			
					$params = Helper::getUrlParams($params);

				}

			}

		}

		// avoid dispatch page if factory exists
		Error::cantDispatchFactory($pagePath, $page);

		// Load page
		Core::loadPage($pagePath, $page, $method, $urlPattern, $params, false, $rowUrl);

	} // end init method

} 