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
use Roducks\Framework\Language;
use Roducks\Framework\URL;
use Roducks\Framework\Error;
use Roducks\Framework\Helper;
use Roducks\Framework\Path;
use Roducks\Data\User;
use Roducks\Libs\Request\Http;
use Roducks\Framework\Observer;

class Page extends GenericPage
{

	const LOGIN_URL = "/login";

	protected $_pageType = 'PAGE';
	protected $_viewport = true;

/*
//---------------------------------
//	PROTECTED METHODS
//---------------------------------
*/

	protected function redirect($url)
	{
		Http::redirect($url);
	}

	protected function hasData($bool)
	{
		if (!$bool) {
			$this->notFound();
			exit;
		}
	}

	protected function isData(array $data)
	{
		$this->hasData(!empty($data));
	}

	protected function forbiddenRequest()
	{
		$this->hasData(false);
	}

	protected function requireLogin()
	{
		if (!User::isLoggedIn()) {
			$this->redirect(static::LOGIN_URL);
		}
	}

/*
//---------------------------------
//	PUBLIC METHODS
//---------------------------------
*/
	public function __construct(array $settings, View $view)
	{
		parent::__construct($settings);

		$this->view = $view;
		$this->view->parentPage($this->_getParentClassName());
		$this->view->page(1);
		$this->view->meta('http-equiv','Content-Type', $this->getConfig('file:config', 'content.type', 'text/html; charset=utf-8'));

		if ($this->_viewport) $this->view->meta('name','viewport', $this->getConfig('file:config', 'viewport', "width=device-width,initial-scale=1,shrink-to-fit=no"));
	}

	public function notFound($overwrite = false)
	{

		Http::sendHeaderNotFound(false);

		$this->view->assets->css(["page-404.css"]);
		$this->view->assets->jsOnReady(["page-404"]);

		if (!$overwrite) {
			$template = $this->getConfig('site', 'template.not.found', '404');
			$this->view->setError();
			$this->view->template($template);
			$this->view->body();
		}

		return $this->view->output();

	}

	public function forbidden()
	{
		$this->view->setError();
		$this->view->template('404');
		$this->view->body();
		return $this->notFound(true);
	}

	public function _lang($type, $lang)
	{

		$dir_languages = Path::getLanguage($lang);
		$set = true;

		if (file_exists($dir_languages)) {

			if (Language::isMultilanguage()) {
				$set = Language::set($lang);
			}

			if ($set) {

				$relativeURL = URL::getRelativeURL();

				if ($relativeURL == DIRECTORY_SEPARATOR) {
					$url = $relativeURL;
				} else {
					$split = explode(DIRECTORY_SEPARATOR, $relativeURL);
					$split = Helper::getUrlParams($split);
					$url = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $split);
				}

				Observer::on('ChooseLanguage', [$lang, $url]);

				Http::redirect($url);
			}

		} else {
			Error::debug(TEXT_FILE_NOT_FOUND,__LINE__, __FILE__, $dir_languages, "Make sure file exists with translations.");
		}

	}

}
