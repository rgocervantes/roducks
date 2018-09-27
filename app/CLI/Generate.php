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
 *	php roducks generate:block
 *	php roducks generate:block [SITE]
 *	php roducks generate:block [SITE] [BLOCK]
 */

namespace App\CLI;

use Roducks\Framework\CLI;
use Lib\Directory as DirectoryHandler;
use Lib\File;
use Path;
use Helper;

class Generate extends CLI
{
  private $_sitesFolder = "app/Sites/",
          $_type;

  static private function _getFolderName($folder)
  {
    return str_replace("/", "", $folder);
  }

  private function _fileModule($path, $site, $module, $type, $page, $method)
  {

    $ns = Helper::getInvertedSlash("App/Sites/{$site}Modules/{$module}/{$type}");
    $use = Helper::getInvertedSlash("Roducks/Page/{$type}");
    $uses = '';
    $construct = '';
    $implements = '';
    $param = '';

    if (in_array($type, ['Page','JSON'])) {
      $implements = " implements {$type}Interface";
      $parent = 'parent::__construct($settings);';

      if ($type == 'Page') {
        $param = ', View $view';
        $parent = 'parent::__construct($settings, $view);';

$uses = <<< EOT
use Roducks\\Page\\View;

EOT;
      }

$construct = <<< EOT

  public function __construct(array \$settings{$param})
  {
    {$parent}
  }

EOT;

    }

    $file = <<< EOT
<?php

namespace {$ns};

use {$use};
{$uses}
class {$module} extends {$type}{$implements}
{
  {$construct}
	public function {$method}()
	{

	}
}
EOT;

    File::create($path, "{$module}.php", $file);

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

  }
}
EOT;

    File::create($path, "{$block}.php", $file);

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

  }
}
EOT;

    File::create($path, "{$service}.php", $file);

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

  }
}
EOT;

    File::create($path, "{$name}.php", $file);

  }

  private function _fileApi($path, $name)
  {
    $ns = Helper::getInvertedSlash("App/API");
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
  public function run()
  {

  }
}
EOT;

    File::create($path, "{$name}.php", $file);

  }

  private function _module($site, $module)
  {

    if (empty($module)) {
      $this->prompt("Module name:");
      $module = $this->getAnswer();
    }

    $module = Helper::getCamelName($module);
    $path = "{$this->_sitesFolder}{$site}Modules/{$module}/";
    $pathPage = "{$path}/Page/";
    $pathPageViews = "{$path}/Page/Views/";
    $pathHelper = "{$path}/Helper/";
    $pathJSON = "{$path}/JSON/";

    DirectoryHandler::make(Path::get(), $pathPage);
    DirectoryHandler::make(Path::get(), $pathPageViews);
    DirectoryHandler::make(Path::get(), $pathHelper);
    DirectoryHandler::make(Path::get(), $pathJSON);

    $this->success("Module '{$module}' was created:");
    $this->success("[x]");
    $this->success("[x]{$path}");

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

    $this->_fileModule($pathPage, $site, $module, 'Page', $page, 'index');
    $this->_fileModule($pathHelper, $site, $module, 'HelperPage', $page, 'getData');
    $this->_fileModule($pathJSON, $site, $module, 'JSON', $page, 'encoded');

  }

  private function _block($site, $block)
  {

    if (empty($block)) {
      $this->prompt("Block name:");
      $block = $this->getAnswer();
    }

    $block = Helper::getCamelName($block);
    $path = "{$this->_sitesFolder}{$site}Blocks/";
    $pathBlock = "{$path}{$block}/";
    $pathBlockViews = "{$pathBlock}/Views/";

    DirectoryHandler::make(Path::get(), $pathBlock);
    DirectoryHandler::make(Path::get(), $pathBlockViews);

    $this->success("Block '{$block}' was created:");
    $this->success("[x]");
    $this->success("[x]{$pathBlock}{$block}.php");

    $siteName = self::_getFolderName($site);

    $this->_fileBlock($pathBlock, $site, $block);

  }

  private function _service($site, $service)
  {

    if (empty($service)) {
      $this->prompt("Service name:");
      $service = $this->getAnswer();
    }

    $service = Helper::getCamelName($service);
    $pathService = "{$this->_sitesFolder}{$site}Services/";

    DirectoryHandler::make(Path::get(), $pathService);

    $this->success("Service '{$service}' was created:");
    $this->success("[x]");
    $this->success("[x]{$pathService}{$service}.php");

    $siteName = self::_getFolderName($site);

    $this->_fileService($pathService, $site, $service);

  }

  private function _api($site, $name)
  {

    if (empty($name)) {
      $this->prompt("API name:");
      $name = $this->getAnswer();
    }

    $name = Helper::getCamelName($name);
    $pathService = "{$this->_sitesFolder}{$site}API/";

    DirectoryHandler::make(Path::get(), $pathService);

    $this->success("API '{$name}' was created:");
    $this->success("[x]");
    $this->success("[x]{$pathService}{$name}.php");

    $siteName = self::_getFolderName($site);

    $this->_fileApi($pathService, $name);

  }

  private function _cli($name)
  {

    if (empty($name)) {
      $this->prompt("CLI name:");
      $name = $this->getAnswer();
    }

    $name = Helper::getCamelName($name);
    $pathCLI = "app/CLI/";

    $this->success("API '{$name}' was created:");
    $this->success("[x]");
    $this->success("[x]{$pathCLI}{$name}.php");

    parent::output();

    $this->_fileCli($pathCLI, $name);

  }

  private function _create($site, $name)
  {
    switch ($this->_type) {
      case 'module':
        $this->_module($site, $name);
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

    if (Path::exists($sitePath)) {
      $this->_create($site, $module);
    } else {
      $this->warning("This Site folder does not exist:");
      $this->warning("[x]");
      $this->warning("[x]{$sitePath}");
      parent::output();

      $this->promptYN("Do you want to create it?");

      if ($this->yes()) {
        DirectoryHandler::make(Path::get(), $sitePath);
        $this->_create($site, $module);
      }
    }

    $this->warning('Run this command:');
		$this->warning('[x]');
    $this->warning("[x]chown -R bitnami:root ".Path::get().$sitePath);

    parent::output();
  }

  public function module($site = "", $module = "")
  {
    $this->_type = 'module';
    $this->_generate($site, $module);
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
  }

}
