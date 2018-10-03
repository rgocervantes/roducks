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

namespace Roducks\Page;

use Roducks\Framework\Core;
use Roducks\Framework\Asset;
use Roducks\Framework\Helper;
use Roducks\Framework\Error;
use Roducks\Framework\Login;
use Roducks\Framework\Language;
use Roducks\Framework\Path;
use Roducks\Libs\Data\Session;
use Roducks\Libs\Output\Html;

final class View
{

	const DEFAULT_TEMPLATE = 'default';

	/* ------------------------------------*/
	/* 		VARS
	/* ------------------------------------*/
	private $_template = "";
	private $_layout = "";
	private $_view = "view";
	private $_body = "";
	private $_meta = "";
	private $_data = [];
	private $_globals = [];
	private $_tpl = [];
	private $_url = [];
	private $_error = false;
	private $_filePath;
	private $_parentPage;
	private $_blocks = ['top' => true, 'bottom' => true];

	/* ------------------------------------*/
	/* 		VIEW GLOBAL VARS
	/* ------------------------------------*/
	private $_TITLE;
	private $_CSS = [];
	private $_JS = [];
	private $_SCRIPTS = [];

	/* ------------------------------------*/
	/* 		Public Asset instance
	/* ------------------------------------*/
	public $assets;

	private function _urlData()
	{
		if (count($this->_url) > 0) {

			if (!empty($this->_url['tpl']))
				$this->load($this->_url['tpl']);

			if (!empty($this->_url['template']))
				$this->template($this->_url['template']);

			if (!empty($this->_layout))
				$this->_layout = $this->_url['layout'];
		}
	}

	private function _htmlTag($name, array $arr)
	{
		$attrs = Html::getAttributes($arr);
		return "<{$name} {$attrs} />\n";
	}

	private function _setGlobals($key, $value = "")
	{
		$this->_globals[$key] = $value;
	}

	private function _getGlobals()
	{
		return $this->_globals;
	}

	/* ------------------------------------*/
	/* 		PUBLIC METHODS
	/* ------------------------------------*/
	public function __construct(Asset $assets, $filePath, $url)
	{

		$this->_filePath = $filePath;

		$this->assets = $assets;
		$this->_template = self::DEFAULT_TEMPLATE;
		$this->_url = $url;
		$idUrl = (isset($this->_url['id_url'])) ? $this->_url['id_url'] : 0;

		if (!Helper::isBlock($filePath)) {
			$this->_setGlobals('_TITLE', PAGE_TITLE);
			$this->data('_PAGE_TITLE', PAGE_TITLE);
			$this->data('_VIEW_TITLE', 'title');
		}

		$this->_setGlobals('_PAGE_ID', $idUrl);
		$this->_setGlobals('_LANG', Language::get());
	}

	public function data($key, $value = "")
	{
		if (is_array($key)) {
			$this->_data = array_merge($this->_data, $key);
		} else {
			$this->_data[$key] = $value;
		}

	}

