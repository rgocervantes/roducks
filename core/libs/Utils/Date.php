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

namespace rdks\core\libs\Utils;
 
class Date{

    const REGEXP_DATE_YYYY_MM_DD = '/^(\d{4})-(\d{2})-(\d{2})$/';
    const REGEXP_DATE_DD_MM_YYYY = '/^(\d{2})-(\d{2})-(\d{4})$/';
    const REGEXP_DATETIME = '/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})$/';
    const DATE_NOT_VALID = "Invalid Date.";

    /*----------------------------*/
    /*---------- PRIVATE ---------*/
    /*----------------------------*/

    /**
     *  Function to add or substract
     */
    static private function convert($inc, $days, $p){
 
        $today = self::getCurrentDate();
        $ds = $inc . $days;
 
        return date('Y-m-d', strtotime("$today $ds $p"));
 
    }

    /**
     *   Go back to a day in the same week.
     */
    static private function reverse($day){
 
        $N = date('N');
 
        if($N > $day){
            $d = ($N - $day); 
 
            return self::convert('-',$d,'day');
        } else {
            return self::getCurrentDate();
        }
 
    } 

    static private function checkDate($d){
        return preg_match(self::REGEXP_DATE_YYYY_MM_DD, $d); 
    }

    /*----------------------------*/
    /*---------- PUBLIC ----------*/
    /*----------------------------*/
    static function matchDateTime($str){
        if(preg_match(self::REGEXP_DATETIME, $str, $matches)){
            return $matches;
        }   

        return false;
    }   

    static function extractDateTime($str, $index = 1){
        $date = self::matchDateTime($str);

        if($date !== false){
            return $date[$index];
        }   

        return $str;
    }

    static function extractDate($str){
        return self::extractDateTime($str, 1);
    }

    static function extractTime($str){
        return self::extractDateTime($str, 2);
    }   

    static function extractDatePart($str, $index){
        $d = self::getMatches(self::VALID_DATE_YYYY_MM_DD, $str);

        return $d[$index];
    }

    static function convertToDMY($date, $sep = "-"){
        if(preg_match(self::REGEXP_DATE_YYYY_MM_DD, $date, $d)){
           return implode($sep,array($d[3],$d[2],$d[1]));
        }

        return $date;
    }

    static function convertToYMD($date, $sep = "-"){
        if(preg_match(self::REGEXP_DATE_DD_MM_YYYY, $date, $d)){
           return implode($sep,array($d[3],$d[2],$d[1]));
        }

        return $date;
    } 

    static function getEmptyDate(){
        return "0000-00-00";
    }

    static function getFormatYMD($sep = "-"){
        return implode($sep, ["yyyy","mm","dd"]);
    }

    static function getFormatDMY($sep = "-"){
        return implode($sep, ["dd","mm","yyyy"]);
    }

    static function getFlatDate($str){
        return str_replace(array("-","/",":"," "), "", $str);
    }

    static function getCurrentDate($sep = "-", $ymd = true){
        if($ymd){
            return date(implode($sep,['Y','m','d']));
        }

        return date(implode($sep,['d','m','Y']));
    }

    static function getDateArray($dateStr){
        $d = explode("-", $dateStr);

        return [
            'y' => intval($d[0]),
            'm' => intval($d[1]),
            'd' => intval($d[2])
        ];
    }

    static function getCurrentDateArray(){
        return self::getDateArray(self::getCurrentDate());
    }

    static function getCurrentDateFlat(){
        return self::getCurrentDate("");
    }
 
    static function getCurrentDateTime(){
        return date('Y-m-d H:i:s');
    }       
 
    static function getCurrentTime(){
        return date('H:i:s');
    }  

    static function getCurrentTimeStamp(){
        return time();
    }      

    static function getCurrentYear(){
        return date('Y');
    } 

    static function getCurrentMonth(){
        return date('m');
    } 

    static function getCurrentDay(){
        return date('d');
    }    

    static function getCurrentWeek(){
        return date('D');
    }         

    static function getCurrentHour(){
        return date('H');
    } 

    static function getCurrentMinute(){
        return date('i');
    } 

    static function getCurrentSecond(){
        return date('s');
    }     

    static function getDate($timestamp){
        return date('Y-m-d', $timestamp);
    }

    static function getDateTime($timestamp){
        return date('Y-m-d H:i:s', $timestamp);
    }

    static function getTime($timestamp){
        return date('H:i:s', $timestamp);
    }    

    static function getWeekDays($lg = 'en')
    {
        $days = array();
        $days['es'] = array('Sun' => 'Domingo',
                            'Mon' => 'Lunes',
                            'Tue' => 'Martes',
                            'Wed' => 'Miércoles',
                            'Thu' => 'Jueves',
                            'Fri' => 'Viernes',
                            'Sat' => 'Sábado'
                            );  
                            
        $days['en'] = array('Sun' => 'Sunday',
                            'Mon' => 'Monday',
                            'Tue' => 'Tuesday',
                            'Wed' => 'Wednesday',
                            'Thu' => 'Thursday',
                            'Fri' => 'Friday',
                            'Sat' => 'Saturday'
                            );
        
        return $days[$lg];
    }

