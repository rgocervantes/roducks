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

namespace Roducks\Modules\Front\Home\Page;

use Roducks\Data\User;
use Roducks\Page\FrontPage;
use DB\Models\Users\Users as UsersTable;

class Home extends FrontPage
{

	public function index()
	{

		$this->view->title(TEXT_WELCOME);
		$this->view->load("index");
		
		return $this->view->output();

	}

	public function createAccount()
	{

		$this->accountSubscriber();

		$this->view->assets->scriptsInline(["form"]);
		$this->view->title("Create Account");
		$this->view->load("account");

		return $this->view->output();

	}

	public function forgottenPassword()
	{

		$this->accountSubscriber();		

		$this->view->assets->scriptsInline(["form"]);
		$this->view->title("Forgotten Password");
		$this->view->load("forgotten-password");

		return $this->view->output();
	}

	public function resetPassword()
	{

		$token = $this->getPairParam('token');

		$db = $this->db();
		$rows = UsersTable::open($db)->results(['token' => $token]);
		
		if (!$rows) {
			$this->forbiddenRequest();
		}

		$this->view->assets->scriptsInline(["form"]);
		$this->view->title("Reset Password");
		$this->view->data("token", $token);
		$this->view->load("reset-password");

		return $this->view->output();
	}	

	public function contactUs()
	{

		$this->view->assets->scriptsInline(["form"]);
		$this->view->load("contact-us");
		
		return $this->view->output();
	}	

} 