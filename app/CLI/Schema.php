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

namespace App\CLI;

use Roducks\Framework\Setup;
use Roducks\Libs\Files\Directory;
use Roducks\Libs\ORM\Query;

class Schema extends Setup
{

	private $_count = 0;

	static function _getFiles($folder)
	{
		$dir = Directory::open(\App::getRealFilePath("app/Schema/{$folder}/"));
		$files = $dir['files'];

		return $files;
	}

	private function _run($script, $run)
	{

		$class = "App\Schema\Setup\\" . $script;
		$dialog = ($run) ? 'success' : 'result';

		if (class_exists($class)) {
			$obj = new $class();
			if (method_exists($obj, 'schema')) {
				if ($run) $obj->schema($this->db());
				if (method_exists($obj, 'store')) { 
					if ($run) $obj->store($this->db());
				}
				$finished = $obj->finished($script, $run);

				if (!is_null($finished['success'])) {
					$this->$dialog($finished['success']);
				}

				if (!is_null($finished['error'])) {
					$this->error($finished['error']);
				}

			} else {
				self::println("Undefined method 'schema' -> {$class}::{$method}");
			}
		}

	}

	private function _search($file, $run = true)
	{

		if ($this->isUnsaved($file, 'php')) {
			$this->_run($file, $run);
		} else {
			$this->_count++;
		}

	}

	public function setup($script = null)
	{

		$prompt = true;

		if (!is_null($script)) {
			$this->_search($script);
			$total = 1;

			if ($total == $this->_count) {
				$this->info("Script is already set up.");
			}

		} else {

			$files = self::_getFiles("Setup");
			$total = count($files);

			foreach ($files as $file) {
				$file = str_replace(FILE_EXT, '', $file);
				$this->_search($file, false);
			}

			if ($total == $this->_count) {
				$prompt = false;
				$this->info("There are no scripts to set up.");
			}

		}

		parent::output();

		if ($prompt) {

			$this->prompt("Do you want to run these scripts?");

			if ($this->yes()) {

				foreach ($files as $file) {
					$file = str_replace(FILE_EXT, '', $file);
					$this->_search($file, true);
				}

			}

			parent::output();
		}

	}

	private function _sql($save = false, $loop = true)
	{

		$files = self::_getFiles("Sql");
		$total = count($files);
		$count = 0;
		$prompt = true;
		$saved = ($this->getFlag('--save') || $save);
		$label = "imported";

		foreach ($files as $file) {

			if ($file == "roducks.sql") {
				$total--;
				continue;
			}

			if ($this->isUnsaved($file, 'sql')) {

				if ($saved) {

					$this->saved($file, 'sql');
					$this->success("{$file} was {$label}!");

				} else {

					$this->info("{$file} needs to be {$label}");

				}
			} else {
				$count++;
			}

		}

		if ($total == $count) {
			$prompt = false;
			$this->info("There are no scripts to be {$label}.");
		}

		parent::output();

		if (!$this->getFlag('--save') && $loop && $prompt) {
			$this->promptYN("Did you already import these SQL scripts by your own?");

			if ($this->yes()) {
				$this->_sql(true, false);
			}

		}

	}

	public function sql()
	{
		$this->_sql(false);
	}

}