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
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * verify / prepare the return json structure, and send the answer to the API client
 *
 * @param mixed $answer answer to send : complete object or array, or string for an error message, or empty (means ok)
 */
function local_ezxlate_return($answer) {
    ob_get_clean();
    header('content-type:application/json');
    if (empty($answer)) $answer = [ "code" => "ko"];
    else if (is_string($answer)) $answer = [ "code" => "ko", "msg" => $answer];
    else if (! is_array($answer) and ! is_object($answer)) $answer = [ "code" => "ko"];
    if (is_array($answer) and empty($answer["code"])) $answer["code"] = "ok";
    if (is_object($answer) and empty($answer->code)) $answer->code = "ok";
    echo json_encode($answer);
}

/**
 * verify / prepare an error message the json answer for an error, and send the answer to the API client
 *
 * @param string $code general code to return (optional, defaults to "error").
 * @param string $message text of the error (optional).
 * 
 */
function local_ezxlate_error($code = 'error', $message = '') {
    ob_get_clean();
    header('content-type:application/json');
    $answer = [ "code" => $code ];
    if ( !empty($message)) $answer["message"] = $message;
    echo json_encode($answer);
}

/**
 * extract the parameters sent by the API client
 * If $alwaysReturn = true and parameters are not readable, directly exit after sending an error answer
 *
 * @param bool $alwaysReturn true to return true/false, false to exit on error  (optional, defaults to false).
 * @return bool true if parameters are decodes, false if not (if $alwaysReturn = true)
 * 
 */
function local_ezxlate_get_parameters($alwaysReturn = false) {
    // Get parameters (json) from php://input
    // Exit if not correct and if not $alwaysReturn
    $request = file_get_contents("php://input");
    $request = json_decode($request);
    if ($request === false and ! $alwaysReturn) {
        local_ezxlate_error( "error", "Parameters format is incorrect");
        exit;
    }
    return $request;
}