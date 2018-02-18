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
 *	php roducks schema:setup --pro
 *	php roducks schema:setup --pro Setup_2018_02_18_Rod
 *	php roducks schema:sql --pro
 *	php roducks schema:sql --pro --save
 */

namespace App\Cli;

use Roducks\Framework\Cli;
use Roducks\Libs\Files\Directory;

class Schema extends Cli
{

	protected $_params = [
		'script'
	];

	private function _run($script)
	{

		$class = "App\Schema\Setup\\" . $script;
		$method = "schema";

		$obj = new $class();
		if(method_exists($obj, $method)){
			$obj->$method($this->db());
			$obj->store($this->db());
			$obj->finished($script);
		} else {
			self::println("Undefined method 'schema' -> {$class}::{$method}");
		}

	}

	public function setup()
	{

		$script = $this->getParam('script', null);

		if (!is_null($script)) {
			$this->_run($script);
		} else {

			$dir = Directory::open(\App::getRealFilePath("app/Schema/Setup/"));

			foreach ($dir['files'] as $file) {
				$this->_run(str_replace(FILE_EXT, '', $file));
			}

		}

	}

	public function sql()
	{

		if ($this->getFlag('save')) {
			$this->setResult("Saved!"); 
		} else {
			$dir = Directory::open(\App::getRealFilePath("app/Schema/Sql/"));

			foreach ($dir['files'] as $file) {
				$this->setResult("{$file} needs to be imported"); 
			}
		}

		parent::output();

	}

}