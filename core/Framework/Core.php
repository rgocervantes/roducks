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

use Roducks\Libs\ORM\DB;
use Roducks\Page\View;

class Core
{

	const DEFAULT_SUBDOMAIN = 'www';
	const ADMIN_SUBDOMAIN = 'admin';
	const ALL_SITES_DIRECTORY = 'All';
	const NS = 'Roducks/';

	static function requirements()
	{
		$version = '7.0.0';

		if (version_compare(PHP_VERSION, $version) <= 0) {
			$text = "requires version {$version} or later.";
			if (Environment::inDEV()) {
				Error::requirements('PHP VERSION '.PHP_VERSION, 0, '', '', ['Roducks '.$text], "<b>Roducks</b> {$text}");
			} else {
				Error::pageNotFound();
			}

		}

		if (function_exists('extension_loaded')) {
			$error = 0;
			$items = [];
			$exts = [
				'date',
				'libxml',
				'SimpleXML',
				'curl',
				'gd',
				'json',
				'mbstring',
				'mysqlnd',
				'mysqli',
				'PDO',
				'pdo_mysql',
				'xml',
				'xmlreader',
				'xmlrpc',
				'xmlwriter',
				'zip'
			];
			$message = '<ul>';

			foreach ($exts as $ext) {
				if (!extension_loaded($ext)) {
					$error++;
					array_push($items, $ext);
					$message .= '<li>'.$ext.'</li>';
				}
			}
			$message .= '</ul>';

			if ($error > 0) {
				if (Environment::inDEV()) {
					Error::requirements('PHP Required Extentions', 0, '', '', $items, $message);
				} else {
					Error::pageNotFound();
				}
			}

		}

	}

	static function getVersion()
	{
		return RDKS_VERSION;
	}

	static function getClassNamespace($class)
	{
		if ($class == Helper::PAGE_NOT_FOUND) {
			return $class;
		}

		$class = str_replace("app/", "App/", $class);

		return str_replace('/', '\\', $class);
	}

	static function duckling()
	{
		$file = Path::get(DIR_APP_CONFIG.'duckling'.FILE_INC);

		if (file_exists($file)) {
			include_once $file;
		}
	}

	static function db()
	{
		try {
			return DB::get([DB_HOST,DB_USER,DB_PASSWORD,DB_NAME]);
		} catch (\Exception $e) {

			$file = (Environment::inDEV() || RDKS_ERRORS) ? 'database.local' : 'database';
			$config = self::getAppConfigPath($file);

			if (RDKS_ERRORS) {
				if (!Environment::inCLI()) {
					if ($e->getMessage() == 'credentials') {
						Error::missingDbConfig("Missing DB Credentails", __LINE__, __FILE__, $config, $e->getMessage(), '');
					} else {
						Error::fatal("MySQLi", __LINE__, __FILE__, $config, $e->getMessage());
					}
				} else {
					CLI::printError("Unconfigured file: {$config}", CLI::FAILURE);
				}

			} else {
				if (!Environment::inCLI()) {
					Error::pageNotFound();
				} else {
					CLI::printError("Unconfigured file: {$config}", CLI::FAILURE);
				}
			}
		}
	}

	static function openDb(array $conn = [])
	{
		try {
			return DB::open($conn);
		} catch (\Exception $e) {
			if (RDKS_ERRORS) {
				Error::fatal("Missing DB Credentails", __LINE__, __FILE__, '', $e->getMessage());
			} else {
				if (!Environment::inCLI()) {
					Error::pageNotFound();
				} else {
					CLI::printError("Missing DB Credentails", CLI::FAILURE);
				}
			}
		}
	}

	static function getSitePath($site = "")
	{
		$folder = (!empty($site)) ? $site : RDKS_SITE;
		return DIR_APP_SITES . $folder . DIRECTORY_SEPARATOR;
	}

	static function getGlobalPath()
	{
		return DIR_APP_SITES . self::ALL_SITES_DIRECTORY . DIRECTORY_SEPARATOR;
	}

	static function getAppConfigPath($file)
	{
		return DIR_APP_CONFIG . $file . FILE_INC;
	}

	static function getSiteConfigPath($file, $site = "")
	{
		return self::getSitePath($site) . DIR_CONFIG . $file . FILE_INC;
	}

	static function getSiteModuleConfigPath($site, $module)
	{
		return self::getSitePath($site) . DIR_MODULES . $module . DIRECTORY_SEPARATOR . DIR_CONFIG . "config" . FILE_INC;
	}

