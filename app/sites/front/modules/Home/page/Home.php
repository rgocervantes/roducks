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

namespace rdks\app\sites\front\modules\Home\page;

use rdks\core\modules\front\Home\page\Home as HomePage;

class Home extends HomePage {

	public function index(){

		$this->view->title(TEXT_WELCOME);
		$this->view->data("foo", "Initialized FooVar!");
		$this->view->assets->scriptsInline(["foo"]);
		$this->view->assets->scriptsOnReady(["home"]);
		$this->view->load("index");
		
		return $this->view->output();

	}
} 
