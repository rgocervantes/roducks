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

use Symfony\Component\Yaml\Yaml;
use Roducks\Libs\Files\File;

abstract class Config
{

  /**
   * VAR
   */
  private static function _get($path, $name)
  {

    $local = "{$name}.local";
    $files = [$local, $name];
    $exts = [FILE_YML, FILE_INC];
    $exclude = ['aliases', 'router', 'duckling'];

    if (in_array($name, $exclude)) {
      unset($exts[0]);
      $exts = array_values($exts);
    }

    foreach ($files as $key => $file) {
      foreach ($exts as $ext) {
        $config = $path . $file . $ext;
        if (File::exists($config)) {
          switch ($ext) {
            case FILE_YML:
              $data = Yaml::parseFile($config);
              break;

            case FILE_INC:
   
              $data = include $config;
              break;
          }

          return [
            'full_path' => $config,
            'path' => Path::clean($config),
            'data' => $data,
          ];

        }
      }
    }

    return [
      'full_path' => $config,
      'path' => Path::clean($config),
      'data' => [],
    ];

  }

  public static function set($name, array $content, $path = NULL)
  {
    $cpath = (is_null($path)) ? Path::get(DIR_APP_CONFIG) : $path;
    File::putContent($cpath, $name . FILE_YML, Yaml::dump($content));
  }

  public static function remove($name)
  {
    File::remove(Path::get(DIR_APP_CONFIG) . $name . FILE_YML);
  }

  public static function get($name = 'app')
  {
    return self::_get(Path::get(DIR_APP_CONFIG), $name);
  }

  public static function fromSite($name = 'config', $site = NULL)
  {
    $path = Path::getAppSite($site) . DIR_CONFIG;
    return self::_get($path, $name);
  }

  public static function fromModule($module, $name = 'config', $site = NULL)
  {
    $path = Path::getAppSiteModule($module . DIRECTORY_SEPARATOR, $site) . DIR_CONFIG;
    return self::_get($path, $name);
  }

  public static function fromGlobal($config = 'config')
  {
    return self::fromSite($config, Path::SITE_ALL);
  }

  private static function _search($config)
  {

    $tree = [
      'module',
      'site',
      'global',
      'default'
    ];

    foreach ($tree as $key => $value) {
      switch ($value) {
        case 'module':

          if (!defined('RDKS_MODULE')) {
            continue;
          }

          $ret = self::fromModule(RDKS_MODULE, $config, Path::SITE_ALL);
          if (!empty($ret['data'])) {
            return $ret;
          }

          $ret = self::fromModule(RDKS_MODULE, $config);
          if (empty($ret['data'])) {
            continue;
          }

            return $ret;
          break;

        case 'site':
          $ret = self::fromSite($config);
          if (empty($ret['data'])) {
            continue;
          }

            return $ret;
          break;

        case 'global':
          $ret = self::fromGlobal($config);
          if (empty($ret['data'])) {
            continue;
          }

            return $ret;
          break;

        case 'default':
          return self::get($config);
          break;
      }
    }

    return [];
  }

  public static function getAny($config, $default)
  {
    if ($default) {
      return self::get($config);
    }

    return self::_search($config);
  }

  public static function getMenu($name, $site = NULL)
  {
    if (!is_null($site)) {
      $path = Path::getAppSite($site) . DIR_MENUS;
    } else {
      $path = Path::getMenu($name);
      $path = str_replace([FILE_YML, FILE_INC], '', $path);
      $name = '';
    }

    return self::_get($path, $name, false);

  }

  public static function getRouter()
  {
    return self::fromSite('router');
  }

  public static function getEnvs()
  {
    return self::get('environments');
  }

  /**
   * Current site's modules config
   * @return Array
   */
  public static function getSiteModules()
  {
    return self::fromSite('modules');
  }

  public static function getAssets($default = false)
  {
    return self::getAny('assets', $default);
  }

  public static function getDb($default = false)
  {
    return self::getAny('database', $default);
  }

  public static function getEvents($default = false)
  {
    return self::getAny('events', $default);
  }

  public static function getPlugins($default = false)
  {
    return self::getAny('plugins', $default);
  }

  public static function getAliases($default = false)
  {
    return self::getAny('aliases', $default);
  }

  public static function getSmtp($default = false)
  {
    return self::getAny('smtp', $default);
  }

  public static function getMemcache($default = false)
  {
    return self::getAny('memcache', $default);
  }

}
