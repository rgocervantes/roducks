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

use Roducks\Page\View;
use Roducks\Page\Block;
use Roducks\Page\Service;
use Lib\File;

abstract class Render
{

  private static function _invokeMethod($class, $method, $obj, $path, $params)
  {

    $underscore = (Helper::regexp('/^_/', $method));

    if (in_array($method, ['_lang','_email'])) {
      $underscore = false;
    }

    if (method_exists($class, $method) && !$underscore) {
      call_user_func_array(array($obj, $method), $params);
    } else {
      $error = ($underscore) ? "Methods with \"<b style=\"color:#e69d97\">underscore</b>\" is not allowed." : null;
      Error::methodNotFound(TEXT_METHOD_NOT_FOUND, __LINE__, __FILE__, $path, $class, $method, $obj->getParentClassName(), $error);
    }
    
  }

  public static function service($name, array $settings)
  {
    $path = Path::getFile($name);
    $class = Helper::getClassName($name);
    return self::view($path, $class, NULL, array(), $settings, TRUE);
  }

	static function observer($e, $settings)
	{
		if (!is_array($settings)) {
			$settings = [$settings];
		}

    $observers = Config::getObservers()['data'];

		if (isset($observers[$e])) {
			$dispatch = $observers[$e];

			if (Helper::regexp('#::#', $dispatch)) {
				list($class, $method) = explode("::", $dispatch);

        $path = Path::getObserver($class);
        
				if (file_exists($path)) {
          include_once $path;
        }

        $cpath = Path::clean($path);
        $class = Path::getClassName($cpath);

				if (class_exists($class)) {
					self::view($path, $class, $method, array(), $settings);
				}
			}
		}
	}

  public static function view($path, $className, $method, array $queryString = [], array $params = [], $return = false, array $url = [])
  {

    $autoload = false;
    $isBlock = false;
    $cpath = Path::clean($path);

    if (File::exists($path)) {

      include_once $path;

      $class = Path::getClassName($cpath);

      if (class_exists($class)) {
        $autoload = true;
      }
    } else {
      Error::debug(TEXT_FILE_NOT_FOUND,__LINE__, __FILE__, $cpath);
    }

    if ($autoload) {
      $pageObj = [
        'class' => $className,
        'className' 	=> $class,
        'method' 		=> $method,
        'path' 			=> $path,
        'params' 		=> $params,
        'filePath'		=> $cpath,
        'fileName' 		=> $cpath,
        'urlParam'		=> $queryString,
        'url_dispatcher' => true
      ];

      if (Helper::isService($cpath) || Helper::isObserver($cpath)) {
				$pageObj['url_dispatcher'] = false;
			}

      if (Helper::isPage($cpath) || Helper::isBlock($cpath)) {

        // Asset Instance
        $asset = new Asset;
    
        // View Instance
        $view = new View($asset, $pageObj, $url);

        if (Helper::isPage($cpath)) {

          $assetsMap = [];
          $assetsMap['js'] = "js";
          $assetsMap['css'] = "css";
          $assetsMap['plugins'] = "plugins";
          $assetsMap['js.inline'] = "jsInline";
          $assetsMap['js.onready'] = "jsOnReady";

          // Load assets into the document html
          $assetsFile = Config::getAssets()['data'];

          foreach ($assetsMap as $key => $value) {
            if (isset($assetsFile[$key])) {
              $overwrite = ($key != 'plugins');
              $view->assets->$value($assetsFile[$key], $overwrite);
            }
          }

        }

        $obj = new $class($pageObj, $view);
      } else {
        $obj = new $class($pageObj);
      }

      if ($obj instanceof Block) {
        $obj->setVars($queryString);
      }

			if (Helper::isApi($cpath) && isset($params['jwt'])) {
				unset($params['jwt']);
				$obj->verifyToken();
			}

      if ($obj instanceof Service) {
        if (
          Helper::regexp('/^get/', $method) && 
          method_exists($class, $method) && 
          $return === FALSE
        ) {
					$obj->_disableServiceUrl($method);
        }

        if ($method == 'output') {
          $method = 'rest';
        }

      }

      if ($return) {
        return $obj;
      } else {
        self::_invokeMethod($class, $method, $obj, $cpath, $params);
      }

    }

  }
}