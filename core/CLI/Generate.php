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
 *	-----------------
 *	COMMAND LINE
 *	-----------------
 *	php roducks generate:module
 *	php roducks generate:module [SITE]
 *	php roducks generate:module [SITE] [MODULE]
 *	php roducks generate:module-xml
 *	php roducks generate:module-xml [SITE]
 *	php roducks generate:module-xml [SITE] [XML]
 *	php roducks generate:block
 *	php roducks generate:block [SITE]
 *	php roducks generate:block [SITE] [BLOCK]
 *  php roducks generate:service
 *  php roducks generate:service [SITE]
 *  php roducks generate:service [SITE] [SERVICE]
 *  php roducks generate:api
 *  php roducks generate:api [SITE]
 *  php roducks generate:api [SITE] [API]
 *  php roducks generate:cli
 *  php roducks generate:cli [NAME]
 *  php roducks generate:setup
 *  php roducks generate:setup [NAME]
 *  php roducks generate:model
 *  php roducks generate:model [FOLDER]
 *  php roducks generate:model [FOLDER] [MODEL]
 *  php roducks generate:model-xml
 *  php roducks generate:model-xml [FOLDER]
 *  php roducks generate:model-xml [FOLDER] [MODEL]
 *  php roducks generate:model-factory
 *  php roducks generate:model-factory [FOLDER]
 *  php roducks generate:model-factory [FOLDER] [MODEL]
 *  php roducks generate:join
 *  php roducks generate:join [FOLDER]
 *  php roducks generate:join [FOLDER] [MODEL]
 *  php roducks version
 */

namespace Roducks\CLI;

use Roducks\Framework\CLI;
use Lib\Directory as DirectoryHandler;
use Lib\File;
use Path;
use Helper;
use Request;

class Generate extends CLI
{
  private $_sitesFolder = "app/Sites/",
          $_type,
          $_title = true;

  static private function _getFolderName($folder)
  {
    return str_replace("/", "", $folder);
  }

  static private function _make($path)
  {
    DirectoryHandler::make(Path::get(), $path);
  }

  private function _requiredValue($message)
  {
    $this->error("{$message} must not be blank.");
    parent::output();
    exit;
  }

  private function _create($path, $name, $content, $type)
  {
    File::create($path, "{$name}.php", $content);

    if ($this->_title) {
      $this->success("{$type} '{$name}' was created:");
      $this->success("[x]");
    }

    $this->success("[x]{$path}{$name}.php");

  }

  private function _config($path)
  {

$content = <<< EOT
<?php

return [
];
EOT;

    File::create($path, "modules.inc", $content);
  }

  private function _file($path, $name, $content, $type = "")
  {
    $ext = $name.".php";
    $file = $path.$ext;

    if (Path::exists($file)) {
      $this->warning("This file already exists:");
      $this->warning("[x]");
      $this->warning("[x]{$file}");
      parent::output();
      $this->promptYN("Do you want to overwrite it?");

      if ($this->yes()) {
        $this->_create($path, $name, $content, $type);
      }
    } else {
      $this->_create($path, $name, $content, $type);
    }

  }

  private function _fileModule($path, $site, $module, $type, $page, $method)
  {
    $folder = ($page == 'HelperPage') ? 'Helper' : $page;
    $ns = Helper::getInvertedSlash("App/Sites/{$site}Modules/{$module}/{$folder}");

    $use = Helper::getInvertedSlash("Roducks/Page/{$page}");
    $uses = '';
    $construct = '';
    $function = '';
    $implements = '';
    $param = '';
    $var = '';
    $ret = '';

    if ($site == 'Admin/' && $type == 'JSON') {
$var = <<< EOT
protected \$_authentication = true;

EOT;
    }

    if ($type == 'JSON' || $type == 'XML') {
$var .= <<< EOT
protected \$_dispatchUrl = true;

EOT;
    }

    if (in_array($type, ['Page','JSON','XML','Factory'])) {
      $implements = " implements {$type}Interface";
      $parent = 'parent::__construct($settings);';

      if ($type == 'JSON') {
$ret = <<< EOT

    parent::output();
EOT;

      }

      if ($type == 'XML') {
$ret = <<< EOT

    \$this->output();
EOT;

      }

      if ($type == 'Page' || $type == 'Factory') {

        $param = ', View $view';

        if ($type == 'Page') {

$ret = <<< EOT
\$this->view->load('index');

    return \$this->view->output();
EOT;

          $parent = 'parent::__construct($settings, $view);';

        }

$uses .= <<< EOT
use Roducks\\Page\\View;

EOT;

        if ($type == 'Factory') {
$parent = <<< EOT
\$page = new {$module}Page(\$settings, \$view);
    parent::__construct(\$settings, \$page);
EOT;

$uses .= 'use ' . Helper::getInvertedSlash("App/Sites/{$site}Modules/{$module}/Page/{$module} as {$module}Page;");
$uses .= "\n";
        }

      }

$construct = <<< EOT

  public function __construct(array \$settings{$param})
  {
    {$parent}
  }

EOT;

    }

    if ($type != 'HelperPage') {
$uses .= <<< EOT
use Roducks\\Interfaces\\{$type}Interface;

EOT;
    }

    if ($type != 'Factory') {
$function = <<< EOT

  public function {$method}()
  {
    {$ret}
  }
EOT;
    }

    $file = <<< EOT
<?php

namespace {$ns};

use {$use};
{$uses}
class {$module} extends {$page}{$implements}
{
  {$var}{$construct}{$function}
}
EOT;

    $this->_file($path, $module, $file, 'Module');

  }

