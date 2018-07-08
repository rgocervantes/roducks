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
 *	-----------------
 *	COMMAND LINE
 *	-----------------
 *	php roducks cache:make [dir]
 *	php roducks cache:remove [dir]
 *	php roducks cache:clean [dir] 
 *	php roducks cache:clean [dir] --remain-folders
 *	php roducks cache:move [dir-origin] [dir-destination]
 */

namespace App\CLI;

use Roducks\Framework\CLI;

use Lib\Directory as DirectoryHandler;
use Path;

class Directory extends CLI
{
	private function _command()
	{
		$this->error('Run this command:');
		$this->error('[x]');
	}

	public function make($dir = "")
	{

		if (empty($dir)) {
			$this->_command();
			$this->error(self::command(__CLASS__, __FUNCTION__, '[dir]'));
		} else {
			DirectoryHandler::make(Path::getData(), $dir);

			$this->success("{$dir} was made!");
		}

		parent::output();
	}

	public function remove($dir = "")
	{
		if (empty($dir)) {
			$this->_command();
			$this->error(self::command(__CLASS__, __FUNCTION__, '[dir]'));
		} else {
			DirectoryHandler::remove(Path::getData($dir));

			$this->success("{$dir} was removed!");
		}

		parent::output();
	}

	public function clean($dir = "")
	{

		if (empty($dir)) {
			$this->_command();
			$this->error(self::command(__CLASS__, __FUNCTION__, '[dir] [flag:--remain-folders]'));
		} else {

			$options = [DirectoryHandler::REMOVE_FILES];

			if ($this->getFlag('--remain-folders')) {
				array_push($options, DirectoryHandler::REMAIN_FOLDERS);
			}

			DirectoryHandler::clean(Path::getData($dir), $options);

			$this->success("{$dir} was cleaned up!");

		}

		parent::output();
	}

	public function move($origin = "", $destination = "")
	{
		if (empty($origin) || empty($destination)) {
			$this->_command();
			$this->error(self::command(__CLASS__, __FUNCTION__, '[dir-origin] [dir-destination]'));
		} else {
			DirectoryHandler::move(Path::getData($dir));

			$this->success("{$dir} was moved!");
		}

		parent::output();
	}
}