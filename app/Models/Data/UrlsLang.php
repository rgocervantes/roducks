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

namespace App\Models\Data;

use Roducks\Libs\ORM\Model;

class UrlsLang extends Model
{

	var $id = "id_url_lang";
	var $fields = [	
		'id_url'		 => Model::TYPE_INTEGER,
		'id_lang'		 => Model::TYPE_INTEGER,
		'url'			 => Model::TYPE_BLOB,
		'dispatch' 		 => Model::TYPE_VARCHAR,
		'title'	 		 => Model::TYPE_BLOB,
		'layout'		 => Model::TYPE_VARCHAR,
		'template'		 => Model::TYPE_VARCHAR,
		'pview'	 		 => Model::TYPE_VARCHAR,	
		'updated_date'	 => Model::TYPE_DATETIME				
	];

}