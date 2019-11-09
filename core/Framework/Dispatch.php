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

use Roducks\Page\JSON;
use Roducks\Libs\Data\Session;
use Roducks\Libs\Utils\Date;
use Roducks\Libs\Request\Http;
use Roducks\Libs\Request\CORS;
use DB\Models\SEO\UrlsUrlsLang;
use DB\Models\Users\Users as UsersTable;
use Request;
use User;

class Dispatch
{

	static $httpMethods = ['GET','POST','OPTIONS','PUT','PATCH','DELETE'];

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

	static function page($class, $method = "index")
	{
		$class = Helper::getCamelName($class);
		return "{$class}/Page/{$class}::{$method}";
	}

	static function _page($class, $method)
	{
		return "/_page/{$class}/{$method}";
	}

	static function factory($class, $method)
	{
		$class = Helper::getCamelName($class);
		return "{$class}/Factory/{$class}::{$method}";
	}

	static function _factory($class, $method)
	{
		return "/_factory/{$class}/{$method}";
	}

	static function json($class, $method = "output")
	{
		$class = Helper::getCamelName($class);
		return "{$class}/JSON/{$class}::{$method}";
	}

	static function _json($class, $method)
	{
		return "/_json/{$class}/{$method}";
	}

	static function xml($class, $method = "preview")
	{
		$class = Helper::getCamelName($class);
		return "{$class}/XML/{$class}::{$method}";
	}

	static function _xml($class, $method)
	{
		return "/_xml/{$class}/{$method}";
	}

	static function service($class, $method = "output")
	{
		$class = Helper::getCamelName($class);
		return "Services/{$class}::{$method}";
	}

	static function _service($class, $method)
	{
		return "/_service/{$class}/{$method}";
	}

	static function api($class, $method = "")
	{
		$class = Helper::getCamelName($class);
		return "API/{$class}::{$method}";
	}

	static function event($class, $method = "run")
	{
		$class = Helper::getCamelName($class);
		$method = Helper::getCamelName($method, false);
		return "{$class}::{$method}";
	}

	static function values($arr) {
		return "(".implode("|", $arr).")";
	}

	static private function _getLastParams($params)
	{

		// Avoid empty params
		$total = count($params) - 1;

		if ($total > 2) {
			$last = (isset($params[$total]) && empty($params[$total]) && $params[$total] != '0');
		} else {
			$last = (isset($params[2]) && ($params[2] == '0' || $params[2] == ""));
		}

		return $last;
	}

	static private function _httpRequestMethods($dispatcher)
	{

		$methods = self::$httpMethods;
		$allowedMethods = [];

		foreach ($methods as $method) {
			if (isset($dispatcher[$method])) {
				array_push($allowedMethods, $method);
			}
		}

		if (!in_array(Http::getRequestMethod(), $allowedMethods)) {
			Http::sendMethodNotAllowed(false);
			JSON::stOutput(['data' => ['code' => 405, 'message' => "Method Not Allowed"]]);
		}

		return $allowedMethods;

	}

	static private function _getRequestBody(array $values = [])
	{

		$obj = Request::obj();

		if (count($values) > 0) {
			foreach ($values as $key => $value) {
				$value = (Helper::isInteger($value)) ? intval($value) : $value;
				$obj->$key = $value;
			}
		}

		return $obj;

	}

