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

use rdks\core\framework\Login;

class FrontPage extends Page {

	protected $login;

	public function __construct(array $settings, View $view){
		parent::__construct($settings, $view);

		$this->view->meta('name','viewport',"width=device-width,initial-scale=1,shrink-to-fit=no");
		$this->login = new Login(Login::SESSION_FRONT, $this->loginUrl);
	}

} 