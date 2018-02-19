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
 *	php roducks schema:setup --pro Setup_2017_09_02_Rod
 *	php roducks schema:sql --pro
 *	php roducks schema:sql --pro --save
 */

namespace App\Cli;

use Roducks\Framework\Setup;
use Roducks\Libs\Files\Directory;
use Roducks\Libs\ORM\Query;

class Schema extends Setup
{

	protected $_params = [
		'script'
	];

	private $_count = 0;

	static function _getFiles($folder)
	{
		$dir = Directory::open(\App::getRealFilePath("app/Schema/{$folder}/"));
		$files = $dir['files'];

		return $files;
	}

	private function _run($script)
	{

		$class = "App\Schema\Setup\\" . $script;
		$method = "schema";

		if (class_exists($class)) {
			$obj = new $class();
			if(method_exists($obj, $method)){
				$obj->$method($this->db());
				$obj->store($this->db());
				$finished = $obj->finished($script);

				if (!is_null($finished['success'])) {
					$this->setResult($finished['success']);
				}

				if (!is_null($finished['error'])) {
					$this->setError($finished['error']);
				}

			} else {
				self::println("Undefined method 'schema' -> {$class}::{$method}");
			}
		}

	}

	private function _search($file)
	{

		if ($this->isUnsaved($file, 'php')) {
			$this->_run($file);
		} else {
			$this->_count++;
		}

	}

	public function setup()
	{

		$script = $this->getParam('script', null);

		if (!is_null($script)) {
			$this->_search($script);
			$total = 1;

			if ($total == $this->_count) {
				$this->setResult("Script is already set up.");
			}

		} else {

			$files = self::_getFiles("Setup");
			$total = count($files);

			foreach ($files as $file) {
				$file = str_replace(FILE_EXT, '', $file);
				$this->_search($file);
			}

			if ($total == $this->_count) {
				$this->setResult("There are no scripts to set up.");
			}

		}

		parent::output();

	}

	public function sql()
	{

		$files = self::_getFiles("Sql");
		$total = count($files);
		$count = 0;
		$label = ($this->getFlag('save')) ? "saved" : "imported";

		foreach ($files as $file) {

			if ($this->isUnsaved($file, 'sql')) {

				if ($this->getFlag('save')) {

					$this->saved($file, 'sql');
					$this->setResult("{$file} is {$label}!");

				} else {

					$this->setResult("{$file} needs to be {$label}");

				}
			} else {
				$count++;
			}

		}

		if ($total == $count) {
			$this->setResult("There are no scripts to be {$label}.");
		}

		parent::output();

	}

}