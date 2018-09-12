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

namespace DB\Models\Content;

use Model;

class PageTypes extends Model
{
  var $id = 'id_type';
  var $fields = [
    'title' => Model::TYPE_INTEGER,
    'name' => Model::TYPE_VARCHAR,
    'active' => Model::TYPE_INTEGER,
    'created_at' => Model::TYPE_DATETIME,
    'updated_at' => Model::TYPE_DATETIME,
    'deleted_at' => Model::TYPE_DATETIME,
  ];

  public function getList()
  {
    return $this->filter(['active' => 1]);
  }

  public function getByName($name)
  {
    return $this
    ->select([
      self::field('id_type', 'id'),
      self::field('title')
    ])
    ->filter(['name' => $name])
    ->fetch('object');

  }
}
