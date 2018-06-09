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

    # CREATE A CSV
    $csv = new CSV();
    $csv->file(DIR_UPLOADS, "my_csv_example");
    
    $txt = ''; // important to use a variable to append data     

    # HEADERS
    $txt .= $csv->headers(array("ID",
                            "TITLE",
                            "DESC"
                            ));

    # ROWS
    for($i = 1; $i<=10; $i++) :
        $txt .= $csv->row(array($i,
                                "Lorem Ipsum",
                                "This is an example"
                            ));
    endfor;

    # SAVE & EXPORT
    $csv->save($txt); 
    $csv->download($txt); 

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

    private function escape($fields)
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

    /**
    *   Set your own delimiter, by default is separated by commas
    */
    public function delimiter($s)
    {
        $this->_delimiter = $s;
    }    

    public function file($path, $name)
    {
        $this->_doc = self::_ext($name);
        $this->_file = $path . $this->_doc;
    }

    public function row(array $rows = [])
    {
        $raw = '';
 
        if (is_array($rows) && count($rows) > 0) :
            $raw .= $this->escape($rows);
        endif;
 
        return $raw . "\n";
    }

    public function headers($obj)
    {
        return $this->row($obj);
    }

    /*------------ SAVE CSV --------------*/
    public function save($report)
    {
        $csv_file = fopen($this->_file,"w");
                    fwrite($csv_file,$report);
                    fclose($csv_file);
    }
 
    /*------------ EXPORT CSV ------------*/
    public function download($report)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$this->_doc.'"');
        echo $report;
    }

    /*
        # READ CSV

        $csv = new CSV();
        $csv->file(\App::getRealFilePath("app/Schema/Data/"), "sample_table");
        
        if ($csv->read()) {

            while (($data = $csv->fetch()) !== FALSE) {
                Helper::pre($data);
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
