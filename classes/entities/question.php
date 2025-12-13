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
 * Class to manage the entity "question"
 *
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ezxlate\entities;

/**
 * Class to manage a question text tree
 * 
 * See local_ezxlate\entity and local_ezxlate\tree_interface
 * 
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question extends \local_ezxlate\entity {
    
    protected $maintable = "question";
    
    protected function define_fields() {
        if (!is_numeric($this->record("questiontext"))) $this->add_field("questiontext");
        $this->add_field("generalfeedback");
        // Detailled feedback
        $type = $this->record("qtype");
        $fbfields = [ "correctfeedback", "partiallycorrectfeedback", "incorrectfeedback" ];
        if ($type == "multichoice")
            $this->link_table("qtype_multichoice_options", "questionid", $fbfields);
        else if ($type == "match")
            $this->link_table("qtype_match_options", "questionid", $fbfields);
        else if ($type == "ordering")
            $this->link_table("qtype_ordering_options", "questionid", $fbfields);
        else if ($type == "randomsamatch")
            $this->link_table("qtype_randomsamatch_options", "questionid", $fbfields);
        else if ($type == "calculated")
            $this->link_table("question_calculated_options", "question", $fbfields);
        else if ( ! in_array($type, [ "multianswer", "numerical", "truefalse", "essay", "shortanswer"])) {
            $record = $this->link_table("qtype_$type", "questionid", $fbfields);
            if (empty($record)) $record = $this->link_table("question_$type", "question", $fbfields);
        }
        //answers
        $this->add_entities_from_table("answers", [ "answer", "feedback"], "question_answers", "question");
        // subquestions
        if (($type == "match"))
            $this->add_entities_from_table("subquestions", [ "questiontext", "answertext"], "qtype_match_subquestions", "questionid");
        // hints
        $this->add_entities_from_table("hints",  [ "hint"], "question_hints", "questionid");
    }
}