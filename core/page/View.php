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

namespace rdks\core\page;

use rdks\core\framework\Core;
use rdks\core\framework\Asset;
use rdks\core\framework\Helper;
use rdks\core\framework\Error;
use rdks\core\framework\Login;
use rdks\core\page\Template;
use rdks\core\page\Layout;
use rdks\core\libs\Data\Session;
use rdks\core\libs\Output\Html;

final class View{

	const DEFAULT_TEMPLATE = 'default';
	
	/* ------------------------------------*/
	/* 		VARS
	/* ------------------------------------*/
	protected $_template = "";
	protected $_layout = "";
	protected $_view = "";
	protected $_body = "";		
	protected $_meta = "";
	protected $_data = [];
	protected $_url = [];
	protected $_error = false;
	protected $_filePath;
	protected $_parentPage;

	/* ------------------------------------*/
	/* 		VIEW GLOBAL VARS
	/* ------------------------------------*/		
	protected $_TITLE;
	protected $_CSS = [];
	protected $_JS = [];
	protected $_SCRIPTS = [];
	
	/* ------------------------------------*/
	/* 		Public Asset instance
	/* ------------------------------------*/	
	public $assets;

	private function _urlData(){
		if(count($this->_url) > 0){
			$this->title($this->_url['title']);
			$this->load($this->_url['pview']);
			$this->template($this->_url['template']);
			if(!empty($this->_layout))
				$this->_layout = $this->_url['layout'];
		}
	}

	/* ------------------------------------*/
	/* 		PUBLIC METHODS
	/* ------------------------------------*/	
	public function __construct(Asset $assets, $filePath, $url){
		
		$this->_filePath = $filePath;

		$this->assets = $assets;
		$this->_template = self::DEFAULT_TEMPLATE;
		$this->_url = $url;
		$idUrl = (isset($this->_url['id_url'])) ? $this->_url['id_url'] : 0;

		if(!Helper::isBlock($filePath)){
			$this->data('_TITLE', PAGE_TITLE);
			$this->data('_PAGE_TITLE', PAGE_TITLE);
			$this->data('_VIEW_TITLE', "title");
		}
		
		$this->data('_URL_ID', $idUrl);
	}

	public function data($key, $value = ""){
		if(is_array($key)){
			$this->_data = array_merge($this->_data, $key);
		}else{
			$this->_data[$key] = $value;
		}	
		
	}

	public function getData(){
		return $this->_data;
	}

	public function getUrl(){
		return $this->_url;
	}

	public function page($n){
		$this->data('_PAGED', $n);
	}

	public function parentPage($page){
		$this->_parentPage = $page;
	}

	public function meta($attr, $name, $content){
		$this->_meta .= "<meta $attr=\"$name\" content=\"$content\" />\n";
	}

	public function htmlTag($name, array $arr){
		$attrs = Html::getAttributes($arr);
		$this->_meta .= "<$name $attrs />\n";
	}

	public function template($template = null){
		if(!is_null($template)){
			$this->_template = $template;
		}
	}

	public function layout($layout = null, array $mapping = []){
		if(!is_null($layout)){
			$this->_layout = $layout;
			Layout::$data = $mapping;
		}
	}	

	public function viewTitle($str){
		$this->data('_VIEW_TITLE', $str);
	}

	public function title($str, $overwrite = false){
		$title = $str;
		
		if(!$overwrite){
			$title = PAGE_TITLE . " - " . $str;
		}

		$this->data('_PAGE_TITLE', $str);
		$this->data('_TITLE', $title);
	}

	public function load($name){
		$this->_view = Helper::ext($name,'phtml');
	}

	public function body(){
		$this->_body = "body";
	}

	public function setView($name){
		$ret = [];
		if(is_array($name)){
			foreach ($name as $key => $value) {
				$ret[] = ['VIEW',$value];
			}
		} else {
			return ['VIEW',$name];
		}
		
	}

	public function setTemplate($name){
		$ret = [];
		if(is_array($name)){
			foreach ($name as $key => $value) {
				$ret[] = ['TEMPLATE',$value];
			}
		} else {
			return ['TEMPLATE',$name];
		}
	}

