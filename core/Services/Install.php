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
use Roducks\Framework\Error;
use Roducks\Framework\Config;
use DB\Models\Users\Users as UsersTable;
use Crypt\Hash;
use Lib\File;
use Path;
use Helper;

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

  public function goLive()
  {
    $hash = Hash::getToken();
    $hash = substr($hash, 0, 32);
    File::create(Path::getData(), self::LOCK, $hash);
  }

  public function configLocal($data)
  {

    $find_url_in_db = (isset($data['site']['find_url_in_db'])) ? 'true' : 'false';
    $allow_subscribers_register = (isset($data['site']['allow_subscribers_register'])) ? 'true' : 'false';
    $multilanguage = (isset($data['site']['multilanguage'])) ? 'true' : 'false';
    $browser_language = (isset($data['site']['browser_language'])) ? 'true' : 'false';

    $config = [
      'domain.name' => '*',
      'timezone' => $data['site']['timezone'],
      'site.title' => $data['site']['title'],
      'email' => [
          'to' => $data['site']['email_to'],
          'from' => $data['site']['email_from'],
      ],
      'logo.image' => 'roducks_logo.png',
      'find.url.in.db' => $find_url_in_db,
      'subscribers' => [
          'allow.register' => $allow_subscribers_register,
          'expire' => false,
          'how.long' => 2, 
          'period' => MONTHS,
      ],
      'language' => [
          'default' => $data['default_language'],
          'multilanguage' => $multilanguage,
          'user.browser' => $browser_language,
      ],
      'content.type' => 'text/html; charset=utf-8',
      'viewport' => 'width=device-width,initial-scale=1,shrink-to-fit=no',
    ];

    Config::set('config.local', $config);

  }

  public function rest()
  {

    $this->post->required();
    $data = $this->post->data();
    Error::json();

    $database = [
      'host' => $data['database']['host'],
      'port' => $data['database']['port'],
      'name' => $data['database']['name'],
      'user' => $data['database']['user'],
      'password' => $data['database']['password']
    ];

    if (strlen($data['user']['password']) >= 7) {

      $db = $this->openDb([$data['database']['host'], $data['database']['user'], $data['database']['password'], $data['database']['name'], $data['database']['port']]);
      $user = UsersTable::open($db);
      $total = $user->getTableTotalRows();
      $gender = $data['user_gender'];
      $id_role = ($total == 0) ? 1 : 2;

      $fields = [
        'id_user_tree' => '0',
        'id_role' => $id_role,
        'email' => $data['user']['email'],
        'password' => $data['user']['password'],
        'first_name' => $data['user']['first_name'],
        'last_name' => $data['user']['last_name'],
        'gender' => $gender,
        'picture' => Helper::getUserIcon($gender),
      ];

      $tx = $user->create($fields);

      if ($tx) {
        $this->goLive();
        $this->configLocal($data);
        Config::set('database.local', $database);
      } else {
        $this->setError(2, 'User could not be created.');
      }

    } else {
      $this->setError(1, 'Password must be greater than 7 chars.');
    }

    parent::output();
  }
}
