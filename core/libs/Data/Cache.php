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
/*
	USAGE:
	
	$cache = Cache::init(['123.4.5.1'],11211);
	$cacheName = "myCacheName";
	$xmlResponse = $cache->get($cacheName);

	if($xmlResponse === false) {
		$xmlUrl = "http://example.org/data.xml";
	  $xmlResponse = file_get_contents($xmlUrl) or die("Error");
	  $cache->set($cacheName, $xmlResponse, Cache::expirationInMinutes(5)); // 5 minutes
	}
	 
	echo $xmlResponse;   

*/
namespace rdks\core\libs\Data;


class Cache{

	static public $memcacheObj = NULL;
	static $servers = [];
	static $port = 0;
	
	static function init(array $servers = [],$port) {
		if (self::$memcacheObj == NULL) {
			if (class_exists('Memcached')) {
				self::$memcacheObj = new Memcached;
				self::$servers = $servers;
				self::$port = $port;
				foreach($servers as $server){
					self::$memcacheObj->addServer($server, $port);
				}
			} else {
				return false;
			}
		}
		return self::$memcacheObj;
	}

	static function getItems(){

		foreach (self::$servers as $server) {

			$items = self::cacheSlab(self::sendCommand($server,self::$port,"stats items"));
			$data = array();

			foreach ($items as $key => $value) {
				$vars = self::cacheItems(self::sendCommand($server,self::$port,"stats cachedump $key $value[number]"));
				foreach ($vars as $var) {
					$data[] = $var;
				}	
			}	

		}

		return $data;

	}

	static function removeItems(){
		$items = self::getItems();

		foreach ($items as $item) {
			self::$memcacheObj->delete($item);
		}
	}

	static function cacheSlab($arr){

		$data = [];

		foreach ($arr as $value) {
			if(preg_match('/^STAT items:(\d+):(\w+) (\d+)$/', $value, $matches)){
				$data[$matches[1]][$matches[2]] = $matches[3];  
			}
		}

		return $data;

	}

	static function cacheItems($arr){

		$data = [];

		foreach ($arr as $value) {
			if(preg_match('/^ITEM (\w+) \[.+\]$/', $value, $matches)){
				$data[] = $matches[1];
			}
		}

		return $data;

	}

	static function sendCommand($server,$port,$command){

		$s = @fsockopen($server,$port);
		if (!$s){
			die("Cant connect to:".$server.':'.$port);
		}

		fwrite($s, $command."\r\n");

		$buf='';
		while ((!feof($s))) {
			$buf .= fgets($s, 256);
			if (strpos($buf,"END\r\n")!==false){ // stat says end
			    break;
			}
			if (strpos($buf,"DELETED\r\n")!==false || strpos($buf,"NOT_FOUND\r\n")!==false){ // delete says these
			    break;
			}
			if (strpos($buf,"OK\r\n")!==false){ // flush_all says ok
			    break;
			}
		}
	    fclose($s);

	    $lines = explode("\r\n",$buf);

	    return $lines;
	}

	static function expirationInMinutes($minutes = 1){
		return time() + (60*$minutes);
	}

	static function expirationInHours($hours = 1){
		return self::expirationInMinutes(60*$hours);
	}	

	static function getCacheName($parent,$key, array $args = []){

		$params = [];
		$end = "";
		
		if(count($args) > 0){
			foreach ($args as $arg) {
				$params[] = str_replace("-","",$arg);
			}

			$end = "_" . implode("_", $params);
		}
		

		return $parent.$key.$end;
	}

}

?>