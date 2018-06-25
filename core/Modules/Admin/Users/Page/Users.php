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

namespace Roducks\Modules\Admin\Users\Page;

use Roducks\Framework\Login;
use Roducks\Framework\Role;
use Roducks\Framework\URL;
use Roducks\Framework\Post;
use Roducks\Framework\Helper;
use Roducks\Page\AdminPage;
use Roducks\Page\View;
use Roducks\Page\JSON;
use Roducks\Libs\Data\Session;
use Roducks\Libs\Utils\Date;
use Roducks\Services\Storage;
use App\Models\Users\Users as UsersTable;
use App\Models\Users\Roles as RolesTable;
use App\Models\Users\UsersRoles;
use App\Sites\Admin\Modules\Roles\Helper\Roles as RolesHelper;

class Users extends AdminPage
{

	const DATE_RANGE_USERS = 'RDKS_DATE_RANGE_USERS';
	const DATE_RANGE_LOGS = 'RDKS_DATE_RANGE_LOGS';
	const ROWS_PER_PAGE = 15;
	
	protected $_type;
	protected $_url;
	protected $_title;

	// GET Params
	var $page = 1;
	var $trash = 0;
	var $tree = 0;

	public function __construct(array $settings, View $view)
	{
		parent::__construct($settings, $view);

		$this->role(Role::TYPE_USERS, $this->_url);
	}	

	protected function _form($db)
	{

		$query = RolesTable::open($db);
		$roles = $query->getList($this->_type);

		if ($query->getTotalRows() == 0) {
			return $this->view->error('protected',__METHOD__, "There's no roles for: ".Role::getList($this->_type)['title']);
		}

		$this->view->assets->scriptsInline(["form","users","roles.modal"]);
		$this->view->data("url", $this->_url);
		$this->view->data("roles", $roles);
	}

	public function index()
	{

		$this->grantAccess->view();

		$db = $this->db();
		$access = $this->getAccess();

		$search = [];
		$isFiltered = false;
		$start_date = Date::getFormatDMY("/");
		$end_date = Date::getFormatDMY("/");	
		$filter = ['u.trash' => $this->trash];

		if (!Login::isSuperAdmin()) {
			
			if ($this->grantAccess->hasAccess("descendants")) {
				$filter['u.id_user_parent'] = Login::getAdminId();
			}

			if ($this->grantAccess->hasAccess("tree") && Login::roleSuperAdmin()) {
				$filter['[BEGIN_COND]'] = "(";
					$filter['[NON]u.id_user_parent:>'] = Login::getAdminData('id_user_parent');
					$filter['[OR]u.id_role:>'] = Login::getAdminData('id_role');
				$filter['[END_COND]'] = ")";

				if ($this->tree == 1) {
					unset($access['tree']);

					unset($filter['[BEGIN_COND]']);
					unset($filter['[NON]u.id_user_parent:>']);
					unset($filter['[OR]u.id_role:>']);
					unset($filter['[END_COND]']);

					$filter['u.id_user_tree:%like%'] = Login::getAdminId();
				}
			}
		} else {
			$access['tree'] = 1;
			
			if ($this->tree == 1) {
				unset($access['tree']);
				$filter['u.id_user_parent'] = Login::getAdminId();
			}
		}

		if (Session::exists(self::DATE_RANGE_USERS)) {
			$range = Session::get(self::DATE_RANGE_USERS);
			$start_date = $range[0];
			$end_date = $range[1];
			$search['u.created_at:date:between'] = $range;
			$isFiltered = true;
		}

		if (Post::stSentData()) {
			$post = Post::init();

			if ($post->sent("email")) {
				$search['u.email'] = $post->text("email");
			}

			if ($post->sent("id_user")) {
				$search['u.id_user'] = $post->text("id_user");
			}	

			if ($post->sent("start_date") && $post->sent("end_date")) {
				$start_date = $post->param("start_date");
				$end_date = $post->param("end_date");
				$range = [$start_date, $end_date];
				$search['u.created_at:date:between'] = $range;
				Session::set(self::DATE_RANGE_USERS, $range);
			}

			$isFiltered = true;
			$search['u.trash'] = $post->hidden("trash", 0);
		}

		$cond = array_merge($filter, $search);

		$users = UsersRoles::open($db)->getAll($this->_type, $this->page, static::ROWS_PER_PAGE, "desc", $cond);
		$inTrash = UsersRoles::open($db)->inTrash($this->_type, $filter);
		$totals = UsersRoles::open($db)->totals($this->_type, $filter);
		$isTrash = ($this->trash == 1) ? true : false;
		$paramTrash = "";

		if ($isTrash) {
			$totals = $inTrash;
			$paramTrash = URL::setQueryString(['trash' => $this->trash]);
		}

		$this->view->assets->scriptsInline(["pager","grid","popover","users","roles.modal"]);
		$this->view->assets->scriptsOnReady(["pager.ready","pager.focus.ready","grid.ready","datepicker-range.ready"]);		
		
		$this->view->title($this->_title, true, "title-users");
		$this->view->page($this->page);
		$this->view->data("data", $users);
		$this->view->data("totals", $totals);
		$this->view->tpl("totalPages", $users->getTotalPages());
		$this->view->tpl("pageRedirect", URL::setQueryString(['page' => ""]));
		$this->view->data("access", $access);
		$this->view->data("type", $this->_type);
		$this->view->data("icon", RolesHelper::getIcon($this->_type));
		$this->view->data("url", $this->_url);
		$this->view->data("trash", $this->trash);
		$this->view->data("inTrash", $inTrash);
		$this->view->data("isTrash", $isTrash);
		$this->view->data("isFiltered", $isFiltered);
		$this->view->data("start_date", Date::convertToDMY($start_date, "/"));
		$this->view->data("end_date", Date::convertToDMY($end_date, "/"));			
		$this->view->data("urlDatepicker", "{$this->_url}/search");
		$this->view->data("urlReset", "{$this->_url}/reset-filter{$paramTrash}");		
		$this->view->tpl("btnCreateUrl", "{$this->_url}/add");		

		$this->view->layout("sidebar-content",[
			'CONTENT' => [
				$this->view->setTemplate("go-back"),
				$this->view->setTemplate("controllers"),				
				$this->view->setView("index")
			],
			'SIDEBAR' => [
				$this->view->setTemplate("sidebar-left")
			],				
			'SIDEBAR-CHILD-LEFT' => [
				$this->view->setTemplate("sidebar-dashboard"),
				$this->view->setTemplate("sidebar-users"),
				$this->view->setTemplate("sidebar-roles")
			]						
		]);

		return $this->view->output();
	}

