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

namespace rdks\core\libs\ORM;

class DB{

    static $mysqli = null;

    // Open a new connection
    static function open($display_errors, array $conn = []){

        $mysqli = new \mysqli($conn[0],$conn[1],$conn[2],$conn[3]);
        $mysqli->set_charset('utf8');

        if ($mysqli->connect_errno && $display_errors === TRUE) {
            echo $mysqli->connect_errno." :: ".$mysqli->connect_error;
            exit;
        }

        return $mysqli;

    }

    // Get Singleton :)
    static function get($display_errors, array $conn = []){

        if(is_null(self::$mysqli)){
            self::$mysqli = self::open($display_errors, $conn);
        }

        return self::$mysqli;
    }

}

?>