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

namespace rdks\core\blocks\User;

use rdks\core\page\Block;
use rdks\core\framework\Dispatch;
use rdks\core\framework\Helper;
use rdks\core\framework\Login;
use rdks\core\framework\Role;
use rdks\core\framework\Path;
use rdks\core\libs\Utils\Date;
use rdks\app\sites\_global\data\LogData;
use rdks\app\models\Users\UsersRoles;

class User extends Block{

	var $id,
		$date;


	private function _picture($img, $resize){
		
		$squared = Path::getIcon("users/{$img}");
		$original = $squared;

		if(!empty($img)){

			$cropped = Path::getCropName($img, $resize);
			
			if(file_exists( Path::getUploadsUsers($cropped) )){
				$squared = Path::getUploadedUsers($cropped);
			} else {
				$cropped = Path::getCropName($img, 'full');
			}

			if(file_exists( Path::getUploadsUsers($img) )){
				$original = Path::getUploadedUsers($img);
			} 

		}

		$this->view->data('original', $original);
		$this->view->data('squared', $squared);
		$this->view->data('resize', $resize);		
	}

	public function card($id = ""){

		if(empty($id)){
			return $this->view->error('public', __METHOD__, "\$id param cannot be zero.");
		}

		$db = $this->db();
		$join = UsersRoles::open($db);
		$user = $join->getUser($id)->fetch();

		if($join->rows()){
	
			$this->_picture($user['picture'], 150);
			$this->view->data("first_name", $user['first_name']." ".$user['last_name']);
			$this->view->data("email", $user['email']);
			$this->view->data("gender", $user['gender']);
			$this->view->data("role", $user['role']);
			$this->view->data("id_role", $user['id_role']);
			
			$this->view->load("card");

			return $this->view->output();
		}

		return $this->view->error(__METHOD__, "'{$id}' is not a valid user id.");

	}	

	public function logs(){

		$id = $this->id;
		$date = $this->date;

		$this->params([
			'id' => [$id, 'PARAM', Dispatch::PARAM_INTEGER],
			'date' => [$date, 'PARAM', Dispatch::PARAM_STRING, Helper::VALID_DATE_YYYY_MM_DD]
		]);

		$logData = LogData::init($id);
		$data = $logData->getContent($date);

		$this->view->data("data", $data);
		$this->view->load("logs");

		return $this->view->output();

	}

	public function picture($img, $resize, $tpl = "cropped"){

		$this->_picture($img, $resize);		
		$this->view->load($tpl);

		return $this->view->output();

	}

	public function output($type, $resize = 150){

		$this->params([
			'type' => [$type, 'PARAM', Dispatch::PARAM_INTEGER, Dispatch::values(Role::getIds())],
			'resize' => [$resize, 'PARAM', Dispatch::PARAM_INTEGER]
		]);		

		$img = "";

		switch ($type) {
			case Role::TYPE_USERS:
				$img = Login::getAdminPicture();
				break;
			case Role::TYPE_SUBSCRIBERS:
				$img = Login::getSubscriberPicture();
				break;
			case Role::TYPE_CLIENTS:
				$img = Login::getClientPicture();
				break;				
		}

		return $this->picture($img, $resize);

	}

} 