	public function resetFilter()
	{
		Session::reset(self::DATE_RANGE_USERS);
		$paramTrash = "";

		if ($this->trash == 1) {
			$paramTrash = URL::setQueryString(['trash' => $this->trash]);
		}

		$this->redirect("{$this->_url}{$paramTrash}");
	}

	public function resetLogs()
	{
		$id_user = $this->getUrlParam('userId');
		Session::reset(self::DATE_RANGE_LOGS);

		$this->redirect("{$this->_url}/logs/id/{$id_user}");
	}

	public function logs()
	{

		$id_user = $this->getUrlParam('userId', Login::getAdminId());

		$db = $this->db();
		$user = UsersTable::open($db);		
		$row = $user->row($id_user);
		$this->hasData( $user->rows() );
		
		$this->grantAccess->editDescendent($id_user, $row, $user->isDescendent($id_user, Login::getAdminId()), "logs");

		$filter = [];
		$isFiltered = false;
		$start_date = Date::getFormatDMY("/");
		$end_date = Date::getFormatDMY("/");

		if (Session::exists(self::DATE_RANGE_LOGS)) {
			$range = Session::get(self::DATE_RANGE_LOGS);
			$start_date = $range[0];
			$end_date = $range[1];
			$filter['text:date:between'] = $range;
			$isFiltered = true;
		}

		if (Post::stSentData()) {
			$post = Post::init();

			if ($post->sent("start_date") && $post->sent("end_date")) {
				$start_date = $post->param("start_date");
				$end_date = $post->param("end_date");
				$range = [$start_date, $end_date];
				$filter['text:date:between'] = $range;
				Session::set(self::DATE_RANGE_LOGS, $range);
				$isFiltered = true;
			}
		}

		try {
			$data = Storage::user($id_user)->getRows('log_date', "desc", $this->page, static::ROWS_PER_PAGE, $filter);
		} catch (\Exception $e) {
			die($e->getMessage());
		}

		$this->view->assets->scriptsInline(["pager","logs"]);
		$this->view->assets->scriptsOnReady(["pager.ready","pager.focus.ready","datepicker-range.ready.inc"]);		

		$this->view->title("Logs: " . $this->_title, true, "title-logs");
		$this->view->page($this->page);
		$this->view->data("data", $data['data']);
		$this->view->data("isFiltered", $isFiltered);
		$this->view->data("start_date", Date::convertToDMY($start_date, "/"));
		$this->view->data("end_date", Date::convertToDMY($end_date, "/"));			
		$this->view->data("urlDatepicker", "{$this->_url}/logs/datepicker/id/{$id_user}");
		$this->view->data("urlReset", "{$this->_url}/reset-logs/id/{$id_user}");
		$this->view->data("id_user", $id_user);
		$this->view->tpl("totalPages", $data['pages']);
		$this->view->tpl("pageRedirect", URL::setQueryString(['page' => ""]));		

		$this->view->layout("sidebar-content",[
			'CONTENT' => [
				$this->view->setTemplate("go-back"),
				$this->view->setTemplate("controllers"),				
				$this->view->setView("logs")
			],
			'SIDEBAR' => [
				$this->view->setTemplate("sidebar-left")
			],				
			'SIDEBAR-CHILD-LEFT' => [
				$this->view->setTemplate("sidebar-dashboard"),
				$this->view->setTemplate("sidebar-users"),
				$this->view->setTemplate("sidebar-roles")
			]						
		]);

		return $this->view->output();

	}

