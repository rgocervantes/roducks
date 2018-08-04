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

namespace App\Schema\Setup;

use Roducks\Framework\Setup;
use Roducks\Interfaces\SetupInterface;
use Roducks\Libs\ORM\DB;

class Setup_2017_09_02_Rod extends Setup implements SetupInterface
{

	const COMMENTS = "Create Sample Table and insert some values.";

	public function schema(\mysqli $db)
	{

		DB::dropTable($db, 'Sample');

		DB::createTable($db, 'Sample', function ($table) {

			$table->id('id_sample');

			$table->bigint8('id_rel', function ($attrs) {
				$attrs->nullable = false;
			});

			$table->varchar('entity', function ($attrs) {
				$attrs->nullable = false;
			});

			$table->int('age', function ($attrs) {
				$attrs->nullable = false;
			});

			$table->enum('category', function ($attrs) {
				$attrs->value = ['abc','xyz'];
				$attrs->nullable = false;
				$attrs->comment = "Lorem ipsum";
			});

			$table->decimal('price', function ($attrs) {
				$attrs->value = [14, 2];
				$attrs->nullable = false;
				$attrs->comment = "Price Tag";
			});

			$table->tinyint('active', function ($attrs) {
				$attrs->value = 1;
				$attrs->default = 1;
			});

			$table->timestamps();

			$table->index('entity');

		});

	}

	public function data(\mysqli $db)
	{

		DB::truncateTable($db, 'Sample');

		Setup::fillTableFromCSV($db, 'Sample', "Fill_SampleTable");

	}

}
