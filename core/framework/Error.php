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

namespace rdks\core\framework;

use rdks\core\libs\Utils\Date;
use rdks\core\libs\Protocol\Http;

if(!defined('RDKS_ERRORS')){
	define('RDKS_ERRORS', false);
}

class Error{

	static function log($message, $file = ""){
		$code = (!empty($file)) ? 3 : 0;
		error_log(Date::getCurrentDateTime() . " - " . $message);
	}

	static function on(){
		error_reporting(E_ALL);
		//error_reporting(E_ALL && !E_NOTICE);
		ini_set("display_errors", 1);
	}

	static function off(){
		error_reporting(0);
		ini_set("display_errors", 0);
	}	

	static function display(){
		if(RDKS_ERRORS){
			self::on();
		} else {
			self::off();
		}
	}

	static function pageNotFound(){
		$data = array();
		Core::loadPage(DIR_CORE_PAGE, "Page", "pageNotFound", $data, $data);
		exit;
	}

	static function block($title, $line, $path, $file, $error = ""){

		$markup = '<div style="font-family:Arial; text-align:left; padding:10px; background:#FCC; border:solid 2px #C00; margin-bottom:5px;">';
		$markup .= '<h1 style="margin:0">'. $title .'</h1>'; 
		$markup .= '<div style="font-family:monospace;font-size:16px;"><b>File: </b>' . $file . '</div><br>'; 
		
		if(!empty($error)){
			$markup .= '<h2 style="margin:0">Error Message:</h2>'; 
			$markup .= '<div style="background:#fff; font-family:monospace; font-size:16px; padding:10px; color: #333; margin: 10px 0; border:solid 1px #999;">'; 	
			$markup .= $error;
			$markup .= '</div>'; 				
		}		
		$markup .= '<div style="font-family:monospace;font-size:16px;"><b>Line: </b>' . $line . '</div>'; 
		$markup .= '<div style="font-family:monospace;font-size:16px;"><b>Executed in: </b>' . $path . '</div>'; 
		$markup .= '</div>';
		
		return $markup;
	}

	static function _throw($title, $line, $path, $file, $error = ""){
		$markup = '<html><head><title>Roducks Debugger</title></head><body style="background: #f5f5f5;">';
		$markup .= '<div style="text-align:center; margin-top: 40px;">';		
			$markup .= '<div style="font-family:monospace;"><img src="data:image/png;base64,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" /><h1>{ Debugger }</h1></div>';
			$markup .= '<div style="display:inline-block;">';
			$markup .= self::block($title, $line, $path, $file, $error);
			$markup .= '<div style="font-family:Arial; text-align: left; "><a href="javascript: window.history.back();" style="color: #444;">&larr; Back</a></div>';
			$markup .= '</div>';
		$markup .= '</div>';
		$markup .= '</body></html>';

		die($markup);
	}

	static function debug($title, $line, $path, $file, $error = ""){

		if(RDKS_ERRORS){
			if(Helper::isBlock($file) && !Helper::isBlockDispatched()){
				echo self::block($title, $line, $path, $file, $error);
			} else {
				$params = URL::getGETParams();
				
				if(isset($params['rdks']) && $params['rdks'] == 1){
					echo self::block($title, $line, $path, $file, $error);
				} else {
					self::_throw($title, $line, $path, $file, $error);
				}
			}
		}else{
			if(!Helper::isBlock($file) && !Helper::isBlockDispatched()){
				if(!Environment::inCLI())
					self::pageNotFound();
			}
		}
	}
	
	static function warning($title, $line, $path, $file, $error = ""){
		if(RDKS_ERRORS){	
			echo self::block($title, $line, $path, $file, $error);
		}
	}

	static function fatal($title, $line, $path, $file, $error = ""){
		if(RDKS_ERRORS){
			self::_throw($title, $line, $path, $file, $error);
		} else {
			if(!Environment::inCLI())
				Http::sendHeaderNotFound();
		}	
	}

	static function cantDispatchFactory($pagePath, $page){

		$factory = preg_replace('#/page/#', '/factory/', $pagePath).$page.FILE_EXT;

		$params = URL::getRealParams();
		$correctUrl = preg_replace('/^(\/_)page(.*)/', '$1factory$2', URL::getRelativeURL());

		$error = "This <b>module</b> has <span style=\"color: #c00;\">factory</span> defined, so it can't be dispatched like <span style=\"color: green;\">/_page/</span><br><br>";
		$error .= "Correct:<br><br>";
		$error .= "<a href=\"{$correctUrl}\" style=\"color: blue; text-decoration:none;\">{$correctUrl}</a><br>";
		$error .= "^<br>";
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Click on this link</span><br><br>";

		if(isset($params[0]) && $params[0] == '_page' && file_exists($factory)){
			self::debug("Can't dispatch URL",__LINE__, __FILE__, $pagePath.$page.FILE_EXT, $error); 
		}		
	}	

