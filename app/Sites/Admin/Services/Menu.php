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

namespace App\Sites\Admin\Services;

use Roducks\Page\Service;

class Menu extends Service
{

  public function getMenu()
  {

    $menu = [];
    $model = $this->model('content/page-types')->getList();

    while ($row = $model->fetch('object')) {
      $menu[] = [
      		'link' => "/content/new/{$row->name}",
      		'text' => __("New {$row->title}"),
      		'access' => ["users","view"],
      		'icon' => 'file'
      ];
    }

    return $menu;

  }

}