    static public function getMonths($lg = "en")
    {
        $months = array();
        $months['es'] = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');    
        $months['en'] = array('January','February','March','April','May','June','July','August','September','October','November','December');
        
        return $months[$lg];
    }

    static function getDay($date){
        return date('d', strtotime($date));
    }

    static function getMonthDay($date, $lg = "en"){
        $m = date('m', strtotime($date));
        $months = self::getMonths($lg);

        return $months[$m-1];

    }

    static function getCurrentMonthDay($lg = "en"){
        $m = self::getCurrentMonth();
        $months = self::getMonths($lg);

        return $months[$m-1];

    }

    # Ex. March (3)
    static function getMonthLabel($m, $lg = "en"){
        $months = self::getMonths($lg);

        return $months[$m-1];       
    }

    # Ex. Thursday
    static function getWeekDay($date, $lg = "en"){

        if(!self::checkDate($date)) return self::DATE_NOT_VALID;
        
        $y = date('D', strtotime($date));

        $days = self::getWeekDays($lg);  

        return $days[$y];

    }

    static function getCurrentWeekDay($lg = "en"){
        $D = self::getCurrentWeek();

        $days = self::getWeekDays($lg);  

        return $days[$D];        
    }

    # Ex. June 19th of 2014
    static function getDateFormat($date, $lang = "en", $mode = true){

        if(!self::checkDate($date)) return self::DATE_NOT_VALID;
 
        $args = explode("-",$date);
     
        $months = array();
        $months['es'] = array();
        $months['en'] = array();
     
        $months['es']['01'] = "Enero";
        $months['es']['02'] = "Febrero";
        $months['es']['03'] = "Marzo";
        $months['es']['04'] = "Abril";
        $months['es']['05'] = "Mayo";
        $months['es']['06'] = "Junio";
        $months['es']['07'] = "Julio";
        $months['es']['08'] = "Agosto";
        $months['es']['09'] = "Septiembre";
        $months['es']['10'] = "Octubre";
        $months['es']['11'] = "Noviembre";
        $months['es']['12'] = "Diciembre";
     
        $months['en']['01'] = "January";
        $months['en']['02'] = "February";
        $months['en']['03'] = "March";
        $months['en']['04'] = "April";
        $months['en']['05'] = "May";
        $months['en']['06'] = "June";
        $months['en']['07'] = "July";
        $months['en']['08'] = "August";
        $months['en']['09'] = "September";
        $months['en']['10'] = "October";
        $months['en']['11'] = "November";
        $months['en']['12'] = "December";
     
        $day = (substr($args[2], 0,1) == 0) ? substr($args[2], 1,1) : $args[2];
        $month = $months[$lang][ $args[1] ];
     
        if(!$mode) return $args;
     
        switch($lang){
            case 'es':
                return $day . " de " . $month . " de " . $args[0];
            break;
            case 'en':
     
                $digit = substr($day, -1, 1);
               if($day != 11 && $day != 12){
                    switch ($digit) {
                        case '1':
                            $th = 'st';
                            break;
                        case '2':
                            $th = 'nd';
                            break;
                        case '3':
                            $th = 'rd';
                        break;
                        default:
                            $th = 'th';
                            break;
                    }                
                }else{
                    $th = 'th';
                }

                return  $month . " " . $day . $th . " of " . $args[0];
            break;
        }
     
    }

    # Ex. Thursday June 19th of 2014
    static function getDateFormatLong($date, $lg = "en", $mode = true)
    {
        if(!self::checkDate($date)) return self::DATE_NOT_VALID;

        $w = self::getWeekDay($date, $lg);
        $f = self::getDateFormat($date, $lg, $mode);
        
        if($mode):
            return  $w . ', ' . $f;
        else:
            return array('week' => $w,
                        'date' => $f);
        endif;    
    }       

    static function getCurrentDateFormat($lg = "en", $mode = true){
        return self::getDateFormat(self::getCurrentDate(), $lg, $mode);
    } 

    static function getCurrentDateFormatLong($lg = "en", $mode = true){
        return self::getDateFormatLong(self::getCurrentDate(), $lg, $mode);
    }         

    static function addHours($date, $hrs){
        return date("H:i:s", strtotime("$date +$hrs hour"));
    }

    static function addHoursToCurrentDate($hrs){
        return self::addHours(self::getCurrentDate(), $hrs);
    }

    static function subtractHours($date, $hrs){
        return date("H:i:s", strtotime("$date -$hrs hour"));
    }

    static function subtractHoursToCurrentDate($hrs){
        return self::subtractHours(self::getCurrentDate(), $hrs);
    }    

    static function addDays($date, $ds){
        return date("Y-m-d", strtotime("$date +$ds day"));
    }

    static function addDaysToCurrentDate($ds){
        return self::addDays(self::getCurrentDate(), $ds);
    }

    static function subtractDays($date, $ds){
        return date("Y-m-d", strtotime("$date -$ds day"));
    } 

