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

namespace Roducks\Libs\ORM;

class DB
{

    static $_mysqli = null;
    static $_error = 0;

    // Open a new connection
    static function open(array $conn = [])
    {

        if (empty($conn[1])) {
            throw new \Exception("credentials", 1);
        }

        mysqli_report(MYSQLI_REPORT_STRICT);

        try {
            $mysqli = new \mysqli($conn[0],$conn[1],$conn[2],$conn[3]);

            if ($mysqli->connect_errno) {
                throw new \Exception("{$mysqli->connect_errno} :: {$mysqli->connect_error}", 1);
            }

            $mysqli->set_charset('utf8');

            return $mysqli;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1);
        }

    }

    // Get Singleton :)
    static function get(array $conn = [])
    {

        if(is_null(self::$_mysqli)){
            self::$_mysqli = self::open($conn);
        }

        return self::$_mysqli;
    }

    static function transaction($tx)
    {
        if ($tx === FALSE) {
            self::$_error++;
        }
    }

    static function success()
    {
        return (self::$_error == 0);
    }

    static function reset()
    {
        self::$_error = 0;
    }

    static function createTable(\mysqli $db, $name, $callback)
    {
        $table = new Table($db, $name);
        $callback($table);

        $table->create();
    }

    static function dropTable(\mysqli $db, $name)
    {
        $table = new Table($db, $name);
        $table->drop();
    }

    static function truncateTables(\mysqli $db, array $tables = [])
    {
        $table = new Table($db);
        $table->truncate($tables);
    }

    static function truncateTable(\mysqli $db, $table = "")
    {
        self::truncateTables($db, [$table]);
    }

    static function insertInto(\mysqli $db, $name, $callback)
    {
        $table = new Table($db, $name);
        $query = new Query($db, $name);

        $callback($table);
        $columns = $table->getColumns();

        foreach ($columns as $column) {
            $query->insert($column);
        }

    }

    static function alterTable(\mysqli $db, $name, $callback)
    {
        $table = new Table($db, $name);
        $callback($table);

        $table->alter();
    }

}