  private function _fileBlock($path, $site, $block)
  {
    $ns = Helper::getInvertedSlash("App/Sites/{$site}Blocks/{$block}");
    $use = Helper::getInvertedSlash("Roducks/Page/Block");
    $uses = '';
    $construct = '';

$file = <<< EOT
<?php

namespace {$ns};

use {$use};
use Roducks\\Page\\View;
use Roducks\\Interfaces\\BlockInterface;

class {$block} extends Block implements BlockInterface
{
  public function __construct(array \$settings, View \$view)
  {
    parent::__construct(\$settings, \$view);
  }

  public function output()
  {
    \$this->view->load('default');

    return \$this->view->output();
  }
}
EOT;

    $this->_file($path, $block, $file, 'Block');

  }

  private function _fileService($path, $site, $service)
  {
    $ns = Helper::getInvertedSlash("App/Sites/{$site}Services");
    $use = Helper::getInvertedSlash("Roducks/Page/Service");
    $uses = '';
    $construct = '';

$file = <<< EOT
<?php

namespace {$ns};

use {$use};
use Roducks\\Interfaces\\ServiceInterface;

class {$service} extends Service implements ServiceInterface
{
  public function __construct(array \$settings)
  {
    parent::__construct(\$settings);
  }

  public function rest()
  {

    parent::output();
  }
}
EOT;

    $this->_file($path, $service, $file, 'Service');

  }

  private function _fileCli($path, $name)
  {
    $ns = Helper::getInvertedSlash("App/CLI");
    $use = Helper::getInvertedSlash("Roducks/Framework/CLI");
    $uses = '';
    $construct = '';

$file = <<< EOT
<?php

namespace {$ns};

use {$use};
use Roducks\\Interfaces\\CLIInterface;

class {$name} extends CLI implements CLIInterface
{
  public function run()
  {

    parent::output();
  }
}
EOT;

    $this->_file($path, $name, $file, 'CLI');

  }

  private function _fileApi($path, $site, $name)
  {
    $ns = Helper::getInvertedSlash("App/Sites/{$site}API");
    $use = Helper::getInvertedSlash("Roducks/Framework/API");
    $uses = '';
    $construct = '';

$file = <<< EOT
<?php

namespace {$ns};

use {$use};
use Roducks\\Interfaces\\APIInterface;

class {$name} extends API implements APIInterface
{
  /**
	 * @type GET
	 */
	public function row(\$id)
  {

    parent::output();
  }

	/**
	 * @type GET
	 */
	public function catalog(Request \$request)
  {

    parent::output();
  }

	/**
	 * @type POST
	 */
	public function store(Request \$request)
  {

    parent::output();
  }

	/**
	 * @type PUT
	 */
	public function update(Request \$request, \$id)
  {

    parent::output();
  }

	/**
	 * @type DELETE
	 */
	public function remove(Request \$request, \$id)
  {

    parent::output();
  }

}
EOT;

    $this->_file($path, $name, $file, 'API');

  }

  private function _fileSetup($path, $name)
  {
    $ns = Helper::getInvertedSlash("DB/Schema/Setup");
    $use = Helper::getInvertedSlash("Roducks/Framework/Setup");
    $uses = '';
    $construct = '';

$file = <<< EOT
<?php

namespace {$ns};

use {$use};
use Roducks\\Interfaces\\SetupInterface;
use DB;

class {$name} extends Setup implements SetupInterface
{

	public function schema(\mysqli \$db)
  {

  }

	public function data(\mysqli \$db)
  {

  }

}
EOT;

    $this->_file($path, $name, $file, 'Setup');

  }

  private function _fileModel($path, $folder, $name, $type)
  {
    $ns = Helper::getInvertedSlash("DB/Models/{$folder}");

switch ($type) {
  case 'Model':

$body = <<< EOT

  var \$id = "id";
  var \$fields = [

  ];
EOT;

    break;
  case 'Join':
$body = <<< EOT

  public function __construct(\mysqli \$mysqli)
  {
    \$this
    ->table('TABLE_1', 'a')
    ->join('TABLE_2', 'b', ['a.id' => 'b.id']);

    parent::__construct(\$mysqli);

  }
EOT;
    break;
}

$file = <<< EOT
<?php

namespace {$ns};

use $type;

class {$name} extends {$type}
{
  {$body}

}
EOT;

    $this->_file($path, $name, $file, 'Model');

  }

