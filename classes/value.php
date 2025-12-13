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
 * Class to manage a direct value (so not attached to the database)
 * 
 * See local_ezxlate\entity and local_ezxlate\tree_interface
 * 
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class value implements tree_interface {
    
    protected $value;
    protected $onlyget = false;
    protected $error = "ok";
    
    function __construct($value) {
         $this->value = $value;
    }
    function only_get() {
        $this->onlyget = true;
        return $this;
    }

    function get() {
        if ($this->value === 0 or $this->value === "0") return 0;
        if (empty($this->value)) return null;
        return $this->value;
    }
    
    function update($newValue, $previous = null) {
        $this->error = "notfound";      // simple values can't be updated
    }
    
    function get_errors() {
        // Return all errors in the tree
        if ($this->error != "ok") return $this->error;
    }
}