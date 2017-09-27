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

define('DIR_CONFIG', "config/"); 
define('DIR_EMAILS', "emails/");
define('DIR_MODULES', "modules/");
define('DIR_BLOCKS', "blocks/");
define('DIR_SERVICES', "services/");
define('DIR_EVENTS', "events/");
define('DIR_MENUS', "menus/");
define('DIR_XMLS', "xmls/");
define('DIR_MODELS', "models/");		
define('DIR_VIEWS', "views/");
define('DIR_TEMPLATES', "templates/");
define('DIR_LAYOUTS', "layouts/");
define('DIR_IMAGES', "images/");

define('DIR_APP', "app/");
	define('DIR_APP_CONFIG', DIR_APP . DIR_CONFIG);
	define('DIR_APP_DATA', DIR_APP . "data/");
	define('DIR_APP_LANGUAGES', DIR_APP . "lang/");
	define('DIR_APP_SITES', DIR_APP . "sites/");
	
# STORAGE
define('DIR_DATA_STORAGE', DIR_APP_DATA . "storage/");
	define('DIR_DATA_STORAGE_CSV', DIR_DATA_STORAGE . "csv/");					
	define('DIR_DATA_STORAGE_IMAGES', DIR_DATA_STORAGE . "images/");	
	define('DIR_DATA_STORAGE_PDF', DIR_DATA_STORAGE . "pdf/");	
	define('DIR_DATA_STORAGE_ZIP', DIR_DATA_STORAGE . "zip/");
	define('DIR_DATA_STORAGE_JSON', DIR_DATA_STORAGE . "json/");
	define('DIR_DATA_STORAGE_XML', DIR_DATA_STORAGE . "xml/");
		
# TMP 		
define('DIR_DATA_TMP', DIR_APP_DATA . "tmp/");
	define('DIR_DATA_TMP_CSV', DIR_DATA_TMP . "csv/");					
	define('DIR_DATA_TMP_IMAGES', DIR_DATA_TMP . "images/");	
	define('DIR_DATA_TMP_PDF', DIR_DATA_TMP . "pdf/");	
	define('DIR_DATA_TMP_ZIP', DIR_DATA_TMP . "zip/");	
	define('DIR_DATA_TMP_JSON', DIR_DATA_TMP . "json/");
	define('DIR_DATA_TMP_XML', DIR_DATA_TMP . "xml/");

# UPLOADS
define('DIR_DATA_UPLOADS', DIR_APP_DATA . "uploads/");
	define('DIR_DATA_UPLOADS_CSV', DIR_DATA_UPLOADS . "csv/");
	define('DIR_DATA_UPLOADS_IMAGES', DIR_DATA_UPLOADS . "images/");
		define('DIR_DATA_UPLOADS_USERS', DIR_DATA_UPLOADS_IMAGES . "users/");
	define('DIR_DATA_UPLOADS_PDF', DIR_DATA_UPLOADS . "pdf/");
	define('DIR_DATA_UPLOADS_ZIP', DIR_DATA_UPLOADS . "zip/");

# UPLOADED
define('DIR_DATA_UPLOADED', "/static/");
define('DIR_DATA_UPLOADED_CSV', DIR_DATA_UPLOADED . "csv/");
define('DIR_DATA_UPLOADED_IMAGES', DIR_DATA_UPLOADED . DIR_IMAGES);
	define('DIR_DATA_UPLOADED_USERS', DIR_DATA_UPLOADED_IMAGES . "users/");
define('DIR_DATA_UPLOADED_PDF', DIR_DATA_UPLOADED . "pdf/");
define('DIR_DATA_UPLOADED_ZIP', DIR_DATA_UPLOADED . "zip/");

define('DIR_ROLES', DIR_DATA_STORAGE_JSON . "roles/");

define('DIR_ASSETS', "/public/");
	define('DIR_ASSETS_IMAGES', DIR_ASSETS . DIR_IMAGES);
	define('DIR_ASSETS_ICONS', DIR_ASSETS . "icons/");
	define('DIR_ASSETS_CSS', DIR_ASSETS . "css/");
	define('DIR_ASSETS_JS', DIR_ASSETS . "js/");
	define('DIR_ASSETS_PLUGINS', DIR_ASSETS . "plugins/");
	define('DIR_ASSETS_SCRIPTS', DIR_APP . "scripts/");

	define('DIR_APP_IMAGES', "app" . DIR_ASSETS_IMAGES);
	define('DIR_APP_ICONS', "app" . DIR_ASSETS_ICONS);

define('DIR_CORE', "core/");
	define('DIR_CORE_CONFIG', DIR_CORE . DIR_CONFIG);
	define('DIR_CORE_PAGE', DIR_CORE . "page/");