  private function _module($site, $module)
  {

    if (empty($module)) {
      $this->prompt("Module name:");
      $module = $this->getAnswer();
      if (empty($module)) {
        $this->_requiredValue('Module name');
      }
    }

    $module = Helper::getCamelName($module);
    $path = "{$this->_sitesFolder}{$site}Modules/{$module}/";
    $pathConfig = "{$this->_sitesFolder}{$site}Config/";
    $pathPage = "{$path}Page/";
    $pathPageViews = "{$pathPage}Views/";
    $pathHelper = "{$path}Helper/";
    $pathJSON = "{$path}JSON/";

    $siteName = self::_getFolderName($site);

    switch ($siteName) {
      case 'Front':
        $page = 'FrontPage';
        break;
      case 'Admin':
        $page = 'AdminPage';
        break;
      default:
        $page = 'Page';
        break;
    }

    $conf = "{$pathConfig}modules.inc";
    $config = Request::getContent($conf);
    if (!preg_match('#\''.$module.'\' => true,#', $config)) {
      $cnf = preg_replace_callback('/return \[(.*?)\];/sm', function($ret) use($module) {
        return "return [{$ret[1]}\t\t'{$module}' => true,\n];";
      }, $config);

      File::create($pathConfig, "modules.inc", $cnf);
    }

    self::_make($pathPageViews);
    self::_make($pathHelper);
    self::_make($pathJSON);

    File::create($pathPageViews, "index.tpl", '<h1>{{% $_PAGE_TITLE %}}</h1>');

    $this->_fileModule($pathPage, $site, $module, 'Page', $page, 'index');
    $this->_title = false;
    $this->_fileModule($pathHelper, $site, $module, 'HelperPage', 'HelperPage', 'getData');
    $this->_fileModule($pathJSON, $site, $module, 'JSON', 'JSON', 'encoded');

  }

  private function _xml($site, $module)
  {

    if (empty($module)) {
      $this->prompt("Module name:");
      $module = $this->getAnswer();
      if (empty($module)) {
        $this->_requiredValue('Module name');
      }
    }

    $module = Helper::getCamelName($module);
    $path = "{$this->_sitesFolder}{$site}Modules/{$module}/";
    $pathXml = "{$path}XML/";

    self::_make($pathXml);

    $this->_fileModule($pathXml, $site, $module, 'XML', 'XML', 'preview');

  }

  private function _factory($site, $module)
  {

    if (empty($module)) {
      $this->prompt("Module name:");
      $module = $this->getAnswer();
      if (empty($module)) {
        $this->_requiredValue('Module name');
      }
    }

    $module = Helper::getCamelName($module);
    $path = "{$this->_sitesFolder}{$site}Modules/{$module}/";
    $pathXml = "{$path}Factory/";

    self::_make($pathXml);

    $this->_fileModule($pathXml, $site, $module, 'Factory', 'Factory', 'index');

  }

  private function _block($site, $block)
  {

    if (empty($block)) {
      $this->prompt("Block name:");
      $block = $this->getAnswer();
      if (empty($block)) {
        $this->_requiredValue('Block name');
      }
    }

    $block = Helper::getCamelName($block);
    $path = "{$this->_sitesFolder}{$site}Blocks/";
    $pathBlock = "{$path}{$block}/";
    $pathBlockViews = "{$pathBlock}/Views/";

    self::_make($pathBlockViews);
    File::create($pathBlockViews, "default.tpl", '<h1>{{% $_PAGE_TITLE %}}</h1>');

    $this->_fileBlock($pathBlock, $site, $block);

  }

  private function _service($site, $service)
  {

    if (empty($service)) {
      $this->prompt("Service name:");
      $service = $this->getAnswer();
      if (empty($service)) {
        $this->_requiredValue('Service name');
      }
    }

    $service = Helper::getCamelName($service);
    $pathService = "{$this->_sitesFolder}{$site}Services/";

    self::_make($pathService);

    $this->_fileService($pathService, $site, $service);

  }

  private function _api($site, $name)
  {

    if (empty($name)) {
      $this->prompt("API name:");
      $name = $this->getAnswer();
      if (empty($name)) {
        $this->_requiredValue('API name');
      }
    }

    $name = Helper::getCamelName($name);
    $pathService = "{$this->_sitesFolder}{$site}API/";

    self::_make($pathService);

    $this->_fileApi($pathService, $site, $name);

  }

