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

namespace Roducks\Blocks\User;

use Roducks\Page\Block;
use Roducks\Page\JSON;
use Roducks\Framework\Dispatch;
use Roducks\Framework\Helper;
use Roducks\Data\User as UserData;
use Roducks\Framework\Path;
use Roducks\Libs\Utils\Date;
use Roducks\Services\Storage;
use DB\Models\Users\UsersRoles;

class User extends Block
{
	var $id,
		$date;

	private function _picture($img, $resize)
	{
		$squared = Path::getIcon("users/{$img}");
		$full = $squared;

		if (!empty($img)) {

			$cropped = Path::getCropName($img, $resize);

			if (\App::fileExists( Path::getUploadsUsers($cropped) )) {
				$squared = Path::getUploadedUsers($cropped);
			} else {
				$cropped = Path::getCropName($img, 'full');
			}

			if (\App::fileExists( Path::getUploadsUsers($img) )) {
				$full = Path::getUploadedUsers($img);
			}

		}

		$this->view->data('full', $full);
		$this->view->data('img', $squared);
		$this->view->data('resize', $resize);
	}

	public function card($id = "")
	{

		if (empty($id)) {
			return $this->view->error('public', __METHOD__, "\$id param cannot be zero.");
		}

		$db = $this->db();
		$join = UsersRoles::open($db);
		$user = $join->getUser($id);

		if ($join->rows()) {

			$this->_picture($user['picture'], 150);
			$this->view->data("first_name", $user['first_name']." ".$user['last_name']);
			$this->view->data("email", $user['email']);
			$this->view->data("gender", $user['gender']);
			$this->view->data("role", $user['role']);
			$this->view->data("id_role", $user['id_role']);

			$this->view->load("card");

			return $this->view->output();
		}

		return $this->view->error('public', __METHOD__, "'{$id}' is not a valid user id.");

	}

	public function logs()
	{

		$id = $this->id;
		$date = $this->date;

		$this->params([
			'id' => [$id, 'PARAM', Dispatch::PARAM_INTEGER],
			'date' => [$date, 'PARAM', Dispatch::PARAM_STRING, Helper::VALID_DATE_YYYY_MM_DD]
		]);

		$data = Storage::log($id, $date)->getContent();

		$this->view->data("data", $data);
		$this->view->load("logs");

		return $this->view->output();

	}

	public function picture($resize = 150)
	{

		$this->view->data('img', UserData::getPicture(false, $resize));
		$this->view->data('resize', $resize);
		$this->view->load('picture');

		return $this->view->output();

	}

	public function pictureModal($resize = 150)
	{
		$img = UserData::getPicture(false);
		$this->view->data('full', $img);
		$this->view->data('img', $img);
		$this->view->data('resize', $resize);
		$this->view->load('picture-modal');

		return $this->view->output();
	}

	public function profile($resize = 150)
	{

		$this->params([
			'resize' => [$resize, 'PARAM', Dispatch::PARAM_INTEGER]
		]);

		$this->view->data('user_name', UserData::getFullName());
		$this->view->data('email', UserData::getEmail());
		$this->view->load('profile');

		return $this->view->output();
	}

	public function output($picture, $resize = 150, $display = 'picture')
	{
		$this->_picture($picture, $resize);
		$this->view->load($display);

		return $this->view->output();

	}

}
