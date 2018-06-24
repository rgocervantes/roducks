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
 *	php roducks schema:setup --pro Setup_2017_09_02_Rod --run
 *	php roducks schema:sql --pro
 *	php roducks schema:sql --pro --save
 *	php roducks schema:sql --pro test.sql --save
 */

namespace App\CLI;

use Roducks\Framework\Setup;
use Roducks\Libs\Files\Directory;
use Roducks\Libs\ORM\Query;
use Helper;

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

		$class = "App\\Schema\\Setup\\" . $script;
		$dialog = ($run) ? 'success' : 'info';

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
		$files = self::_getFiles("Setup");
		$setup = Helper::ext($script, 'php');
		$run = $this->getFlag('--run');

		if (!is_null($script)) {
			if (in_array($setup, $files)) {
				$this->_search($script, $run);
				$total = 1;
				$files = [];
				array_push($files, $setup);

				if ($total == $this->_count) {
					$prompt = false;
					$this->warning("[*]{$script} is already set up.");
				} else {
					if (!$run) {
						$this->info("[*]{$script}");
					}
				}
			} else {
				$prompt = false;
				$this->error("[*]File: 'app/Schema/Setup/{$setup}' does not exist.");
			}

		} else {

			$total = count($files);

			foreach ($files as $file) {
				$file = str_replace(FILE_EXT, '', $file);
				$this->_search($file, $run);
			}

			if ($total == $this->_count) {
				$prompt = false;
				$this->info("There are no scripts to set up.");
			}

		}

		parent::output();

		if ($prompt && !$run) {

			$this->promptYN("Do you want to run these scripts?");

			if ($this->yes()) {

				foreach ($files as $file) {
					$file = str_replace(FILE_EXT, '', $file);
					$this->_search($file, true);
				}

			}

			parent::output();
		}

	}

	private function _sql($script, $save = false, $loop = true)
	{

		$files = self::_getFiles("Sql");
		$total = count($files);
		$count = 0;
		$prompt = true;
		$saved = ($this->getFlag('--save') || $save);
		$label = "imported";
		$message = "Did you already import these SQL scripts by your own?";

		if (is_null($script)) {
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
		} else {
			$total = 1;
			$count = 0;
			$script = Helper::ext($script, 'sql');
			$sql = "app/Schema/Sql/{$script}";

			if (in_array($script, $files)) {

				if ($this->isUnsaved($script, 'sql') ) {

					if ($saved) {
						$this->saved($script, 'sql');
						$this->success("File: {$sql} was {$label}!");
						$prompt = false;
					} else {
						$this->info("File: {$sql} needs to be {$label}");
						$message = "Did you already import this SQL script by your own?";
					}
				} else {
					$prompt = false;
					$this->warning("[*]File: {$sql} was already {$label}!");
				}

			} else {
				$prompt = false;
				$this->error("[*]File: {$sql} does not exist.");
			}

		}

		if ($total == $count) {
			$prompt = false;
			$this->info("There are no scripts to be {$label}.");
		}

		parent::output();

		if (!$this->getFlag('--save') && $loop && $prompt) {
			$this->promptYN($message);

			if ($this->yes()) {
				$this->_sql($script, true, false);
			}

		}

	}

	public function sql($script = null)
	{
		$this->_sql($script, false);
	}

}