	static function cantDispatchURL($title, $page, $line, $path, $file, $extend){

		$class = Helper::getClassName($page);
		$ns = Helper::getClassName($page,'$1');
		$ns = Helper::getInvertedSlash($ns);

		$error = "<span style=\"color:purple; font-weight:bold;\">namespace</span> {$ns};<br><br>";
		$error .= "<i style=\"color:blue; font-weight:bold;\">class</i> <b style=\"color:#C00;\">{$class}</b> <i style=\"color:green; font-weight:bold;\">extends</i> <b style=\"color:#C00;\">{$extend}</b> {<br><br>";
		$error .= "&nbsp;&nbsp;<span style=\"color:purple; font-weight:bold;\">protected</span> <span style=\"color:#f46536;\">\$_dispatchUrl</span> = <span style=\"color:blue;\">true</span>;<br>";
		$error .= "&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Make sure this property is defined.</span><br>...";			

		self::debug($title, $line, $path, $file, $error);
	}

	static function view($title, $line, $path, $file, $visibility, $extend, $call, $alert = "An error occurred in"){
		$method = Helper::getClassName($call,'$2');
		$ns = Helper::getClassName($call,'$1');
		$ns = Helper::getInvertedSlash($ns);
		$error = "";

		if(Helper::regexp('#::#', $method)){

			list($class, $method) = explode("::", $method);

			$error = "<span style=\"color:purple; font-weight:bold;\">namespace</span> {$ns};<br><br>";
			$error .= "<i style=\"color:blue; font-weight:bold;\">class</i> <b style=\"color:#C00;\">{$class}</b> <i style=\"color:green; font-weight:bold;\">extends</i> <i style=\"color:#c00; font-weight:bold;\">{$extend}</i> {<br><br>";
			$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:purple; font-weight:bold;\">{$visibility}</span> <span style=\"color:blue; font-weight:bold;\">function</span> <b style=\"color:#C00;\">$method()</b>{<br>";
			$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br>";		
			$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">{$alert}</span><br>";		
			$error .= "&nbsp;&nbsp;&nbsp;&nbsp;...<br>";
			$error .= "&nbsp;&nbsp;&nbsp;}<br>";
			$error .= "}<br>";

		}

		self::debug($title, $line, $path, $file, $error);
	}

	static function undefinedVariable($title, $page, $line, $path, $file, $param, $extend){

		$class = Helper::getClassName($page);
		$ns = Helper::getClassName($page,'$1');
		$ns = Helper::pageByFactory($ns);
		$ns = Helper::getInvertedSlash($ns);	

		$error = "<span style=\"color:purple; font-weight:bold;\">namespace</span> {$ns};<br><br>";
		$error .= "<i style=\"color:blue; font-weight:bold;\">class</i> <b style=\"color:#C00;\">{$class}</b> <i style=\"color:green; font-weight:bold;\">extends</i> <b style=\"color:#C00;\">{$extend}</b> {<br><br>";
		$error .= "&nbsp;&nbsp;<span style=\"color:purple; font-weight:bold;\">var</span> <span style=\"color:#f46536;\">\${$param};</span><br>";
		$error .= "&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Make sure this property is defined.</span><br>...";			
		
		self::debug($title, $line, $path, $file, $error);
	}	

	static function classNotFound($title, $line, $path, $file, $page){

		$class = Helper::getClassName($page);
		$ns = Helper::getClassName($page,'$1');
		$ns = Helper::getInvertedSlash($ns);		

		$error = "<span style=\"color:purple; font-weight:bold;\">namespace</span> {$ns};<br><br>";
		$error .= "<i style=\"color:blue; font-weight:bold;\">class</i> <b style=\"color:#C00;\">{$class}</b> <i style=\"color:green; font-weight:bold;\">extends</i> <i style=\"color:blue; font-weight:bold;\">...</i> {<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br>";		
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Make sure this class is defined.</span><br>";					
		$error .= " ... <br>";
		$error .= "}<br>";

		self::debug($title, $line, $path, $file, $error);
	}	

