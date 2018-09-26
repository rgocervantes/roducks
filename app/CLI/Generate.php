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

class {$block} extends Block
{
  public function output()
  {

  }
}
EOT;

    File::create($path, "{$block}.php", $file);

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

  private function _create($site, $module)
  {
    switch ($this->_type) {
      case 'module':
        $this->_module($site, $module);
        break;
      case 'block':
        $this->_block($site, $module);
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
        $this->error("Please enter a integer value.");
        parent::output();
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

}
