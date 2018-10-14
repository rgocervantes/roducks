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

namespace Roducks\Services;

use Roducks\Page\Service;
use Crypt\Hash;
use Lib\File;
use Path;

class Install extends Service
{
  const LOCK = 'install.lock';
  protected $_dispatchUrl = true;

  public function __construct(array $settings)
  {
    parent::__construct($settings);

    if (Path::exists(self::LOCK)) {
      $this->setError(501, 'Service Not Available', true);
      parent::output();
    }

  }

  public function rest()
  {

    $hash = Hash::getToken();
		$hash = substr($hash, 0, 32);
    $data = $this->post->data();

$config = <<< EOT
<?php

return [
//-------------------------------------------------------------------
//  Your Domain Name (WITHOUT www or any subdomain)
//-------------------------------------------------------------------
	'domain_name' 					=> '*', // Example: yoursite.test
//-------------------------------------------------------------------
//  Timezone @url http://php.net/manual/en/timezones.php
//-------------------------------------------------------------------
	'default_timezone' 			=> '{$data['site']['timezone']}',
//-------------------------------------------------------------------
//  Default Title for all pages
//-------------------------------------------------------------------
	'page_title' 					=> '{$data['site']['title']}',
//-------------------------------------------------------------------
//   Email reply
//-------------------------------------------------------------------
	'email_from'					=> '{$data['site']['email_from']}',
//-------------------------------------------------------------------
//   Email sender
//-------------------------------------------------------------------
	'email_to' 						=> '{$data['site']['email_to']}',
//-------------------------------------------------------------------
//  Logo
//-------------------------------------------------------------------
	'logo_image' 					=> 'roducks_logo.png',
//-------------------------------------------------------------------
//  Find Request URL in Database
//-------------------------------------------------------------------
	'find_url_in_db' 				=> false,
//-------------------------------------------------------------------
//  Allow Subscribers to register
//-------------------------------------------------------------------
	'allow_subscribers_register' 	=> true,
//-------------------------------------------------------------------
//  Subscribers expires in ? days
//-------------------------------------------------------------------
	'subscribers_expire'			=> false,
	'subscribers_expire_time'		=> 'MONTHS', // DAYS | MONTHS
	'subscribers_expire_in' 		=> 2,
//-------------------------------------------------------------------
//  Is your site multilanguage?
//-------------------------------------------------------------------
	'multilanguage' 				=> true,
//-------------------------------------------------------------------
//  Allows user's browser language as default
//-------------------------------------------------------------------
	'browser_language' 				=> true,
//-------------------------------------------------------------------
//  Default language ISO
//-------------------------------------------------------------------
	'default_language' 				=> 'en' // ISO: en | es
];
EOT;

$database = <<< EOT
<?php

return [
//-------------------------------------------------------------------
//  Host name
//-------------------------------------------------------------------
	'host' 				=> 'localhost',
//-------------------------------------------------------------------
//  Data Base name
//-------------------------------------------------------------------
	'name' 				=> '{$data['database']['name']}',
//-------------------------------------------------------------------
//  User
//-------------------------------------------------------------------
	'user' 				=> '{$data['database']['user']}',
//-------------------------------------------------------------------
//  Password
//-------------------------------------------------------------------
	'password' 			=> '{$data['database']['password']}'
];
EOT;

    File::create(Path::get(DIR_APP_CONFIG), 'config.local.inc', $config);
    File::create(Path::get(DIR_APP_CONFIG), 'database.local.inc', $database);
		File::create(Path::getData(), self::LOCK, $hash);

    parent::output();
  }
}