	static function methodNotFound($title, $line, $path, $file, $page, $method, $extend){

		$class = Helper::getClassName($page);
		$ns = Helper::getClassName($page,'$1');
		$ns = Helper::getInvertedSlash($ns);		

		$error = "<span style=\"color:purple; font-weight:bold;\">namespace</span> {$ns};<br><br>";
		$error .= "<i style=\"color:blue; font-weight:bold;\">class</i> <b style=\"color:#C00;\">{$class}</b> <i style=\"color:green; font-weight:bold;\">extends</i> <i style=\"color:#c00; font-weight:bold;\">{$extend}</i> {<br><br>";
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:purple; font-weight:bold;\">public</span> <span style=\"color:blue; font-weight:bold;\">function</span> <b style=\"color:#C00;\">$method()</b>{<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br>";		
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Make sure this method is defined.</span><br>";		
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;...<br>";
		$error .= "&nbsp;&nbsp;&nbsp;}<br>";
		$error .= "}<br>";

		self::debug($title, $line, $path, $file, $error);
	}		

	static function siteFolderNotFound($title, $line, $path, $file, $dir){
		
		$subdomain = RDKS_SUBDOMAIN;
		$site = RDKS_SITE;

		$error = "<span style=\"color:purple; font-weight:bold;\">use</span> rdks\core\\framework\Environment;<br><br>";

		$error .= "<span style=\"color:#f46536;\">\$config</span> <span style=\"color:#c00;\">=</span> [<br>";
		$error .= "...<br>";		
		$error .= "&nbsp;&nbsp;<span style=\"color:#3F51B5; \">'{$subdomain}'</span> <span style=\"color:#c00;\">=></span> [<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#3F51B5; \">'site'</span> <span style=\"color:#c00;\">=></span> <span style=\"color:green;\">\"{$site}\"</span>,<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Make sure this folder exists in: <span style=\"color:blue; \">{$dir}</span></span><br><br>";
		$error .= "&nbsp;&nbsp;],<br>";
		$error .= "...<br>";
		$error .= "];";

		self::debug($title, $line, $path, $file, $error);
	}

	static function moduleDisabled($title, $line, $path, $file, $module){

		$error = "<span style=\"color:#f46536;\">\$modules</span> <span style=\"color:#c00;\">=</span> [<br>";
		$error .= "...<br>";		
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#3F51B5; \">'{$module}'</span> <span style=\"color:#c00;\">=></span> <span style=\"color:blue;\">true</span><br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Make sure it is defined.</span><br><br>";
		$error .= "...<br>";
		$error .= "];<br>";

		self::debug($title, $line, $path, $file, $error);	
	}

	static function defaultPageIsMissing($title, $line, $path, $file){

		$error = "<span style=\"color:#f46536;\">\$router</span> <span style=\"color:#c00;\">=</span> [<br>";
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#3F51B5; \">'/'</span> <span style=\"color:#c00;\">=></span> [<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#c00;\">^</span><br>";
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Make sure to add a 'slash' as default page</span><br><br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#3F51B5; \">'dispatch'</span> <span style=\"color:#c00;\">=></span> <span style=\"color:purple; font-weight:bold;\">Dispatch</span><b style=\"color:#C00;\">::</b><span style=\"color:green; font-weight:bold;\">page</span>(<span style=\"color:#3F51B5; \">\"Home\"</span>,<span style=\"color:#3F51B5; \">\"index\"</span>)<br>";
		$error .= "&nbsp;&nbsp;&nbsp;],<br>";
		$error .= "...<br>";
		$error .= "];<br>";

		self::debug($title, $line, $path, $file, $error);	
	}

	static function missingDbConfig($title, $line, $path, $file, $key, $value){
		
		$error = "<span style=\"color:#f46536;\">\$database</span> <span style=\"color:#c00;\">=</span> [<br>";
		$error .= "...<br>";
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#3F51B5; \">'{$key}'</span> <span style=\"color:#c00;\">=></span> <span style=\"color:#3F51B5; \">'{$value}'</span><br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#c00; background:#eee; border:solid 1px #ccc; padding:4px;\">Make sure it is defined.</span><br><br>";
		$error .= "...<br>";		
		$error .= "];<br>";

		self::debug($title, $line, $path, $file, $error);	
	}

	static function missingParams($title, $line, $path, $file, $missingParams){
		$error = "";
		foreach ($missingParams as $key => $value) {
			$error .= "{$value}<br>";
		}
		
		self::debug($title, $line, $path, $file, $error);		
	}

	static function security(){

		$error = '<div class="alert alert-danger" role="alert">';
			$error .= '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
			$error .= '<span class="alert-text">'. TEXT_LOGGED_IN_STRANGER .'</span>';
		$error .= '</div>';

		echo $error;

	}


} 