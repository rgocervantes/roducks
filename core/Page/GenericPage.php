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

use Roducks\Framework\Helper;
use Roducks\Framework\URL;
use Roducks\Framework\Post;
use Roducks\Framework\Path;
use Roducks\Libs\Request\Http;
use Roducks\Libs\Request\Request;
use Roducks\Libs\Data\Session;

class GenericPage extends Frame
{

	const SESSION_EMAIL = "RDKS_EMAIL";

	private $_helper;

	private function _callHelper()
	{

		$found = false;
		$className = $this->pageObj->className;
		$file = $this->pageObj->fileName;
		$coreFile = Helper::getHelperFileName($file);

		if (Helper::isPage($className) || Helper::isJson($className) || Helper::isXml($className)) {

			if (Path::exists(Helper::getHelperPath($file))) {
				$found = true;
			} else if (Path::exists(Helper::getHelperPath($coreFile))) {
				$className = Helper::getCoreHelperclassName($className);
				$found = true;
			}

			if ($found) {
				$helper = Helper::getHelperPath($className);
				$this->_helper = $helper::init();
			}
		}
	}

	public function __construct(array $settings = [])
	{
		parent::__construct($settings);
		$this->_callHelper();
	}

	protected $_jsonData = [];

	protected function getJsonData()
	{
		return $this->_jsonData;
	}

	protected function invalidRequest()
	{
		Http::setHeaderInvalidRequest();
	}

	protected function helper()
	{
		return $this->_helper;
	}

	/**
	*	EMAIL SERNDER
	*/
	/*	
		$this->sendEmail("contact-us", function ($sender) {
			$sender->to = "example@domain.com";
			$sender->from = EMAIL_FROM;
			$sender->company = PAGE_TITLE;
			$sender->subject = "Contact Form";
		});

	*/
	protected function sendEmail($template, callable $callback)
	{

		$attrs = new \stdClass;
		$attrs->cookie = true;
		$callback($attrs);

		$headers = [
			'to' => $attrs->to,
			'from' => $attrs->from,
			'company' => $attrs->company,
			'subject' => $attrs->subject
		];

		$store = [];
		$data = $this->getViewData();
		$data = array_merge($data, $this->getJsonData());

		// send form post data 
		$store['form'] = Helper::cleanData(Post::stData());
		// set custom data
		$store['data'] = $data;

		// store in a session to retrieve data by requesting email template
		Session::set(self::SESSION_EMAIL, $store);

		$url = URL::setAbsoluteURL("/_email/{$template}");

		// get html
		$request = Request::init('GET', $url);

		if ($attrs->cookie) {
			$request->persistSession();
		}

		$request->execute();

		if ($request->getContentType() != Http::getHeaderJSON()) :
			$message = $request->getOutput();
			// this function sends an email with html format.
			return Helper::mailHTML($headers, $message);
		endif;	
		
		return false;

	}
}