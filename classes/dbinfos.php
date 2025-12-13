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
 * Class to manage database structure (including extendings fields)
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate;

/**
 * Class to access data structure (using Moodle API)
 * 
 * This class provides only static methods
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dbinfos {
    
    /** 
     * Cache for field sizes
     *      array, index is a table name, associated value is an array of fields of this table :
     *          Indexed by field name, value is false (field doesn't exists,  or maximum length (only text fields)
     *            
     * @var array $tables Cache for field sizes 
     */
    static protected $tables = [];
    
    /** 
     * array to keep initial maximal lenght value, when a field structure is modified
     * Same structure as $table (but for initial values)
     *            
     * @var array $initial Initial fields sizes 
     */    
    static protected $initial = [];    // As tables, but only for modified fields, with the initial length
    
    /** @var bool $canextend tue if we have and are allowed to extends fields when they are too little */
    static protected $canextend = null;
    
    
    static function can_extend() {
        global $CFG, $DB;
        if ( is_null(static::$canextend)) {
            if (get_config("local_ezxlate", "extend") == 0 ) static::$canextend = false;
            else static::$canextend = true;
        }
        return static::$canextend;
    }
    
    static function adjust_field($table, $field, $len) {
        global $DB;
        // Check if it's allowed to extend the size of the field in database structure
        if (!static::can_extend()) return false;
        
        $size = static::get_field_size($table, $field);
        if ($size >= 1333 or empty($size) or $size == -1 or $size >= $len) return false; 
                // We extend don't need or can't extend
        
        // Compute the new size, as a multiple of 256 minus 1, able to contain $len bytes
        $size = 255;
        if ( $len > $size) $size = 511;
        if ( $len > $size) $size = 1023;
        if ( $len > $size) $size = 1333;
        if ($len > $size) return false;     // We can't extend enough
        
                
        // Store size before extending it's it's the first extend in this API call
        if ( ! isset(static::$initial[$table])) static::$initial[$table] = [];
        if ( ! isset(static::$initial[$table][$field])) static::$initial[$table][$field] = $size;
        
        // Change the maximal lengh
        $dbman = $DB->get_manager();
        $dbtable = new \xmldb_table($table);
        $dbfield = new \xmldb_field($field, XMLDB_TYPE_CHAR, "$size");
        $dbman->change_field_precision($dbtable, $dbfield);
        
        // Update current size
        static::get_field_size($table, $field, true);
        
        return true;
    }
    
    /**
     * Get all fields extension we did from the API call (to prodide the list in the answer)
     * 
     * @return array array of array [ "tableName => [ fieldName => "previousSize" => x, "newSize" => y ], ... ], ... ]
     */
    static function get_extensions() {
        $result = [];
        foreach(static::$initial as $table => $fields) {
            $result[$table] = [];
            foreach($fields as $name => $size) {
                $result[$table][$name] = [ "previousSize" => $size,
                            "newSize" =>  static::get_field_size($table, $name)];
            }
        }
        return $result;
    }
    
    /**
     * Get the maximum length of a field
     * 
     * @param string $table Name of the table
     * @param string $field Name of the field
     * @param bool $force true to force the request on data structure, false to use cache of this class
     * @return int size of the field
     */
    static function get_field_size($table, $field, $force = false) {
        global $CFG, $DB;
        
        // Already in local cache ?
        if (isset(static::$tables[$table][$field]) and !$force) 
            return static::$tables[$table][$field];
        
        if ( ! isset(static::$tables[$table])) static::$tables[$table] = [];
        $columns = $DB->get_columns($table, false);
        if (empty($columns[$field])) static::$tables[$table][$field] = false;   // false if $field doesn't exist
        else {
            $col = $columns[$field];
            if (empty($col->max_length)) static::$tables[$table][$field] = false;
            else if ($col->max_length == -1) static::$tables[$table][$field] = 1024 * 1024 * 1024;     // Very long....
            else static::$tables[$table][$field] = $col->max_length;
        }
        
        return static::$tables[$table][$field];
    }

}