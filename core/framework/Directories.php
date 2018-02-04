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

App::define('APP_NS', "App");
App::define('CORE_NS', "Roducks");

App::define('DIR_CONFIG', "Config/"); 
App::define('DIR_EMAILS', "Emails/");
App::define('DIR_MODULES', "Modules/");
App::define('DIR_BLOCKS', "Blocks/");
App::define('DIR_SERVICES', "Services/");
App::define('DIR_EVENTS', "Events/");
App::define('DIR_MENUS', "Menus/");
App::define('DIR_XMLS', "Xmls/");
App::define('DIR_MODELS', "Models/");		
App::define('DIR_VIEWS', "Views/");
App::define('DIR_TEMPLATES', "Templates/");
App::define('DIR_LAYOUTS', "Layouts/");
App::define('DIR_IMAGES', "images/");
App::define('DIR_ICONS', "icons/");

App::define('DIR_APP', "app/");
	App::define('DIR_APP_CONFIG', DIR_APP . DIR_CONFIG);
	App::define('DIR_APP_DATA', DIR_APP . "Data/");
	App::define('DIR_APP_LANGUAGES', DIR_APP . "Lang/");
	App::define('DIR_APP_SITES', DIR_APP . "Sites/");
	App::define('DIR_APP_LIBS', DIR_APP . "Libs/");

# STORAGE
App::define('DIR_DATA_STORAGE', DIR_APP_DATA . "storage/");
	App::define('DIR_DATA_STORAGE_CSV', DIR_DATA_STORAGE . "csv/");					
	App::define('DIR_DATA_STORAGE_IMAGES', DIR_DATA_STORAGE . DIR_IMAGES);	
	App::define('DIR_DATA_STORAGE_PDF', DIR_DATA_STORAGE . "pdf/");	
	App::define('DIR_DATA_STORAGE_ZIP', DIR_DATA_STORAGE . "zip/");
	App::define('DIR_DATA_STORAGE_JSON', DIR_DATA_STORAGE . "json/");
	App::define('DIR_DATA_STORAGE_XML', DIR_DATA_STORAGE . "xml/");
		
# TMP 		
App::define('DIR_DATA_TMP', DIR_APP_DATA . "tmp/");
	App::define('DIR_DATA_TMP_CSV', DIR_DATA_TMP . "csv/");					
	App::define('DIR_DATA_TMP_IMAGES', DIR_DATA_TMP . DIR_IMAGES);	
	App::define('DIR_DATA_TMP_PDF', DIR_DATA_TMP . "pdf/");	
	App::define('DIR_DATA_TMP_ZIP', DIR_DATA_TMP . "zip/");	
	App::define('DIR_DATA_TMP_JSON', DIR_DATA_TMP . "json/");
	App::define('DIR_DATA_TMP_XML', DIR_DATA_TMP . "xml/");

# UPLOADS
App::define('DIR_DATA_UPLOADS', DIR_APP_DATA . "uploads/");
	App::define('DIR_DATA_UPLOADS_CSV', DIR_DATA_UPLOADS . "csv/");
	App::define('DIR_DATA_UPLOADS_IMAGES', DIR_DATA_UPLOADS . DIR_IMAGES);
		App::define('DIR_DATA_UPLOADS_USERS', DIR_DATA_UPLOADS_IMAGES . "users/");
	App::define('DIR_DATA_UPLOADS_PDF', DIR_DATA_UPLOADS . "pdf/");
	App::define('DIR_DATA_UPLOADS_ZIP', DIR_DATA_UPLOADS . "zip/");

# UPLOADED
App::define('DIR_DATA_UPLOADED', "/static/");
App::define('DIR_DATA_UPLOADED_CSV', DIR_DATA_UPLOADED . "csv/");
App::define('DIR_DATA_UPLOADED_IMAGES', DIR_DATA_UPLOADED . DIR_IMAGES);
	App::define('DIR_DATA_UPLOADED_USERS', DIR_DATA_UPLOADED_IMAGES . "users/");
App::define('DIR_DATA_UPLOADED_PDF', DIR_DATA_UPLOADED . "pdf/");
App::define('DIR_DATA_UPLOADED_ZIP', DIR_DATA_UPLOADED . "zip/");

App::define('DIR_ROLES', DIR_DATA_STORAGE_JSON . "roles/");

App::define('DIR_ASSETS', "assets/");
App::define('DIR_PUBLIC', "public/");
	App::define('DIR_ASSETS_IMAGES', "/" . DIR_PUBLIC . DIR_IMAGES);
	App::define('DIR_ASSETS_ICONS', "/" . DIR_PUBLIC . DIR_ICONS);
	App::define('DIR_ASSETS_CSS', "/" . DIR_PUBLIC . "css/");
	App::define('DIR_ASSETS_JS', "/" . DIR_PUBLIC . "js/");
	App::define('DIR_ASSETS_PLUGINS', "/" . DIR_PUBLIC . "plugins/");
	App::define('DIR_ASSETS_SCRIPTS', DIR_APP . "Scripts/");

	App::define('DIR_APP_IMAGES', DIR_PUBLIC . DIR_ASSETS . DIR_IMAGES );
	App::define('DIR_APP_ICONS', DIR_PUBLIC . DIR_ASSETS . DIR_ICONS );

App::define('DIR_CORE', "core/");
	App::define('DIR_CORE_CONFIG', DIR_CORE . DIR_CONFIG);
	App::define('DIR_CORE_PAGE', DIR_CORE . "Page/");


