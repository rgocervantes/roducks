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

use Roducks\Libs\Utils\Date;
use Roducks\Libs\Request\Http;

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
		Core::loadPage(DIR_CORE_PAGE, Helper::PAGE_NOT_FOUND, "pageNotFound");
		exit;
	}

	static function block($title, $line, $path, $file, $error = ""){

		$markup = '<div style="font-family:Arial; text-align:left; padding:10px; background:#076364; border:solid 2px #4d9191; margin-bottom:5px; color: #bcdee0;">';
		$markup .= '<h1 style="margin:0">'. $title .'</h1>'; 
		$markup .= '<div style="font-family:monospace;font-size:16px;"><b>File: </b>' . $file . '</div><br>'; 
		
		if(!empty($error)){
			$markup .= '<h2 style="margin:0">Error Message:</h2>'; 
			$markup .= '<div style="background:#07292b; font-family:monospace; font-size:16px; padding:10px; color: #c0dfdf; margin: 10px 0; border:solid 1px #4d9191;">'; 	
			$markup .= $error;
			$markup .= '</div>'; 				
		}		
		$markup .= '<div style="font-family:monospace;font-size:16px;"><b>Line: </b>' . $line . '</div>'; 
		$markup .= '<div style="font-family:monospace;font-size:16px;"><b>Executed in: </b>' . $path . '</div>'; 
		$markup .= '</div>';
		
		return $markup;
	}

	static function _throw($title, $line, $path, $file, $error = ""){
		$markup = '<html><head><title>Roducks Debugger</title></head><body style="background: #031415; color: #fff;">';
		$markup .= '<div style="text-align:center; margin-top: 40px;">';		
			$markup .= '<div style="font-family:monospace;">';
			$markup .= '<img src="data:image/png;base64,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" />';
			$markup .= '<h1>{ Debugger }</h1></div>';
			$markup .= '<div style="display:inline-block;">';
			$markup .= self::block($title, $line, $path, $file, $error);
			$markup .= '<div style="font-family:Arial; text-align: left; "><a href="javascript: window.history.back();" style="color: #c0dfdf;">&larr; Back</a></div>';
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
					exit;
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

		$factory = preg_replace('#/Page/#', '/Factory/', $pagePath).$page.FILE_EXT;

		$params = URL::getSplittedURL();
		$correctUrl = preg_replace('/^(\/_)page(.*)/', '$1factory$2', URL::getRelativeURL());

		$error = "This <b>module</b> has <span style=\"color: #00BCD4;\">factory</span> defined, so it can't be dispatched like <span style=\"color: #bc99e0;\">/_page/</span><br><br>";
		$error .= "Correct:<br><br>";
		$error .= "<a href=\"{$correctUrl}\" style=\"color: #bcd92e; text-decoration:none;\">{$correctUrl}</a><br>";
		$error .= "^<br>";
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Click on this link</span><br><br>";

		if(isset($params[0]) && $params[0] == '_page' && file_exists($factory)){
			self::debug("Can't dispatch URL",__LINE__, __FILE__, $pagePath.$page.FILE_EXT, $error); 
		}		
	}	

	static function cantDispatchURL($title, $page, $line, $path, $file, $extend){

		$class = Helper::getClassName($page);
		$ns = Helper::getClassName($page,'$1');
		$ns = Helper::getInvertedSlash($ns);

		$error = "<span style=\"color:#3bde9a; \">namespace</span> {$ns};<br><br>";
		$error .= "<span style=\"color:#bcd92e;\">class</span> <span style=\"color:#00BCD4;\">{$class}</span> <span style=\"color:#bc99e0;\">extends</span> <span style=\"color:#00BCD4;\">{$extend}</span> {<br><br>";
		$error .= "&nbsp;&nbsp;<span style=\"color:#3bde9a; \">protected</span> <span style=\"color:#FF9800;\">\$_dispatchUrl</span> = <span style=\"color:#bcd92e;\">true</span>;<br>";
		$error .= "&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Make sure this property is defined.</span><br>...";			

		self::debug($title, $line, $path, $file, $error);
	}

	static function view($title, $line, $path, $file, $visibility, $extend, $call, $alert = "An error occurred in"){
		$method = Helper::getClassName($call,'$2');
		$ns = Helper::getClassName($call,'$1');
		$ns = Helper::getInvertedSlash($ns);
		$error = "";

		if(Helper::regexp('#::#', $method)){

			list($class, $method) = explode("::", $method);

			$error = "<span style=\"color:#3bde9a; \">namespace</span> {$ns};<br><br>";
			$error .= "<span style=\"color:#bcd92e;\">class</span> <span style=\"color:#00BCD4;\">{$class}</span> <span style=\"color:#bc99e0;\">extends</span> <span style=\"color:#00BCD4;\">{$extend}</span> {<br><br>";
			$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#3bde9a; \">{$visibility}</span> <span style=\"color:#bcd92e; \">function</span> <span style=\"color:#00BCD4;\">$method()</span>{<br>";
			$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br>";		
			$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">{$alert}</span><br>";		
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

		$error = "<span style=\"color:#3bde9a;\">namespace</span> {$ns};<br><br>";
		$error .= "<span style=\"color:#bcd92e;\">class</span> <span style=\"color:#00BCD4;\">{$class}</span> <span style=\"color:#bc99e0;\">extends</span> <span style=\"color:#00BCD4;\">{$extend}</span> {<br><br>";
		$error .= "&nbsp;&nbsp;<span style=\"color:#3bde9a; \">var</span> <span style=\"color:#FF9800;\">\${$param};</span><br>";
		$error .= "&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Make sure this property is defined.</span><br>...";			
		
		self::debug($title, $line, $path, $file, $error);
	}

	static function classNotFound($title, $line, $path, $file, $page){

		$class = Helper::getClassName($page);
		$ns = Helper::getClassName($page,'$1');
		$ns = Helper::getInvertedSlash($ns);		

		$error = "<span style=\"color:#3bde9a;\">namespace</span> {$ns};<br><br>";
		$error .= "<span style=\"color:#bcd92e;\">class</span> <span style=\"color:#00BCD4;\">{$class}</span> <span style=\"color:#bc99e0;\">extends</span> <span style=\"color:#bcd92e;\">...</span> {<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br>";		
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Make sure this class is defined.</span><br>";					
		$error .= " ... <br>";
		$error .= "}<br>";

		self::debug($title, $line, $path, $file, $error);
	}	

	static function methodNotFound($title, $line, $path, $file, $page, $method, $extend, $alert = "Make sure this method is defined."){

		$class = Helper::getClassName($page);
		$ns = Helper::getClassName($page,'$1');
		$ns = Helper::getInvertedSlash($ns);		

		$error = "<span style=\"color:#3bde9a;\">namespace</span> {$ns};<br><br>";
		$error .= "<span style=\"color:#bcd92e;\">class</span> <span style=\"color:#00BCD4;\">{$class}</span> <span style=\"color:#bc99e0;\">extends</span> <span style=\"color:#00BCD4;\">{$extend}</span> {<br><br>";
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#3bde9a; \">public</span> <span style=\"color:#bcd92e; \">function</span> <span style=\"color:#00BCD4;\">$method()</span>{<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br>";		
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">{$alert}</span><br>";		
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;...<br>";
		$error .= "&nbsp;&nbsp;&nbsp;}<br>";
		$error .= "}<br>";

		self::debug($title, $line, $path, $file, $error);
	}		

	static function siteFolderNotFound($title, $line, $path, $file, $dir){
		
		$subdomain = RDKS_SUBDOMAIN;
		$site = RDKS_SITE;

		$error = "<span style=\"color:#3bde9a; \">use</span> Roducks\Framework\Environment;<br><br>";

		$error .= "<span style=\"color:#FF9800;\">return</span> [<br>";
		$error .= "...<br>";		
		$error .= "&nbsp;&nbsp;<span style=\"color:#e2b75b; \">'{$subdomain}'</span> <span style=\"color:#00BCD4;\">=></span> [<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#e2b75b; \">'site'</span> <span style=\"color:#00BCD4;\">=></span> <span style=\"color:#bc99e0;\">\"{$site}\"</span>,<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Make sure this folder exists in: <span style=\"color:#bcd92e; \">{$dir}</span></span><br><br>";
		$error .= "&nbsp;&nbsp;],<br>";
		$error .= "...<br>";
		$error .= "];";

		self::debug($title, $line, $path, $file, $error);
	}

	static function moduleDisabled($title, $line, $path, $file, $module){

		$error = "<span style=\"color:#FF9800;\">return</span> [<br>";
		$error .= "...<br>";		
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#e2b75b; \">'{$module}'</span> <span style=\"color:#00BCD4;\">=></span> <span style=\"color:#bcd92e;\">true</span><br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Make sure it is defined.</span><br><br>";
		$error .= "...<br>";
		$error .= "];<br>";

		self::debug($title, $line, $path, $file, $error);	
	}

	static function defaultPageIsMissing($title, $line, $path, $file){

		$error = "<span style=\"color:#3bde9a; \">Router</span><span style=\"color:#00BCD4;\">::</span><span style=\"color:#bc99e0; \">init</span><span style=\"color:#00BCD4;\">(</span><span style=\"color:#FF9800;\">function</span> <span style=\"color:#00BCD4;\">()</span> {<br>";
		$error .= "...<br>";
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#3bde9a; \">Router</span><span style=\"color:#00BCD4;\">::</span><span style=\"color:#bc99e0; \">get</span>(<span style=\"color:#e2b75b; \">\"/\"</span><span style=\"color:#00BCD4;\">,</span> <span style=\"color:#3bde9a; \">Dispatch</span><span style=\"color:#00BCD4;\">::</span><span style=\"color:#bc99e0; \">page</span>(<span style=\"color:#e2b75b; \">\"home\"</span>,<span style=\"color:#e2b75b; \">\"index\"</span>));<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#00BCD4;\">^</span><br>";
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Make sure to add a 'slash' as default page.</span><br><br>";
		$error .= "...<br>";
		$error .= "});<br>";

		self::debug($title, $line, $path, $file, $error);	
	}

	static function missionDispatchIndex($url, $title, $line, $path, $file){

		$error = "<span style=\"color:#FF9800;\">return</span> [<br>";
		$error .= "...<br>";
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#e2b75b; \">'{$url}'</span> <span style=\"color:#00BCD4;\">=></span> [<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#e2b75b; \">'dispatch'</span> <span style=\"color:#00BCD4;\">=></span> <span style=\"color:#3bde9a; \">Dispatch</span><span style=\"color:#00BCD4;\">::</span><span style=\"color:#bc99e0; \">page</span>(<span style=\"color:#e2b75b; \">\"home\"</span>,<span style=\"color:#e2b75b; \">\"index\"</span>)<br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"color:#00BCD4;\">^</span><br>";
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Make sure this index is defined.</span><br><br>";
		$error .= "&nbsp;&nbsp;&nbsp;],<br>";
		$error .= "...<br>";
		$error .= "];<br>";

		self::debug($title, $line, $path, $file, $error);	
	}

	static function missingDbConfig($title, $line, $path, $file, $key, $value){
		
		$error = "<span style=\"color:#FF9800;\">return</span> [<br>";
		$error .= "...<br>";
		$error .= "&nbsp;&nbsp;&nbsp;<span style=\"color:#e2b75b; \">'{$key}'</span> <span style=\"color:#00BCD4;\">=></span> <span style=\"color:#e2b75b; \">'{$value}'</span><br>";
		$error .= "&nbsp;&nbsp;&nbsp;&nbsp;^<br>";
		$error .= "<span style=\"color:#fff4d1; background:#0d4547; border:solid 1px #1b6364; padding:4px;\">Make sure it is defined.</span><br><br>";
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