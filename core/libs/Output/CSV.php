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
    $csv->file("my_csv_example");
    
    $txt = ''; // important to use a variable to append data     

    # HEADERS
    $txt .= $csv->headers(array("ID",
                            "TITLE",
                            "DESC"
                            ));

    # ROWS
    for($i = 1; $i<=10; $i++):
        $txt .= $csv->row(array($i,
                                "Lorem Ipsum",
                                "This is an example"
                            ));
    endfor;

    # SAVE & EXPORT
    $csv->save(DIR_UPLOADS, $txt); 
    $csv->download($txt); 

*/

namespace rdks\core\libs\Output;   

class CSV{

    protected $_delimiter = ',';
    protected $handle;
    protected $procced = false;
    protected $_length = 1000;
    protected $doc;
    protected $path;

    private function escape($fields){
        $fill = array();
 
        foreach($fields as $f):
            $fill[] = '"' . utf8_encode($f) . '"';
        endforeach;
 
        return implode($this->_delimiter, $fill);
    }

    protected function ext($str){
        $ext = ".csv";
        $name = substr($str, -4);
        if($name != $ext) return $str . $ext;

        return $str;
    }

    /**
    *   Set your own delimiter, by default is separated by commas
    */
    public function delimiter($s){
        $this->_delimiter = $s;
    }    

    public function file($path, $name){   

        $this->doc = $this->ext($name);
        $this->path = $path;

        $csv = $this->path . $this->doc;

        if(file_exists($csv)){ // read
            if (($this->handle = fopen($csv, "r")) !== FALSE) {
               return true;
            }
        }

        return false;
    }       

    public function headers($obj){
        return $this->row($obj);
    }

    public function row($rows = array()){
        $raw = '';
 
        if(is_array($rows) && count($rows) > 0):
            $raw .= $this->escape($rows);
        endif;
 
        return $raw . "\n";
    }

    /*------------ SAVE CSV --------------*/
    public function save($report){
        $csv_file = fopen($this->path . $this->doc,"w");
                    fwrite($csv_file,$report);
                    fclose($csv_file);
    }
 
    /*------------ EXPORT CSV ------------*/
    public function download($report){
        header('Content-type: text/csv');
        header('Content-disposition: attachment; filename="'.$this->doc.'"');
        echo $report;
    }

    /*
        # READ CSV

        $csv = new CSV();
        $csv->file(DIR_TMP . "profiles_");
        
            while (($data = $csv->fetch()) !== FALSE) {
                $d = $csv->columns($data);
                echo implode(" - ", $d) . "<br />";
            }

            $csv->stop();

    */

    /**
    *   Set length
    */
    public function length($n){
        $this->_length = $n;
    }    

    /**
    *   Close the file
    */
    public function stop(){
        fclose($this->handle);
    }

    /**
    *   Get row
    *   @return object 
    */
    public function fetch(){
        return fgetcsv($this->handle, $this->_length, $this->_delimiter);
    }    
 
}


?>