	static function getGlobalConfigPath($file = "config")
	{
		return self::getGlobalPath() . DIR_CONFIG . $file . FILE_INC;
	}

	static function getPageConfigPath($page)
	{
		return self::getModulesPath() . Helper::getCamelName($page) . DIRECTORY_SEPARATOR . DIR_CONFIG . "config" . FILE_INC;
	}

	static function getPath($dir, $tpl, $slash = true)
	{

		$ds = ($slash) ? DIRECTORY_SEPARATOR : '';
		$global = self::getGlobalPath() . $dir . $tpl . $ds;
		$global = \App::getRealFilePath($global);
		$site = self::getSitePath() . $dir . $tpl . $ds;
		list($realPath, $fileExists) = \App::getRealPath($site);

		if ($fileExists) {
			return $realPath;
		}

		return $global;
	}

	/**
	*
	*/

	static function getLanguagesPath($lang)
	{
		return DIR_APP_LANGUAGES . $lang . FILE_INC;
	}

	static function getServicesPath($service = "")
	{

		$path1 = self::getSitePath();
		$path2 = self::getGlobalPath();
		$path3 = DIR_CORE;
		$path = $path3;

		$site = $path1 . $service . FILE_EXT;
		$global = $path2 . $service . FILE_EXT;
		$core = $path3 . $service . FILE_EXT;

		if (\App::fileExists($site)) {
			$path = $path1;
		} else if (\App::fileExists($global)) {
			$path = $path2;
		} else if (\App::fileExists($core)) {
			$path = $path3;
		}

		return ['path' => $path, 'service' => $service];

	}

	static function getCoreModulesPath($ns = true)
	{

		$namespace = CORE_NS;

		if (!$ns) {
			$namespace = "";
		}

		return $namespace . DIRECTORY_SEPARATOR . DIR_CORE . DIR_MODULES . RDKS_SITE . DIRECTORY_SEPARATOR;
	}

	static function getModulesPath()
	{
		return self::getSitePath() . DIR_MODULES;
	}

	static function getCoreModulesPathFrom($path)
	{
		return str_replace(self::getModulesPath(), self::getCoreModulesPath(false), $path);
	}

	static function getBlocksPath($tpl)
	{

		$path0 = DIR_CORE . DIR_BLOCKS . $tpl . DIRECTORY_SEPARATOR;
		$path1 = self::getSitePath() . DIR_BLOCKS . $tpl  . DIRECTORY_SEPARATOR;
		$path2 = self::getGlobalPath() . DIR_BLOCKS . $tpl . DIRECTORY_SEPARATOR;
		$path = $path1;

		$core = $path0 . $tpl . FILE_EXT;
		$site = $path1 . $tpl . FILE_EXT;
		$global = $path2 . $tpl . FILE_EXT;

		if (\App::fileExists($site)) {
			$path = $path1;
		} else if (\App::fileExists($global)) {
			$path = $path2;
		} else if (\App::fileExists($core)) {
			$path = $path0;
		}

		return $path;

	}

	static function getViewsPath($parentPage, $path, $tpl, $parser = true)
	{

		if ($path == Helper::PAGE_NOT_FOUND) {
			return DIR_CORE;
		}

		$view = DIR_VIEWS . $tpl;
		$found = false;

		$coreModules = self::NS . DIR_MODULES;
		$coreBlocks = DIR_CORE . DIR_BLOCKS;
		$siteBlocks = self::getSitePath() . DIR_BLOCKS;
		$globalBlocks = self::getGlobalPath() . DIR_BLOCKS;

		$parentPath = Helper::getClassName($parentPage, '$1');
		$pathCore = $parentPath . DIRECTORY_SEPARATOR;
		$file = Helper::getClassName($path);

		// Remove underscore for block name, example: app/Sites/Admin/Blocks/_Roles/
		if (Helper::regexp(Helper::REGEXP_IS_URL_DISPATCH, $file)) {
			$path = Helper::getBlockName($path);
		}

		if (\App::fileExists($path.$view)) {

			$found = true;

		} else if (Helper::regexp('#^'. $coreModules .'#', $parentPath)) {

			$file = str_replace($coreModules, "", $parentPath);
			$path = DIR_CORE . DIR_MODULES . $file . DIRECTORY_SEPARATOR;

			if (\App::fileExists($path.$view)) {
				$found = true;
			}
		} else {

			$file = Helper::removeUnderscore($file);

			if (Helper::isBlock($path)) {

				if (\App::fileExists($siteBlocks.$file.$view) && !empty($tpl)) {
					$path = $siteBlocks.$file;
					$found = true;
				} else if (\App::fileExists($globalBlocks.$file.$view) && !empty($tpl)) {
					$path = $globalBlocks.$file;
					$found = true;
				} else if (\App::fileExists($coreBlocks.$file.$view) && !empty($tpl)) {
					$path = $coreBlocks.$file;
					$found = true;
				}
			}
		}

		if (!$found) {
			if ($parser) {
				return self::getViewsPath($parentPage, $path, str_replace(FILE_PHTML, FILE_TPL, $tpl), false);
			} else {
				Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $path.$view);
			}
		}

