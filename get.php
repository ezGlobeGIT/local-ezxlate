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
/*
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * This program is the entry point for the "get" API (get texts to translate)
 * See documentation for parameters and returned informations
 */

define('AJAX_SCRIPT', true);
define('WS_SERVER', false);

require('../../config.php');
require('locallib.php');

$parameters = local_ezxlate_get_parameters();    // Directly exit if not correct

$api = new \local_ezxlate\api_get($parameters);
local_ezxlate_return( $api->process());