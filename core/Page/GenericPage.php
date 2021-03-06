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
use Roducks\Framework\Form;
use Roducks\Framework\Config;
use Roducks\Framework\Error;
use Roducks\Framework\Settings;
use Roducks\Libs\Request\Http;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class GenericPage extends Frame
{

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
				$this->_helper = $helper::init($className);
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

	protected function csrfToken()
	{
		$this->view->meta('name', "csrf-token", Form::getKey());
	}

	protected function csrfTokenValidation()
	{
		Form::setKey($this->post->param('csrf_token', null));
	}

	/**
	*	EMAIL SERNDER
	*/
	/*

		$this->sendEmail('test', function(PHPMailer $mail) {
			$mail->Subject = 'Welcome';
			$mail->addAddress('sender@example.com', 'John Doe');
		});

	*/
	protected function sendEmail($template, callable $callback)
	{

		$tpl = Path::getEmail($template);
		$store = [];
		$data = $this->getViewData() + $this->getJsonData();

		// send form post data
		$store['form'] = Helper::cleanData(Post::stData());
		// set custom data
		$store['data'] = $data;

		if (file_exists($tpl)) {

			extract($store);
	
			ob_start();
			include $tpl;
			$html = ob_get_contents();
			ob_end_clean();

			$mail = new PHPMailer(true);
			$smtp = Config::getSmtp()['data'];

			try {
					//Server settings
					$mail->SMTPDebug = 0;                                       // Enable verbose debug output
					$mail->isSMTP();                                            // Set mailer to use SMTP
					$mail->Host       = $smtp['server'];  // Specify main and backup SMTP servers
					
					if (!empty($smtp['username']) && !empty($smtp['password'])) {
						$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
						$mail->Username   = $smtp['username'];                     // SMTP username
						$mail->Password   = $smtp['password'];                               // SMTP password
					}

					$mail->SMTPSecure = $smtp['encryption'];                                  // Enable TLS encryption, `ssl` also accepted
					$mail->Port       = $smtp['port'];                                    // TCP port to connect to
				
					$callback($mail);
	
					$mail->setFrom($smtp['from'], Settings::getPageTitle());
	
					// Content
					$mail->isHTML(true);                                  // Set email format to HTML
					
					$mail->Body    = $html;
					$mail->AltBody = strip_tags($html);
	
					$mail->send();
	
					return true;
	
			} catch (PHPMailerException $e) {
				Error::debug("PHPMailer", __LINE__, __FILE__, $tpl, "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
			}
		
		} else {
			Error::debug(TEXT_FILE_NOT_FOUND, __LINE__, __FILE__, $tpl, "Email template does not exist.");
		}

		return false;

	}
}
