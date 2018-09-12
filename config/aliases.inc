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

return [
	/*
	|----------------------------------------------------------------------
	|	Framework
	|----------------------------------------------------------------------
	*/
	'Helper' 					=> 'Roducks\\Framework\\Helper',
	'URL' 						=> 'Roducks\\Framework\\URL',
	'Language' 				=> 'Roducks\\Framework\\Language',
	'Dispatch' 				=> 'Roducks\\Framework\\Dispatch',
	'Form' 						=> 'Roducks\\Framework\\Form',
	'Post' 						=> 'Roducks\\Framework\\Post',
	'Path' 						=> 'Roducks\\Framework\\Path',
	'Login' 					=> 'Roducks\\Framework\\Login',
	'Router' 					=> 'Roducks\\Framework\\Router',
	'Setup' 					=> 'Roducks\\Framework\\Setup',
	'Crypt\\Hash' 		=> 'Roducks\\Framework\\Hash',
	'API' 						=> 'Roducks\\API\\API',
	/*
	|----------------------------------------------------------------------
	|	Services
	|----------------------------------------------------------------------
	*/
	'Storage' 		=> 'Roducks\\Services\\Storage',
	/*
	|----------------------------------------------------------------------
	|	Page
	|----------------------------------------------------------------------
	*/
	'JSON' 				=> 'Roducks\\Page\\JSON',
	'Block' 			=> 'Roducks\\Page\\Block',
	'Template' 		=> 'Roducks\\Page\\Template',
	'Layout' 			=> 'Roducks\\Page\\Layout',
	/*
	|----------------------------------------------------------------------
	|	Libs
	|----------------------------------------------------------------------
	*/
	'Date' 						=> 'Roducks\\Libs\\Utils\\Date',
	'Request' 				=> 'Roducks\\Libs\\Request\\Request',
	'DB' 							=> 'Roducks\\Libs\\ORM\\DB',
	'Query' 					=> 'Roducks\\Libs\\ORM\\Query',
	'Model' 					=> 'Roducks\\Libs\\ORM\\Model',
	'Join' 						=> 'Roducks\\Libs\\ORM\\Join',
	'Lib\\Directory' 	=> 'Roducks\\Libs\\Files\\Directory',
	'Lib\\Download' 	=> 'Roducks\\Libs\\Files\\Download',
	'Lib\\File' 			=> 'Roducks\\Libs\\Files\\File',
	'Lib\\Image' 			=> 'Roducks\\Libs\\Files\\Image',
	'Lib\\Zip' 				=> 'Roducks\\Libs\\Files\\Zip',
	'Lib\\PDF' 				=> 'Roducks\\Libs\\Files\\PDF',
	'Lib\\CSV' 				=> 'Roducks\\Libs\\Output\\CSV',
	'Lib\\XML' 				=> 'Roducks\\Libs\\Output\\XML',
];
