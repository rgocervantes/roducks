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
use rdks\core\framework\Login;
use rdks\core\framework\Language;
use rdks\core\framework\URL;
use rdks\core\framework\Error;
use rdks\core\framework\Helper;
use rdks\core\framework\Path;
use rdks\core\libs\Protocol\Http;
use rdks\core\libs\Data\Session;

class Page extends GenericPage {
	
	protected $loginUrl = "/login";
	protected $_pageType = 'PAGE';
	protected $viewport = true;

/*
//---------------------------------
//	PROTECTED METHODS
//---------------------------------
*/

	protected function redirect($url){
		Http::redirect($url);
	}

	protected function hasData($bool){
		if(!$bool){
			$this->pageNotFound();
		}
	}

	protected function isData(array $data){
		if(empty($data)){
			$this->pageNotFound();
		}
	}

	protected function forbiddenRequest(){
		$this->hasData(false);
	}

	protected function accountSubscriber($url = "/"){
		if(Login::isSubscriberLoggedIn() || !ALLOW_SUBSCRIBERS_REGISTER){
			$this->redirect($url);
		}
	}	

/*
//---------------------------------
//	PUBLIC METHODS
//---------------------------------
*/
	public function __construct(array $settings, View $view){
		parent::__construct($settings);

		$this->view = $view;
		$this->view->parentPage($this->_getParentClassName());
		if($this->viewport) $this->view->meta('name','viewport',"width=device-width,initial-scale=1,shrink-to-fit=no");
	}

	public function pageNotFound(){
		$this->view->assets->scriptsOnReady(["page-404"]);
		$this->view->template("404");
		$this->view->body();
		$this->view->output();
		exit;
	}

	public function _lang($type, $lang){

		$dir_languages = Core::getLanguagesPath($lang);
		$set = true;

		if(file_exists($dir_languages)){
			
			if(Language::isMultilanguage()){
				$set = Language::set($lang);
			}
				
			if($set){

				$relativeURL = URL::getRelativeURL();

				if($relativeURL == URL::ROOT){
					$url = $relativeURL;
				}else{
					$split = explode(URL::ROOT, $relativeURL);
					$split = Helper::getUrlParams($split);
					$url = URL::ROOT . implode(URL::ROOT, $split);
				}
				
				Http::redirect($url);
			}
	
		}else{
			Error::debug(TEXT_FILE_NOT_FOUND,__LINE__, __FILE__, $dir_languages, "Make sure file exists with translations.");
		}

	}

	/**
	 *
	 */
	public function _email($type, $tpl){

		if(!Session::exists(self::SESSION_EMAIL)){
			$this->pageNotFound();
		}

		$emailPath = Core::getEmailsPath($tpl);

		if(file_exists($emailPath)):

			$store = Session::get(self::SESSION_EMAIL);
			$output = [];
			$output['form'] = (isset($store['form'])) ? $store['form'] : [];
			$output['data'] = (isset($store['data'])) ? $store['data'] : [];
			extract($output);

			Session::reset(self::SESSION_EMAIL);

			include $emailPath;

		else:	
			JSON::stOutput(['data' => ['error' => true]]);
		endif;	

	}

}