	/**
	*	@type GET
	*/
	public function edit()
	{

		$id_user = $this->getUrlParam('userId', Login::getAdminId());
		$db = $this->db();
		$user = UsersTable::open($db);
		$userJoin = UsersRoles::open($db);
		$row = $userJoin->getUser($id_user)->fetch();

		$this->hasData( $userJoin->rows() );
		
		$this->grantAccess->editDescendent($id_user, $row, $user->isDescendent($id_user, Login::getAdminId()), "edit");

		$this->_form($db);

		$row['expiration_date'] = ($row['expiration_date'] == Date::getEmptyDate()) ? "" : $row['expiration_date'];
		$row['expiration_format'] = (!empty($row['expiration_date'])) ? Date::convertToDMY($row['expiration_date'], "/") : Date::getFormatDMY("/");
		
		$expiration_date = (!empty($row['expiration_date'])) ? $row['expiration_date'] : Date::getCurrentDate();
		$date = explode("-", $expiration_date);
		$year = intval($date[0]);
		$month = intval($date[1]);
		$day = intval($date[2]);				
		$date_config = "\"year\": {$year}, \"month\": {$month}, \"day\": {$day}";

		$this->view->title(TEXT_EDIT);
		$this->view->data("date_config", $date_config);		
		$this->view->data("action", "update/{$id_user}");
		$this->view->data("row", $row);
		$this->view->data("edit", true);

		$this->view->layout("form",[
			'FORM' => $this->view->setView("form")
		]);

		return $this->view->output();

	}

	/**
	*	@type GET
	*/
	public function add()
	{

		$this->grantAccess->create();
		$db = $this->db();
		$this->_form($db);
		
		$row = [
			'first_name' => "",
			'last_name' => "",
			'type' => $this->_type,
			'id_role' => "",
			'role' => TEXT_CHOOSE_AN_OPTION,
			'id_user' => 0,
			'active' => 1,
			'expires' => 0,
			'expiration_format' => Date::getFormatDMY("/"),
			'expiration_date' => ""	
		];

		$expiration_date = Date::getCurrentDate();
		$date = explode("-", $expiration_date);
		$year = intval($date[0]);
		$month = intval($date[1]);
		$day = intval($date[2]);				
		$date_config = "\"year\": {$year}, \"month\": {$month}, \"day\": {$day}";

		$this->view->title(TEXT_NEW . " " . $this->_title);	
		$this->view->data("action", "insert");
		$this->view->data("date_config", $date_config);
		$this->view->data("row", $row);	
		$this->view->data("edit", false);	

		$this->view->layout("form",[
			'FORM' => $this->view->setView("form")
		]);

		return $this->view->output();

	}

	/**
	*	@type GET
	*/
	public function resetPassword()
	{

		$id_user = $this->getUrlParam('userId');

		// Can't reset yourself!
		if (Login::getAdminId() == $id_user) {
			$this->forbiddenRequest();
		}

		$db = $this->db();
		$user = UsersTable::open($db);
		$row = $user->row($id_user);
		$this->hasData( $user->rows() );

		$this->grantAccess->editDescendent($id_user, $row, $user->isDescendent($id_user, Login::getAdminId()), "reset");

		$this->view->assets->scriptsInline(["form"]);
		$this->view->title(TEXT_RESET_PASSWORD);
		$this->view->data("id_user", $id_user);	
		$this->view->data("user", $row);		

		$this->view->layout("form",[
			'FORM' => $this->view->setView("reset")
		]);

		return $this->view->output();

	}

	/**
	*	@type GET
	*/
	public function changePassword()
	{

		$this->grantAccess->password();

		$id_user = $this->getUrlParam('userId');

		$this->view->assets->scriptsInline(["form"]);
		$this->view->title(TEXT_CHANGE_PASSWORD);			

		$this->view->layout("form",[
			'FORM' => $this->view->setView("password")
		]);

		return $this->view->output();

	}

	public function picture()
	{

		$id_user = $this->getUrlParam('userId', Login::getAdminId());

		$db = $this->db();
		$user = UsersTable::open($db);		
		$row = $user->row($id_user);
		$this->hasData( $user->rows() );

		$this->grantAccess->editDescendent($id_user, $row, $user->isDescendent($id_user, Login::getAdminId()), "picture");

		$this->view->assets->plugins([
			'bootstrap',
			'jquery-jcrop',
			'roducks'
		], true); // true = overwrite global plugins, false = append more plugins

		$this->view->assets->scriptsInline(["crop","form","picture"]);
		$this->view->assets->scriptsOnReady(["crop.ready"]);

		$this->view->title(TEXT_PROFILE_PICTURE);
		$this->view->tpl("urlJsonPicture", "/_json{$this->_url}/picture/id/{$id_user}");
		$this->view->data("picture", $row['picture']);
		$this->view->data("gender", $row['gender']);

		$this->view->load("picture");

		return $this->view->output();
	}

} 