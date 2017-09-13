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
    
    Download::attachment("my_file.zip", "myCustomDownloadFile-0001");
*/

namespace rdks\core\libs\Files;
 
class Download{

    const SALT_STRING = ":rdks";
 
    private static function contentType($file_name){
 
        if(strpos($file_name,".") !== false){
 
            $ext = explode(".",$file_name);
            $totals = count($ext);
            $type = ($totals > 2 ? $ext[$totals-1] : $ext[1]);
 
            return strtolower($type);
 
        }
 
        return false;
 
    }
 
    private static function randId(){
        return md5( time() . self::SALT_STRING);
    }
 
    public static function attachment($file_name, $download_name = ""){

        if(!file_exists($file_name)){
            header("HTTP/1.1 404 Not Found");
            die("File Not Found.");
        }

        $type = self::contentType($file_name);
        $randId = self::randId();

        if($type !== FALSE){
 
            switch($type){
     
                // ZIP or RAR
                case 'zip':
                case 'rar':

                    $file = (!empty($download_name)) ? $download_name : "FILE-" . $randId;
     
                    header ("Content-Type: application/octet-stream");
                    header ("Accept-Ranges: bytes");
                    header ("Content-Length: ".filesize($file_name));
                    header ("Content-Disposition: attachment; filename=". $file . '.' . $type);           
     
                break;
     
                // PDF
                case 'pdf':
                    
                    $file = (!empty($download_name)) ? $download_name : "PDF-" . $randId;

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename='. $file . '.' . $type);
     
                break;
     
                // IMAGE
                case 'jpeg':
                case 'jpg':
                case 'png':
                case 'gif':

                    $file = (!empty($download_name)) ? $download_name : "PDF-" . $randId;
     
                    header('Content-Type: image/' . ($type == 'jpg' ? 'jpeg' : $type));
                    header('Content-Disposition: inline; filename=' . $file . '.' . $type);
     
                break;
                case 'csv':

                    $file = (!empty($download_name)) ? $download_name : "CSV-" . $randId;

                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename='. $file . '.' . $type);
  
                break;
            }
     
            readfile($file_name);
 
        }

    }
 
}
 
?>