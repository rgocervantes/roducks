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

namespace Roducks\Blocks\Language;

use Roducks\Framework\URL;
use Roducks\Framework\Language as LanguageCore;
use Roducks\Page\Block;
use App\Models\Data\UrlsLang;

class Language extends Block{

	public function output($id_url, $tpl){

		$langs = LanguageCore::getList();
		$ret = [];
		$urls = [];
	
		if($id_url > 0){

			$db = $this->db();
			$query = UrlsLang::open($db);
			$query->filter(['id_url' => $id_url]);

			if($query->rows()){

				while($url = $query->fetch()){
					$id_lang = $url['id_lang'];
					$urls[ $id_lang ] = $url;
				}

				foreach($langs as $lang){
					$id = $lang['id'];
					if(isset($urls[ $id ])){
						$u = $urls[ $id ];
						$u = array_merge($u, $lang);
						$uri = (!empty($u['redirect'])) ? $u['redirect'] : $u['url'];
						$u['link'] = URL::lang($lang['iso'], false) . $uri;
						$ret[] = $u;	
					}	
				}
			}

		}else{
				
			foreach($langs as $lang){		
				$lang['link'] = URL::lang($lang['iso']);
				$ret[] = $lang;	
			}
		}

		$this->view->data('langs', $ret);
		$this->view->load($tpl);

		return $this->view->output();

	}

} 