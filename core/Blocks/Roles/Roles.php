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

namespace Roducks\Blocks\Roles;

use Roducks\Page\Block;
use Path;

class Roles extends Block
{

	var $config;
	var $type;

	public function modal()
	{

		$this->role();

		$name = $this->config . ".json";
		$config = $this->grantAccess->getFileConfig($name);

		if (isset($config['data']) && count($config['data']) == 0) {
			return $this->view->error('public', __METHOD__, "Invalid role config: " . Path::getRoles() . $name);
		}

		return $this->output($config['data'], $this->type, ["all" => 1]);
	}

	public function output($config, $type, $access)
	{

		$this->view->data('data', $config);
		$this->view->data('access', $access);
		$this->view->load("role-type-{$type}");
		return $this->view->output();
	}

}
