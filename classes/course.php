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
 * Class to check courses
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate;

/**
 * Class to get general infomation on a course
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class course {
    
    protected $record = null;
    
    function __construct($idorshortname) {
        if (is_numeric($idorshortname)) $this->record = database::get("course", $idorshortname, "id");
        else $this->record = database::get("course", $idorshortname, "shortname");
    }
    
    function __get($name) {
        if (isset($this->record->$name)) return $this->record->$name;
        else return null;
    }
    
    function get() {
        return $this->record;
    }
    
    function is() {
        return ! empty($this->record);
    }
    
    function allowed() {
        if ( ! $this->is()) return false;
        if ( ! $this->check_if_in_list("allowed_courses")) return false;
        if ( $this->check_if_in_list("restricted_courses", false)) return false;
        return true;
    }
    
    protected function check_if_in_list($name, $emptyisyes = true) {
        $config = get_config("local_ezxlate", $name);
        $config = str_replace(",", "\n", $config);
        $empty = true;
        foreach(explode("\n", $config) as $course) {
            $course = trim($course);
            if (empty($course)) continue;
            $empty = false;
            if (is_numeric($course) and $course == $this->record->id) return true;
            if (is_string($course) and $course == $this->record->shortname) return true;
        }
        if ($empty) return $emptyisyes;
        else return false;
    }
    
}