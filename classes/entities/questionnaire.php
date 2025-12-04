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
 * Class to manage the activity "questionnaire"
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate\entities;

/**
 * Class to manage a questionnaire plugin activity
 * 
 * See local_ezxlate\entity and local_ezxlate\tree_interface
 * 
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class questionnaire extends \local_ezxlate\entity {
    
    protected $mainTable = "questionnaire";
    
    protected function define_fields() {
        $this->add_fields("name", "intro");
        $this->fields["name"]->gradebook();
        $this->link_table("questionnaire_survey", [ "id" => "sid" ],
                [ "title", "subtitle", "info", "thank_head", "thank_body", "feedbacknotes"]);
        $this->add_entities_from_table("questions",  "questionnaire_question", "questionnaire_question", [ "surveyid" => "sid"]);
        $this->add_entities_from_table("sections",  "questionnaire_section", "questionnaire_fb_sections", [ "surveyid" => "sid"]);
    }
}