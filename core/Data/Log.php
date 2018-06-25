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

namespace Roducks\Data;

use Roducks\Framework\Data;
use Roducks\Libs\Utils\Date;
use Roducks\Libs\Output\XML;

class Log extends Data
{

	private $_dataId;

	static function getFileName($id)
	{
		return "user_{$id}";
	}

	static function getFilePath($date)
	{
		$d = explode("-", $date);
		return "log/users/{$d[0]}/{$d[1]}/{$d[2]}/";
	}

	public function __construct($id)
	{
		$date = Date::getCurrentDate();
		$this->_dataId = $id;
		$this->_filePath = self::getFilePath($date);
		$this->_fileName = self::getFileName($id, $date);
	
		parent::__construct();
	}

	public function getContent($date)
	{

		$fileName = self::getFilePath($date) . self::getFileName($this->_dataId);

		$xml = new XML;
		$xml->file($fileName);
		$data = $xml->content()->children();

		return $data;
	}

}