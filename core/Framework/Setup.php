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

namespace Roducks\Framework;

use Roducks\Libs\ORM\DB;
use Roducks\Libs\ORM\Query;
use Roducks\Libs\Output\CSV;

abstract class Setup extends CLI
{

	const COMMENTS = "";
	private $_query;

	static private function _getCSV($file)
	{
		$csv = new CSV($file);
		$csv->path(Path::get(DIR_SCHEMA_DATA));

		return $csv;
	}

	static function fillTableFromCSV($db, $tbl, $file)
	{

		$csv = self::_getCSV($file);

		if ($csv->read()) {

			DB::insertInto($db, $tbl, function ($table) use ($csv) {

				$c = 0;
				$headers = [];

				while (($data = $csv->fetch()) !== FALSE) {

					if ($c == 0) {
						$headers = $data;
					} else {

						$table->values(function () use ($table, $headers, $data) {
							for ($i=0; $i < count($headers); $i++) {
								$table->column($headers[$i], $data[$i]);
							}
						});

					}

					$c++;
				}

				$csv->stop();

			});

		}

	}

	static function updateTableFromCSV($db, $tbl, $file)
	{

		$query = new Query($db, $tbl);
		$csv = self::_getCSV($file);
		$headers = [];

		if ($csv->read()) {

			$c = 0;

			while (($data = $csv->fetch()) !== FALSE) {

				if ($c == 0) {
					$headers = $data;
				} else {
					$row = [];
					$key = $headers[0];
					$value = $data[0];

					for ($i=0; $i < count($headers); $i++) {
						if ($i > 0) {
							$row[$headers[$i]] = $data[$i];
						}
					}

					$tx = $query->update([$key => $value], $row);
					DB::transaction($tx);
				}

				$c++;

			}

			$csv->stop();

		}
	}

	static function deleteTableFromCSV($db, $tbl, $file)
	{

		$query = new Query($db, $tbl);
		$csv = self::_getCSV($file);
		$headers = [];

		if ($csv->read()) {

			$c = 0;

			while (($data = $csv->fetch()) !== FALSE) {

				if ($c == 0) {
					$headers = $data;
				} else {

					$key = $headers[0];
					$value = $data[0];

					$tx = $query->delete([$key => $value]);
					DB::transaction($tx);
				}

				$c++;
			}

			$csv->stop();

		}

	}

	public function __construct(array $args = [])
	{

		parent::__construct($args);

		$db = $this->db();
		$this->_query = new Query($db, 'Setup');
	}

	protected function isUnsaved($file, $type)
	{

		$this->_query->filter(['file' => $file, 'type' => $type]);

		return (!$this->_query->rows()) ? true : false;
	}

	protected function saved($file, $type)
	{

		$this->_query->insert([
			'file' => $file,
			'type' => $type,
			'executed_at' => Query::now()
		]);
	}

	public function finished($script, $run)
	{

		$success = null;
		$error = null;

		if (DB::success()) {
			$success = "{$script} - " . static::COMMENTS;
			if ($run) $this->saved($script, 'php');
		} else {
			DB::reset();
			$error = "{$script} failed!";
		}

		return [
			'success' => $success,
			'error' => $error
		];

	}

}