	static function init()
	{

		/* ------------------------------------*/
		/* 		PREVENT POSSIBLE CSRF ATTACK
		/* ------------------------------------*/
		URL::preventCSRFAttack();
		/* ------------------------------------*/

		/* ------------------------------------*/
		/* 		ROUTER URLS
		/* ------------------------------------*/
		if (Path::isSiteAll()) {
			Error::debug("Can't dispatch URL", __LINE__, __FILE__, Path::clean(Path::getAppAllSite()), "Can't use 'All' site folder to dispatch URLs.<br>It is used to <b>extend</b> classes to the other available sites and avoid duplicated code.");
		}

		/* ------------------------------------*/
		/* 		START SESSION
		/* ------------------------------------*/
		Session::start();
		/* ------------------------------------*/

		/* ------------------------------------*/
		/* 		LOG OUT
		/* ------------------------------------*/
		$secure = true;

		$siteConfig = Config::fromSite()['data'];

		if (isset($siteConfig['session.name'])) {
			$sessionName = $siteConfig['session.name'];
			$isLoggedIn = Session::exists($sessionName);

			if ($isLoggedIn) {
				$id_user = User::getId($sessionName);

				$ip = Http::getIPClient();
				$db = Core::db();

				$user = UsersTable::open($db);
				$row = $user->row($id_user);
				$location = (!empty($row['location'])) ? $row['location'] : $ip;

				// Expiration account
				if ($row['expires'] == 1 && Date::getCurrentDate() >= Date::parseDate($row['expiration_date'])) {
					$user->update(['id_user' => $id_user],['loggedin' => 0,'active' => 0]);
					$row['active'] = 0;
					$secure = false;
				}

				// Log Out when User is disabled - or - Admin forces User to log out
				if ( $row['active'] == 0 || $row['loggedin'] == 0 ) {

					if ($location == $ip && User::security(true) && $secure === TRUE) {
						$user->update(['id_user' => $id_user],['loggedin' => 1]);
					} else {
						User::logout();
					}

				}
			}
		}

		$routerConfig = Config::getRouter();
		$routerPath = $routerConfig['path'];
		$URI = URL::getRelativeURL();
		$path = Path::getCorePage();
		$baseURL = URL::getBaseURL();
		$params = URl::getSplittedURL();
		$module = 'Page';
		$method = 'notFound';
		$prefix = '';
		$type = 'module';
		$queryString = URL::getQueryString();
		$urlParams = [];
		$dispatcher = [];
		$urlData = [];
		$found = false;
		$urlDispatcher = false;

		Core::requireConfig($routerConfig);
		$router = Router::dispatch();

		$_router['/_lang/(?P<LANG>\w{2}).*'] = ['dispatch' =>  'Page::_lang'];
		$_router['/_(page|json|xml|factory)/' . Helper::REGEXP_URL_DISPATCH] = ['dispatch' => 'Output::module'];
		$_router['/_block/' . Helper::REGEXP_URL_DISPATCH] = ['dispatch' => 'Output::block'];
		$_router['/_service/' . Helper::REGEXP_URL_DISPATCH] = ['dispatch' => 'Output::service'];

		// We are in root
		if ($URI == DIRECTORY_SEPARATOR) {

			// Default view controller
			if (isset($router[$URI])) {
				$dispatcher = $router[$URI];
				$found = true;
			} else {
				Error::defaultPageIsMissing("Undefined default page",__LINE__, __FILE__, $routerPath);
			}

		} else {

			if (Helper::isUrlDispatch()) {
				$router = $_router;
				$urlDispatcher = true;
			} else {

				$prefix = DIRECTORY_SEPARATOR . $params[0];
				
				if (isset($router[$prefix]['path']) ) {
					$routerx = $router[$prefix];
					$count = count($params);

					if ($count > 1) {
						$router = $routerx['path'];
					} else if ($count == 1 && Helper::regexp('/\/$/', $URI)) {
						$router = [];
						$router[$prefix.DIRECTORY_SEPARATOR] = $routerx;
						$prefix = '';
					} else {
						unset($router[$prefix]);
						$prefix = '';
					}

				} else {
					$prefix = '';
				}
			}

			// Let's take a look 
			foreach ($router as $url => $dispatcher) {

				$url = preg_replace_callback('#{([a-zA-Z_]+):(int|chars|str|slug)}#', function ($match) {

					switch ($match[2]) {
							case 'int':
									$type = '\d';
									break;

							case 'chars':
									$type = '\w';
									break;

							case 'str':
									$type = '.';
									break;

							case 'slug':
									$type = '[a-z0-9\-]';
									break;

					}

					$name = $match[1];

					return "(?P<{$name}>{$type}+)";

				}, $url);

				if (preg_match('#^' . $prefix . $url . URL::REGEXP_GET . '$#', $URI, $urlParams)) {
					$found = true;
					break;
				}
			}

		}

		// Let's search URL in database
		if (!$found && FIND_URL_IN_DB) {

			$db = Core::db();
			$baseURL = URL::getBaseURL();

			$queryUrl = UrlsUrlsLang::open($db);
			$queryUrl->filter([
				'[BEGIN_COND]' => "(",
					'[NON_1]ul.url' => $baseURL,
					'[OR]ul.url_redirect' => $baseURL,
				'[END_COND]' => ")",
				'u.active' => 1
			]);

			// It was found it
			if ($queryUrl->rows()) {

				$urlData = $queryUrl->fetch();
				$dispatcher = ['dispatch' => $urlData['dispatch']];

				if ($urlData['url'] == $baseURL && !empty($urlData['url_redirect'])) {

					if (!Environment::inDEV()) {
						Http::sendHeaderMovedPermanently(false);
					}

					Http::redirect($urlData['url_redirect']);
				}
			}

		}

		if ($found) {

			// Make sure we have a valid dispatcher
			if (isset($dispatcher['dispatch'])) {
				if (Helper::regexp('#::#', $dispatcher['dispatch'])) {
					list($class, $method) = explode("::", $dispatcher['dispatch']);
				} else {
					Error::debug("Bad dispatcher syntax",__LINE__, __FILE__, $routerPath);
				}
			} else {
				Error::missionDispatchIndex($URI ,'Missing "dispatch" index',__LINE__, __FILE__, $routerPath);
			}

			if ($urlDispatcher) {

				$module = Helper::getCamelName($params[1]);

				if ($method != '_lang') {
					$urlParams = Helper::getUrlParams($params);
					$queryString = URL::getQueryString();
				}

				$type = $method;

				switch ($type) {
					case 'module':

						switch ($params[0]) {

							case '_page':
								$path = Path::getModulePage($module);
								$method = 'index';
							break;

							case '_json':
								$path = Path::getModuleJson($module);
								$method = 'encoded';
							break;

							case '_xml':
								$path = Path::getModuleXml($module);
								$method = 'preview';
							break;
						}

						break;

					case 'block':
						$path = Path::getBlock($module);
						$method = 'output';
						break;

					case 'service':
						$path = Path::getService($module);
						$method = 'rest';
					break;
					
				}

				if (isset($params[2]) && $method != '_lang') {
					$method = Helper::getCamelName($params[2], false);
				}

			} else {

				/**
				 * API
				 */
				if (Helper::isApi($class)) {

					$api = Helper::getClassName($class);
					$path = Path::getAPI($api);
					$module = 'API';

					$requestMethod = Http::getRequestMethod();
					$allowedMethods = self::_httpRequestMethods($dispatcher);

					if (count($allowedMethods) > 0) {
						CORS::methods($allowedMethods);

						$params = [];
						$u = 0;

						if (empty($method)) {
							switch ($requestMethod) {
								case 'POST':
									$method = 'store';
									break;
								case 'PUT':
									$method = 'update';
									break;
								case 'DELETE':
									$method = 'remove';
									break;
							}

							if (in_array($requestMethod, ['PUT','DELETE'])) {
								$request = Http::getBody();
							}

							if ($requestMethod == 'GET') {
								$request = $queryString;
							}

							if ($requestMethod == 'POST') {
								$request = Post::stData();
							}

							$params['request'] = self::_getRequestBody($request);

						}

						foreach ($urlParams as $key => $value) {
							if (!Helper::isInteger($key)) {
								$params[$key] = (Helper::isInteger($value)) ? intval($value) : $value;
								$u++;
							}
						}

						if ($requestMethod == 'GET') {

							if ($u > 0) {
								$method = 'row';
								unset($params['request']);
							} else {
								$method = 'catalog';
							}

						}

						if (isset($dispatcher['jwt']) && $dispatcher['jwt'] === TRUE) {
							$params['jwt'] = true;
						}

						$urlParams = $params;

					}
				
				} else if(Helper::isService($class)) {
					$module = Helper::getClassName($class);
					$path = Path::getService($module);
					$type = 'service';
				} else {
					$path = Path::getModule($class);
					$module = Helper::getModule($class);
				}

				/* ----------------------------------------------*/
				// Cross Domain
				/* ----------------------------------------------*/
				if (isset($dispatcher['headers'])) {
					CORS::headers($dispatcher['headers']);
				}

				if (isset($dispatcher['cors'])) {

					CORS::allowDomains($dispatcher['cors']);

					if (isset($dispatcher['max-age'])) {
						CORS::maxAge($dispatcher['max-age']);
					} else {
						CORS::maxAge();
					}

				}

			}

			if (Helper::isModule($path)) {
				\App::define('RDKS_MODULE', $module);
			}

		}

		// Modules Map
		$siteModules = Config::getSiteModules();
		$modulesMap = $siteModules['data'];

		if ((!isset($modulesMap[$module]) || $modulesMap[$module] === FALSE) && !in_array($module, ['Page', 'API']) && $type == 'module') {
			Error::moduleDisabled("Module is disabled or undefined", __LINE__, __FILE__, $siteModules['path'], $module);
		}

		/*
		|--------------------------------|
		|		  SET LANGUAGE
		|--------------------------------|
		*/
		if (isset($urlData['id_lang']) && $method != '_lang') {
			$iso = Language::getIso($urlData['id_lang']);

			if ($iso != Language::get()) {
				Http::redirect(URL::lang($iso));
			}
		}

		Core::duckling();

		// Output data
		Render::view($path, $module, $method, $queryString, $urlParams, false, $urlData);

	} // end init method

}
