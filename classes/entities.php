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
 * Class to manage entities lists
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate;

/**
 * Class to manage a node that is a list of entities (from the same table, it corresponds to a back link)
 * 
 * see local_ezxlate\entity and local_ezxlate\tree_interface
 * 
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entities implements tree_interface {
    
    protected $list = [];   
    protected $onlyget = false;
    protected $error = "ok";
    protected $entitieserror = [];
    
    /**
     * Constructor
     * 
     * @param stdClass|array list of records of lines to use to make each entity of the list
     * @param string|array type $entityname of entity to join
     *          if string : name of an entity (name of a class of namespace local_ezxlate\entities, ie question)
     *          if array : fields name to make enties with the fields as nodes
     * @param string $indexon name of the field to take the indexes of entities list (default is the primary key)
     */    
    function __construct($values, $entityname, $indexon) {
        // $values : records from the main table
        // $entityName : name of entity (class for entity)
        //          or array with  main table name, then field names for a basic entity based on a few fields of the table
        // $indexOn : field on witch index the entities
        if (is_array($entityname)) {
            $table = array_shift($entityname);
            $fields = $entityname;
            $entity = "\\local_ezxlate\\entity";
        } else {
            $entity = '\\local_ezxlate\\entities\\' . $entityname;
            $table = null;
            $fields = [];
        }
        foreach($values as $record) {
            $this->list[$record->$indexon] = new $entity($record, $table, $fields);
        }
    }

    function only_get() {
        $this->onlyget = true;
        return $this;
    }
    
    function get() {
        // Return value of field if it's allowed for GET API
        if (empty($this->list)) return null;
        $result = [];
        foreach ($this->list as $index => $entity) {
            $value = $entity->get();
            if (!empty($value)) $result[$index] = $value;
        }
        if (empty($result)) return null;
        return $result;
    }

    function update($data, $previous = null) {
        // Update the sub-entities
        if ($this->onlyget) return $this->error("notfound");
        if (!is_object($data) and !is_array($data)) return $this->error("error");
        $ko = false;
        foreach ($data as $index => $entitydata) {
            if (!isset($this->list[$index])) {
                $this->entitieserror[$index] = "notfound";
                $ko = true;
                continue;
            }
            if (isset($previous->$index)) $thatprevious = $previous->$index;
            else $thatprevious = new \stdClass();
            if ( ! $this->list[$index]->update($entitydata, $thatprevious)) {
                $ko = true;
                $this->entitieserror[$index] = "partial";
                $this->error = "partial";
            }
        }
        return !$ko;
    }
    
    
    protected function error($error = "error") {
        $this->error = $error;
        return false;
    }
    
    
    function get_errors() {
        // Return all errors in the tree
        if ($this->error != "ok") return $this->error;
        if (empty($this->entitieserror)) return null;
        $result = [];
        foreach ($this->entitieserror as $index => $error) {
            if ($error == "partial") {
                $subresult = $this->list[$index]->get_errors();
                if (!empty($subresult)) $result[$index] = $subresult;
            } else if ($error != "ok") $result[$index] = $error;
        }
        if (empty($result)) return null;
        else return $result;
    }
    
}