	public function tpl($key, $value = "")
	{
		$this->_tpl[$key] = $value;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function getUrlData()
	{
		return $this->_url;
	}

	public function page($n)
	{
		$this->data('_PAGED', $n);
	}

	public function parentPage($page)
	{
		$this->_parentPage = $page;
	}

	public function meta($attr, $name, $content)
	{
		$this->_meta .= $this->_htmlTag("meta", [$attr => $name, 'content' => $content]);
	}

	public function htmlTag($name, array $arr)
	{
		$this->_meta .= $this->_htmlTag($name, $arr);
	}

	public function template($template = null, $top = true, $bottom = true)
	{
		if (!is_null($template)) {
			$this->_template = $template;
		}

		$this->_blocks['top'] = $top;
		$this->_blocks['bottom'] = $bottom;
	}

	public function layout($layout = null, array $mapping = [])
	{
		if (!is_null($layout)) {
			$this->_layout = $layout;
			Layout::$data = $mapping;
		}
	}

	public function title($str, $overwrite = false, $tpl = null)
	{
		$title = $str;

		if (!$overwrite) {
			$title = $str . " - " . PAGE_TITLE;
		}

		$this->data('_PAGE_TITLE', $str);
		$this->data('_TITLE', $title);

		if (!is_null($tpl)) {
			$this->data('_VIEW_TITLE', $tpl);
		}
	}

	public function load($name)
	{
		$this->_view = Helper::ext($name,'phtml');
	}

	public function body()
	{
		$this->_body = "body";
	}

	public function setView($name)
	{
		$ret = [];
		if (is_array($name)) {
			foreach ($name as $key => $value) {
				$ret[] = ['VIEW',$value];
			}
		} else {
			return ['VIEW',$name];
		}

	}

	public function setTemplate($name, array $data = [], $merge = false)
	{
		$ret = [];
		if (is_array($name)) {
			foreach ($name as $key => $value) {
				$ret[] = ['TEMPLATE',$value, $data, $merge];
			}
		} else {
			return ['TEMPLATE',$name, $data, $merge];
		}
	}

	public function setError()
	{
		$this->_error = true;
	}

	public function error($visibility = "", $method = "", $alert = "An error ocurred in this method")
	{

		if (Helper::regexp('#app#', $this->_filePath)) {

			$page = preg_replace('/^.+\/modules\/([a-zA-Z]+)\/page\/$/', '$1', $this->_filePath);
			$file = str_replace("/", "", Helper::getClassName($this->_filePath, '$2'));
			$class = ($file == 'page') ? $page : $file;
			$filePath = $this->_filePath . $class . FILE_EXT;
			$extend = "\\" . $this->_parentPage;

			if (Helper::regexp('#::#', $method)) {
				list($cls, $mt) = explode("::", $method);
				$method = "rdks/{$this->_filePath}{$class}::{$mt}";
			}

		} else {

			$filePath = Helper::getClassName($this->_parentPage, '$1');
			$class = Helper::getClassName($this->_parentPage);
			$filePath = $filePath . "/" . $class . FILE_EXT;
			$extend = '\Roducks\Page\Block';

		}

		Error::view("View Error", __LINE__, __FILE__ , $filePath, $visibility, $extend, $method, $alert);
		return false;
	}

	public function output($header_footer = true, $scripts = true)
	{

		if ($this->_error && empty($this->_body)) {
			return false;
		}

		$this->_urlData();

		$dir_templates = Core::getTemplatesPath($this->_template);
		$dir_layouts = Core::getLayoutsPath($this->_layout);
		$dir_view = Core::getViewsPath($this->_parentPage, $this->_filePath, $this->_view);

		// If it's a block header & footer are not required
		if (Helper::isBlock($this->_filePath)) {
			$header_footer = false;
		} else {
			Layout::$path = $dir_view;
		}

		// Make sure 404 template exists if else throw a fatal error because we don't have any other template to show.
		if ($this->_template == "404" && !file_exists($dir_templates)) {
			Error::fatal("404 Folder Not Found", __LINE__, __FILE__, $dir_templates);
		}

		// Make sure layouts exists in case it is required to be shown.
		if (!file_exists($dir_layouts) && !empty($this->_layout)) {
			Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $dir_layouts);
		}

		$this->data("tpl", $this->_tpl);

		// Get Stylesheets & javascripts
		$this->_setGlobals('_CSS', $this->assets->getCss());
		$this->_setGlobals('_JS', $this->assets->getJs());

		// Get meta tags
		$this->_setGlobals('_META', $this->_meta);

		// Favicon
		$this->_setGlobals('_FAVICON', $this->_htmlTag('link',['rel' => "shortcut icon", 'type' => "image/png", 'href' => Path::getIcon("favicon.png")]));

		$data = array_merge($this->getData(), $this->_getGlobals());

		// Get data passed from page
		extract($data);

		// Include Header
		if ($header_footer) {
			$header = $dir_templates . "header" . FILE_PHTML;
			$header_alt = $dir_templates . "header" . FILE_TPL;
			if (file_exists($header)) {
				include $header;
				$top = $dir_templates . "top" . FILE_PHTML;

				if (file_exists($top) && $this->_blocks['top']) {
					include $top;
				}

				if (Session::exists(Login::SESSION_SECURITY)) {
					Error::security();
					Login::security(false);
				}
			} else if(file_exists($header_alt)) {

				echo Duckling::parser($header_alt, $this->_getGlobals());

				$top = $dir_templates . "top" . FILE_TPL;

				if (file_exists($top) && $this->_blocks['top']) {
					$topData = $this->_getGlobals();
					unset($topData['_CSS']);
					unset($topData['_JS']);
					unset($topData['_META']);
					unset($topData['_FAVICON']);

					echo Duckling::parser($top, $topData);
				}

				if (Session::exists(Login::SESSION_SECURITY)) {
					Error::security();
					Login::security(false);
				}

			} else {
				Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $header);
			}
		}

		// Set template data
		if (!Helper::isBlock($this->_filePath)) {
			Template::$data = array_merge(Template::$data, $this->getData());
		}

		Template::$path = $dir_templates;

		// Load layout if exists
		if (file_exists($dir_layouts)) {
			include $dir_layouts;
		} else if (file_exists($dir_view) && !empty($this->_view)) {

			if (Helper::isPage($dir_view)) {
				Layout::view(null, $this->_error);
			} else {
				if (preg_match('/\.tpl$/', $dir_view)) {
					echo Duckling::parser($dir_view, $data);
				} else {
					include $dir_view;
				}
			}
		}

		// Load body *ONLY* for 404 templates
		if (!empty($this->_body)) {
			//Core::loadFile($dir_templates,$this->_body.FILE_PHTML);
			$body = $dir_templates.$this->_body.FILE_PHTML;
			$body_alt = $dir_templates.$this->_body.FILE_TPL;

			if (file_exists($body)) {
				include $body;
			} else if(file_exists($body_alt)) {
				echo Duckling::parser($body_alt, []);
			} else {
				Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $body);
			}
		}

		// Include Bottom & Footer
		if ($header_footer) {

				$bottom = $dir_templates . "bottom" . FILE_PHTML;
				$bottom_alt = $dir_templates . "bottom" . FILE_TPL;
				$footer = $dir_templates . "footer" . FILE_PHTML;
				$footer_alt = $dir_templates . "footer" . FILE_TPL;

				if (file_exists($bottom) && $this->_blocks['bottom']) {
					include $bottom;
				} else if(file_exists($bottom_alt) && $this->_blocks['bottom']) {
					echo Duckling::parser($bottom_alt, ['_JS' => $this->assets->getJs()]);
				}

				if ($scripts) {
					echo "<script type=\"text/javascript\">\n";
					Asset::includeInLine($this->assets->getScriptsInline(), $this->getData());
					Asset::includeOnReady($this->assets->getScriptsOnReady(), $this->getData());
					echo "</script>\n";
				}

				if (file_exists($footer)) {
					include $footer;
				} else if(file_exists($footer_alt)) {
					echo Duckling::parser($footer_alt, []);
				} else {
					Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $footer);
				}

		}

		return true;
	}

}
