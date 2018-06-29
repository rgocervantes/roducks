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

    $dir = Path::getData("csv/");
    Directory::make($dir);
     
    $csv = new CSV("sample");
    $csv->path($dir);
     
    // HEADERS
    $csv->headers([
        "ID",
        "TITLE",
        "DESC"
    ]);
     
    // ROWS
    for($i = 1; $i<=10; $i++) :
        $csv->row([
            $i,
            "Lorem Ipsum",
            "This is an example"
        ]);
    endfor;
     
    // SAVE & DOWNLOAD
    $csv->save();
    $csv->download();

*/

namespace Roducks\Libs\Output;   

class CSV
{

    private $_delimiter = ',';
    private $_handle;
    private $_length = 1000;
    private $_doc;
    private $_path;
    private $_file;
    private $_rows = '';

    private function _escape($fields)
    {
        $fill = [];
 
        foreach ($fields as $f) :
            $fill[] = '"' . utf8_encode($f) . '"';
        endforeach;
 
        return implode($this->_delimiter, $fill);
    }

    static private function _ext($str)
    {
        $ext = ".csv";
        if (!preg_match('/\.csv$/', $str)) return $str . $ext;

        return $str;
    }

    public function __construct($name)
    {
        $this->_doc = self::_ext($name);
    }

    /**
    *   Set your own delimiter, by default is separated by commas
    */
    public function delimiter($s)
    {
        $this->_delimiter = $s;
    }    

    public function path($path)
    {
        $this->_file = $path . $this->_doc;
    }

    public function row(array $rows = [])
    {
        $raw = '';
 
        if (is_array($rows) && count($rows) > 0) :
            $raw .= $this->_escape($rows);
        endif;
 
        $this->_rows .= $raw . "\n";
    }

    public function headers($obj)
    {
        $this->_rows .= $this->row($obj);
    }

    /*------------ SAVE CSV --------------*/
    public function save()
    {
        $csv_file = fopen($this->_file,"w");
                    fwrite($csv_file,$this->_rows);
                    fclose($csv_file);
    }
 
    /*------------ EXPORT CSV ------------*/
    public function download()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$this->_doc.'"');
        echo $this->_rows;
    }

    /*
        # READ CSV

        $csv = new CSV("sample_table");
        $csv->path(Path::get("app/Schema/Data/"));
        
        if ($csv->read()) {

            while (($row = $csv->fetch()) !== FALSE) {
                Helper::pre($row);
            }

            $csv->stop();

        }

    */

    /**
    *   Set length
    */
    public function length($n)
    {
        $this->_length = $n;
    }

    public function read()
    {

        if (file_exists($this->_file)) { // read
            if (($this->_handle = fopen($this->_file, "r")) !== FALSE) {
                return true;
            }
        }

        return false;
    }

    /**
    *   Get row
    *   @return object 
    */
    public function fetch()
    {
        return fgetcsv($this->_handle, $this->_length, $this->_delimiter);
    } 

    /**
    *   Close the file
    */
    public function stop()
    {
        fclose($this->_handle);
    }

}
