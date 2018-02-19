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

abstract class Setup extends Cli
{

	private $_query;

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

	public function finished($script)
	{

		$success = null;
		$error = null;

		if (DB::success()) {
			$success = "{$script} setup is finished!";
			$this->saved($script, 'php');
		} else {
			DB::reset();
			$error = "{$script} setup failed! =(";
		}
		
		return [
			'success' => $success,
			'error' => $error
		];
		
	}

}