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

namespace Roducks\Modules\All\Install\Page;

use Roducks\Page\Page;
use Roducks\Framework\Core;
use Helper;
use Path;

class Install extends Page
{

  public function run()
  {

    $this->view->assets->css([
      'admin.css',
      'installer.css'
    ], true);

    $this->view->assets->jsInline([
      'form',
      'installer'
    ]);

    $this->view->assets->jsOnReady([
      'installer.ready'
    ]);

    $loaded = Core::extensions();

    $this->view->template('install', false);

    $this->view->data('dir_data_exists', file_exists(Path::getData()));
    $this->view->data('data_writable', is_writable(Path::getData()));
    $this->view->data('config_writable', is_writable(Path::get('config/')));
    $this->view->data('dir_data', Path::getData());
    $this->view->data('dir_config', Path::get('config/'));
    $this->view->data('php_version', $loaded['php']);
    $this->view->data('php_exts', $loaded['loaded']['exts']);
    $this->view->data('roducks_version', RDKS_VERSION);

    $this->view->load('install');

    return $this->view->output();
  }

}
