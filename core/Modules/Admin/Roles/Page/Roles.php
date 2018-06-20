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

namespace Roducks\Modules\Admin\Roles\Page;

use Roducks\Page\View;
use Roducks\Page\JSON;
use Roducks\Page\AdminPage;
use Roducks\Framework\URL;
use Roducks\Framework\Login;
use Roducks\Framework\Role;
use Roducks\Libs\Data\Session;
use App\Models\Users\Roles as RolesTable;
use App\Sites\Admin\Modules\Roles\Helper\Roles as RolesHelper;

class Roles extends AdminPage
{

	var	$page = 1,
		$type = 1;

	protected $_allowedRoles = [],
			  $_rowsPerPage = 15;

	public function __construct(array $settings, View $view)
	{
		parent::__construct($settings, $view);

		$this->role(Role::TYPE_USERS); // Only Admins can modify Roles
	}

	private function _getRoleType()
	{
		return Session::get('ROLE_TYPE');
	}	

	private function _form()
	{

		$this->view->title("Roles", true, "title-roles");
		$this->view->assets->scriptsInline(["form","tooltip"]);
		$this->view->assets->scriptsOnReady(["roles.ready"]);

		$this->view->data("type", $this->_getRoleType());

	}

	public function index()
	{

		$this->grantAccess->view();

		$roles = Role::getList(RolesHelper::$list);

		$this->view->title("Roles", true, "title-roles");
		$this->view->data("roles", $roles);

		$this->view->layout("sidebar-content",[
			'CONTENT' => [
				$this->view->setTemplate("go-back"),
				$this->view->setTemplate("headline"),
				$this->view->setView("index")
			],
			'SIDEBAR' => [
				$this->view->setTemplate("sidebar-left")
			],		
			'SIDEBAR-CHILD-LEFT' => [
				$this->view->setTemplate("sidebar-dashboard"),
				$this->view->setTemplate("sidebar-roles")
			]					
		]);

		return $this->view->output();
	}

	public function listing()
	{

		$this->grantAccess->view();

		Session::set('ROLE_TYPE', $this->type);

		$db = $this->db();
		$data = RolesTable::open($db)->getAll($this->type, $this->page, $this->_rowsPerPage);
		$access = $this->getAccess();
		
		$this->view->assets->scriptsInline(["pager","grid","popover","roles","roles.modal"]);
		$this->view->assets->scriptsOnReady(["pager.ready","pager.focus.ready","grid.ready"]);

		$this->view->title("Roles - " . Role::getTitle($this->type), true, "title-roles");
		$this->view->page($this->page);

		$this->view->data('data', $data);
		$this->view->data("access", $access);

		$autocomplete = [
			'url' => "/_json/roles/search",
			'redirect' => "/roles/edit/id/",
			'callback' => "cbRolesAutocomplete",
			'params' => JSON::encode(['type' => $this->type])
		];

		$this->view->tpl("totalPages", $data->getTotalPages());
		$this->view->tpl("pageRedirect", URL::setQueryString(['page' => ""]));
		$this->view->tpl("btnCreateUrl", "/roles/add");
		$this->view->tpl("autocomplete", $autocomplete);

		$this->view->layout("sidebar-content",[
			'CONTENT' => [
				$this->view->setTemplate("go-back"),
				$this->view->setTemplate("controllers"),
				$this->view->setView("list")
			],
			'SIDEBAR' => [
				$this->view->setTemplate("sidebar-left")
			],		
			'SIDEBAR-CHILD-LEFT' => [
				$this->view->setTemplate("sidebar-dashboard"),	
				$this->view->setTemplate("sidebar-roles")
			]					
		]);

		return $this->view->output();
	}

	public function add()
	{

		$this->grantAccess->create();

		$this->_form();

		$this->view->data("data", []);
		$this->view->data("id_role", 0);
		$this->view->data("_name", "");
		$this->view->data("config", "");

		$this->view->data("edit", true);
		$this->view->data("method", "insert");
		$this->view->data("action", "save");	
		$this->view->data("id_role", 0);				
		$this->view->data("insert", true);
		$this->view->data("access", $this->_allowedRoles);
		
		$this->view->layout("form",[
			'FORM' => [
				$this->view->setView("form")
			]
		]);
		
		return $this->view->output();
	}

	public function edit()
	{

		$this->grantAccess->edit();

		$id_role = $this->getUrlParam("roleId");
		$edit = ($id_role > 6);

		// Can't edit role you belong to.
		if($id_role == 1 || $id_role == Login::getAdminData('id_role') || $id_role < Login::getAdminData('id_role')){
			$this->pageNotFound();
		}		

		$db = $this->db();
		$RolesTable = RolesTable::open($db);

		$role = $RolesTable->row( $id_role );
		$this->hasData( $RolesTable->rows() );
		
		// get File
		$config = $this->grantAccess->getFileConfig( $role['config'] );

		$this->_form();

		// Get permissions logged admin has
		$access = $this->grantAccess->getData();

		$this->view->data("data", $config['data']);
		$this->view->data("id_role", $id_role);
		$this->view->data("_name", $role['name']);
		$this->view->data("config", $role['config']);

		$this->view->data("edit", $edit);
		$this->view->data("method", "update");	
		$this->view->data("action", "save/{$id_role}");	
		$this->view->data("id_role", $id_role);		
		$this->view->data("insert", false);
		$this->view->data("access", $access);		
		
		$this->view->layout("form",[
			'FORM' => [
				$this->view->setView("form")
			]
		]);
		
		return $this->view->output();

	}

} 