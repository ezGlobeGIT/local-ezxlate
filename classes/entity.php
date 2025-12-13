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
 * Class to manage an entity supported by API
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate;

/**  
 * Class to manage an entity (for exemple an activity, a question), with its dependecies
 * 
 * This class can be used for a generic entity, or overload for specific entities
 *  
 * This class has several categories of public methods 
 *   - methods to describe the tree :
 *        __construct : general definition of the entity, with main table and managed fields in main table
 *        define_fields : method to oveload in inherited classes, to define all fields and nodes
 * 
 *   - methods to build the tree (used mainly in define_fields)
 *        add_direct : to direcly add a node (of any type) with its name and it's value
 *        add_field : to add a node of type field (with the informations to reach it)
 *        add_table : to declare an alias for a table and add somes fields as nodes
 *        link_table : to add several node of type field reached by a JOIN
 *        add_fields : to add several node of type field take in the current line (with alisas on names)
 *        add_entities_from_table : to add an node which is a list of entities (class entities), directly form a table
 *        only_get : to tell this entity is available only to get information and can't be updated
 *        
 * 
 *   - methods to get the tree
 *        get : get the tree
 * 
 *   - methods to updates fields of the tree, and get the status of udating
 *        error : declare an error
 *        update : update texts in the tree
 *        get_errors : get the tree of errors
 *         
 * 
 *   - tools 
 *       get_module_name : get the name of the module (activity plugin) for a cmid
 *       record : to get the current line in the database when needed
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity implements tree_interface {
    
    /** @var string $maintable table name to get mains fields, can be defined in inherited class */
    protected $maintable;       
    
    /** @var bool $onlyget true if this entity can't be updated */
    protected $onlyget = false;
    
    /** @var int $id primary key in the table for the current entity */
    protected $id;
    
    /** @var array $fields direct nodes of this entity's tree, indexes are the names, value is a node (tree_interface) */
    protected $fields = [];
    
    /** @var int|stdClass $record content of the record (row) from database, -1 if not loaded */
    protected $record = -1;
    
    /** @var array $otherTables array of record from other tables */
    protected $otherTables = [];
    
    /** @var array $otherDef definition of other tables, index is the alias, value is an array of tableName, id value */
    protected $otherDef = [];    // [ code => [ table, id ], .... ]
    
    /** @var string $error result code of update ("ok" or error code) */
    protected $error = 'ok';
    
    /** @var array $fieldsError array of errors for fields (nodes) index is node name, value is "ok" or error code */
    protected $fieldsError = [];
    
    
    /* ============================================
     * Management of modules names (static)
     * ============================================ */ 
    
    /** @var array  cache for module name, by module Id */
    static protected $modules = null;  // [ moduleId => moduleName, .... ]
    
    protected static function make_modules() {
        static::$modules = [];
        foreach(database::get_all("modules")  as $record) {
            static::$modules[$record->id] = $record->name;
        }
    }
    
    static function module_name($moduleId) {
        if (is_null(static::$modules)) static::make_modules();
        if (isset(static::$modules[$moduleId])) return static::$modules[$moduleId];
        else return "";
    }
     
    /**
     * Constructor
     * 
     * @param int|stdClass id of row to load, or content of the row
     * @param string $maintable table where the record is loaded, null possible if the attribut mainTable is set
     * @return array $fields list of field to make nodes directly, editable nodes
     * @return array $protectedFields list of field to make read only nodes directly
     */
    function __construct($idOrRecord, $maintable = null, $fields = [], $protectedFields = []) {
        if (is_array($idOrRecord)) $idOrRecord = (object) $idOrRecord;
        if (is_object($idOrRecord)) {
            $this->record = $idOrRecord;
            $this->id = $this->record(database::id_name($this->maintable));
        } else $this->id = $idOrRecord;
        if (!empty($maintable)) $this->maintable = $maintable;
        foreach($protectedFields as $name => $value) $this->add_direct($name, $value)->only_get();
        if (!empty($fields)) $this->add_fields(...$fields);
        $this->define_fields();
    }
    
    /**
     * Other nodes definition (not define in constructor parameters)
     * 
     * Class called by constructor, to overload in inherited classes
     */
    protected function define_fields() {

    }
    
    /**
     * Add a node with it's value
     * 
     * @param string $name name of the node
     * @param local_ezxlate\tree_interface $value value of the node (can be a single value, a field, an entity, a list of entities)
     * @return local_ezxlate\tree_interface the value of the created node
     */
    function add_direct($name, $value) {
        // Add fields from name and value
        if (! $value instanceof field and ! $value instanceof entities)
            $value = new value($value);
        $this->fields[$name] = $value;
        return $this->fields[$name];
    }
    
    /**
     * Add some nodes froms fields (columns) of the current table
     * 
     * @param string ...$names names of the columns from main table to add
     */
    function add_fields(...$names) {
        // Add fields from this table
        // each name is dbname or alias:dbname
        foreach($names as $name) $this->add_field($name);
    }

    /**
     * Add a table with an alias name, and fields of this table as nodes
     * 
     * @param string $name alis name for the table
     * @param string $table name of the table in the database
     * @param stdClass @record record of the line from the table to add
     * @param array $fields array of field names to add directly as nodes (names can be alias:columnName)
     * @return local_ezxlate\field the value of the created node
     */     
    function add_table($name, $table, $record, $fields = []) {
        // Add table's record to define more fields
        $idname = database::id_name($table);
        $this->otherDef[$name] = [ $table, $record->$idname];
        $this->otherTables[$name] = $record;
        foreach($fields as $fieldName) $this->add_field($fieldName, $name);
    }

    /**
     * Add a node (type field) from a colmun of an other table
     * 
     * @param string $name name of the field in the table, or alias:name
     * @param string $aliasTableName alias name of the table (previously declared by add_table() )
     * @return local_ezxlate\field the value of the created node
     */    
    function add_field($name, $aliasTableName = null) {
        // name is dbname or alias:dbname
        if (strpos($name, ":")) {
            $name = explode(":", $name);
            $alias = $name[0];
            $name = $name[1];
        } else $alias = $name;
        if (is_null($aliasTableName)) {
            $field = new field($this->record(), $this->maintable, $this->id, $name);
        } else {
            $field = new field($this->otherTables[$name], $this->otherDef[$aliasTableName][0], $this->otherDef[$aliasTableName][1], $name);
        }
        $this->fields[$alias] = $field;
        return $field;
    }
    
    /**
     * Join a table and add field nodes
     * 
     * @param string $table name of the table to join in database
     * @param string|array $join way to join the table : 
     *      the name of the field of main table that must be equal to the id of joined table
     *      or an array [ target => local ] to explain mainTable.local = joinedTable.target
     * @param array $fields array of field names to add directly as nodes (names can be alias:columnName)
     * @return stdClass record for the raw of new table joined
     */      
    function link_table($table, $join, $fields = []) {
        if (is_array($join)) {
            foreach($join as $targetname=>$thisname) break;
        } else {
            $targetname = $join;
            $thisname = database::id_name($this->maintable);
        }
        $record = database::get($table, $this->record($thisname), $targetname);
        if (empty($record)) return null;
        $idname = database::id_name($table);
        $id = $record->$idname;
        foreach ($fields as $name) {
            if (strpos($name, ":")) {
                $name = explode(":", $name);
                $alias = $name[0];
                $name = $name[1];
            } else $alias = $name;
            $field = new field($record, $table, $id, $name);
            $this->fields[$alias] = $field;
        }
        return $record;
    }

    /**
     * Join a table with a back-link, so add an entities node
     * 
     * @param string $name name of the node
     * @param string|array type $entityname of entity to join
     *          if string : name of an entity (name of a class of namespace local_ezxlate\entities, ie question)
     *          if array : fields name to make enties with the fields as nodes
     * @param string $table name of the table to join in the database
     * @param string|array $join way to join the table : 
     *      the name of the field of joined table that must be equal to the id of main table
     *      or an array [ target => local ] to explain mainTable.local = joinedTable.target
     * @param string $indexon name of the field to take the indexes of entities list (default is the primary key)
     * @return local_ezxlate\entities created node
     */        
    function add_entities_from_table($name, $entityname, $table, $join, $indexon = null) {
        if (is_null($indexon)) $indexon = database::id_name($table);
        if (is_array($join)) {
            foreach($join as $targetname=>$thisname) break;
        } else {
            $targetname = $join;
            $thisname = database::id_name($this->maintable);
        }
        $values = database::get_all($table, $this->record($thisname), $targetname);
        if (is_array($entityname)) array_unshift($entityname, $table);
        $this->fields[$name] = new entities($values, $entityname, $indexon);
        return $this->fields[$name];
    }
    
    /**
     * Extract branch from this node
     * 
     * @return array|string|int full tree from this node or value of the node
     */
    function get() {
        $result = [];
        foreach($this->fields as $name => $obj) {
            $objresult = $obj->get();
            if ( ! empty($objresult) or $objresult === 0 or $objresult === "0" )
                    $result[$name] = $objresult;
        }
        return $result;
    }
    

    /**
     * Give the module name (activity plugin name) for a cmid
     * 
     * @param int $cmid id of the module in course_modules
     * @return string module name
     */
    function get_module_name($cmid) {
        if (is_null(static::$modules)) static::make_modules();
        $cm = database::get("course_modules", $cmid);
        if (empty($cm)) return "";
        if (isset(static::$modules[$cm->module])) return static::$modules[$cm->module];
        else return "";
    }

    /**
     * Get the value of a column, or the whole row, for the current row in main table (request it in database if needed)
     * 
     * @param string|null $name name of the field, null to get the full row
     * @return stdClass|mixed object for the row, or value of the column in this row
     */
    protected function record($name = null) {
        // Get the curent record from database
        if ( ! is_object($this->record) and $this->record == -1) {
            $record = database::get($this->maintable, $this->id);
            if (empty($record)) $record = new \stdClass();
            $this->record = $record;
        } else $record = $this->record;
        if (empty($name)) return $record;
        if (isset($record->$name)) return $record->$name;
        else return null;
    }
    
    /**
     * Declare an error
     * 
     * @param string $error code for the error (default is "error")
     * @return bool always false (usefull to direcly return after an error)
     */
    protected function error($error = 'error') {
        $this->error = $error;
        return false;
    }
    
    /**
     * Update the tree from this node (including this node)
     * 
     * @param array|string|int full tree or values to update this tree or node
     * @param array|string|int full tree or previous values before updating this tree or node
     * @return array|string|int full tree from this node or value of the node
     */
    function update($data, $previous = null) {
        // Update the fields
        if ($this->onlyget) return $this->error("notfound");
        if (!is_object($data) and !is_array($data)) return $this->error("error");
        $ko = false;
        foreach ($data as $index => $value) {
            if (!isset($this->fields[$index])) {
                $this->fieldsError[$index] = "notfound";
                continue;
            }
            if (!empty($previous) and isset($previous->$index)) $thatprevious = $previous->$index;
            else $thatprevious = null;
            if ( ! $this->fields[$index]->update($value, $thatprevious)) {
                $ko = true;
                $this->fieldsError[$index] = "partial";
            }
        }
        return !$ko;
    }
    
    /**
     * Get the tree of errors
     * 
     * @return array|string|int full tree of errors encoutered when working with this tree / node
     */
    function get_errors() {
        // Return all errors in the tree
        if ($this->error != "ok") return $this->error;
        if (empty($this->fieldsError)) return null;
        $result = [];
        foreach ($this->fieldsError as $index => $error) {
            if ($error == "partial") {
                $subresult = $this->fields[$index]->get_errors();
                if (!empty($subresult)) $result[$index] = $subresult;
            } else if ($error != "ok") $result[$index] = $error;
        }
        if (empty($result)) return null;
        else return $result;
    }
    
    /**
     * Declare this node as a node only for get entry point, not updatable by the set entry point
     * 
     * This is useful to describe the same nodes for get and set, but to protect the node from any update
     * 
     * @return tree_interface the object itself (return $this)
     */    
    function only_get() {
        $this->onlyget = true;
        return $this;
    }
}