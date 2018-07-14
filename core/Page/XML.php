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

namespace Roducks\Page;

use Roducks\Libs\Output\XML as XMLDoc;
use Lib\Directory;
use Path;
use Helper;

class XML extends Frame
{

	protected $fileName = '';
	protected $xml;
	protected $readable = true;
	protected $overwrite = false;
	protected $path = 'xml/';
	protected $name = '';
	protected $NS = [];
	protected $root = 'xml';

	public function __construct(array $settings)
	{
		parent::__construct($settings);

		$overwrite = ($settings['method'] == 'overwrite' || $this->overwrite);

		if ($overwrite || $settings['method'] == 'write') {
			Directory::make(Path::getData($this->path));
		}

		$this->xml = XMLDoc::init();

		if (empty($this->name)) {
			$this->name = Helper::getConventionName(Helper::getClassName(get_called_class()), '_');
		}

		if (!empty($this->name)) {
			$this->fileName = Path::getData("{$this->path}{$this->name}");

			if ($settings['method'] != 'read') {
				$this->xml->file($this->fileName);
			}

			if (!$this->overwrite && $this->xml->exists() && $settings['method'] == 'preview') {
				$overwrite = true;
			}

		}

		switch ($settings['method']) {
			case 'write':
			case 'overwrite':
			case 'preview':

				if ($overwrite) {
					$this->xml->overwrite();
				}

				$this->xml->root($this->root, $this->NS);

				break;
		}

	}

	public function read()
	{
		if ($this->readable) {
			$this->xml->read();
		} else {
			echo "XML is not able to be shown.";
		}
	}

	protected function output()
	{
		switch ($this->pageObj->method) {
			case 'write':
			case 'overwrite':

				$this->xml->save();

				break;
		}

		$this->xml->output();
	}

}
