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

class XML extends Frame
{

	private $_xmlName = '';

	protected $doc;
	protected $readable = true;
	protected $overwrite = false;
	protected $path = 'xml/';
	protected $file = '';
	protected $NS = [];
	protected $root = 'xml';
	protected $rootNS = '';

	public function __construct(array $settings = [])
	{

		parent::__construct($settings);

		$overwrite = ($settings['method'] == 'overwrite' || $this->overwrite);

		if ($overwrite) {
			Directory::make(Path::getData($this->path));
		}

		$this->doc = XMLDoc::init();

		if (!empty($this->file)) {
			$this->_xmlName = Path::getData("{$this->path}{$this->file}");

			if ($settings['method'] != 'read') {
				$this->doc->file($this->_xmlName);
			}
		}

		switch ($settings['method']) {
			case 'write':
			case 'overwrite':
			case 'output':

				if (!empty($this->rootNS)) {
					$this->doc->rootNS($this->rootNS);
				}

				if ($overwrite) {
					$this->doc->overwrite();
				}

				$this->doc->root($this->root, $this->NS);

				break;
			case 'parse':
				$this->doc->load();

				if ($overwrite) {
					$this->doc->overwrite();
				}

				break;	
		}

	}

	public function read()
	{
		if ($this->readable) {
			$this->doc->read($this->_xmlName);
		} else {
			echo "XML is not able to be shown.";
		}
	}

}