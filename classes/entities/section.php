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
 * Class to manage the entity "course"
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate\entities;

/**
 * Class to manage a course section
 * 
 * See local_ezxlate\entity and local_ezxlate\tree_interface
 * 
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends \local_ezxlate\entity {
    
    protected $maintable = "course_sections";
    
    protected function define_fields() {
        $this->add_fields("name", "summary");
        $modules = [];
        foreach(explode(",", $this->record("sequence")) as $cmid) {
            $modulename = $this->get_module_name($cmid);
            if ($modulename == "subsection") continue;
            if (!empty($modulename)) $modules[$cmid] = $modulename;
        }
        $this->add_direct("modules", $modules)->only_get();
    }
}