		return \App::getRealFilePath($path.$view);

	}

	static function getTemplatesPath($tpl)
	{
		return self::getPath(DIR_TEMPLATES, $tpl);
	}

	static function getLayoutsPath($tpl)
	{
		return self::getPath(DIR_LAYOUTS, $tpl . FILE_PHTML, false);
	}

	static function getMenuPath($tpl)
	{
		return self::getPath(DIR_MENUS, $tpl . FILE_INC, false);
	}

	static function getEmailsPath($tpl)
	{
		return self::getPath(DIR_EMAILS, $tpl . FILE_PHTML, false);
	}

	static function getEventsPath()
	{

		$global = self::getGlobalPath() . DIR_EVENTS;
		$site = self::getSitePath() . DIR_EVENTS;
		list($realPath, $fileExists) = \App::getRealPath($site);

		if ($fileExists) {
			return $site;
		}

		return $global;
	}

	/**
	*
	*/
	static function getCacheConfig($local = true)
	{
		$local = (Environment::inDEV() || $local) ? ".local" : "";
		$siteMemcache = self::getSiteConfigPath("memcache{$local}");
		$appMemcache = self::getAppConfigPath("memcache{$local}");

		list($realPath1, $fileExists1) = \App::getRealPath($siteMemcache);
		list($realPath2, $fileExists2) = \App::getRealPath($appMemcache);

		if ($fileExists1) {
			return include_once $realPath1;
		} else if ($fileExists2) {
			return include_once $realPath2;
		}

		return [];
	}

	static function getFileVar($path, $name, $required = true)
	{
		list($realPath, $fileExists) = \App::getRealPath($path);
		if ($name == "menu") {
			$fileExists = file_exists($path);
			$realPath = $path;
		}

		if ($name == "config") {
			$local = str_replace("config", "config.local", $path);
			list($realPathx, $fileExistsx) = \App::getRealPath($local);
			if ($fileExistsx) {
				$fileExists = true;
				$realPath = $realPathx;
			}
		}

		if ($fileExists) {
			if ($name == "router") {
				include $realPath;
			} else {
				$config = include $realPath;

				if (!is_array($config)) {
					return [];
				}

				return $config;
			}
		} else {
			if ($required) {
				Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $path);
			} else {
				return [];
			}
		}
	}

	static function getLocalConfigFile($name, $var = "config", $required = true)
	{
		$local = "{$name}.local";
		$file_local = self::getAppConfigPath($local);

		list($realPath, $fileExists) = \App::getRealPath($file_local);

		$file = ($fileExists) ? $local : $name;

		return self::getFileVar(self::getAppConfigPath($file), $var, $required);
	}

	/**
	*	Environments config
	*/
	static function getEnvConfigFile()
	{
		return self::getLocalConfigFile("environments","environments");
	}

	/**
	*	App config
	*/
	static function getAppConfigFile($name = "config", $required = true)
	{
		return self::getLocalConfigFile($name);
	}

	/**
	*	Providers config
	*/
	static function getAliasesConfigFile()
	{
		return self::getLocalConfigFile("aliases");
	}

	/**
	*	Site config
	*/
	static function getSiteByNameConfigFile($site, $required = true)
	{
		$name = "config";
		return self::getFileVar(self::getSiteConfigPath($name, $site), $name, $required);
	}

	static function getSiteConfigFile($name = "config", $required = true)
	{
		return self::getFileVar(self::getSiteConfigPath($name), $name, $required);
	}

	static function getSiteModuleConfigFile($site, $module)
	{
		return self::getFileVar(self::getSiteModuleConfigPath($site, $module), "config", false);
	}

	static function getRouterFile()
	{
		self::getFileVar(self::getSiteConfigPath("router"), "router", true);
	}

	static function getModulesFile()
	{
		return self::getSiteConfigFile("modules");
	}

	static function getAssetsFile()
	{
		return self::getSiteConfigFile("assets");
	}

	static function getMenuFile($file)
	{
		return self::getFileVar(self::getMenuPath($file), "menu", false);
	}

	/**
	*	Global configs
	*/
	static function getGlobalConfigFile($name = "config")
	{
		return self::getFileVar(self::getGlobalConfigPath($name), $name, false);
	}

	static function getPluginsFile()
	{
		return self::getLocalConfigFile("plugins", "plugins", false);
	}

	static function getEventsFile()
	{
		return self::getLocalConfigFile("events", "events", false);
	}

	/**
	*	Page configs
	*/
	static function getModuleConfigFile($class, $required = true)
	{
		return self::getFileVar(self::getPageConfigPath($class), "config", $required);
	}

	/**
	*	Database configs
	*/
	static function getDbSiteConfigFile($name, $required = true)
	{
		return self::getFileVar(self::getSiteConfigPath($name), "database", $required);
	}

	static function getDbAppConfigFile($name, $required = true)
	{
		return self::getFileVar(self::getAppConfigPath($name), "database", $required);
	}

	/**
	*	Load file
	*/
	static function loadFile($path, $file)
	{
		if (empty($path) || empty($file)) return false;

		$resource = $path.$file;

		list($realPath, $fileExists) = \App::getRealPath($resource);

		if ($fileExists) {
			include_once $realPath;
		} else {
			Error::debug("File Not Found", __LINE__, __FILE__, $resource);
		}
	}

	static function loadConfig($name)
	{
		self::loadFile(DIR_CORE_CONFIG, $name . FILE_INC);
	}

	static function loadAppLanguages($iso = "")
	{
		$lang = (empty($iso)) ? Language::get() : $iso;
		self::loadFile(DIR_APP_LANGUAGES, $lang . FILE_INC);
	}

	static function callMethod($class, $method, $obj, $path, $params)
	{

		$underscore = (Helper::regexp('/^_/', $method));

		if (in_array($method, ['_lang','_email'])) {
			$underscore = false;
		}

		if (method_exists($class, $method) && !$underscore) {
			call_user_func_array(array($obj,$method), $params);
		} else {
			$error = ($underscore) ? "Methods with \"<b style=\"color:#e69d97\">underscore</b>\" is not allowed." : null;
			Error::methodNotFound(TEXT_METHOD_NOT_FOUND, __LINE__, __FILE__, $path['fileName'], $class, $method, $obj->getParentClassName(), $error);
		}
	}

	static private function _getPageObj($path, $page, $action, $params, $urlParam)
	{

		$method = Helper::getCamelName($action, false);
		$className = $path.$page;

		if (
			(Helper::isService($page) && preg_match('/^core/', $path)) ||
			(Helper::isApi($page) && preg_match('/^core/', $path))
		) {
			$className = "Roducks\\{$page}";
		}

		$class = self::getClassNamespace($className);

		$filePath = preg_replace(Helper::REGEXP_PATH, '$1', $path . $page);
		$filePath = Helper::pageByFactory($filePath);
		$fileName = preg_replace(Helper::REGEXP_PATH, '$2', $page);

		$pageObj = [
				'className' 	=> $class,
				'method' 		=> $method,
				'path' 			=> $path,
				'params' 		=> $params,
				'filePath'		=> $filePath,
				'fileName' 		=> $filePath . $fileName . FILE_EXT,
				'urlParam'		=> $urlParam
		];

		return [$method, $page, $className, $class, $filePath, $pageObj];

	}

	static function loadPage($path, $page, $action, array $urlParam = [], array $params = [], $return = false, array $url = [])
	{

		$browserUrl = URL::getParams();
		$loadCoreClass = false;
		$autoload = true;
		$isBlock = false;

		$corePath = (Helper::isDispatch($browserUrl[0])) ? preg_replace('/^app\/Sites\/[a-zA-Z]+\/Modules\/(.+)$/', '$1', $path) : '';
		$corePathAll =  DIR_MODULES . self::ALL_SITES_DIRECTORY . DIRECTORY_SEPARATOR . $corePath;
		$coreFileAll = DIR_CORE ."{$corePathAll}{$page}".FILE_EXT;
		$corePathSite =  DIR_MODULES . RDKS_SITE . DIRECTORY_SEPARATOR . $corePath;
		$coreFileSite = DIR_CORE ."{$corePathSite}{$page}".FILE_EXT;

		list($method, $page, $className, $class, $filePath, $pageObj) = self::_getPageObj($path, $page, $action, $params, $urlParam);

		if (!\App::fileExists($pageObj['fileName']) && !Helper::isBlock($path) && $browserUrl[0] != '_block') {
			if (\App::fileExists($coreFileSite)) {
				$path = self::NS . $corePathSite;
				$loadCoreClass = true;
			} else if (\App::fileExists($coreFileAll)) {
				$path = self::NS . $corePathAll;
				$loadCoreClass = true;
			}

			if ($loadCoreClass) {
				list($method, $page, $className, $class, $filePath, $pageObj) = self::_getPageObj($path, $page, $action, $params, $urlParam);

				$pageObj['filePath'] = str_replace(self::NS, DIR_CORE, $pageObj['filePath']);
				$pageObj['fileName'] = str_replace(self::NS, DIR_CORE, $pageObj['fileName']);
				$pageObj['path'] = str_replace(self::NS, DIR_CORE, $pageObj['path']);

				$filePath = $pageObj['filePath'];
			}

		}

		// ONLY for Pages and Blocks pass assets and view instance
		if (Helper::isPage($class) || Helper::isBlock($class) || Helper::isFactory($class)) {

			// Asset Instance
			$asset = new Asset;

			// View Instance
			$view = new View($asset, $filePath, $url);

			if (Helper::isPage($class) || Helper::isFactory($class)) {

				$assetsMap = [];
				$assetsMap['js'] = "JS";
				$assetsMap['css'] = "CSS";
				$assetsMap['plugins'] = "PLUGINS";
				$assetsMap['scriptsInline'] = "SCRIPTS_INLINE";
				$assetsMap['scriptsOnReady'] = "SCRIPTS_ONREADY";

				$view->page(1);
				$view->meta('http-equiv','Content-Type','text/html; charset=utf-8');

				// Load assets into the document html
				$assetsFile = self::getAssetsFile();

				foreach ($assetsMap as $key => $value) {
					if (isset($assetsFile[$value])) {
						if ($key == "plugins") {
							$view->assets->$key($assetsFile[$value], false);
						} else {
							$view->assets->$key($assetsFile[$value]);
						}
					}
				}
			}

			if (Helper::isBlock($class)) {

				$path = $pageObj['fileName'];
			    $class = Helper::getBlockClassName($class);
			    $isBlock = true;
			    list($realPath, $fileExists) = \App::getRealPath($path);

			    if ($fileExists) {

					include_once $realPath;

					if (!class_exists($class)) {
						$autoload = false;
					}
			    } else {
			    	$autoload = false;
			    	Error::debug(TEXT_FILE_NOT_FOUND,__LINE__, __FILE__, $path);
			    }

			}

			if ($autoload) {
				// Call Page|JSON|Block and pass View
				$obj = new $class($pageObj, $view);

				if ($isBlock) {
					$obj->setVars($urlParam);
				}
			}

		} else {

			// Call API|Service|Event|XML
			$obj = new $class($pageObj);

			if (Helper::isApi($page) && isset($params['jwt'])) {
				unset($params['jwt']);
				$obj->verifyToken();
			}

		    if (Helper::isService($page)) {
				if (Helper::regexp('/^get/', $method)) {
					if (method_exists($class, $method)) {
						$obj->_disableServiceUrl($method);
					}
		   		}
		    }

		}

		if (!Helper::isFactory($class)) {
			if (!$return) {
				if ($autoload)
					self::callMethod($class, $method, $obj, $pageObj, $params);
			} else {
				return $obj;
			}
		}

	}

	static function loadService($page)
	{

		$page = Helper::getClassName($page);
		$page = DIR_SERVICES . Helper::getCamelName($page);

		$servicePath = self::getServicesPath($page);
		$pagePath = $servicePath['path'];

		return self::loadPage($pagePath, $page, "", array(),array(), true);
	}

	static function dispatchEvent($e, $settings)
	{
		if (!is_array($settings)) {
			$settings = [$settings];
		}

		$events = self::getEventsFile();

		if (isset($events[$e])) {
			$dispatch = $events[$e];

			if (Helper::regexp('#::#', $dispatch)) {
				list($page,$method) = explode("::", $dispatch);

				$path = self::getEventsPath();
				$class = self::getClassNamespace($path) . $page;
				$file = $path . $page . FILE_EXT;

				if (Path::exists($file)) {
					include_once Path::get($file);
				}

				if (class_exists($class)) {
					self::loadPage($path, $page, $method, array(), $settings);
				}
			}
		}
	}

	static function CLI($arguments)
	{
		$params = [];
		$values = [];
		$flags = [];

		if (is_array($arguments) && count($arguments) > 0) :
		unset($arguments[0]);

		    foreach ($arguments as $arg) :

		    	if($arg == '--') :
		    		continue;
		    	endif;

		    	if (in_array($arg, ['--dev','--pro'])) :

					if (!isset($flags['--dev'])) :
						$flags[$arg] = 1;
					endif;

					continue;

		    	endif;

		    	if (preg_match('#=#', $arg)) :
		        	list($k, $v) = explode("=",$arg);
		       		$values[$k] = $v;
		       	else:
		       		if (preg_match('/^--/', $arg)) :
						$flags[$arg] = 1;
		       		else :
						$params[] = $arg;
		       			$values[$arg] = 1;
		       		endif;
		       	endif;
		    endforeach;
		endif;

		if (!isset($flags['--dev']) && !isset($flags['--pro'])) :
			$flags['--pro'] = 1;
		endif;

		/*
		|--------------------------------|
		|		 LOAD LANGUAGE			 |
		|--------------------------------|
		*/
		self::loadAppLanguages('en');

		/*
		|--------------------------------|
		|		 CHECK ENVIRONMENT  	 |
		|--------------------------------|
		*/

		$db_name = "database";
		$dev = isset($flags['--dev']);
		$db = (isset($values['db'])) ? $values['db'] : $db_name;

		if ($dev) {
			$db_name = $db . '.local';
		}

		if (isset($flags['--debug'])) {
			$dev = true;
		}

		$environment = [
			'errors' => $dev,
			'subdomain' => self::DEFAULT_SUBDOMAIN,
			'site' => "Front",
			'mode' => Environment::CLI,
			'database' => $db_name
		];

		/*
		|--------------------------------|
		|						RUN SCRIPT
		|--------------------------------|
		*/
		require "./core/Framework/Run" . FILE_EXT;

		/*
		|--------------------------------|
		|		    SYSTEM REQUIREMENT
		|--------------------------------|
		*/
		self::requirements();

		if (isset($params[0])) {

			$method = "run";
			$name = $params[0];

			if (preg_match('#:#', $name)) {
				list($name, $method) = explode(":", $name);
				$method = Helper::getCamelName($method, false);
			}

			$cls = $name;
			$name = Helper::getCamelName($name);

			$script = "Roducks\\CLI\\{$name}";
			$appScript = "App\\CLI\\{$name}";

			if (Path::exists("app/CLI/{$name}".FILE_EXT)) {
				$script = $appScript;
			} else if (!Path::exists("core/CLI/{$name}".FILE_EXT)) {
				CLI::printError("Unknown command: {$cls}:{$method}", CLI::FAILURE);
			}

			$class = new $script($flags, $values);
			$class->inLocal($dev);
			if (method_exists($class, $method)) {
				$values = Helper::getCliParams($values);
				call_user_func_array(array($class,$method), $values);
			} else {
				CLI::println("Unknown command: {$cls}:{$method}", CLI::FAILURE);
			}

		} else {
			CLI::println("Please set a command", CLI::FAILURE);
		}

	}

	static function getEnvironment($appConfig)
	{

		$domain_name = (isset($appConfig['domain_name'])) ? $appConfig['domain_name'] : '';
		$environment = Environment::getConfig();
		$environment['domain_name'] = $domain_name;
		$environment['missing_domain_name'] = false;
		$environment['debugger'] = false;

		if (!isset($environment['domain_name']) || empty($environment['domain_name'])) {

			$environment['missing_domain_name'] = true;

		    if (\App::fileExists(self::getAppConfigPath('config.local'))) {
		    	$environment['mode'] = Environment::DEV;
		    	$environment['errors'] = true;
		    	$environment['debugger'] = true;
		    } else {
		    	$environment['mode'] = Environment::PRO;
		    	$environment['site'] = 'Front';
		    }

		}

		return $environment;

	}

	static function checkApp($config)
	{
		if ($config['missing_domain_name']) {
			if ($config['debugger']) {
				Error::missingDbConfig("Undefined Domain Name", __LINE__, __FILE__, self::getAppConfigPath('config.local'), 'domain_name', '');
			} else {
				Error::pageNotFound();
			}
		}
	}

}
