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
 * Class to manage entity fields not directly connected to DB
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate;

/**
 * Interface for the entity tree
 * 
 * The entity tree describe an object (a couse, a activityn a question) with all it's dependencies 
 *      (for exemple, choices for a question, questions of a category modules of a section of a course)
 * See the documentation of the API to have precises exemples.
 * 
 * All classes in the tree describing an entity (a question, an activity, etc...) must conform to this interface
 * The tree nodes can be :
 *      - an object of class entity or an inherited class (in classes/entities) : an row of a table (and subtrees)
 *              so, it's decribed by a table name, and an id in this table
 *      - an object of class entities : it's a list of objects entity (with his own tree)
 *              for exemple sections of a course is a node "entities" of the course entity
 *      - an object of class field : it's a field of row of a table 
 *              (a table name, an id, a field name, and the value of field)
 *      - an objet of the class value : it's a node or a subtree directly inserted, 
 *              without direct attachement to the database
 */
interface tree_interface {  
    
    /**
     * Declare this node as a node only for get entry point, not updatable by the set entry point
     * 
     * This is useful to describe the same nodes for get and set, but to protect the node from any update
     * 
     * @return tree_interface the object itself (return $this)
     */
    function only_get();
    
    /**
     * Extract branch from this node
     * 
     * @return array|string|int full tree from this node or value of the node
     */
    function get();
    
    /**
     * Update the tree from this node (including this node)
     * 
     * @param array|string|int full tree or values to update this tree or node
     * @param array|string|int full tree or previous values before updating this tree or node
     * @return array|string|int full tree from this node or value of the node
     */
    function update($data, $previous = null);
    
    /**
     * Get the tree of errors
     * 
     * @return array|string|int full tree of errors encoutered when working with this tree / node
     */
    function get_errors();
    
}