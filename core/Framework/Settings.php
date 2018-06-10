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

class Settings extends EAV
{

	const ENTITY = "_settings";

	public function __construct()
	{
			
		$this->_id = 0;
		$this->_entity = self::ENTITY;
		
		parent::__construct();
	}
	
	static function getPageTitle()
	{
		return PAGE_TITLE;
	} 

	static function getEmailFrom()
	{
		return EMAIL_FROM;
	}

	static function getEmailTo()
	{
		return EMAIL_TO;
	} 

	static function getLogoImage()
	{
		return LOGO_IMAGE;
	}

}