<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Class to access to database (using DB API from Moodle)
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate;

/**
 * Class to access data (using Moodle API)
 * 
 * This class provides only static methods
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class database {
        
    protected static $idnames = [];     // Tables where id name is not "id" : [ "tableName" => "id_name", ... ]
    
    static function id_name($table) {
        if (isset(static::$idnames[$table])) return static::$idnames[$table];
        else return "id";
    }
    
    static function get($table, $value, $name = null) {
        if (is_null($name)) $name = static::id_name($table);
        return static::load_one("SELECT * FROM {" . $table . "} WHERE  $name = :$name", [ "$name" => $value]);
    }
    
    static function get_all($table, $value = null, $name = null) {
        if (is_null($name)) $name = static::id_name($table);
        if (is_null($value)) return static::load_multiple("SELECT * FROM {" . $table . "}");
        else return static::load_multiple("SELECT * FROM {" . $table . "} WHERE  $name = :$name", [ "$name" => $value]);
    }
    
    static function load_one($sql, $param = []) {
        global $DB;
        try {
            $result = $DB->get_record_sql($sql, $param);
            if (empty($result)) return null;
            else return $result;
        } catch (\dml_exception $ex) {
            return null;
        } catch (Exception $ex) {
            return null;
        }
    }
    
    static function load_multiple($sql, $param = []) {
        global $DB;
        try {
            $result = $DB->get_records_sql($sql, $param);
            return (array) $result;
        } catch (\dml_exception $ex) {
            return [];
        } catch (Exception $ex) {
            return [];
        }
    }
    
    static function update($table, $id, $fieldname, $newvalue) {
        global $DB;
        $object = new \stdClass;
        $idname = static::id_name($table);
        $object->$idname = $id;
        $object->$fieldname = $newvalue;
        try {
            $result = $DB->update_record($table, $object);
            return $result;
        } catch (\coding_exception $e) {
            return false;
        } catch (\dml_write_exception $e) {
            return false;
        } catch (\dml_exception $ex) {
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }
    
}