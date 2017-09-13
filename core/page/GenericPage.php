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

use rdks\core\framework\Helper;
use rdks\core\framework\URL;
use rdks\core\framework\Post;
use rdks\core\libs\Protocol\Http;
use rdks\core\libs\Data\Session;
use rdks\core\libs\Data\Request;

class GenericPage extends Frame {

	const SESSION_EMAIL = "RDKS_EMAIL";

	protected $_jsonData = [];

	protected function getJsonData(){
		return $this->_jsonData;
	}	

	/**
	*	EMAIL SERNDER
	*/
	/*
	|--------------------------------------------|
	 
	  		$headers = [
	 			'to' => "example@domain.com",
	 			'from' => EMAIL_FROM,
	 			'company' => PAGE_TITLE,
	 			'subject' => "Contact Form"
	 			];
	|--------------------------------------------|	 			
	*/
	protected function invalidRequest(){
		Http::setHeaderInvalidRequest();
	}
	
	protected function sendEmail($headers, $template, $cookie = true){

		$store = [];
		$data = $this->getViewData();
		$data = array_merge($data, $this->getJsonData());

		// send form post data 
		$store['form'] = Helper::cleanData(Post::stData());
		// set custom data
		$store['data'] = $data;

		// store in a session to retrieve data by requesting email template
		Session::set(self::SESSION_EMAIL, $store);

		$url = URL::getURL() . "/_email/" . $template;

		// get html
		$request = Request::init('GET', $url);

		if($cookie){
			$request->persistSession();
		}

		$request->execute();

		if($request->getContentType() != Http::getHeaderJSON()):
			$message = $request->getOutput();
			// this function sends an email with html format.
			return Helper::mailHTML($headers, $message);
		endif;	
		
		return false;

	}
} 