    static function subtractDaysToCurrentDate($ds){
        return self::subtractDays(self::getCurrentDate(), $ds);
    }

    static function addMonths($date, $ds){
        return date("Y-m-d", strtotime("$date +$ds month"));
    }

    static function addMonthsToCurrentDate($ds){
        return self::addMonths(self::getCurrentDate(), $ds);
    }

    static function subtractMonths($date, $ds){
        return date("Y-m-d", strtotime("$date -$ds month"));
    }        
  
    static function subtractMonthsToCurrentDate($ds){
        return self::subtractMonths(self::getCurrentDate(), $ds);
    }
  
    static function getPreviousDay(){
        return self::convert('-',1,'day');
    }
 
    static function getNextDay(){
        return self::convert('+',1,'day');
    }
 
    static function parseDate($date){
        if($date == self::getEmptyDate()){
            return self::getCurrentDate();
        }

        return date("Y-m-d", strtotime($date));
    }

    /**
    *   Go to an specific day (in the same week), no matter what day is.
    *
    */
    static function goToDay($day){
 
        $N = date('N');
 
        if($day > $N){
            $d = ($day - $N); 
 
            return self::convert('+',$d,'day');
 
        } elseif($day == $N){
 
            return self::getCurrentDate();   
 
        } else {
 
            return self::reverse($day);
 
        }
 
    }
 
    /**
    *   Go back to previous weekday
    *
    *   On Saturday, Sunday or Monday returns previous Friday
    *   if not
    *   Returns Previous Day
    */
    static function getPreviousWeekDay(){
 
        $N = date('N');
 
        switch ($N) {
 
            case 1: // Monday
                $d = 3; // Go to Friday
            break;
            case 6: // Saturday
                $d = 1; // Go to Friday
            break;
            case 7: // Sunday
                $d = 2; // Go to Friday
            break;
            default:
                $d = 1; // Go to Previous Day
            break;
        }
 
        return self::convert('-',$d,'day');
 
    }   
 
    /*
    *   Go to next weekday
    *
    *   On Friday, Saturday or Sunday returns next Monday
    *   if not
    *   Returns Next Day
    */
    static function getNextWeekDay(){
 
        $N = date('N');
 
        switch ($N) {
 
            case 5: // Friday
                $d = 3; // Go to Monday
            break;
            case 6: // Saturday
                $d = 2; // Go to Monday
            break;
            case 7: // Sunday
                $d = 1; // Go to Monday
            break;
            default:
                $d = 1; // Go to Next Day
            break;
        }
 
        return self::convert('+',$d,'day');
 
    }
 
    /*
    *   Is Monday?
    */
    static function isMonday(){
 
        $N = date('N');
 
        if($N == 1) return true;
 
        return false;
 
    }
 
    /*
    *   Is Tuesday?
    */
    static function isThuesday(){
 
        $N = date('N');
 
        if($N == 2) return true;
 
        return false;
 
    }
 
    /*
    *   Is Wednesday?
    */
    static function isWednesday(){
 
        $N = date('N');
 
        if($N == 3) return true;
 
        return false;
 
    }
 
    /*
    *   Is Thursday?
    */
    static function isThursday(){
 
        $N = date('N');
 
        if($N == 4) return true;
 
        return false;
 
    }   
 
    /*
    *   Is Friday?
    */
    static function isFriday(){
 
        $N = date('N');
 
        if($N == 5) return true;
 
        return false;
 
    }   
 
    /*
    *   Is Saturday?
    */
    static function isSaturday(){
 
        $N = date('N');
 
        if($N == 6) return true;
 
        return false;
 
    }
 
    /*
    *   Is Sunday?
    */
    static function isSunday(){
 
        $N = date('N');
 
        if($N == 7) return true;
 
        return false;
 
    }
 
    /*
    *   Is Saturday or Sunday?
    */
    static function isWeekend(){
 
        $N = date('N');
 
        if($N == 6 || $N == 7) return true;
 
        return false;
 
    }
 
    /*
    *   This method is useful when it's saturday or sunday, go to previous friday, if not, returns current date
    */
    static function todayOrLastFriday(){
 
        /* when it's saturday or sunday */
        if(self::isWeekend()):
            /* get latest friday */
            $date = self::getPreviousWeekDay();
        else:
            $date = self::getCurrentDate();
        endif;  
 
        return $date;
    }           
 
    /*
    *   This method is useful when it's saturday or sunday, go to next monday, if not, returns current date
    */
    static function todayOrNextMonday(){
 
        /* when it's saturday or sunday */
        if(self::isWeekend()):
            /* get next monday */
            $date = self::getNextWeekDay();
        else:
            $date = self::getCurrentDate();
        endif;  
 
        return $date;
    }   

    static function getLastDaysInMonth($year, $month){
        $date = $year."-".$month."-1";
        return date("t", strtotime($date));
    }

    static function getLastDaysOfCurrentMonth(){
        return self::getLastDaysInMonth(self::getCurrentYear(),self::getCurrentMonth());
    }
 
}

?>