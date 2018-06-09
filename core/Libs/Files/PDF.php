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

namespace Roducks\Libs\Files;
 
class PDF
{
 
    private $pdf,
            $log = array();
 
    public function __construct($path, $name)
    {
        $this->pdf = $path . self::cleaner($name,'pdf') . ".pdf";
    }
 
    static function cleaner($name, $ext)
    {
        return preg_replace('/^(.+)\.'.$ext.'$/', '$1', $name);
    }
 
    public function getTotalPages()
    {
 
        if (!file_exists($this->pdf)) return 0;
 
        $pages = exec('/usr/bin/pdfinfo '.$this->pdf.' | grep Pages | awk \' {print ($NF--)}\'');
        return $pages;
    }
 
    public function makeImage($n = 0, $savePath = null, $saveName = "", $params = null, $isSecuencial = true)
    {
 
        if (is_null($savePath) || !file_exists($savePath)) return false;
 
        $size = "";
        $quality = 85;
        $density = 72; //dpi
 
        if (is_array($params)) {
            if (isset($params['resize'])) $size = "-resize ".$params['resize']." ";
            if (isset($params['quality'])) $quality = $params['quality'];
            if (isset($params['density'])) $density = $params['density'];
        }
 
        $secuencial = ($isSecuencial) ? $n : "";
        #exec('convert -colorspace RGB -define pdf:use-cropbox=true '.$size.'-quality '.$quality.' -density '.$density.' -antialias -strip -background white -flatten -normalize -units PixelsPerInch "'. $this->pdf.'['.$n.']" "'. $savePath . self::cleaner($saveName,"jpg") . $secuencial . ".jpg" .'"', $output, $return_var);//-brightness
        exec('convert -strip -interlace none -density '.$density.' -interpolate nearest-neighbor '.$size.'-quality '.$quality.' -colorspace RGB "'. $this->pdf.'['.$n.']" "'. $savePath . self::cleaner($saveName,"jpg") . $secuencial . ".jpg" .'"', $output, $return_var);//-brightness
        $this->log[] = $output;
    }   
 
    public function makeAllImages($savePath = null, $saveName = "", $resize = null)
    {
 
        if (is_null($savePath) || !file_exists($savePath)) return false;
 
        for($i = 0; $i<$this->getTotalPages(); $i++) {
            $this->makeImage($i, $savePath, $saveName, $resize);
        }
 
    }
 
    public function getLog()
    {
        return $this->log;
    }

}
