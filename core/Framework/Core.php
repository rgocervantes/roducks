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
	const GLOBAL_DIRECTORY = '_Global';

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

	static function db()
	{
		try {
			return DB::get([DB_HOST,DB_USER,DB_PASSWORD,DB_NAME]);
		} catch (\Exception $e) {

			$file = (Environment::inDEV() || RDKS_ERRORS) ? 'database.local' : 'database';
			$config = self::getAppConfigPath($file);

			if (RDKS_ERRORS) {
				if (!Environment::inCLI()) {
					if ($e->getMessage() == 'user') {
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
		return DIR_APP_SITES . self::GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR;
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

	static function getGlobalConfigPath($file)
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
			$path = "Roducks\\";
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

	static function getViewsPath($parentPage, $path, $tpl)
	{

		if ($path == Helper::PAGE_NOT_FOUND) {
			return DIR_CORE;
		}

		$view = DIR_VIEWS . $tpl;
		$found = false;

		$coreModules = "Roducks/" . DIR_MODULES;
		$coreBlocks = DIR_CORE . DIR_BLOCKS;
		$siteBlocks = self::getSitePath() . DIR_BLOCKS;
		$globalBlocks = self::getGlobalPath() . DIR_BLOCKS;

		$parentPath = Helper::getClassName($parentPage, '$1');
		$pathCore = $parentPath . DIRECTORY_SEPARATOR;
		$file = Helper::getClassName($path);

		// Remove underscore for block name, example: app/sites/admin/blocks/_Roles/
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
			Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $path.$view);
		}

		return \App::getRealFilePath($path.$view);

	}	

	static function getTemplatesPath($tpl)
	{
		return self::getPath(DIR_TEMPLATES, $tpl);
	}	

	static function getLayoutsPath($tpl)
	{
		return self::getPath(DIR_LAYOUTS, $tpl . FILE_TPL, false);
	}

	static function getMenuPath($tpl)
	{
		return self::getPath(DIR_MENUS, $tpl . FILE_INC, false);
	}			

	static function getEmailsPath($tpl)
	{
		return self::getPath(DIR_EMAILS, $tpl . FILE_TPL, false);		
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
				return include $realPath;
			}
		} else {
			if ($required) {
				Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $path);
			} else {
				return [];
			}
		}
	}

	static function getLocalConfigFile($name, $var = "config")
	{
		$local = "{$name}.local";
		$file_local = self::getAppConfigPath($local);

		list($realPath, $fileExists) = \App::getRealPath($file_local);

		$file = ($fileExists) ? $local : $name;

		return self::getFileVar(self::getAppConfigPath($file), $var);		
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
	static function getGlobalConfigFile()
	{
		return self::getFileVar(self::getGlobalConfigPath("config"), "config", false);
	}

	static function getPluginsFile()
	{
		return self::getFileVar(self::getAppConfigPath("plugins"), "plugins", false);
	}	

	static function getEventsFile()
	{
		return self::getFileVar(self::getAppConfigPath("events"), "events", false);
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

		if (method_exists($class, $method)) {
			call_user_func_array(array($obj,$method), $params);
		} else {
			Error::methodNotFound(TEXT_METHOD_NOT_FOUND, __LINE__, __FILE__, $path['fileName'], $class, $method, $obj->getParentClassName());
		}
	}
	
	static function loadPage($path, $page, $action, array $urlParam = [], array $params = [], $return = false, array $url = [])
	{

		$autoload = true;
		$isBlock = false; 
		$method = Helper::getCamelName($action, false);
		$page = (Helper::isService($page)) ? $page : Helper::getCamelName($page);
		$className = ($path . $page);
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
				$obj->setLang(Language::get());

				if ($isBlock) {
					$obj->setVars($urlParam);
				}
			}

		} else {

			// Call Api|Service
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

}