  private function _cli($name)
  {

    if (empty($name)) {
      $this->prompt("CLI name:");
      $name = $this->getAnswer();
      if (empty($name)) {
        $this->_requiredValue('CLI name');
      }
    }

    $name = Helper::getCamelName($name);
    $pathCLI = "app/CLI/";

    $this->_fileCli($pathCLI, $name);

  }

  private function _setup($name)
  {

    if (empty($name)) {
      $this->prompt("Type your name:");
      $name = $this->getAnswer();
      if (empty($name)) {
        $this->_requiredValue('Name');
      }
      $name = "Setup_".date('Y_m_d')."_{$name}";
    }

    $name = Helper::getCamelName($name);
    $pathSetup = "database/Schema/Setup/";

    $this->_fileSetup($pathSetup, $name);

  }

  private function _model($folder, $name, $type)
  {
    if (empty($folder)) {
      $this->prompt("Type folder name:");
      $folder = $this->getAnswer();
      if (empty($folder)) {
        $this->_requiredValue('Name');
      }
    }

    $folder = Helper::getCamelName($folder);
    $path = "database/Models/{$folder}/";

    self::_make($path);

    if (empty($name)) {
      $this->prompt("{$type} name:");
      $name = $this->getAnswer();
      if (empty($name)) {
        $this->_requiredValue('Name');
      }
    }

    $name = Helper::getCamelName($name);

    $this->_fileModel($path, $folder, $name, $type);
  }

  private function _run($site, $name)
  {
    switch ($this->_type) {
      case 'module':
        $this->_module($site, $name);
        break;
      case 'module:xml':
        $this->_xml($site, $name);
        break;
      case 'module:factory':
        $this->_factory($site, $name);
        break;
      case 'block':
        $this->_block($site, $name);
        break;
      case 'service':
        $this->_service($site, $name);
        break;
      case 'api':
        $this->_api($site, $name);
        break;
    }
  }

  private function _generate($site, $module)
  {

    if (empty($site)) {
      $sites = DirectoryHandler::open(Path::get($this->_sitesFolder));
      foreach ($sites['folders'] as $key => $folder) {
        $index = $key + 1;
        $this->dialogInfo("Sites");
        $this->info("[x][{$index}] " . self::_getFolderName($folder));
      }
      $this->info("[x]");
      $this->info("[x][X] Cancel");

      parent::output();

      $this->prompt("Type number:");
      $opt = $this->getAnswer();
      if (is_integer($opt)) {

        $id = $opt - 1;
        if (isset($sites['folders'][$id])) {
          $site = $sites['folders'][$id];
        } else {
          $this->error("Option {$opt} is not available, Please try again.");
          parent::output();
          exit;
        }

      } else {
        if (strtolower($opt) != 'x') {
          $this->error("Please enter a integer value.");
          parent::output();
        }
        exit;
      }

    } else {
      $site = Helper::getCamelName($site);
      $site .= "/";
    }

    $sitePath = $this->_sitesFolder.$site;
    $run = true;

    if (Path::exists($sitePath)) {
      $this->_run($site, $module);
    } else {
      $this->warning("This Site folder does not exist:");
      $this->warning("[x]");
      $this->warning("[x]{$sitePath}");
      parent::output();

      $this->promptYN("Do you want to create it?");

      if ($this->yes()) {
        $configPath = "{$sitePath}Config/";
        self::_make($configPath);
        $this->_config($configPath);
        $this->_run($site, $module);
      } else {
        $run = false;
      }
    }

    if ($run) {
      $this->warning('Run this command:');
  		$this->warning('[x]');
      $this->warning("[x]chown -R bitnami:root ".Path::get().$sitePath);
    }

    $this->_title = true;

    parent::output();
  }

  public function module($site = "", $module = "")
  {
    $this->_type = 'module';
    $this->_generate($site, $module);
  }

  public function moduleXml($site = "", $xml = "")
  {
    $this->_type = 'module:xml';
    $this->_generate($site, $xml);
  }

  public function moduleFactory($site = "", $factory = "")
  {
    $this->_type = 'module:factory';
    $this->_generate($site, $factory);
  }

  public function block($site = "", $module = "")
  {
    $this->_type = 'block';
    $this->_generate($site, $module);
  }

  public function service($site = "", $service = "")
  {
    $this->_type = 'service';
    $this->_generate($site, $service);
  }

  public function api($site = "", $service = "")
  {
    $this->_type = 'api';
    $this->_generate($site, $service);
  }

  public function cli($name = "")
  {
    $this->_cli($name);
    parent::output();
  }

  public function setup($name = "")
  {
    $this->_setup($name);
    parent::output();
  }

  public function model($folder = "", $model = "")
  {
    $this->_model($folder, $model, 'Model');
    parent::output();
  }

  public function join($folder = "", $join = "")
  {
    $this->_model($folder, $join, 'Join');
    parent::output();
  }

}
