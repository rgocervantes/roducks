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

use Lib\File;

abstract class Path
{

	const RDKS_NS = 'Roducks/';
	const APP_NS = 'App/';
	const SITE_ALL = 'All';
	const DEFAULT_SUBDOMAIN = 'www';
	const ADMIN_SUBDOMAIN = 'admin';

	static function get($path = "")
	{
		return \App::getRealFilePath($path);
	}

	static function exists($file)
	{
		return file_exists(self::get($file));
	}

	static function getAppConfig($path = "")
	{
		return self::get(DIR_APP_CONFIG) . $path;
	}

	static function getData($path = "")
	{
		return self::get(DIR_APP_DATA . $path);
	}

	static function getStorage($path = "")
	{
		return self::getData("storage/{$path}");
	}

	static function getTmp($path = "")
	{
		return self::getData("tmp/{$path}");
	}

	static function getRoles($path = "")
	{
		return self::getData(DIR_ROLES . $path);
	}

	static function getCropName($src, $s)
	{

		if (!Helper::regexp('/\.\w{3,4}$/', $src)) {
			return self::getIcon('unavailable.jpg');
		}

		$img = explode(".", $src);
		$size = count($img);

		if ($size > 2) :
			$res = $size - 1;
			$ext = $img[ $res ];
			unset($img[ $res ]);
			$name = implode(".", $img);
		else:
			$name = $img[0];
			$ext = $img[1];
		endif;

		return $name . '_' . $s . '.' . $ext;
	}

	static function getUploads($file = "")
	{
		return DIR_DATA_UPLOADS . $file;
	}

	static function getUploaded($file = "")
	{
		return DIR_DATA_UPLOADED . $file;
	}

	static function getUploadsImages($file = "")
	{
		return DIR_DATA_UPLOADS_IMAGES . $file;
	}

	static function getUploadedImages($file = "")
	{
		return DIR_DATA_UPLOADED_IMAGES . $file;
	}

	static function getUploadsCsv($file = "")
	{
		return DIR_DATA_UPLOADS_CSV . $file;
	}

	static function getUploadedCsv($file = "")
	{
		return DIR_DATA_UPLOADED_CSV . $file;
	}

	static function getUploadsPdf($file = "")
	{
		return DIR_DATA_UPLOADS_PDF . $file;
	}

	static function getUploadedPdf($file = "")
	{
		return DIR_DATA_UPLOADED_PDF . $file;
	}

	static function getUploadsZip($file = "")
	{
		return DIR_DATA_UPLOADS_ZIP . $file;
	}

	static function getUploadedZip($file = "")
	{
		return DIR_DATA_UPLOADED_ZIP . $file;
	}

	static function getUploadsJson($file = "")
	{
		return DIR_DATA_UPLOADS_JSON . $file;
	}

	static function getUploadedJson($file = "")
	{
		return DIR_DATA_UPLOADED_JSON . $file;
	}

	static function getUploadsXml($file = "")
	{
		return DIR_DATA_UPLOADS_XML . $file;
	}

	static function getUploadedXml($file = "")
	{
		return DIR_DATA_UPLOADED_XML . $file;
	}

	/**
	*
	*/
	static function getUploadsUsers($file = "")
	{
		return DIR_DATA_UPLOADS_USERS . $file;
	}

	static function getUploadedUsers($file = "")
	{
		return DIR_DATA_UPLOADED_USERS . $file;
	}

	/**
	*
	*/
	static function getAppUploadedImage($file)
	{
		return [DIR_DATA_UPLOADS_IMAGES, DIR_DATA_UPLOADED_IMAGES, $file];
	}

	static function getAppImage($file)
	{
		return [DIR_APP_IMAGES, DIR_ASSETS_IMAGES, $file];
	}

	static function getAppIcon($file)
	{
		return [DIR_APP_ICONS, DIR_ASSETS_ICONS, $file];
	}

	static function getImage($file = "")
	{
		return DIR_ASSETS_IMAGES . $file;
	}

	static function getIcon($file = "")
	{
		return DIR_ASSETS_ICONS . $file;
	}

	static function getLogo()
	{
		return self::getImage(LOGO_IMAGE);
	}

	static function getImageAbsolute($file)
	{
		return URL::setAbsoluteURL(self::getImage($file));
	}

	static function getPublicUploadedUsers($file, $crop = 0, $absolute = true)
	{
		$path = self::getUploadedUsers($file);

		if (preg_match('#(male|female)#', $file)) {
			$path = self::getIcon("users/{$file}");
		} else {
			if ($crop > 0) {
				$path = self::getCropName($path, $crop);
			}
		}

		if ($absolute) {
			return URL::setAbsoluteURL($path);
		}

		return $path;
	}

	public static function clean($path)
	{
		return str_replace(RDKS_ROOT, '', $path);
	}

	public static function getCurrentSite()
	{
		return RDKS_SITE;
	}

	public static function isSiteAll()
	{
		return self::getCurrentSite() == self::SITE_ALL;
	}

  /**
   * DEFAULT
   */
  private static function _getDefaultSite($site)
  {
		// Avoid PHP Warnings.
		if (!defined('RDKS_SITE')) {
			return false;
		}

    return (is_null($site)) ? self::getCurrentSite() : $site;
  }

	public static function getLanguage($lang)
	{
		return self::get(DIR_APP_LANGUAGES) . $lang . FILE_INC;
	}

  /**
   * CORE
   */
  public static function getCore($dir, $folder)
  {
    return self::get(DIR_CORE) . $dir . $folder;
  }

