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
use Roducks\Framework\Environment;
use Roducks\Data\User;
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
	private $_view = "";
	private $_body = "";
	private $_meta = "";
	private $_data = [];
	private $_globals = [];
	private $_tpl = [];
	private $_url = [];
	private $_error = false;
	private $_isBlock = false;
	private $_pageObj;
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

	private static function _guide($path, $tag)
	{
		if (Environment::inDEV()) {
			echo "<!-- @{$tag}-view: {$path} -->\n";
		}
	}

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

	private function _htmlTag($name, array $attrs)
	{
		return Html::tag($name, "", $attrs, false);
	}

	private function _setGlobals($key, $value = "")
	{
		$this->_globals[$key] = $value;
	}

	private function _getGlobals()
	{
		return $this->_globals;
	}

	private function _notFound($tpl)
	{
		$error = ($this->_template == '404') ? 'fatal' : 'debug';
		Error::$error(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $tpl);
	}

	/* ------------------------------------*/
	/* 		PUBLIC METHODS
	/* ------------------------------------*/
	public function __construct(Asset $assets, array $pageObj, $url)
	{

		$this->_pageObj = $pageObj;

		$this->assets = $assets;
		$this->_template = self::DEFAULT_TEMPLATE;
		$this->_url = $url;
		$idUrl = (isset($this->_url['id_url'])) ? $this->_url['id_url'] : 0;

		if (Helper::isBlock($this->_pageObj['filePath'])) {
			$this->_isBlock = true;
		} else {
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

	public function getPageId()
	{
		return $this->_globals['_PAGE_ID'];
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
		$this->_setGlobals('_TITLE', $title);

		if (!is_null($tpl)) {
			$this->data('_VIEW_TITLE', $tpl);
		}
	}

	public function load($name)
	{
		$this->_view = $name;
	}

	public function body($tpl = 'body')
	{
		$this->_body = $tpl;
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
		Error::view("View Error", __LINE__, __FILE__ , $this->_pageObj['filePath'], $visibility, '\\' . $this->_parentPage, $method, $alert);
		return false;
	}

	public function output($header_footer = true, $scripts = true)
	{

		if ($this->_error && empty($this->_body)) {
			return false;
		}

		$this->_urlData();

		$data = $this->getData();

		$this->data("tpl", $this->_tpl);

		// Get Stylesheets & Javascripts
		$this->_setGlobals('_CSS', $this->assets->getCss());
		$this->_setGlobals('_JS', $this->assets->getJs());

		// Get Meta tags
		$this->_setGlobals('_META', $this->_meta);

		// Favicon
		$this->_setGlobals('_FAVICON', $this->_htmlTag('link', ['rel' => "shortcut icon", 'type' => "image/png", 'href' => Path::getIcon("favicon.png")]));

		if ($this->_isBlock) {
			$header_footer = false;
			$dir_view = Path::getBlockView($this->_pageObj['class'], $this->_view);
		} else {
			$dir_view = Path::getPageView($this->_pageObj['class'], $this->_view);
			$data = $this->_getGlobals() + $this->getData();
			Template::$name = $this->_template;
			Template::$data = Template::$data + $data;
		}

		extract($data);

		if ($header_footer) {

			$template_header = Path::getTemplate($this->_template, 'header');
			$template_top = Path::getTemplate($this->_template, 'top');
			$template_body = Path::getTemplate($this->_template, 'body');
			$template_bottom = Path::getTemplate($this->_template, 'bottom');
			$template_footer = Path::getTemplate($this->_template, 'footer');	

			if (file_exists($template_header)) {
				self::_guide($template_header, 'start');
				if (Helper::isTpl($template_header)) {
					echo Duckling::parser($template_header, $this->_getGlobals());
				} else {
					include $template_header;
				}
				self::_guide($template_header, 'end');
	
				if (Session::exists(User::SESSION_SECURITY)) {
					Error::security();
					User::security(false);
				}

				if (file_exists($template_top) && $this->_blocks['top']) {
					self::_guide($template_top, 'start');
					if (Helper::isTpl($template_top)) {
						$topData = $this->_getGlobals();
						unset($topData['_CSS']);
						unset($topData['_JS']);
						unset($topData['_META']);
						unset($topData['_FAVICON']);
						echo Duckling::parser($template_top, $topData);
					} else {
						include $template_top;
					}
					self::_guide($template_top, 'end');
				}
	
			} else {
				$this->_notFound($template_header);
			}
		}

		if (!empty($this->_body)) {
			$dir_view = $template_body;
		}

		self::_guide($dir_view, 'start');

		if (file_exists($dir_view)) {
			if (Helper::isTpl($dir_view)) {
				echo Duckling::parser($dir_view, $data);
			} else {
				include $dir_view;
			}
		} else {
			$this->_notFound($dir_view);
		}

		self::_guide($dir_view, 'end');

		if ($header_footer) {
			if (file_exists($template_footer)) {

				if (file_exists($template_bottom) && $this->_blocks['bottom']) {
					self::_guide($template_bottom, 'start');
					if (Helper::isTpl($template_bottom)) {
						echo Duckling::parser($template_bottom, ['_JS' => $this->assets->getJs()]);
					} else {
						include $template_bottom;
					}
					self::_guide($template_bottom, 'end');
				}
	
				if ($scripts) {
					echo "<script type=\"text/javascript\">\n";
					Asset::includeInLine($this->assets->getJsInline(), $this->getData());
					Asset::includeOnReady($this->assets->getJsOnReady(), $this->getData());
					echo "</script>\n";
				}
	
				self::_guide($template_footer, 'start');
				if (Helper::isTpl($template_footer)) {
					echo Duckling::parser($template_footer, []);
				} else {
					include $template_footer;
				}
				self::_guide($template_footer, 'end');
		
			} else {
				$this->_notFound($template_footer);
			}
		}

		return true;

	}

}
