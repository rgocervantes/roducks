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

namespace Roducks\Modules\Front\Account\Page;

use Roducks\Page\View;
use Roducks\Page\FrontPage;
use Roducks\Framework\Login;
use Roducks\Framework\Role;
use DB\Models\Users\Users as UsersTable;

class Account extends FrontPage
{

	public function __construct(array $settings, View $view)
	{
		parent::__construct($settings, $view);
		
		$this->login->required();
		$this->role(Role::TYPE_SUBSCRIBERS);
	}

	public function index()
	{

		$this->grantAccess->view();

		$this->view->title(TEXT_WELCOME);
		$this->view->data("picture", Login::getSubscriberPicture());
		$this->view->data("gender", Login::getSubscriberData('gender'));		
		$this->view->load("index");
		
		return $this->view->output();

	}

	public function edit()
	{

		$this->grantAccess->edit();

		$this->view->data("first_name", Login::getSubscriberName());
		$this->view->data("last_name", Login::getSubscriberLastName());
		$this->view->load("edit");

		return $this->view->output();

	}

	public function changePassword()
	{

		$this->grantAccess->password();
		
		$this->view->load("change-password");

		return $this->view->output();
	}

	public function picture()
	{

		$this->grantAccess->picture();

		$this->view->assets->plugins([
			'bootstrap',
			'jquery-jcrop',
			'roducks'
		], true);

		$this->view->assets->scriptsInline(["crop","form","picture"]);
		$this->view->assets->scriptsOnReady(["crop.ready"]);

		$this->view->title("Foto");
		$this->view->tpl("urlJsonPicture", "/_json/account/picture");
		$this->view->data("picture", Login::getSubscriberPicture());
		$this->view->load("picture");

		return $this->view->output();
	}

}
