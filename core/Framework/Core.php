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
use Roducks\Libs\Files\File;

abstract class Core
{
	public static function getAliases()
	{
		return Config::getAliases()['data'];
	}

	static function extensions()
	{
		$php = [];
		$php['version_require'] = '7.0.0';
		$php['version'] = PHP_VERSION;
		$php['compare'] = (version_compare($php['version'], $php['version_require']) <= 0);
		$php['alert'] = ($php['compare']) ? "requires version {$php['version_require']} or later." : "This version looks fine to run Roducks!";
		$error = 0;
		$loaded = [];
		$list = '';

		if (function_exists('extension_loaded')) {

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
				'zip',
			];

			foreach ($exts as $ext) {
				if (!extension_loaded($ext)) {
					$error++;
					$loaded['items'] = $ext;
					$loaded['exts'][] = ['flag' => 'danger', 'type' => 'remove', 'name' => $ext];
					$list .= '<li>'.$ext.'</li>';
				} else {
					$loaded['exts'][] = ['flag' => 'success', 'type' => 'ok', 'name' => $ext];
				}
			}

		}

		return [
			'loaded' => $loaded,
			'list' => $list,
			'error' => $error,
			'php' => $php
		];

	}

	static function requirements()
	{
		$loaded = self::extensions();

		if ($loaded['php']['compare']) {
			if (Environment::inDEV()) {
				Error::requirements('PHP VERSION '.PHP_VERSION, 0, '', '', ['Roducks '.$loaded['php']['alert']], "<b>Roducks</b> {$loaded['php']['alert']}");
			} else {
				Error::page();
			}
		}

		if ($loaded['error'] > 0) {
			$message = '<ul>';
			$message .= $loaded['list'];
			$message .= '</ul>';

			if (Environment::inDEV()) {
				Error::requirements('PHP Required Extentions', 0, '', '', $loaded['loaded']['items'], $message);
			} else {
				Error::page();
			}
		}

	}

	static function getVersion()
	{
		return RDKS_VERSION;
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
			return DB::get([DB_HOST,DB_USER,DB_PASSWORD,DB_NAME,DB_PORT]);
		} catch (\Exception $e) {

			$config = DB_FILE;

			if (RDKS_ERRORS) {
				if (!Environment::inCLI()) {
					if ($e->getMessage() == 'credentials') {
						Error::debug("Invalid DB Credentails", __LINE__, __FILE__, $config, 'user is required.', '');
					} else {
						Error::fatal("MySQLi", __LINE__, __FILE__, $config, $e->getMessage());
					}
				} else {
					CLI::printError("Unconfigured file: {$config}", CLI::FAILURE);
				}

			} else {
				if (!Environment::inCLI()) {
					Error::page();
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
					Error::page();
				} else {
					CLI::printError("Missing DB Credentails", CLI::FAILURE);
				}
			}
		}
	}

	/**
	*	Load file
	*/
	public static function loadFile($path)
	{
		if (empty($path)) return;

		if (file_exists($path)) {
			include_once $path;
		} else {
			Error::debug("File Not Found", __LINE__, __FILE__, $path);
		}
	}

	public static function loadAppLanguages($iso = NULL)
	{
		$lang = (is_null($iso)) ? Language::get() : $iso;
		self::loadFile(Path::getLanguage($lang));
	}

	public static function CLI($arguments)
	{
		$params = [];
		$values = [];
		$flags = [];

		if (is_array($arguments) && count($arguments) > 0) :
		unset($arguments[0]);

		    foreach ($arguments as $arg) :

		    	if ($arg == '--' || $arg == '__version' || $arg == 'version') :
		    		continue;
					endif;
					
					if ($arg == '--version') {
						$arg = '__version';
					}

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
		|		 			LOAD LANGUAGE
		|--------------------------------|
		*/
		self::loadAppLanguages('en');

		/*
		|--------------------------------|
		|		 		CHECK ENVIRONMENT
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
			'subdomain' => Path::DEFAULT_SUBDOMAIN,
			'site' => Path::SITE_ALL,
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

			if ($name == '-v' || $name == '__version') {
				$name = 'version';
			}

			if (preg_match('#:#', $name)) {
				list($name, $method) = explode(":", $name);
				$method = Helper::getCamelName($method, false);
			}

			$cls = $name;
			$name = Helper::getCamelName($name);
			$cmd = ($method == 'run') ? $cls : "{$cls}:{$method}";

			$script = "Roducks\\CLI\\{$name}";
			$appScript = "App\\CLI\\{$name}";

			if (Path::exists("app/CLI/{$name}".FILE_EXT)) {
				$script = $appScript;
			} else if (!Path::exists("core/CLI/{$name}".FILE_EXT)) {
				CLI::printError("Unknown command: {$cmd}", CLI::FAILURE);
			}

			$class = new $script($flags, $values);

			if (method_exists($class, $method)) {
				$values = Helper::getCliParams($values);
				if ($method == 'run') {
					$values = array_merge([$cls], $values);
				}
				call_user_func_array(array($class, $method), $values);
			} else {
				CLI::println("Unknown command: {$cmd}", CLI::FAILURE);
			}

		} else {
			CLI::println("Please set a command", CLI::FAILURE);
		}

	}

	static function getEnvironment($appConfig)
	{

		$domain_name = (isset($appConfig['domain.name'])) ? $appConfig['domain.name'] : '';
		$environment = Environment::getConfig();
		$environment['domain_name'] = $domain_name;
		$environment['missing_domain_name'] = (empty($environment['domain_name']));

		return $environment;

	}

	static function requireConfig(array $config)
	{
		if (!file_exists($config['full_path'])) {
			Error::debug(TEXT_FILE_NOT_FOUND,__LINE__, __FILE__, $config['path']);
		}
	}

	static function checkApp($config)
	{
		if ($config['missing_domain_name']) {
			if ($config['errors']) {
				Error::missingDbConfig("Undefined Domain Name", __LINE__, __FILE__, DB_FILE, 'domain.name', '');
			} else {
				Error::page();
			}
		}
	}

	static function install()
	{

		$key = 'Install';

		if ( Helper::onInstall() ) {
			File::remove(Path::getAppConfig('config.local' . FILE_INC));
			File::remove(Path::getAppConfig('database.local' . FILE_INC));
			Render::view(Path::getCoreModulePageAll($key), Path::setModulePage($key), 'run');
			exit;
		}
	}

}