  /**
   * APP
   */
  public static function getAppSite($site = NULL)
  {
		$site = self::_getDefaultSite($site);
    $path = DIR_APP . DIR_SITES . $site . DIRECTORY_SEPARATOR;
    return self::get($path);
	}

  public static function getAppAllSite()
  {
    return self::getAppSite(self::SITE_ALL);
	}

  public static function getAppSiteFolder($dir, $folder, $site = NULL)
  {
    return self::getAppSite($site) . $dir . $folder;
	}

	public static function getAppSiteModule($module, $site = NULL)
  {
		return self::getAppSiteFolder(DIR_MODULES, $module, $site);
	}

  public static function getAny($dir, $file, array $exts, $debug = false)
  {
    $paths = [];
    $ret = [];

    $paths[] = self::getAppSiteFolder($dir, $file);
    $paths[] = self::getAppSiteFolder($dir, $file, self::SITE_ALL);

		$paths[] = self::getCore($dir, self::getCurrentSite() . DIRECTORY_SEPARATOR . $file);
		$paths[] = self::getCore($dir, self::SITE_ALL . DIRECTORY_SEPARATOR . $file);

		if (!in_array($dir, [DIR_MODULES, DIR_MENUS])) {
			$paths[] = self::getCore($dir, $file);
		}

    foreach ($paths as $path) {
      foreach ($exts as $ext) {
        $f = $path . $ext;
        $ret[] = $f;
        if (!File::exists($f)) {
          continue;
        }
  
        return $f;
      }

		}

		if ($debug) {
			return $ret;
		}

    return end($ret);
  }

	public static function getModule($name, $subfolder = '')
	{
		return Path::getAny(DIR_MODULES . $subfolder, $name, [FILE_EXT]);
	}

	public static function setModule($name, $type)
	{
		return implode('/', [$name, $type, $name]);
	}

	public static function setModulePage($name)
	{
		return self::setModule($name, 'Page');
	}

	public static function getModulePage($name, $subfolder = '')
	{
		return self::getModule(self::setModulePage($name), $subfolder);
	}

	public static function getModuleJson($name, $subfolder = '')
	{
		return self::getModule(self::setModule($name, 'JSON'), $subfolder);
	}

	public static function getPageView($module, $name)
	{
		return Path::getAny(DIR_MODULES, $module . DIRECTORY_SEPARATOR .  DIR_PAGE . DIR_VIEWS . $name, [FILE_PHTML, FILE_TPL]);
	}

	public static function getCorePage()
	{
		return self::get(DIR_CORE . 'Page/Page' . FILE_EXT);
	}

	public static function getCoreModulePageAll($name)
	{
		return self::getModulePage($name, self::SITE_ALL . DIRECTORY_SEPARATOR);
	}

  /**
   * GET ANY PATH
   */
	public static function getMenu($name)
	{
		return Path::getAny(DIR_MENUS, $name, [FILE_YML, FILE_INC]);
	}

  public static function getObserver($name)
  {

		if (defined('RDKS_MODULE')) {
			$alt = Path::getAny(DIR_MODULES, RDKS_MODULE . DIRECTORY_SEPARATOR .  DIR_OBSERVERS . $name, [FILE_EXT]);
			if (file_exists($alt)) {
				return $alt;
			}
		}

    return self::getAny(DIR_OBSERVERS, $name, [FILE_EXT]);
	}

  public static function getBlock($name)
  {
    $file = "{$name}/{$name}";

    return self::getAny(DIR_BLOCKS, $file, [FILE_EXT]);
  }

  public static function getBlockView($name, $view)
  {
    $file = $name . DIRECTORY_SEPARATOR . DIR_VIEWS . $view;

    return self::getAny(DIR_BLOCKS, $file, [FILE_TPL, FILE_PHTML]);
  }

  public static function getTemplate($folder, $name)
  {
    $file = "{$folder}/{$name}";

    return self::getAny(DIR_TEMPLATES, $file, [FILE_TPL, FILE_PHTML]);
  }

  public static function getLayout($folderOrFile)
  {
    return self::getAny(DIR_LAYOUTS, $folderOrFile, [FILE_TPL, FILE_PHTML]);
	}

  public static function getEmail($name)
  {
    return self::getAny(DIR_EMAILS, $name, [FILE_TPL, FILE_PHTML]);
	}

  public static function getAPI($name)
  {
    return self::getAny(DIR_API, $name, [FILE_EXT]);
	}

	public static function getService($name)
  {
    return self::getAny(DIR_SERVICES, $name, [FILE_EXT]);
	}

	public static function getClassName($cpath)
	{
		$class = str_replace(FILE_EXT, '', $cpath);

		if (preg_match('/^app\//', $class)) {
			$class = str_replace(DIR_APP, self::APP_NS, $class);
		} else if(preg_match('/^core\//', $class)) {
			$class = str_replace(DIR_CORE, self::RDKS_NS, $class);
		}

		$class = str_replace("/", "\\", $class);
		
		return $class;
	}

	public static function getFile($class)
	{
		$path = str_replace('\\', '/', $class);

		if (preg_match('/^App\//', $path)) {
			$path = str_replace(self::APP_NS, DIR_APP, $path);
		} else if(preg_match('/^Roducks\//', $path)) {
			$path = str_replace(self::RDKS_NS, DIR_CORE, $path);
		}

		return self::get($path . FILE_EXT);
	}

}