	public function error($visibility, $method = "", $alert = "An error ocurred in this method"){

		if(Helper::regexp('#app#', $this->_filePath)){

			$page = preg_replace('/^.+\/modules\/([a-zA-Z]+)\/page\/$/', '$1', $this->_filePath);
			$file = str_replace("/", "", Helper::getClassName($this->_filePath, '$2'));
			$class = ($file == 'page') ? $page : $file;
			$filePath = $this->_filePath . $class . FILE_EXT;
			$extend = "\\" . $this->_parentPage;	

			if(Helper::regexp('#::#', $method)){
				list($cls, $mt) = explode("::", $method);
				$method = "rdks/{$this->_filePath}{$class}::{$mt}";
			}

		} else {
		
			$filePath = Helper::getClassName($this->_parentPage, '$1');
			$filePath = str_replace("rdks/","", $filePath);
			$class = Helper::getClassName($this->_parentPage);
			$filePath = $filePath . "/" . $class . FILE_EXT;
			$extend = '\rdks\core\page\Block';
		
		}

		Error::view("View Error", __LINE__, __FILE__ , $filePath, $visibility, $extend, $method, $alert);
		return false;
	}

	public function output($header_footer = true, $scripts = true){

		$this->_urlData();

		$dir_templates = Core::getTemplatesPath($this->_template);
		$dir_layouts = Core::getLayoutsPath($this->_layout);
		$dir_view = Core::getViewsPath($this->_parentPage, $this->_filePath, $this->_view);

		// If it's a block header & footer are not required
		if(Helper::isBlock($this->_filePath)) {
			$header_footer = false;
		} else {
			Layout::$path = $dir_view;
		}

		// Make sure 404 template exists if else throw a fatal error because we don't have any other template to show.
		if($this->_template == "404" && !file_exists($dir_templates)){
			Error::fatal("404 Folder Not Found", __LINE__, __FILE__, $dir_templates);
		}

		// Make sure layouts exists in case it is required to be shown.
		if(!file_exists($dir_layouts) && !empty($this->_layout)){
			Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $dir_layouts);
		}

		// Get Stylesheets & javascripts
		$this->data('_CSS', $this->assets->getCss());
		$this->data('_JS', $this->assets->getJs());

		// Get meta tags
		$this->data('_META', $this->_meta);

		// Get data passed from page
		extract($this->_data);
		
		// Include Header
		if($header_footer){
			$header = $dir_templates . "header" . FILE_TPL;
			if(file_exists($header)){
				include $header;

				$top = $dir_templates . "top" . FILE_TPL;
				
				if(file_exists($top)){
					include $top;
				}

				if(Session::exists(Login::SESSION_SECURITY)){
					Error::security();
					Login::security(false);
				} 

			}else{
				Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $header);
			}
		}

		// Set template data
		if(!Helper::isBlock($this->_filePath)) {
			Template::$data = array_merge(Template::$data,$this->_data);
		}
		Template::$path = $dir_templates;

		// Load layout if exists
		if(file_exists($dir_layouts)){
			include $dir_layouts;
		} else if(file_exists($dir_view) && !empty($this->_view)) {
			include $dir_view;
		}

		// Load body *ONLY* for 404 templates
		if(!empty($this->_body)){
			Core::loadFile($dir_templates,$this->_body.FILE_TPL);
		}

		// Include Bottom & Footer
		if($header_footer){

				$bottom = $dir_templates . "bottom" . FILE_TPL;
				$footer = $dir_templates . "footer" . FILE_TPL;
				
				if(file_exists($bottom)){
					include $bottom;
				}

				if($scripts){
					echo "\n\n<script type=\"text/javascript\">\n\n";
					Asset::includeInLine($this->assets->getScriptsInline(),$this->_data);
					Asset::includeOnReady($this->assets->getScriptsOnReady(),$this->_data);
					echo "</script>\n";
				}

				if(file_exists($footer)){
					include $footer;
				}else{
					Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $footer);
				}

		} 

		return true;
	}

}
